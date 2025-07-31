<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$status = [];

// Buscar canais do banco
$canais = [];
$resCanais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id");
if ($resCanais) {
    while ($canal = $resCanais->fetch_assoc()) {
        $canais[] = $canal;
    }
}
    
foreach ($canais as $canal) {
    $porta = intval($canal['porta']);
    $canal_id = intval($canal['id']);
    
    $conectado = false;
    $lastSession = null;
    
    // CORREÇÃO: Verificar cada canal individualmente pela sua porta
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php?action=status&porta=$porta");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Status-Canais-API/1.0'
    ]);
        
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    file_put_contents(__DIR__.'/debug_status_update.log', date('Y-m-d H:i:s')." - Canal $canal_id (Porta $porta) - Resposta ajax_whatsapp: $result\n", FILE_APPEND);
    curl_close($ch);
    
    if ($result && $httpCode === 200) {
        $json = json_decode($result, true);
        
        // CORREÇÃO: Usar a mesma lógica do frontend
        if ($json) {
            // Verificar se está conectado usando múltiplos campos
            $isConnected = false;
            
            // 1. Verificar campo ready
            if (isset($json['ready']) && $json['ready'] === true) {
                $isConnected = true;
            }
            
            // 2. Verificar status direto
            if (isset($json['status']) && in_array($json['status'], ['connected', 'already_connected', 'authenticated', 'ready'])) {
                $isConnected = true;
            }
            
            // 3. Verificar raw_response_preview (mesma lógica do frontend)
            if (isset($json['debug']['raw_response_preview'])) {
                try {
                    $parsedResponse = json_decode($json['debug']['raw_response_preview'], true);
                    if ($parsedResponse) {
                        $realStatus = $parsedResponse['status']['status'] ?? $parsedResponse['status'] ?? null;
                        if (in_array($realStatus, ['connected', 'already_connected', 'authenticated', 'ready'])) {
                            $isConnected = true;
                        }
                    }
                } catch (Exception $e) {
                    // Ignorar erro de parse
                }
            }
            
            $conectado = $isConnected;
            $lastSession = $json['lastSession'] ?? null;
        }
    }
    
    // Atualizar status no banco apenas se mudou
    $novo_status = $conectado ? 'conectado' : 'pendente';
    $status_atual = $canal['status'] ?? 'pendente';
    
    // Só atualizar se o status realmente mudou
    if ($novo_status !== $status_atual) {
        $mysqli->query("UPDATE canais_comunicacao SET status = '" . $mysqli->real_escape_string($novo_status) . "' WHERE id = $canal_id");
        file_put_contents(__DIR__.'/debug_status_update.log', date('Y-m-d H:i:s')." - Canal $canal_id (Porta $porta) atualizado de '$status_atual' para '$novo_status' | Linhas afetadas: ".$mysqli->affected_rows."\n", FILE_APPEND);
    } else {
        file_put_contents(__DIR__.'/debug_status_update.log', date('Y-m-d H:i:s')." - Canal $canal_id (Porta $porta) mantém status '$status_atual' (sem mudança)\n", FILE_APPEND);
    }
    
    // Ajuste de fuso horário para lastSession
    if ($lastSession) {
        try {
            $dt = new DateTime($lastSession, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            $lastSession = $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // Se der erro, mantém original
        }
    }
    
    $status[] = [
        'id' => $canal['id'],
        'nome' => $canal['nome_exibicao'],
        'porta' => $porta,
        'conectado' => $conectado,
        'lastSession' => $lastSession,
        'tipo' => $canal['tipo'] ?? null,
        'identificador' => $canal['identificador'] ?? null
    ];
}

echo json_encode($status);
?> 