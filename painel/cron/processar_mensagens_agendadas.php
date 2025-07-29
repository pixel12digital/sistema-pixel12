<?php
/**
 * Processador de Mensagens Agendadas
 * Executar via cron: 0,5,10,15,20,25,30,35,40,45,50,55 * * * * php /caminho/para/painel/cron/processar_mensagens_agendadas.php
 */

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Função para formatar número WhatsApp (garante sempre código +55 do Brasil)
function ajustarNumeroWhatsapp($numero) {
    // Remover todos os caracteres não numéricos
    $numero = preg_replace('/\D/', '', $numero);
    
    // Se já tem código do país (55), remover para processar
    if (strpos($numero, '55') === 0) {
        $numero = substr($numero, 2);
    }
    
    // Para números muito longos, pegar apenas os últimos 11 dígitos (DDD + telefone)
    if (strlen($numero) > 11) {
        $numero = substr($numero, -11);
    }
    
    // Verificar se tem pelo menos DDD (2 dígitos) + número (mínimo 7 dígitos)
    if (strlen($numero) < 9) {
        return null; // Número muito curto
    }
    
    // Extrair DDD e número
    $ddd = substr($numero, 0, 2);
    $telefone = substr($numero, 2);
    
    // Verificar se o DDD é válido (deve ser um DDD brasileiro válido)
    $ddds_validos = ['11','12','13','14','15','16','17','18','19','21','22','24','27','28','31','32','33','34','35','37','38','41','42','43','44','45','46','47','48','49','51','53','54','55','61','62','63','64','65','66','67','68','69','71','73','74','75','77','79','81','82','83','84','85','86','87','88','89','91','92','93','94','95','96','97','98','99'];
    
    if (!in_array($ddd, $ddds_validos)) {
        return null; // DDD inválido
    }
    
    // Regras de formatação:
    // 1. Se tem 9 dígitos e começa com 9, manter como está (celular com 9)
    if (strlen($telefone) === 9 && substr($telefone, 0, 1) === '9') {
        // Manter como está - é um celular válido
    }
    // 2. Se tem 8 dígitos, adicionar 9 no início (celular sem 9)
    elseif (strlen($telefone) === 8) {
        $telefone = '9' . $telefone;
    }
    // 3. Se tem 7 dígitos, adicionar 9 no início (telefone fixo convertido para celular)
    elseif (strlen($telefone) === 7) {
        $telefone = '9' . $telefone;
    }
    
    // Verificar se o número final é válido (deve ter 8 ou 9 dígitos)
    if (strlen($telefone) < 8 || strlen($telefone) > 9) {
        return null; // Número inválido
    }
    
    // GARANTIR SEMPRE o código +55 do Brasil + DDD + número
    return '55' . $ddd . $telefone;
}

// Log do início da execução
$log_data = date('Y-m-d H:i:s') . " - Iniciando processamento de mensagens agendadas\n";
file_put_contents('../logs/processamento_agendadas.log', $log_data, FILE_APPEND);

