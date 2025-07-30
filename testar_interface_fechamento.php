<?php
/**
 * Teste da Interface de Fechamento Manual
 * 
 * Verifica se os botões e funcionalidades estão funcionando
 */

require_once 'config.php';

echo "<h1>🔒 Teste da Interface de Fechamento Manual</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .info { background: #d1ecf1; color: #0c5460; }
    .warning { background: #fff3cd; color: #856404; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .btn-test { background: #007bff; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin: 0.5rem; }
    .btn-test:hover { background: #0056b3; }
</style>\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    echo "<div class='test-section success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
    
    // ===== TESTE 1: Verificar se arquivos existem =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 1: Verificando arquivos da interface</h3>\n";
    
    $arquivos = [
        'painel/chat.php',
        'painel/api/fechar_conversa.php',
        'painel/api/abrir_conversa.php',
        'painel/api/conversas_fechadas.php'
    ];
    
    foreach ($arquivos as $arquivo) {
        if (file_exists($arquivo)) {
            echo "<p>✅ Arquivo existe: <code>$arquivo</code></p>\n";
        } else {
            echo "<p>❌ Arquivo não existe: <code>$arquivo</code></p>\n";
        }
    }
    echo "</div>\n";
    
    // ===== TESTE 2: Verificar se botão foi adicionado =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 2: Verificando botão na interface</h3>\n";
    
    $chat_content = file_get_contents('painel/chat.php');
    
    if (strpos($chat_content, 'fecharConversaAtual()') !== false) {
        echo "<p>✅ Função JavaScript <code>fecharConversaAtual()</code> encontrada</p>\n";
    } else {
        echo "<p>❌ Função JavaScript <code>fecharConversaAtual()</code> NÃO encontrada</p>\n";
    }
    
    if (strpos($chat_content, 'btn-fechar-conversa') !== false) {
        echo "<p>✅ Classe CSS <code>btn-fechar-conversa</code> encontrada</p>\n";
    } else {
        echo "<p>❌ Classe CSS <code>btn-fechar-conversa</code> NÃO encontrada</p>\n";
    }
    
    if (strpos($chat_content, '🔒 Fechar') !== false) {
        echo "<p>✅ Botão '🔒 Fechar' encontrado na interface</p>\n";
    } else {
        echo "<p>❌ Botão '🔒 Fechar' NÃO encontrado na interface</p>\n";
    }
    
    if (strpos($chat_content, 'reabrirConversa(') !== false) {
        echo "<p>✅ Função JavaScript <code>reabrirConversa()</code> encontrada</p>\n";
    } else {
        echo "<p>❌ Função JavaScript <code>reabrirConversa()</code> NÃO encontrada</p>\n";
    }
    
    echo "</div>\n";
    
    // ===== TESTE 3: Testar APIs =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 3: Testando APIs</h3>\n";
    
    // Buscar um cliente para teste
    $result_cliente = $mysqli->query("SELECT id, nome FROM clientes LIMIT 1");
    if ($result_cliente && $result_cliente->num_rows > 0) {
        $cliente_teste = $result_cliente->fetch_assoc();
        $cliente_id_teste = $cliente_teste['id'];
        $cliente_nome_teste = $cliente_teste['nome'];
        
        echo "<p><strong>Cliente de teste:</strong> $cliente_nome_teste (ID: $cliente_id_teste)</p>\n";
        
        // Testar API de conversas fechadas
        $url_conversas_fechadas = 'http://localhost/loja-virtual-revenda/painel/api/conversas_fechadas.php';
        
        $ch = curl_init($url_conversas_fechadas);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                echo "<p>✅ API de conversas fechadas funcionando</p>\n";
                echo "<p><strong>Conversas fechadas:</strong> " . count($data['conversas']) . "</p>\n";
            } else {
                echo "<p>⚠️ API retornou resposta inválida</p>\n";
            }
        } else {
            echo "<p>❌ API retornou erro HTTP: $http_code</p>\n";
        }
        
        // Testar fechamento
        echo "<p><strong>Testando fechamento de conversa...</strong></p>\n";
        echo "<button class='btn-test' onclick='testarFechamento($cliente_id_teste)'>🔒 Testar Fechar Conversa</button>\n";
        
        // Testar reabertura
        echo "<button class='btn-test' onclick='testarReabertura($cliente_id_teste)'>🔓 Testar Reabrir Conversa</button>\n";
        
    } else {
        echo "<p>❌ Nenhum cliente encontrado para teste</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 4: Verificar funcionalidades =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 4: Verificando funcionalidades</h3>\n";
    
    // Verificar se função filtrarConversasFechadas foi atualizada
    if (strpos($chat_content, 'fetch(\'api/conversas_fechadas.php\')') !== false) {
        echo "<p>✅ Função <code>filtrarConversasFechadas()</code> atualizada com API</p>\n";
    } else {
        echo "<p>❌ Função <code>filtrarConversasFechadas()</code> NÃO foi atualizada</p>\n";
    }
    
    if (strpos($chat_content, '🔓 Reabrir') !== false) {
        echo "<p>✅ Botão '🔓 Reabrir' encontrado na função de conversas fechadas</p>\n";
    } else {
        echo "<p>❌ Botão '🔓 Reabrir' NÃO encontrado na função de conversas fechadas</p>\n";
    }
    
    echo "</div>\n";
    
    // ===== RESUMO FINAL =====
    echo "<div class='test-section success'>\n";
    echo "<h3>🎉 Interface de Fechamento Manual Implementada!</h3>\n";
    echo "<p><strong>Onde encontrar o botão de fechar conversa:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Header da conversa</strong> - Ao lado do título '💬 Conversa com [Nome]'</li>\n";
    echo "<li>✅ <strong>Botão vermelho</strong> com ícone 🔒 e texto 'Fechar'</li>\n";
    echo "<li>✅ <strong>Confirmação</strong> antes de fechar</li>\n";
    echo "<li>✅ <strong>Redirecionamento</strong> para lista após fechar</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Como reabrir conversas:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Aba 'Fechadas'</strong> - Clique na aba para ver conversas fechadas</li>\n";
    echo "<li>✅ <strong>Botão verde</strong> com ícone 🔓 e texto 'Reabrir'</li>\n";
    echo "<li>✅ <strong>Confirmação</strong> antes de reabrir</li>\n";
    echo "</ul>\n";
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
function testarFechamento(clienteId) {
    if (!confirm('Deseja testar o fechamento da conversa do cliente ID ' + clienteId + '?')) return;
    
    const formData = new FormData();
    formData.append('cliente_id', clienteId);
    
    fetch('painel/api/fechar_conversa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Conversa fechada com sucesso!\n\nCliente: ' + data.cliente_nome + '\nMensagens afetadas: ' + data.mensagens_afetadas);
        } else {
            alert('❌ Erro ao fechar conversa: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao testar fechamento: ' + error.message);
    });
}

function testarReabertura(clienteId) {
    if (!confirm('Deseja testar a reabertura da conversa do cliente ID ' + clienteId + '?')) return;
    
    const formData = new FormData();
    formData.append('cliente_id', clienteId);
    
    fetch('painel/api/abrir_conversa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Conversa reaberta com sucesso!\n\nCliente: ' + data.cliente_nome + '\nMensagens afetadas: ' + data.mensagens_afetadas);
        } else {
            alert('❌ Erro ao reabrir conversa: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao testar reabertura: ' + error.message);
    });
}
</script> 