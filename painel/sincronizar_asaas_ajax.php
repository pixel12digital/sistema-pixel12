<?php
header('Content-Type: application/json');
set_time_limit(0);

try {
    // Primeiro executar debug para identificar o problema
    ob_start();
    passthru('php ' . escapeshellarg(__DIR__ . '/debug_sync_web.php') . ' 2>&1', $debugExitCode);
    $debugOutput = ob_get_clean();
    
    // Log do debug
    file_put_contents(__DIR__ . '/../logs/sync_web_debug.log', date('Y-m-d H:i:s') . " - Debug output: $debugOutput\n", FILE_APPEND);
    
    // Agora executar a sincronização
    ob_start();
    passthru('php ' . escapeshellarg(__DIR__ . '/sincroniza_asaas.php') . ' 2>&1', $exitCode);
    $output = ob_get_clean();
    
    if ($exitCode === 0) {
        echo json_encode(['success' => true, 'output' => $output, 'debug' => $debugOutput]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao sincronizar', 'output' => $output, 'debug' => $debugOutput]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 