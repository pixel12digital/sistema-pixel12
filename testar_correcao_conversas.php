<?php
/**
 * Teste da Correção de Conversas
 * Verifica se as conversas fechadas não aparecem mais na lista de abertas
 */

require_once 'config.php';

echo "<h1>🧪 Teste da Correção de Conversas</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .info { background: #d1ecf1; color: #0c5460; }
    .warning { background: #fff3cd; color: #856404; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
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
                   LIMIT 5";
    
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
    }
    echo "</div>\n";
    
    // ===== TESTE 2: Verificar última mensagem do Charles =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 2: Última mensagem do Charles</h3>\n";
    
    $sql_ultima = "SELECT 
                       m.id,
                       m.mensagem,
                       m.status_conversa,
                       m.data_hora,
                       m.direcao
                   FROM mensagens_comunicacao m
                   INNER JOIN clientes c ON m.cliente_id = c.id
                   WHERE c.nome LIKE '%Charles%'
                   ORDER BY m.data_hora DESC
                   LIMIT 1";
    
    $result_ultima = $mysqli->query($sql_ultima);
    
    if ($result_ultima && $result_ultima->num_rows > 0) {
        $ultima = $result_ultima->fetch_assoc();
        echo "<p><strong>Última mensagem:</strong></p>\n";
        echo "<p>ID: {$ultima['id']}</p>\n";
        echo "<p>Mensagem: " . substr($ultima['mensagem'], 0, 100) . "...</p>\n";
        echo "<p>Status: <strong>{$ultima['status_conversa']}</strong></p>\n";
        echo "<p>Data/Hora: {$ultima['data_hora']}</p>\n";
        echo "<p>Direção: {$ultima['direcao']}</p>\n";
        
        if ($ultima['status_conversa'] === 'fechada') {
            echo "<p style='color: #721c24;'><strong>✅ CORREÇÃO FUNCIONANDO:</strong> Última mensagem tem status 'fechada'</p>\n";
        } else {
            echo "<p style='color: #856404;'><strong>⚠️ ATENÇÃO:</strong> Última mensagem não tem status 'fechada'</p>\n";
        }
    }
    echo "</div>\n";
    
    // ===== TESTE 3: Testar query corrigida =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 3: Query corrigida</h3>\n";
    
    $sql_teste = "SELECT 
                    c.id as cliente_id,
                    c.nome,
                    ultima.mensagem as ultima_mensagem,
                    ultima.status_conversa as ultimo_status
                  FROM clientes c
                  INNER JOIN (
                      SELECT 
                          cliente_id,
                          mensagem,
                          status_conversa,
                          data_hora,
                          ROW_NUMBER() OVER (PARTITION BY cliente_id ORDER BY data_hora DESC) as rn
                      FROM mensagens_comunicacao 
                      WHERE cliente_id IS NOT NULL
                  ) ultima ON c.id = ultima.cliente_id AND ultima.rn = 1
                  WHERE c.nome LIKE '%Charles%'";
    
    $result_teste = $mysqli->query($sql_teste);
    
    if ($result_teste && $result_teste->num_rows > 0) {
        while ($row = $result_teste->fetch_assoc()) {
            echo "<p><strong>Charles (ID: {$row['cliente_id']}):</strong></p>\n";
            echo "<p>Última mensagem: " . substr($row['ultima_mensagem'], 0, 50) . "...</p>\n";
            echo "<p>Status da última mensagem: <strong>{$row['ultimo_status']}</strong></p>\n";
            
            if ($row['ultimo_status'] === 'fechada') {
                echo "<p style='color: #155724;'><strong>✅ CORREÇÃO FUNCIONANDO:</strong> Charles deve aparecer apenas na aba 'Fechadas'</p>\n";
            } else {
                echo "<p style='color: #856404;'><strong>⚠️ ATENÇÃO:</strong> Charles pode aparecer na aba 'Abertas'</p>\n";
            }
        }
    }
    echo "</div>\n";
    
    // ===== TESTE 4: Verificar se Charles aparece na lista de abertas =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 4: Lista de conversas abertas</h3>\n";
    
    // Simular a query corrigida
    $sql_abertas = "SELECT 
                        c.id as cliente_id,
                        c.nome,
                        COALESCE(ultima.mensagem, 'Sem mensagens') as ultima_mensagem,
                        COALESCE(ultima.data_hora, c.data_criacao) as ultima_data
                    FROM clientes c
                    LEFT JOIN (
                        SELECT 
                            cliente_id,
                            mensagem,
                            data_hora,
                            ROW_NUMBER() OVER (PARTITION BY cliente_id ORDER BY data_hora DESC) as rn
                        FROM mensagens_comunicacao 
                        WHERE cliente_id IS NOT NULL
                    ) ultima ON c.id = ultima.cliente_id AND ultima.rn = 1
                    WHERE ultima.cliente_id IS NOT NULL
                    AND c.id NOT IN (
                        SELECT DISTINCT m.cliente_id 
                        FROM mensagens_comunicacao m
                        INNER JOIN (
                            SELECT cliente_id, MAX(data_hora) as ultima_data
                            FROM mensagens_comunicacao 
                            WHERE cliente_id IS NOT NULL
                            GROUP BY cliente_id
                        ) ultima_msg ON m.cliente_id = ultima_msg.cliente_id 
                        AND m.data_hora = ultima_msg.ultima_data
                        WHERE m.status_conversa = 'fechada' 
                        AND m.cliente_id IS NOT NULL
                    )
                    ORDER BY ultima.data_hora DESC
                    LIMIT 10";
    
    $result_abertas = $mysqli->query($sql_abertas);
    
    if ($result_abertas && $result_abertas->num_rows > 0) {
        echo "<p><strong>Conversas que aparecem na aba 'Abertas':</strong></p>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Última Mensagem</th><th>Data</th></tr>\n";
        
        $charles_encontrado = false;
        while ($row = $result_abertas->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['cliente_id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>" . substr($row['ultima_mensagem'], 0, 50) . "...</td>";
            echo "<td>{$row['ultima_data']}</td>";
            echo "</tr>\n";
            
            if (strpos($row['nome'], 'Charles') !== false) {
                $charles_encontrado = true;
            }
        }
        echo "</table>\n";
        
        if ($charles_encontrado) {
            echo "<p style='color: #721c24;'><strong>❌ PROBLEMA:</strong> Charles ainda aparece na lista de abertas</p>\n";
        } else {
            echo "<p style='color: #155724;'><strong>✅ SUCESSO:</strong> Charles não aparece mais na lista de abertas</p>\n";
        }
    } else {
        echo "<p>Nenhuma conversa encontrada na lista de abertas</p>\n";
    }
    echo "</div>\n";
    
    // ===== AÇÕES =====
    echo "<div class='test-section warning'>\n";
    echo "<h3>🛠️ Ações</h3>\n";
    echo "<button onclick='fecharConversa()'>🔒 Fechar Conversa do Charles</button>\n";
    echo "<button onclick='limparCache()'>🗑️ Limpar Cache</button>\n";
    echo "<button onclick='location.reload()'>🔄 Recarregar Teste</button>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='test-section error'>\n";
    echo "<h3>❌ Erro no teste</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "</div>\n";
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?>

<script>
function fecharConversa() {
    if (!confirm('Deseja fechar a conversa do Charles?')) return;
    
    const formData = new FormData();
    formData.append('cliente_id', '4296'); // ID do Charles
    
    fetch('painel/api/fechar_conversa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Conversa fechada com sucesso!\n\nRecarregue a página para ver as mudanças.');
            location.reload();
        } else {
            alert('❌ Erro ao fechar conversa: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao fechar conversa: ' + error.message);
    });
}

function limparCache() {
    if (!confirm('Deseja limpar o cache?')) return;
    
    fetch('painel/api/limpar_cache.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Cache limpo com sucesso!\n\nRecarregue a página para ver as mudanças.');
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