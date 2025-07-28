<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=5'); // Reduzido de 15s para 5s

try {
    // Buscar conversas usando o cache manager existente
    $conversas = cache_conversas($mysqli);
    
    echo json_encode([
        'success' => true,
        'conversas' => $conversas,
        'total' => count($conversas),
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar conversas recentes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'conversas' => []
    ]);
}
?> 