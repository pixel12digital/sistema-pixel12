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
    
    // Agora executar a sincronização CORRIGIDA
    ob_start();
    passthru('php ' . escapeshellarg(__DIR__ . '/sincroniza_asaas_corrigido.php') . ' 2>&1', $exitCode);
    $output = ob_get_clean();
    
    // Log da sincronização corrigida
    file_put_contents(__DIR__ . '/../logs/sincronizacao_corrigida.log', date('Y-m-d H:i:s') . " - Sincronização via AJAX executada\n", FILE_APPEND);
    
    if ($exitCode === 0) {
        echo json_encode([
            'success' => true, 
            'output' => $output, 
            'debug' => $debugOutput,
            'message' => 'Sincronização corrigida executada com sucesso! Dados editados manualmente foram preservados.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Erro ao sincronizar', 
            'output' => $output, 
            'debug' => $debugOutput,
            'message' => 'Erro na sincronização corrigida'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'message' => 'Exceção na sincronização corrigida'
    ]);
}
?> 