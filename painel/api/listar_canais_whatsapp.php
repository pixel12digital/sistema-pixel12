<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once '../db.php';

try {
    // Buscar todos os canais WhatsApp (conectados e pendentes)
    $sql = "SELECT id, nome_exibicao as nome, identificador as numero, status, porta 
            FROM canais_comunicacao 
            WHERE tipo = 'whatsapp' AND status <> 'excluido'
            ORDER BY 
                CASE WHEN status = 'conectado' THEN 1 ELSE 2 END,
                nome_exibicao, id";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception('Erro ao consultar canais: ' . $mysqli->error);
    }
    
    $canais = [];
    while ($row = $result->fetch_assoc()) {
        $canais[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'numero' => $row['numero'] ?: 'Sem número',
            'status' => $row['status'],
            'porta' => $row['porta']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'canais' => $canais,
        'total' => count($canais)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'canais' => []
    ]);
}
?> 