<?php
/**
 * 🔍 VERIFICA NOTIFICAÇÕES PUSH PENDENTES
 * Endpoint para o frontend verificar se há novas mensagens
 * ⚡ OTIMIZADO para economizar consultas ao banco
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
$last_check = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'message' => 'Cliente ID inválido']);
    exit;
}

try {
    // ⚡ OTIMIZAÇÃO: Cache em arquivo para reduzir consultas ao banco
    $cache_file = sys_get_temp_dir() . "/push_cache_{$cliente_id}.json";
    $cache_timeout = 10; // 10 segundos de cache
    
    // Verificar cache primeiro
    if (file_exists($cache_file)) {
        $cache_data = json_decode(file_get_contents($cache_file), true);
        if ($cache_data && (time() - $cache_data['timestamp']) < $cache_timeout) {
            // Usar dados do cache se ainda válidos
            echo json_encode([
                'success' => true,
                'has_notifications' => $cache_data['has_notifications'],
                'notifications' => $cache_data['notifications'],
                'count' => $cache_data['count'],
                'timestamp' => $cache_data['timestamp'],
                'cached' => true
            ]);
            exit;
        }
    }
    
    // ⚡ OTIMIZAÇÃO: Consulta mais eficiente - apenas verificar se há notificações
    $sql = "SELECT COUNT(*) as total, MAX(UNIX_TIMESTAMP(data_hora)) as latest_timestamp 
            FROM notificacoes_push 
            WHERE cliente_id = ? 
            AND status = 'pendente'
            AND UNIX_TIMESTAMP(data_hora) > ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $cliente_id, $last_check);
    $stmt->execute();
    $result = $stmt->get_result();
    $check_data = $result->fetch_assoc();
    $stmt->close();
    
    $has_notifications = $check_data['total'] > 0;
    $latest_timestamp = $check_data['latest_timestamp'] ?: time();
    
    // Só buscar detalhes se há notificações
    $notificacoes = [];
    if ($has_notifications) {
        // Buscar detalhes das notificações
        $sql = "SELECT * FROM notificacoes_push 
                WHERE cliente_id = ? 
                AND status = 'pendente'
                AND UNIX_TIMESTAMP(data_hora) > ?
                ORDER BY data_hora DESC
                LIMIT 10"; // Limitar a 10 notificações
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $cliente_id, $last_check);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $notificacoes[] = [
                'id' => $row['id'],
                'numero' => $row['numero'],
                'mensagem' => $row['mensagem'],
                'mensagem_id' => $row['mensagem_id'],
                'data_hora' => $row['data_hora'],
                'timestamp' => strtotime($row['data_hora'])
            ];
        }
        $stmt->close();
        
        // Marcar notificações como enviadas
        if (!empty($notificacoes)) {
            $ids = array_column($notificacoes, 'id');
            $ids_str = implode(',', $ids);
            $mysqli->query("UPDATE notificacoes_push SET status = 'enviada' WHERE id IN ($ids_str)");
        }
    }
    
    $response_data = [
        'success' => true,
        'has_notifications' => $has_notifications,
        'notifications' => $notificacoes,
        'count' => count($notificacoes),
        'timestamp' => $latest_timestamp
    ];
    
    // ⚡ OTIMIZAÇÃO: Salvar no cache
    file_put_contents($cache_file, json_encode([
        'timestamp' => time(),
        'has_notifications' => $has_notifications,
        'notifications' => $notificacoes,
        'count' => count($notificacoes)
    ]));
    
    echo json_encode($response_data);
    
} catch (Exception $e) {
    error_log("[CHECK PUSH] ❌ Erro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
?> 