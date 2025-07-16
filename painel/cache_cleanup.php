<?php
/**
 * Script de Limpeza Automática de Cache
 * Execute este script periodicamente para manter a performance do cache
 */

require_once 'config.php'; // Carregar config primeiro
require_once 'cache_manager.php';
require_once 'cache_invalidator.php';

class CacheCleanup {
    private $cache;
    private $invalidator;
    
    public function __construct() {
        $this->cache = CacheManager::getInstance();
        $this->invalidator = CacheInvalidator::getInstance();
    }
    
    /**
     * Limpeza completa do cache
     */
    public function fullCleanup() {
        echo "🧹 Iniciando limpeza completa do cache...\n";
        
        $stats_before = $this->cache->stats();
        $cleaned = $this->cache->cleanup();
        $stats_after = $this->cache->stats();
        
        echo "✅ Limpeza concluída:\n";
        echo "   - Arquivos removidos: {$cleaned}\n";
        echo "   - Antes: {$stats_before['total_files']} arquivos ({$stats_before['disk_size_mb']} MB)\n";
        echo "   - Depois: {$stats_after['total_files']} arquivos ({$stats_after['disk_size_mb']} MB)\n";
        echo "   - Espaço liberado: " . round($stats_before['disk_size_mb'] - $stats_after['disk_size_mb'], 2) . " MB\n";
        
        return $cleaned;
    }
    
    /**
     * Limpeza seletiva por idade
     */
    public function cleanupByAge($max_age_hours = 24) {
        echo "🕐 Limpeza de cache antigo (>{$max_age_hours}h)...\n";
        
        $cache_dir = sys_get_temp_dir() . '/loja_virtual_cache/';
        $files = glob($cache_dir . '*.cache');
        $cleaned = 0;
        $cutoff_time = time() - ($max_age_hours * 3600);
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['created']) && $data['created'] < $cutoff_time) {
                @unlink($file);
                $cleaned++;
            }
        }
        
        echo "✅ Removidos {$cleaned} arquivos antigos\n";
        return $cleaned;
    }
    
    /**
     * Pré-aquecimento inteligente de cache
     */
    public function warmupCache() {
        echo "🔥 Iniciando pré-aquecimento de cache...\n";
        
        try {
            require_once 'config.php'; // Garantir que config está carregado
            require_once 'db.php';
            global $mysqli;
            
            // Pré-carregar dados mais acessados
            $items_warmed = 0;
            
            echo "   - Carregando conversas recentes...\n";
            cache_conversas($mysqli);
            $items_warmed++;
            
            echo "   - Carregando status de canais...\n";
            cache_status_canais($mysqli);
            $items_warmed++;
            
            // Pré-carregar dados dos últimos 10 clientes ativos
            echo "   - Carregando clientes ativos...\n";
            $sql = "SELECT DISTINCT cliente_id FROM mensagens_comunicacao 
                    WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 1 DAY) 
                    ORDER BY data_hora DESC LIMIT 10";
            $result = $mysqli->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $cliente_id = $row['cliente_id'];
                cache_cliente($cliente_id, $mysqli);
                $items_warmed++;
            }
            
            echo "✅ Pré-aquecimento concluído: {$items_warmed} itens carregados\n";
            
        } catch (Exception $e) {
            echo "❌ Erro no pré-aquecimento: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Relatório de performance do cache
     */
    public function performanceReport() {
        echo "📊 Relatório de Performance do Cache\n";
        echo "===================================\n";
        
        $stats = $this->cache->stats();
        
        echo "Cache em Disco:\n";
        echo "  - Total de arquivos: {$stats['total_files']}\n";
        echo "  - Arquivos expirados: {$stats['expired_files']}\n";
        echo "  - Tamanho total: {$stats['disk_size_mb']} MB\n";
        echo "  - Arquivos válidos: " . ($stats['total_files'] - $stats['expired_files']) . "\n";
        
        echo "\nCache em Memória:\n";
        echo "  - Itens em memória: {$stats['memory_cache_size']}\n";
        
        // Eficiência
        $efficiency = $stats['total_files'] > 0 ? 
            round((($stats['total_files'] - $stats['expired_files']) / $stats['total_files']) * 100, 1) : 0;
        
        echo "\nEficiência: {$efficiency}%\n";
        
        if ($efficiency < 70) {
            echo "⚠️  Recomendação: Execute limpeza de cache (eficiência baixa)\n";
        } else {
            echo "✅ Cache funcionando eficientemente\n";
        }
        
        return $stats;
    }
    
    /**
     * Otimização automática baseada em métricas
     */
    public function autoOptimize() {
        echo "🚀 Iniciando otimização automática...\n";
        
        $stats = $this->cache->stats();
        $actions = 0;
        
        // Se mais de 50% dos arquivos estão expirados, limpar
        if ($stats['total_files'] > 0 && ($stats['expired_files'] / $stats['total_files']) > 0.5) {
            echo "📝 Detectado muitos arquivos expirados, executando limpeza...\n";
            $this->fullCleanup();
            $actions++;
        }
        
        // Se cache está muito grande (>50MB), limpar arquivos antigos
        if ($stats['disk_size_mb'] > 50) {
            echo "📝 Cache muito grande, removendo arquivos antigos...\n";
            $this->cleanupByAge(12); // Limpar arquivos de mais de 12 horas
            $actions++;
        }
        
        // Se há poucos itens em cache, fazer pré-aquecimento
        if ($stats['total_files'] < 10) {
            echo "📝 Poucos itens em cache, executando pré-aquecimento...\n";
            $this->warmupCache();
            $actions++;
        }
        
        if ($actions === 0) {
            echo "✅ Cache já está otimizado, nenhuma ação necessária\n";
        } else {
            echo "✅ Otimização concluída: {$actions} ações executadas\n";
        }
        
        return $actions;
    }
}

// Execução do script
if (php_sapi_name() === 'cli' || isset($_GET['action'])) {
    $cleanup = new CacheCleanup();
    $action = $_GET['action'] ?? ($argv[1] ?? 'report');
    
    switch ($action) {
        case 'cleanup':
        case 'clean':
            $cleanup->fullCleanup();
            break;
            
        case 'warmup':
        case 'warm':
            $cleanup->warmupCache();
            break;
            
        case 'optimize':
        case 'auto':
            $cleanup->autoOptimize();
            break;
            
        case 'old':
        case 'clean-old':
            $hours = $_GET['hours'] ?? ($argv[2] ?? 24);
            $cleanup->cleanupByAge($hours);
            break;
            
        case 'report':
        case 'status':
        default:
            $cleanup->performanceReport();
            break;
    }
    
    if (isset($_GET['action'])) {
        echo "\n\n<a href='cache_cleanup.php?action=report'>← Voltar ao relatório</a>";
    }
}
?> 