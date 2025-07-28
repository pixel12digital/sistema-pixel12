<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    $cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$cliente_id) {
        throw new Exception('ID do cliente não informado');
    }
    
    // Buscar dados do cliente
    $sql = "SELECT 
                id,
                nome,
                celular,
                contact_name,
                email,
                created_at
            FROM clientes 
            WHERE id = $cliente_id 
            LIMIT 1";
    
    $result = $mysqli->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Cliente não encontrado');
    }
    
    $cliente = $result->fetch_assoc();
    
    // Buscar dados de monitoramento
    $sql_monitoramento = "SELECT monitorado FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1";
    $result_monitoramento = $mysqli->query($sql_monitoramento);
    $monitoramento = $result_monitoramento->fetch_assoc();
    
    $cliente['monitorado'] = $monitoramento ? boolval($monitoramento['monitorado']) : false;
    
    echo json_encode([
        'success' => true,
        'cliente' => $cliente
    ]);

} catch (Exception $e) {
    error_log("Erro ao buscar dados do cliente: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 