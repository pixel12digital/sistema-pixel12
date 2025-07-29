<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../cache_manager.php';

header('Content-Type: application/json; charset=utf-8');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não fornecido']);
    exit;
}

try {
    // Invalidar cache do cliente
    cache_forget("cliente_{$cliente_id}");
    
    // Invalidar cache de detalhes do cliente
    cache_forget("detalhes_cliente_{$cliente_id}");
    
    // Invalidar cache de mensagens do cliente
    cache_forget("mensagens_{$cliente_id}");
    
    // Invalidar cache de conversas (para atualizar contadores)
    $cache_file = __DIR__ . '/../cache/conversas_recentes.cache';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cache invalidado com sucesso',
        'cliente_id' => $cliente_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao invalidar cache: ' . $e->getMessage()
    ]);
}
?> 