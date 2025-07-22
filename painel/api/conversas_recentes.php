<?php
require_once '../config.php';
require_once '../db.php';
require_once '../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=15'); // Cache HTTP de 15 segundos

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