<?php
header('Content-Type: application/json');
set_time_limit(300);
try {
    ob_start();
    passthru('php ' . escapeshellarg(__DIR__ . '/sincroniza_asaas.php') . ' 2>&1', $exitCode);
    $output = ob_get_clean();
    if ($exitCode === 0) {
        echo json_encode(['success' => true, 'output' => $output]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao sincronizar', 'output' => $output]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 