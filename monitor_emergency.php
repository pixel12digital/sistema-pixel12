<?php
/**
 * MONITORAMENTO DE EMERGÊNCIA
 * 
 * Monitora consumo de conexões em tempo real
 */

require_once 'emergency_config.php';
require_once 'emergency_db.php';

echo "🚨 MONITORAMENTO DE EMERGÊNCIA
";
echo "==============================

";

$start_time = time();
$end_time = $start_time + 1800; // 30 minutos
$check_interval = 60; // 1 minuto

echo "🕐 Monitorando por 30 minutos (intervalo: 1 minuto)...

";

while (time() < $end_time) {
    try {
        $db = getEmergencyDB();
        
        // Verificar apenas estatísticas básicas
        $result = $db->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()", 'stats_today');
        $stats = $result->fetch_assoc();
        
        $current_time = date('H:i:s');
        echo "[$current_time] Mensagens hoje: {$stats['total']}
";
        
        // Verificar rate limit
        $rate_limit_file = 'cache/rate_limit_' . date('Y-m-d-H') . '.txt';
        if (file_exists($rate_limit_file)) {
            $current_requests = (int)file_get_contents($rate_limit_file);
            echo "   📊 Requisições esta hora: $current_requests/50
";
        }
        
        // Aguardar
        sleep($check_interval);
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "
";
        sleep($check_interval);
    }
}

echo "
✅ Monitoramento de emergência concluído!
";
?>