<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

// Buscar mensagens enviadas nas últimas 24 horas que ainda não foram entregues
$sql = "SELECT m.*, c.porta, c.nome_exibicao as canal_nome 
        FROM mensagens_comunicacao m 
        JOIN canais_comunicacao c ON m.canal_id = c.id 
        WHERE m.direcao = 'enviado' 
        AND m.status IN ('enviado', 'pendente') 
        AND m.whatsapp_message_id IS NOT NULL 
        AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY m.data_hora DESC";

$result = $mysqli->query($sql);
$mensagens_para_verificar = [];

while ($row = $result->fetch_assoc()) {
    $mensagens_para_verificar[] = $row;
}

$resultados = [];

foreach ($mensagens_para_verificar as $mensagem) {
    $message_id = $mensagem['whatsapp_message_id'];
    $porta = $mensagem['porta'];
    
    // Verificar status no robô
    $ch = curl_init("http://localhost:$porta/message-status/$message_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $status_data = json_decode($response, true);
        
        if ($status_data && $status_data['success']) {
            $whatsapp_status = $status_data['status'];
            $novo_status = 'enviado';
            
            // Mapear status do WhatsApp para nosso sistema
            switch ($whatsapp_status) {
                case 'DELIVERED':
                    $novo_status = 'entregue';
                    break;
                case 'READ':
                    $novo_status = 'lido';
                    break;
                case 'SENT':
                    $novo_status = 'enviado';
                    break;
                case 'PENDING':
                    $novo_status = 'pendente';
                    break;
                default:
                    $novo_status = 'enviado';
            }
            
            // Atualizar status no banco
            $mensagem_id = $mensagem['id'];
            $mysqli->query("UPDATE mensagens_comunicacao SET status = '$novo_status' WHERE id = $mensagem_id");
            
            $resultados[] = [
                'id' => $mensagem_id,
                'message_id' => $message_id,
                'status_anterior' => $mensagem['status'],
                'status_novo' => $novo_status,
                'whatsapp_status' => $whatsapp_status,
                'canal' => $mensagem['canal_nome']
            ];
            
            // Se a mensagem não foi entregue após 1 hora, tentar reenviar
            if ($whatsapp_status === 'SENT' && 
                strtotime($mensagem['data_hora']) < strtotime('-1 hour')) {
                
                // Buscar dados do cliente para reenvio
                $cliente_sql = "SELECT celular FROM clientes WHERE id = " . $mensagem['cliente_id'];
                $cliente_result = $mysqli->query($cliente_sql);
                if ($cliente_result && $cliente_row = $cliente_result->fetch_assoc()) {
                    
                    // Preparar dados para retry
                    $payload = [
                        'to' => $cliente_row['celular'],
                        'message' => $mensagem['mensagem'],
                        'originalMessageId' => $message_id
                    ];
                    
                    // Tentar reenviar
                    $ch_retry = curl_init("http://localhost:$porta/retry");
                    curl_setopt($ch_retry, CURLOPT_POST, true);
                    curl_setopt($ch_retry, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch_retry, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch_retry, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_retry, CURLOPT_TIMEOUT, 10);
                    
                    $retry_response = curl_exec($ch_retry);
                    curl_close($ch_retry);
                    
                    if ($retry_response) {
                        $retry_data = json_decode($retry_response, true);
                        if ($retry_data && $retry_data['success']) {
                            // Registrar nova tentativa
                            $nova_mensagem = "Retry: " . $mensagem['mensagem'];
                            $data_hora = date('Y-m-d H:i:s');
                            $mysqli->query("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, whatsapp_message_id) VALUES ({$mensagem['canal_id']}, {$mensagem['cliente_id']}, '$nova_mensagem', 'texto', '$data_hora', 'enviado', 'reenviado', '{$retry_data['messageId']}')");
                            
                            $resultados[] = [
                                'id' => $mensagem_id,
                                'action' => 'retry_enviado',
                                'new_message_id' => $retry_data['messageId']
                            ];
                        }
                    }
                }
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'mensagens_verificadas' => count($mensagens_para_verificar),
    'resultados' => $resultados,
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 