try {
    // Buscar mensagens agendadas para envio
    $agora = date('Y-m-d H:i:s');
    $sql = "SELECT ma.id, ma.cliente_id, ma.mensagem, ma.tipo, ma.prioridade, ma.data_agendada,
                   c.nome as cliente_nome, c.celular as cliente_celular
            FROM mensagens_agendadas ma
            JOIN clientes c ON ma.cliente_id = c.id
            WHERE ma.status = 'agendada' 
            AND ma.data_agendada <= '$agora'
            AND c.celular IS NOT NULL 
            AND c.celular != ''
            ORDER BY ma.prioridade DESC, ma.data_agendada ASC
            LIMIT 5"; // Processar máximo 5 por vez
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar mensagens agendadas: " . $mysqli->error);
    }
    
    $mensagens_processadas = 0;
    $mensagens_enviadas = 0;
    $erros = 0;
    
    while ($mensagem = $result->fetch_assoc()) {
        $mensagens_processadas++;
        
        try {
            // Verificar se cliente ainda está sendo monitorado
            $monitorado = $mysqli->query("SELECT monitorado FROM clientes_monitoramento WHERE cliente_id = {$mensagem['cliente_id']} LIMIT 1")->fetch_assoc();
            
            if (!$monitorado || $monitorado['monitorado'] != 1) {
                // Cliente não está mais sendo monitorado, cancelar mensagem
                $mysqli->query("UPDATE mensagens_agendadas SET status = 'cancelada', data_atualizacao = NOW() WHERE id = {$mensagem['id']}");
                
                $log_cancel = date('Y-m-d H:i:s') . " - Mensagem {$mensagem['id']} cancelada: cliente {$mensagem['cliente_nome']} não está mais sendo monitorado\n";
                file_put_contents('../logs/processamento_agendadas.log', $log_cancel, FILE_APPEND);
                continue;
            }
            
            // Verificar status real no Asaas antes de enviar
            $status_response = verificarStatusAsaas($mensagem['cliente_id']);
            
            if ($status_response['success'] && $status_response['total_atualizadas'] > 0) {
                // Status foi atualizado, verificar se ainda precisa enviar mensagem
                if (count($status_response['cobrancas_vencidas']) == 0) {
                    // Não há mais cobranças vencidas, cancelar mensagem
                    $mysqli->query("UPDATE mensagens_agendadas SET status = 'cancelada', data_atualizacao = NOW() WHERE id = {$mensagem['id']}");
                    
                    $log_cancel = date('Y-m-d H:i:s') . " - Mensagem {$mensagem['id']} cancelada: cliente {$mensagem['cliente_nome']} não possui mais cobranças vencidas\n";
                    file_put_contents('../logs/processamento_agendadas.log', $log_cancel, FILE_APPEND);
                    continue;
                }
            }
            
            // Enviar mensagem via VPS
            $numero_formatado = ajustarNumeroWhatsapp($mensagem['cliente_celular']);
            if (!$numero_formatado) {
                throw new Exception("Número do cliente inválido para envio no WhatsApp");
            }
            
            $payload = json_encode([
                'sessionName' => 'default',
                'number' => $numero_formatado,
                'message' => $mensagem['mensagem']
            ]);
            
            $ch = curl_init("http://212.85.11.238:3000/send/text");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception("Erro de conexão: " . $error);
            }
            
            $response_data = json_decode($response, true);
            
            if ($http_code !== 200 || !$response_data || !isset($response_data['success'])) {
                throw new Exception("Erro na resposta da VPS: " . $response);
            }
            
            if (!$response_data['success']) {
                throw new Exception("Falha no envio: " . ($response_data['error'] ?? 'Erro desconhecido'));
            }
            
            // Marcar mensagem como enviada
            $mysqli->query("UPDATE mensagens_agendadas SET status = 'enviada', data_atualizacao = NOW() WHERE id = {$mensagem['id']}");
            
            // Salvar no histórico de mensagens
            $mensagem_escaped = $mysqli->real_escape_string($mensagem['mensagem']);
            $tipo_escaped = $mysqli->real_escape_string($mensagem['tipo']);
            
            $sql_historico = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                             VALUES (1, {$mensagem['cliente_id']}, '$mensagem_escaped', '$tipo_escaped', NOW(), 'enviado', 'enviado')";
            $mysqli->query($sql_historico);
            
            $mensagens_enviadas++;
            
            // Log do envio
            $log_envio = date('Y-m-d H:i:s') . " - Mensagem agendada {$mensagem['id']} enviada para {$mensagem['cliente_nome']} (ID: {$mensagem['cliente_id']})\n";
            file_put_contents('../logs/processamento_agendadas.log', $log_envio, FILE_APPEND);
            
            // Aguardar 3 segundos entre envios
            sleep(3);
            
        } catch (Exception $e) {
            $erros++;
            
            // Marcar mensagem como erro
            $erro_escaped = $mysqli->real_escape_string($e->getMessage());
            $mysqli->query("UPDATE mensagens_agendadas SET status = 'erro', observacao = '$erro_escaped', data_atualizacao = NOW() WHERE id = {$mensagem['id']}");
            
            $log_erro = date('Y-m-d H:i:s') . " - Erro ao processar mensagem {$mensagem['id']}: " . $e->getMessage() . "\n";
            file_put_contents('../logs/processamento_agendadas.log', $log_erro, FILE_APPEND);
        }
    }
    
    // Log do resumo
    $log_resumo = date('Y-m-d H:i:s') . " - Processamento concluído: $mensagens_processadas processadas, $mensagens_enviadas enviadas, $erros erros\n";
    file_put_contents('../logs/processamento_agendadas.log', $log_resumo, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'mensagens_processadas' => $mensagens_processadas,
        'mensagens_enviadas' => $mensagens_enviadas,
        'erros' => $erros
    ]);

} catch (Exception $e) {
    $log_erro = date('Y-m-d H:i:s') . " - Erro geral no processamento: " . $e->getMessage() . "\n";
    file_put_contents('../logs/processamento_agendadas.log', $log_erro, FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Verifica status real no Asaas
 */
function verificarStatusAsaas($cliente_id) {
    global $mysqli;
    
    // Buscar chave da API do Asaas
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    
    if (!$config || !$config['valor']) {
        return ['success' => false, 'error' => 'Chave da API não configurada'];
    }
    
    $api_key = $config['valor'];
    
    // Buscar cobranças do cliente
    $sql = "SELECT cob.id, cob.asaas_id, cob.status, cob.valor, cob.vencimento, cob.url_fatura
            FROM cobrancas cob
            WHERE cob.cliente_id = $cliente_id
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.asaas_id IS NOT NULL";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        return ['success' => false, 'error' => 'Erro ao buscar cobranças'];
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
        curl_close($ch);
        
        if ($http_code === 200) {
            $asaas_data = json_decode($response, true);
            
            if ($asaas_data && isset($asaas_data['status'])) {
                $status_real = $asaas_data['status'];
                $status_local = $cobranca['status'];
                
                // Se status divergiu, atualizar no banco
                if ($status_real !== $status_local) {
                    $status_escaped = $mysqli->real_escape_string($status_real);
                    $mysqli->query("UPDATE cobrancas SET status = '$status_escaped' WHERE id = {$cobranca['id']}");
                    
                    $cobrancas_atualizadas[] = [
                        'id' => $cobranca['id'],
                        'status_anterior' => $status_local,
                        'status_atual' => $status_real
                    ];
                }
                
                // Se ainda está vencida após verificação, incluir na lista
                if (in_array($status_real, ['PENDING', 'OVERDUE']) && strtotime($cobranca['vencimento']) < time()) {
                    $cobrancas_vencidas[] = $cobranca;
                }
            }
        }
    }
    
    return [
        'success' => true,
        'cobrancas_atualizadas' => $cobrancas_atualizadas,
        'cobrancas_vencidas' => $cobrancas_vencidas,
        'total_atualizadas' => count($cobrancas_atualizadas),
        'total_vencidas' => count($cobrancas_vencidas)
    ];
}
?> 