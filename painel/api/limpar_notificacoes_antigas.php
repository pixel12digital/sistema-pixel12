<?php
/**
 * 🧹 LIMPA NOTIFICAÇÕES ANTIGAS
 * Remove notificações antigas para manter o banco otimizado
 * ⚡ Executar via cron job diariamente
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Configurações
$dias_para_manter = 7; // Manter apenas 7 dias
$limite_por_cliente = 100; // Máximo 100 notificações por cliente

try {
    echo "🧹 Iniciando limpeza de notificações antigas...\n";
    
    // 1. Remover notificações antigas (mais de 7 dias)
    $sql = "DELETE FROM notificacoes_push 
            WHERE data_hora < DATE_SUB(NOW(), INTERVAL $dias_para_manter DAY)";
    
    $result = $mysqli->query($sql);
    $removidas_antigas = $mysqli->affected_rows;
    
    echo "✅ Removidas $removidas_antigas notificações antigas (> $dias_para_manter dias)\n";
    
    // 2. Limitar notificações por cliente (manter apenas as 100 mais recentes)
    $sql = "DELETE np1 FROM notificacoes_push np1
            INNER JOIN (
                SELECT cliente_id, id
                FROM notificacoes_push
                WHERE id NOT IN (
                    SELECT id FROM (
                        SELECT id
                        FROM notificacoes_push np2
                        WHERE np2.cliente_id = notificacoes_push.cliente_id
                        ORDER BY data_hora DESC
                        LIMIT $limite_por_cliente
                    ) AS recentes
                )
            ) AS para_remover ON np1.id = para_remover.id";
    
    $result = $mysqli->query($sql);
    $removidas_excesso = $mysqli->affected_rows;
    
    echo "✅ Removidas $removidas_excesso notificações em excesso (limite: $limite_por_cliente por cliente)\n";
    
    // 3. Otimizar tabela
    $mysqli->query("OPTIMIZE TABLE notificacoes_push");
    echo "✅ Tabela otimizada\n";
    
    // 4. Estatísticas finais
    $sql = "SELECT COUNT(*) as total FROM notificacoes_push";
    $result = $mysqli->query($sql);
    $total_atual = $result->fetch_assoc()['total'];
    
    echo "📊 Total de notificações após limpeza: $total_atual\n";
    
    // 5. Limpar caches antigos
    $cache_dir = sys_get_temp_dir();
    $cache_files = glob($cache_dir . "/push_cache_*.json");
    $cache_removidos = 0;
    
    foreach ($cache_files as $file) {
        if (filemtime($file) < (time() - 3600)) { // Mais de 1 hora
            unlink($file);
            $cache_removidos++;
        }
    }
    
    echo "🗑️ Removidos $cache_removidos arquivos de cache antigos\n";
    
    echo "🎯 Limpeza concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante limpeza: " . $e->getMessage() . "\n";
    error_log("[LIMPEZA NOTIFICAÇÕES] ❌ Erro: " . $e->getMessage());
}
?> 