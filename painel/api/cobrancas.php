<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

try {
    $result = $mysqli->query("SELECT * FROM cobrancas ORDER BY vencimento DESC");
    if (!$result) {
        throw new Exception('Erro ao buscar cobranças: ' . $mysqli->error);
    }
    $cobrancas = [];
    while ($row = $result->fetch_assoc()) {
        $cobrancas[] = $row;
    }
    echo json_encode(['success' => true, 'cobrancas' => $cobrancas]);
} catch (Exception $e) {
    error_log('[COBRANCAS_API] ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 