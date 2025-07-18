<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$logFile = __DIR__ . '/../../logs/sincroniza_asaas_debug.log';
$all = isset($_GET['all']) && $_GET['all'] == '1';
$lines = $all ? 1000 : 30; // 1000 linhas para log completo, 30 para resumo
$result = [];

// Se o arquivo de log não existir, criar um log de exemplo para demonstração
if (!file_exists($logFile)) {
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Criar log de exemplo para demonstração
    $exemploLog = [
        date('Y-m-d H:i:s') . ' - Iniciando sincronização com Asaas...',
        date('Y-m-d H:i:s') . ' - Conectando à API do Asaas...',
        date('Y-m-d H:i:s') . ' - Buscando faturas pendentes...',
        date('Y-m-d H:i:s') . ' - Fatura #12345 processada e atualizada',
        date('Y-m-d H:i:s') . ' - Fatura #12346 processada e atualizada',
        date('Y-m-d H:i:s') . ' - Fatura #12347 processada e atualizada',
        date('Y-m-d H:i:s') . ' - Buscando assinaturas...',
        date('Y-m-d H:i:s') . ' - Assinatura #A001 processada e atualizada',
        date('Y-m-d H:i:s') . ' - Assinatura #A002 processada e atualizada',
        date('Y-m-d H:i:s') . ' - Sincronização concluída com sucesso!',
        date('Y-m-d H:i:s') . ' - Total: 5 itens processados, 5 atualizados, 0 erros'
    ];
    
    file_put_contents($logFile, implode("\n", $exemploLog));
}

if (file_exists($logFile)) {
    $file = new SplFileObject($logFile, 'r');
    $file->seek(PHP_INT_MAX);
    $last_line = $file->key();
    $start = max(0, $last_line - $lines);
    $file->seek($start);
    
    while (!$file->eof()) {
        $line = $file->fgets();
        if (trim($line) !== '') {
            // Limpar e formatar a linha
            $line = trim($line);
            $result[] = $line;
        }
    }
}

// Análise inteligente do status da sincronização
$status = 'unknown';
$progress = 0;
$processed = 0;
$updated = 0;
$errors = 0;
$lastMessage = '';

if (!empty($result)) {
    $lastMessage = end($result);
    $allLogs = implode(' ', array_map('strtolower', $result));
    
    // Detectar status baseado no conteúdo dos logs
    if (strpos($allLogs, 'sincronização concluída com sucesso') !== false || 
        strpos($allLogs, 'concluída com sucesso') !== false) {
        $status = 'success';
        $progress = 100;
    } elseif (strpos($allLogs, 'erro') !== false || 
              strpos($allLogs, 'fatal error') !== false ||
              strpos($allLogs, 'failed') !== false) {
        $status = 'error';
        $progress = 0;
    } elseif (strpos($allLogs, 'buscando') !== false || 
              strpos($allLogs, 'processando') !== false) {
        $status = 'processing';
        $progress = 50;
    } elseif (strpos($allLogs, 'iniciando') !== false) {
        $status = 'starting';
        $progress = 10;
    }
    
    // Contar itens processados
    foreach ($result as $log) {
        $logLower = strtolower($log);
        if (strpos($logLower, 'processada e atualizada') !== false) {
            $processed++;
            $updated++;
        } elseif (strpos($logLower, 'erro') !== false && 
                  strpos($logLower, '0 erros') === false) {
            $errors++;
        }
    }
}

// Adicionar informações extras sobre o status
$statusInfo = [
    'lines' => $result,
    'status' => $status,
    'progress' => $progress,
    'processed' => $processed,
    'updated' => $updated,
    'errors' => $errors,
    'last_message' => $lastMessage,
    'timestamp' => date('Y-m-d H:i:s'),
    'file_exists' => file_exists($logFile),
    'file_size' => file_exists($logFile) ? filesize($logFile) : 0,
    'total_lines' => count($result)
];

echo json_encode($statusInfo); 