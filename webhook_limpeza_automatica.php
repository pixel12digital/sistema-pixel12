<?php
/**
 * LIMPEZA AUTOMÁTICA DO SISTEMA
 * 
 * Remove logs antigos e otimiza o banco de dados
 */

require_once 'config.php';
require_once 'painel/db.php';

class WebhookCleanup {
    private $mysqli;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    public function cleanOldLogs() {
        $log_dir = 'logs/';
        $files = glob($log_dir . '*.log');
        $deleted = 0;
        
        foreach ($files as $file) {
            $file_time = filemtime($file);
            $days_old = (time() - $file_time) / (60 * 60 * 24);
            
            if ($days_old > 7) { // Manter apenas 7 dias
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
    
    public function cleanTempFiles() {
        $temp_dir = 'temp/';
        if (!is_dir($temp_dir)) {
            return 0;
        }
        
        $files = glob($temp_dir . '*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $file_time = filemtime($file);
                $hours_old = (time() - $file_time) / (60 * 60);
                
                if ($hours_old > 24) { // Manter apenas 24 horas
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            }
        }
        
        return $deleted;
    }
    
    public function optimizeDatabase() {
        // Otimizar tabelas
        $tables = ['mensagens_comunicacao', 'clientes', 'canais_comunicacao'];
        $optimized = 0;
        
        foreach ($tables as $table) {
            $sql = "OPTIMIZE TABLE $table";
            if ($this->mysqli->query($sql)) {
                $optimized++;
            }
        }
        
        return $optimized;
    }
    
    public function run() {
        echo "🧹 LIMPEZA AUTOMÁTICA DO SISTEMA\n";
        echo "================================\n\n";
        
        $logs_deleted = $this->cleanOldLogs();
        echo "📄 Logs antigos removidos: $logs_deleted\n";
        
        $temp_deleted = $this->cleanTempFiles();
        echo "🗑️ Arquivos temporários removidos: $temp_deleted\n";
        
        $tables_optimized = $this->optimizeDatabase();
        echo "⚡ Tabelas otimizadas: $tables_optimized\n";
        
        echo "\n✅ Limpeza concluída!\n";
    }
}

// Executar se chamado diretamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $cleanup = new WebhookCleanup($mysqli);
    $cleanup->run();
}
?>