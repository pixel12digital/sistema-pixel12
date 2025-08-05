<?php
/**
 * 📊 MONITOR DE LOGS DO WEBHOOK
 * Monitora logs em tempo real para identificar problemas
 */

echo "📊 MONITOR DE LOGS DO WEBHOOK\n";
echo "=============================\n\n";

echo "🔍 MONITORANDO LOGS EM TEMPO REAL...\n";
echo "Pressione Ctrl+C para parar\n\n";

// Configuração
$log_file = ini_get('error_log');
if (empty($log_file)) {
    $log_file = '/var/log/apache2/error.log'; // Log padrão do Apache
}

echo "📋 Arquivo de log: $log_file\n";
echo "⏰ Iniciando monitoramento...\n\n";

$last_position = 0;
$monitor_count = 0;

while (true) {
    if (file_exists($log_file)) {
        $current_size = filesize($log_file);
        
        if ($current_size > $last_position) {
            $handle = fopen($log_file, 'r');
            fseek($handle, $last_position);
            
            while (($line = fgets($handle)) !== false) {
                // Filtrar apenas logs relacionados ao webhook
                if (strpos($line, 'WEBHOOK') !== false || 
                    strpos($line, 'ANA') !== false || 
                    strpos($line, 'WHATSAPP') !== false ||
                    strpos($line, 'CURL') !== false) {
                    
                    $timestamp = date('H:i:s');
                    echo "[$timestamp] " . trim($line) . "\n";
                }
            }
            
            fclose($handle);
            $last_position = $current_size;
        }
    }
    
    $monitor_count++;
    if ($monitor_count % 10 == 0) {
        echo "⏳ Monitorando... (" . date('H:i:s') . ")\n";
    }
    
    sleep(1);
}
?> 