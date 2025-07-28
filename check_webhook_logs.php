<?php
/**
 * CHECK WEBHOOK LOGS
 * 
 * Verifica novas mensagens nos logs do webhook via AJAX
 */

header('Content-Type: application/json; charset=utf-8');

// Arquivo de sessão para armazenar o tamanho anterior do log
$session_file = 'temp/webhook_monitor_session.txt';

// Criar diretório temp se não existir
if (!is_dir('temp')) {
    mkdir('temp', 0755, true);
}

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';

try {
    // Ler tamanho anterior do log
    $previous_size = 0;
    if (file_exists($session_file)) {
        $previous_size = (int) file_get_contents($session_file);
    }

    // Verificar se há novas mensagens
    $new_messages = [];
    
    if (file_exists($log_file)) {
        $current_size = filesize($log_file);
        
        if ($current_size > $previous_size) {
            // Ler novas linhas
            $logs = file($log_file);
            $new_lines = array_slice($logs, -5); // Últimas 5 linhas
            
            foreach ($new_lines as $log) {
                $hora = substr($log, 0, 19);
                $conteudo = substr($log, 20);
                
                // Verificar se é uma nova mensagem (não processada antes)
                if (strlen($conteudo) > 0) {
                    $new_messages[] = "Nova mensagem: " . substr($conteudo, 0, 100) . "...";
                }
            }
            
            // Salvar novo tamanho
            file_put_contents($session_file, $current_size);
        }
    }

    // Verificar mensagens no banco de dados (últimas 2 minutos)
    require_once 'config.php';
    require_once 'painel/db.php';

    $sql = "SELECT mc.*, c.nome as cliente_nome 
            FROM mensagens_comunicacao mc
            LEFT JOIN clientes c ON mc.cliente_id = c.id
            WHERE mc.data_hora >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            ORDER BY mc.data_hora DESC
            LIMIT 3";
    
    $result = $mysqli->query($sql);
    $db_messages = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $hora = date('H:i:s', strtotime($row['data_hora']));
            $cliente = $row['cliente_nome'] ?: 'Sem cliente';
            $direcao = $row['direcao'] === 'recebido' ? '📥' : '📤';
            $mensagem = substr($row['mensagem'], 0, 50) . '...';
            
            $db_messages[] = "$direcao [$hora] $cliente: $mensagem";
        }
    }

    echo json_encode([
        'success' => true,
        'newMessages' => $new_messages,
        'dbMessages' => $db_messages,
        'logSize' => file_exists($log_file) ? filesize($log_file) : 0,
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