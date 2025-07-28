<?php
/**
 * MONITORAMENTO EM TEMPO REAL DO WEBHOOK
 * 
 * Monitora logs do webhook em tempo real
 */

echo "🔍 MONITORAMENTO EM TEMPO REAL DO WEBHOOK
";
echo "=========================================

";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';

if (!file_exists($log_file)) {
    echo "❌ Arquivo de log não encontrado: $log_file
";
    exit;
}

$initial_size = filesize($log_file);
echo "📄 Monitorando: $log_file
";
echo "📊 Tamanho inicial: " . round($initial_size / 1024, 2) . " KB
";
echo "🕐 Iniciando monitoramento... (Ctrl+C para parar)

";

while (true) {
    clearstatcache();
    $current_size = filesize($log_file);
    
    if ($current_size > $initial_size) {
        $logs = file($log_file);
        $new_lines = array_slice($logs, -1);
        
        foreach ($new_lines as $log) {
            $hora = substr($log, 0, 19);
            $conteudo = substr($log, 20);
            
            echo "🆕 [$hora] Nova mensagem recebida!
";
            echo "   Conteúdo: " . substr($conteudo, 0, 150) . "...
";
            echo "   " . str_repeat("-", 50) . "
";
        }
        
        $initial_size = $current_size;
    }
    
    usleep(500000); // 0.5 segundos
}
?>