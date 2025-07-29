<?php
/**
 * Script para limpar conexões órfãs e otimizar o sistema
 * Deve ser executado periodicamente via cron
 */

require_once __DIR__ . '/../config.php';

echo "=== LIMPEZA DE CONEXÕES ÓRFÃS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Conectar ao banco para verificar conexões
    $host = DB_PERSISTENT ? 'p:' . DB_HOST : DB_HOST;
    $mysqli = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_errno) {
        throw new Exception('Erro ao conectar: ' . $mysqli->connect_error);
    }
    
    // Verificar conexões ativas
    $result = $mysqli->query("SHOW PROCESSLIST");
    $connections = [];
    $total_connections = 0;
    $sleeping_connections = 0;
    $long_running = 0;
    
    while ($row = $result->fetch_assoc()) {
        $total_connections++;
        
        if ($row['Command'] === 'Sleep') {
            $sleeping_connections++;
            
            // Verificar conexões dormindo há muito tempo (mais de 5 minutos)
            if ($row['Time'] > 300) {
                $long_running++;
                echo "⚠️ Conexão dormindo há {$row['Time']}s - ID: {$row['Id']}\n";
            }
        }
    }
    
    echo "📊 Estatísticas de Conexões:\n";
    echo "   Total: $total_connections\n";
    echo "   Dormindo: $sleeping_connections\n";
    echo "   Longas (>5min): $long_running\n";
    echo "   Limite por hora: 500\n\n";
    
    // Verificar se estamos próximos do limite
    if ($total_connections > 400) {
        echo "🚨 ALERTA: Muitas conexões ativas ($total_connections/500)\n";
        echo "   Recomendação: Aguardar alguns minutos antes de novas conexões\n\n";
    }
    
    // Limpar cache se necessário
    if (function_exists('cache_cleanup')) {
        echo "🧹 Limpando cache...\n";
        cache_cleanup();
        echo "✅ Cache limpo\n\n";
    }
    
    // Verificar arquivos de cache
    $cache_dir = sys_get_temp_dir() . '/loja_virtual_cache/';
    if (is_dir($cache_dir)) {
        $cache_files = glob($cache_dir . '*.cache');
        $expired_files = 0;
        
        foreach ($cache_files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$data || $data['expires'] < time()) {
                unlink($file);
                $expired_files++;
            }
        }
        
        echo "🗂️ Cache de arquivos:\n";
        echo "   Arquivos: " . count($cache_files) . "\n";
        echo "   Expirados removidos: $expired_files\n\n";
    }
    
    // Recomendações
    echo "💡 Recomendações:\n";
    if ($total_connections > 300) {
        echo "   - Reduzir frequência de polling\n";
        echo "   - Aumentar TTL do cache\n";
        echo "   - Verificar scripts que fazem muitas queries\n";
    } else {
        echo "   - Sistema está funcionando normalmente\n";
        echo "   - Manter configurações atuais\n";
    }
    
    echo "\n✅ Limpeza concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?> 