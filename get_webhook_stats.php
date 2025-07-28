<?php
/**
 * GET WEBHOOK STATS
 * 
 * Fornece estatísticas do webhook via AJAX
 */

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';
require_once 'painel/db.php';

try {
    // Contar mensagens de hoje
    $sql_messages = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
                     WHERE DATE(data_hora) = CURDATE()";
    $result_messages = $mysqli->query($sql_messages);
    $total_messages = $result_messages ? $result_messages->fetch_assoc()['total'] : 0;

    // Tamanho do arquivo de log
    $log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
    $log_size = file_exists($log_file) ? round(filesize($log_file) / 1024, 2) : 0;

    // Últimas mensagens
    $sql_recent = "SELECT mc.*, c.nome as cliente_nome 
                   FROM mensagens_comunicacao mc
                   LEFT JOIN clientes c ON mc.cliente_id = c.id
                   WHERE DATE(mc.data_hora) = CURDATE()
                   ORDER BY mc.data_hora DESC
                   LIMIT 5";
    
    $result_recent = $mysqli->query($sql_recent);
    $recent_messages = [];
    
    if ($result_recent) {
        while ($row = $result_recent->fetch_assoc()) {
            $recent_messages[] = [
                'hora' => date('H:i:s', strtotime($row['data_hora'])),
                'cliente' => $row['cliente_nome'] ?: 'Sem cliente',
                'mensagem' => substr($row['mensagem'], 0, 50) . '...',
                'direcao' => $row['direcao'],
                'numero' => $row['numero_whatsapp'] ?: 'N/A'
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'totalMessages' => $total_messages,
        'logSize' => $log_size,
        'recentMessages' => $recent_messages,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?> 