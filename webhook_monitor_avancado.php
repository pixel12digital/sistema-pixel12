<?php
/**
 * MONITORAMENTO AVANÇADO DO WEBHOOK
 * 
 * Monitoramento com alertas e métricas detalhadas
 */

require_once 'config.php';
require_once 'painel/db.php';

class WebhookAdvancedMonitor {
    private $mysqli;
    private $log_file;
    private $stats_file;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = 'logs/webhook_monitor_' . date('Y-m-d') . '.log';
        $this->stats_file = 'temp/webhook_stats.json';
    }
    
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    public function getDetailedStats() {
        // Estatísticas detalhadas
        $stats = [];
        
        // Mensagens por hora
        $sql_hourly = "SELECT 
            DATE_FORMAT(data_hora, '%Y-%m-%d %H:00:00') as hora,
            COUNT(*) as total,
            COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
            COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas
        FROM mensagens_comunicacao 
        WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY DATE_FORMAT(data_hora, '%Y-%m-%d %H:00:00')
        ORDER BY hora DESC";
        
        $result_hourly = $this->mysqli->query($sql_hourly);
        $stats['hourly'] = [];
        
        if ($result_hourly) {
            while ($row = $result_hourly->fetch_assoc()) {
                $stats['hourly'][] = $row;
            }
        }
        
        // Top clientes
        $sql_clients = "SELECT 
            c.nome,
            COUNT(mc.id) as total_mensagens,
            MAX(mc.data_hora) as ultima_mensagem
        FROM mensagens_comunicacao mc
        LEFT JOIN clientes c ON mc.cliente_id = c.id
        WHERE mc.data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY mc.cliente_id, c.nome
        ORDER BY total_mensagens DESC
        LIMIT 10";
        
        $result_clients = $this->mysqli->query($sql_clients);
        $stats['top_clients'] = [];
        
        if ($result_clients) {
            while ($row = $result_clients->fetch_assoc()) {
                $stats['top_clients'][] = $row;
            }
        }
        
        // Erros e problemas
        $sql_errors = "SELECT 
            COUNT(CASE WHEN numero_whatsapp IS NULL THEN 1 END) as sem_numero,
            COUNT(CASE WHEN cliente_id IS NULL THEN 1 END) as sem_cliente,
            COUNT(CASE WHEN mensagem = '' THEN 1 END) as mensagens_vazias
        FROM mensagens_comunicacao 
        WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $result_errors = $this->mysqli->query($sql_errors);
        $stats['errors'] = $result_errors ? $result_errors->fetch_assoc() : [];
        
        return $stats;
    }
    
    public function checkHealth() {
        $health = [
            'status' => 'OK',
            'issues' => [],
            'recommendations' => []
        ];
        
        // Verificar mensagens sem número WhatsApp
        $sql_sem_numero = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
                           WHERE numero_whatsapp IS NULL 
                           AND data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $result_sem_numero = $this->mysqli->query($sql_sem_numero);
        $sem_numero = $result_sem_numero ? $result_sem_numero->fetch_assoc()['total'] : 0;
        
        if ($sem_numero > 0) {
            $health['issues'][] = "$sem_numero mensagens sem número WhatsApp na última hora";
            $health['recommendations'][] = 'Verificar configuração do webhook';
        }
        
        // Verificar volume de mensagens
        $sql_volume = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
                       WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $result_volume = $this->mysqli->query($sql_volume);
        $volume = $result_volume ? $result_volume->fetch_assoc()['total'] : 0;
        
        if ($volume > 100) {
            $health['status'] = 'HIGH_VOLUME';
            $health['recommendations'][] = 'Considerar otimizações de performance';
        }
        
        if (count($health['issues']) > 0) {
            $health['status'] = 'WARNING';
        }
        
        return $health;
    }
    
    public function generateReport() {
        $stats = $this->getDetailedStats();
        $health = $this->checkHealth();
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'health' => $health,
            'stats' => $stats
        ];
        
        // Salvar relatório
        file_put_contents($this->stats_file, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->log("Relatório gerado - Status: {$health['status']}");
        
        return $report;
    }
    
    public function run() {
        $this->log("Iniciando monitoramento avançado");
        
        $report = $this->generateReport();
        
        // Exibir resumo
        echo "🔍 RELATÓRIO DE MONITORAMENTO\n";
        echo "================================\n";
        echo "Status: {$report['health']['status']}\n";
        echo "Timestamp: {$report['timestamp']}\n\n";
        
        if (!empty($report['health']['issues'])) {
            echo "⚠️ PROBLEMAS IDENTIFICADOS:\n";
            foreach ($report['health']['issues'] as $issue) {
                echo "   - $issue\n";
            }
            echo "\n";
        }
        
        if (!empty($report['health']['recommendations'])) {
            echo "💡 RECOMENDAÇÕES:\n";
            foreach ($report['health']['recommendations'] as $rec) {
                echo "   - $rec\n";
            }
            echo "\n";
        }
        
        echo "📊 ESTATÍSTICAS:\n";
        echo "   Mensagens na última hora: " . count($report['stats']['hourly']) . "\n";
        echo "   Top clientes: " . count($report['stats']['top_clients']) . "\n";
        echo "   Erros: " . json_encode($report['stats']['errors']) . "\n";
        
        $this->log("Monitoramento concluído");
    }
}

// Executar se chamado diretamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $monitor = new WebhookAdvancedMonitor($mysqli);
    $monitor->run();
}
?>