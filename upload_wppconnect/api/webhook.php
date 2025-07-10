<?php
/**
 * Webhook para receber mensagens do WPPConnect
 * Simples e funcional
 */

require_once '../painel/config.php';
require_once '../painel/db.php';

// Log da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Salvar log
$log_file = '../logs/webhook_' . date('Y-m-d') . '.log';
$log_data = date('Y-m-d H:i:s') . ' - ' . $input . "\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Verificar se é uma mensagem recebida
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'];
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    // Buscar cliente pelo número
    $numero_limpo = preg_replace('/\D/', '', $numero);
    $sql = "SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
    $result = $mysqli->query($sql);
    
    $cliente_id = null;
    if ($result && $result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        $cliente_id = $cliente['id'];
    }
    
    // Salvar mensagem recebida
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    
    $sql = "INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES (" . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
    
    $mysqli->query($sql);
    
    // Resposta automática simples
    if ($texto && $cliente_id) {
        $resposta = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato.";
        
        // Enviar resposta via WPPConnect
        $wppconnect_url = 'http://localhost:8080/api/sendText/default';
        $data_envio = [
            'number' => $numero,
            'text' => $resposta
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $wppconnect_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        
        // Salvar resposta enviada
        $resposta_escaped = $mysqli->real_escape_string($resposta);
        $sql = "INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, data_hora, direcao, status) 
                VALUES ($cliente_id, '$resposta_escaped', 'text', '$data_hora', 'enviado', 'entregue')";
        $mysqli->query($sql);
    }
}

// Responder OK
http_response_code(200);
echo json_encode(['status' => 'ok']);
?> 