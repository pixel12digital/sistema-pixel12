<?php
/**
 * API para limpar cache
 * Força atualização dos dados após correções
 */

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../db.php';
    
    // Verificar se o cache_manager existe
    $cache_manager_path = __DIR__ . '/../cache_manager.php';
    if (file_exists($cache_manager_path)) {
        require_once $cache_manager_path;
    }
    
    $cache_dir = __DIR__ . '/../cache/';
    $arquivos_removidos = 0;
    
    // Limpar todos os arquivos de cache
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*.cache');
        foreach ($files as $file) {
            if (unlink($file)) {
                $arquivos_removidos++;
            }
        }
    }
    
    // Limpar cache específico se função existir
    if (function_exists('cache_forget')) {
        cache_forget('conversas_recentes');
        cache_forget('conversas_fechadas');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cache limpo com sucesso',
        'arquivos_removidos' => $arquivos_removidos,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao limpar cache: ' . $e->getMessage(),
        'timestamp' => time()
    ]);
}
?> 