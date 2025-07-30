<?php
/**
 * Investigação do Cache de Conversas
 * 
 * Verifica por que conversas ainda aparecem duplicadas após a correção
 */

require_once 'config.php';

echo "<h1>🔍 Investigação do Cache de Conversas</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .info { background: #d1ecf1; color: #0c5460; }
    .warning { background: #fff3cd; color: #856404; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    echo "<div class='test-section success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
    
    // ===== TESTE 1: Verificar status das mensagens do Charles =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 1: Status das mensagens do Charles</h3>\n";
    
    $sql_charles = "SELECT 
                       m.id,
                       m.mensagem,
                       m.status_conversa,
                       m.data_hora,
                       m.direcao
                   FROM mensagens_comunicacao m
                   INNER JOIN clientes c ON m.cliente_id = c.id
                   WHERE c.nome LIKE '%Charles%'
                   ORDER BY m.data_hora DESC
                   LIMIT 10";
    
    $result_charles = $mysqli->query($sql_charles);
    
    if ($result_charles && $result_charles->num_rows > 0) {
        echo "<p><strong>Últimas mensagens do Charles:</strong></p>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Mensagem</th><th>Status</th><th>Data/Hora</th><th>Direção</th></tr>\n";
        
        while ($msg = $result_charles->fetch_assoc()) {
            $status = $msg['status_conversa'] ?: 'NULL';
            $cor_status = $msg['status_conversa'] === 'fechada' ? 'color: #721c24;' : 
                         ($msg['status_conversa'] === 'aberta' ? 'color: #155724;' : 'color: #856404;');
            
            echo "<tr>";
            echo "<td>{$msg['id']}</td>";
            echo "<td>" . substr($msg['mensagem'], 0, 50) . "...</td>";
            echo "<td style='$cor_status'>$status</td>";
            echo "<td>{$msg['data_hora']}</td>";
            echo "<td>{$msg['direcao']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>❌ Nenhuma mensagem do Charles encontrada</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 2: Verificar cache atual =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 2: Verificando cache atual</h3>\n";
    
    $cache_file = 'painel/cache/conversas_recentes.cache';
    if (file_exists($cache_file)) {
        echo "<p>✅ Arquivo de cache existe</p>\n";
        echo "<p><strong>Tamanho:</strong> " . filesize($cache_file) . " bytes</p>\n";
        echo "<p><strong>Última modificação:</strong> " . date('Y-m-d H:i:s', filemtime($cache_file)) . "</p>\n";
        
        $cached_data = unserialize(file_get_contents($cache_file));
        if ($cached_data && isset($cached_data['data'])) {
            echo "<p><strong>Total de conversas no cache:</strong> " . count($cached_data['data']) . "</p>\n";
            
            // Verificar se Charles está no cache
            $charles_no_cache = false;
            foreach ($cached_data['data'] as $conv) {
                if (strpos($conv['nome'], 'Charles') !== false) {
                    $charles_no_cache = true;
                    echo "<p>❌ <strong>PROBLEMA:</strong> Charles ainda está no cache (ID: {$conv['cliente_id']})</p>\n";
                    break;
                }
            }
            
            if (!$charles_no_cache) {
                echo "<p>✅ Charles não está mais no cache</p>\n";
            }
        }
    } else {
        echo "<p>⚠️ Arquivo de cache não existe</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 3: Testar função cache_conversas diretamente =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 3: Testando função cache_conversas</h3>\n";
    
    if (file_exists('painel/cache_manager.php')) {
        require_once 'painel/cache_manager.php';
        
        if (function_exists('cache_conversas')) {
            try {
                $conversas_cache = cache_conversas($mysqli);
                echo "<p>✅ Função cache_conversas executou</p>\n";
                echo "<p><strong>Total de conversas retornadas:</strong> " . count($conversas_cache) . "</p>\n";
                
                // Verificar se Charles está na lista
                $charles_na_lista = false;
                foreach ($conversas_cache as $conv) {
                    if (strpos($conv['nome'], 'Charles') !== false) {
                        $charles_na_lista = true;
                        echo "<p>❌ <strong>PROBLEMA:</strong> Charles ainda aparece na lista (ID: {$conv['cliente_id']})</p>\n";
                        break;
                    }
                }
                
                if (!$charles_na_lista) {
                    echo "<p>✅ Charles não aparece mais na lista</p>\n";
                }
            } catch (Exception $e) {
                echo "<p>❌ Erro na função cache_conversas: " . $e->getMessage() . "</p>\n";
            }
        } else {
            echo "<p>❌ Função cache_conversas não encontrada</p>\n";
        }
    } else {
        echo "<p>❌ Arquivo cache_manager.php não encontrado</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 4: Verificar query direta =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 4: Testando query direta</h3>\n";
    
    $sql_teste = "SELECT 
                    c.id as cliente_id,
                    c.nome,
                    COUNT(CASE WHEN m.status_conversa = 'fechada' THEN 1 END) as mensagens_fechadas
                  FROM clientes c
                  INNER JOIN mensagens_comunicacao m ON c.id = m.cliente_id
                  WHERE c.nome LIKE '%Charles%'
                  GROUP BY c.id, c.nome";
    
    $result_teste = $mysqli->query($sql_teste);
    
    if ($result_teste && $result_teste->num_rows > 0) {
        while ($row = $result_teste->fetch_assoc()) {
            echo "<p><strong>Charles (ID: {$row['cliente_id']}):</strong> {$row['mensagens_fechadas']} mensagens fechadas</p>\n";
            
            if ($row['mensagens_fechadas'] > 0) {
                echo "<p>✅ <strong>CORREÇÃO FUNCIONANDO:</strong> Charles tem mensagens fechadas e deve ser excluído da lista de abertas</p>\n";
            } else {
                echo "<p>⚠️ Charles não tem mensagens fechadas</p>\n";
            }
        }
    }
    echo "</div>\n";
    
    // ===== AÇÃO: Limpar cache =====
    echo "<div class='test-section warning'>\n";
    echo "<h3>🛠️ Ação: Limpar Cache</h3>\n";
    echo "<p>Para forçar a atualização, clique no botão abaixo:</p>\n";
    echo "<button onclick='limparCache()' style='background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;'>🗑️ Limpar Cache</button>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='test-section error'>\n";
    echo "<h3>❌ Erro na investigação</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "</div>\n";
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?>

<script>
function limparCache() {
    if (!confirm('Deseja limpar o cache? Isso forçará a atualização dos dados.')) return;
    
    fetch('painel/api/limpar_cache.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Cache limpo com sucesso!\n\nArquivos removidos: ' + data.arquivos_removidos + '\n\nRecarregue a página do chat para ver as mudanças.');
            location.reload();
        } else {
            alert('❌ Erro ao limpar cache: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao limpar cache: ' + error.message);
    });
}
</script> 