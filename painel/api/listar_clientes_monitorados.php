<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

try {
    // Buscar clientes monitorados
    $sql = "SELECT cm.cliente_id as id, cm.monitorado, c.nome, c.celular
            FROM clientes_monitoramento cm
            JOIN clientes c ON cm.cliente_id = c.id
            WHERE cm.monitorado = 1
            ORDER BY cm.data_atualizacao DESC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar clientes monitorados: " . $mysqli->error);
    }

    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'celular' => $row['celular'],
            'monitorado' => $row['monitorado']
        ];
    }

    echo json_encode([
        'success' => true,
        'clientes' => $clientes,
        'total' => count($clientes)
    ]);

} catch (Exception $e) {
    error_log("Erro ao listar clientes monitorados: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'clientes' => []
    ]);
}
?> 