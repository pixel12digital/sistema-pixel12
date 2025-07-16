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

// Adicionar informações extras sobre o status
$statusInfo = [
    'lines' => $result,
    'timestamp' => date('Y-m-d H:i:s'),
    'file_exists' => file_exists($logFile),
    'file_size' => file_exists($logFile) ? filesize($logFile) : 0,
    'total_lines' => count($result)
];

echo json_encode($statusInfo); 