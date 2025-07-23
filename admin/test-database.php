<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Incluir configurações
    require_once '../config.php';
    require_once '../db.php';
    
    // Testar conexão básica
    if (!$mysqli || $mysqli->connect_error) {
        throw new Exception('Erro de conexão: ' . ($mysqli->connect_error ?? 'Conexão não estabelecida'));
    }
    
    // Testar uma consulta simples
    $result = $mysqli->query("SELECT 1 as test");
    if (!$result) {
        throw new Exception('Erro na consulta: ' . $mysqli->error);
    }
    
    // Verificar tabelas principais
    $tables_required = [
        'clientes',
        'mensagens_comunicacao',
        'canais_comunicacao',
        'cobrancas'
    ];
    
    $tables_status = [];
    foreach ($tables_required as $table) {
        $check = $mysqli->query("SHOW TABLES LIKE '$table'");
        $exists = $check && $check->num_rows > 0;
        $tables_status[$table] = $exists;
        
        if ($exists) {
            // Contar registros na tabela
            $count_result = $mysqli->query("SELECT COUNT(*) as total FROM $table");
            if ($count_result) {
                $count = $count_result->fetch_assoc()['total'];
                $tables_status[$table . '_count'] = $count;
            }
        }
    }
    
    // Verificar mensagens recentes
    $recent_messages = 0;
    $messages_result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    if ($messages_result) {
        $recent_messages = $messages_result->fetch_assoc()['total'];
    }
    
    // Verificar clientes
    $total_clients = 0;
    $clients_result = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
    if ($clients_result) {
        $total_clients = $clients_result->fetch_assoc()['total'];
    }
    
    // Informações do servidor
    $server_info = [
        'version' => $mysqli->server_info,
        'host' => $mysqli->host_info,
        'protocol' => $mysqli->protocol_version
    ];
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Conexão com banco de dados estabelecida com sucesso',
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => $server_info,
        'tables_status' => $tables_status,
        'statistics' => [
            'total_clients' => $total_clients,
            'recent_messages_24h' => $recent_messages,
            'database_size' => getDatabaseSize($mysqli)
        ]
    ]);
    
} catch (Exception $e) {
    // Resposta de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'debug' => [
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ]);
}

/**
 * Obter tamanho aproximado do banco de dados
 */
function getDatabaseSize($mysqli) {
    try {
        $result = $mysqli->query("
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
        ");
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['size_mb'] . ' MB';
        }
        
        return 'N/A';
    } catch (Exception $e) {
        return 'N/A';
    }
}
?> 