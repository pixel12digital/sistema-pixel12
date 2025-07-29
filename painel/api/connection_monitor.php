<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../db.php';
    
    // Obter estatísticas do gerenciador de conexões
    global $dbManager;
    
    if ($dbManager) {
        $stats = $dbManager->getStats();
        
        // Verificar se há muitas conexões ativas
        $warning = false;
        $message = '';
        
        if ($stats['active_connections'] > ($stats['max_connections'] * 0.8)) {
            $warning = true;
            $message = 'Alto número de conexões ativas';
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'warning' => $warning,
            'message' => $message,
            'timestamp' => time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Gerenciador de conexões não disponível',
            'timestamp' => time()
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
?> 