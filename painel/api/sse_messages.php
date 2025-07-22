<?php
/**
 * Server-Sent Events para mensagens em tempo real
 */
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';
require_once '../db.php';

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
$last_check = isset($_GET['last_check']) ? intval($_GET['last_check']) : time();

if (!$cliente_id) {
    echo "data: " . json_encode(['error' => 'Cliente ID requerido']) . "\n\n";
    exit;
}

// Função para enviar eventos
function sendEvent($data, $event = 'message') {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

// Loop para verificar novas mensagens
$max_duration = 30; // 30 segundos máximo
$start_time = time();

while ((time() - $start_time) < $max_duration) {
    // Verificar novas mensagens
    $sql = "SELECT m.*, c.nome_exibicao as canal_nome 
            FROM mensagens_comunicacao m
            LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
            WHERE m.cliente_id = ? AND UNIX_TIMESTAMP(m.data_hora) > ?
            ORDER BY m.data_hora DESC
            LIMIT 5";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $cliente_id, $last_check);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $novas_mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $novas_mensagens[] = $row;
    }
    $stmt->close();
    
    if (!empty($novas_mensagens)) {
        sendEvent([
            'type' => 'new_messages',
            'cliente_id' => $cliente_id,
            'messages' => $novas_mensagens,
            'count' => count($novas_mensagens)
        ], 'update');
        
        $last_check = time();
    }
    
    // Verificar se conexão ainda está ativa
    if (connection_aborted()) {
        break;
    }
    
    // Aguardar 2 segundos antes da próxima verificação
    sleep(2);
    
    // Enviar heartbeat
    sendEvent(['heartbeat' => time()], 'heartbeat');
}

// Finalizar conexão
sendEvent(['status' => 'closed'], 'close');
?> 