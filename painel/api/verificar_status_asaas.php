<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cliente_id'])) {
    echo json_encode(['success' => false, 'error' => 'Cliente ID não informado']);
    exit;
}

$cliente_id = intval($input['cliente_id']);

try {
    // Buscar chave da API do Asaas
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    
    if (!$config || !$config['valor']) {
        throw new Exception("Chave da API do Asaas não configurada");
    }
    
    $api_key = $config['valor'];
    
    // Buscar cobranças do cliente no banco local
    $sql = "SELECT cob.id, cob.asaas_id, cob.status, cob.valor, cob.vencimento, cob.url_fatura
            FROM cobrancas cob
            WHERE cob.cliente_id = $cliente_id
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.asaas_id IS NOT NULL";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar cobranças: " . $mysqli->error);
    }
    
    $cobrancas_atualizadas = [];
    $cobrancas_vencidas = [];
    
    while ($cobranca = $result->fetch_assoc()) {
        // Verificar status real no Asaas
        $ch = curl_init("https://www.asaas.com/api/v3/payments/{$cobranca['asaas_id']}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'access_token: ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Erro ao verificar cobrança {$cobranca['asaas_id']}: " . $error);
            continue;
        }
        
        if ($http_code !== 200) {
            error_log("Erro HTTP $http_code ao verificar cobrança {$cobranca['asaas_id']}");
            continue;
        }
        
        $asaas_data = json_decode($response, true);
        
        if (!$asaas_data || !isset($asaas_data['status'])) {
            error_log("Resposta inválida do Asaas para cobrança {$cobranca['asaas_id']}");
            continue;
        }
        
        $status_real = $asaas_data['status'];
        $status_local = $cobranca['status'];
        
        // Se status divergiu, atualizar no banco
        if ($status_real !== $status_local) {
            $status_escaped = $mysqli->real_escape_string($status_real);
            $update_sql = "UPDATE cobrancas SET status = '$status_escaped' WHERE id = {$cobranca['id']}";
            
            if ($mysqli->query($update_sql)) {
                $cobrancas_atualizadas[] = [
                    'id' => $cobranca['id'],
                    'status_anterior' => $status_local,
                    'status_atual' => $status_real
                ];
                
                // Log da atualização
                $log_data = date('Y-m-d H:i:s') . " - Cobrança {$cobranca['id']} atualizada: $status_local → $status_real\n";
                file_put_contents('../logs/status_asaas.log', $log_data, FILE_APPEND);
            }
        }
        
        // Se ainda está vencida após verificação, incluir na lista
        if (in_array($status_real, ['PENDING', 'OVERDUE']) && strtotime($cobranca['vencimento']) < time()) {
            $cobrancas_vencidas[] = [
                'id' => $cobranca['id'],
                'valor' => $cobranca['valor'],
                'vencimento' => $cobranca['vencimento'],
                'url_fatura' => $cobranca['url_fatura'],
                'status' => $status_real
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'cobrancas_atualizadas' => $cobrancas_atualizadas,
        'cobrancas_vencidas' => $cobrancas_vencidas,
        'total_atualizadas' => count($cobrancas_atualizadas),
        'total_vencidas' => count($cobrancas_vencidas)
    ]);

} catch (Exception $e) {
    error_log("Erro ao verificar status Asaas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 