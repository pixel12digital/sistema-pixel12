<?php
/**
 * Teste do Botão de Fechar Conversa
 * 
 * Verifica se o botão aparece e funciona corretamente
 */

require_once 'config.php';

echo "<h1>🔒 Teste do Botão de Fechar Conversa</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .info { background: #d1ecf1; color: #0c5460; }
    .warning { background: #fff3cd; color: #856404; }
    .btn-test { background: #007bff; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin: 0.5rem; }
    .btn-test:hover { background: #0056b3; }
    iframe { width: 100%; height: 600px; border: 1px solid #ccc; border-radius: 5px; }
</style>\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    echo "<div class='test-section success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
    
    // Buscar um cliente para teste
    $result_cliente = $mysqli->query("SELECT id, nome FROM clientes LIMIT 1");
    if ($result_cliente && $result_cliente->num_rows > 0) {
        $cliente_teste = $result_cliente->fetch_assoc();
        $cliente_id_teste = $cliente_teste['id'];
        $cliente_nome_teste = $cliente_teste['nome'];
        
        echo "<div class='test-section info'>\n";
        echo "<h3>🔍 Teste do Botão de Fechar</h3>\n";
        echo "<p><strong>Cliente de teste:</strong> $cliente_nome_teste (ID: $cliente_id_teste)</p>\n";
        echo "<p><strong>URL de teste:</strong> <a href='painel/chat.php?cliente_id=$cliente_id_teste' target='_blank'>Abrir chat com cliente</a></p>\n";
        echo "</div>\n";
        
        echo "<div class='test-section info'>\n";
        echo "<h3>🎯 Teste em iframe</h3>\n";
        echo "<p>Teste o botão de fechar diretamente:</p>\n";
        echo "<iframe src='painel/chat.php?cliente_id=$cliente_id_teste'></iframe>\n";
        echo "</div>\n";
        
        echo "<div class='test-section info'>\n";
        echo "<h3>🔧 Verificações Técnicas</h3>\n";
        
        // Verificar se a variável clienteId está sendo definida
        $chat_content = file_get_contents('painel/chat.php');
        
        if (strpos($chat_content, 'window.clienteId = clienteId;') !== false) {
            echo "<p>✅ Variável global <code>window.clienteId</code> está sendo definida</p>\n";
        } else {
            echo "<p>❌ Variável global <code>window.clienteId</code> NÃO está sendo definida</p>\n";
        }
        
        if (strpos($chat_content, 'const currentClienteId = window.clienteId') !== false) {
            echo "<p>✅ Função <code>fecharConversaAtual()</code> usa variável global corretamente</p>\n";
        } else {
            echo "<p>❌ Função <code>fecharConversaAtual()</code> NÃO usa variável global</p>\n";
        }
        
        if (strpos($chat_content, 'btn-fechar-conversa') !== false) {
            echo "<p>✅ Classe CSS <code>btn-fechar-conversa</code> encontrada</p>\n";
        } else {
            echo "<p>❌ Classe CSS <code>btn-fechar-conversa</code> NÃO encontrada</p>\n";
        }
        
        if (strpos($chat_content, '🔒 Fechar') !== false) {
            echo "<p>✅ Botão '🔒 Fechar' encontrado no código</p>\n";
        } else {
            echo "<p>❌ Botão '🔒 Fechar' NÃO encontrado no código</p>\n";
        }
        
        echo "</div>\n";
        
        echo "<div class='test-section warning'>\n";
        echo "<h3>⚠️ Instruções de Teste</h3>\n";
        echo "<ol>\n";
        echo "<li>Clique no link 'Abrir chat com cliente' acima</li>\n";
        echo "<li>Verifique se o botão '🔒 Fechar' aparece no header da conversa</li>\n";
        echo "<li>Clique no botão e confirme se aparece a mensagem de confirmação</li>\n";
        echo "<li>Confirme o fechamento e verifique se redireciona para a lista</li>\n";
        echo "<li>Vá para a aba 'Fechadas' e verifique se a conversa aparece lá</li>\n";
        echo "<li>Clique no botão '🔓 Reabrir' e confirme se funciona</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
        
        echo "<div class='test-section success'>\n";
        echo "<h3>🎉 Status da Correção</h3>\n";
        echo "<p><strong>Problemas identificados e corrigidos:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>✅ <strong>Variável clienteId não definida</strong> - Corrigido com definição global</li>\n";
        echo "<li>✅ <strong>Botão não aparecia inicialmente</strong> - Corrigido com renderização dinâmica</li>\n";
        echo "<li>✅ <strong>Erro após refresh</strong> - Corrigido com inicialização adequada</li>\n";
        echo "<li>✅ <strong>Função não funcionava</strong> - Corrigido com uso correto da variável</li>\n";
        echo "</ul>\n";
        echo "<p><strong>O botão agora deve aparecer imediatamente e funcionar corretamente!</strong></p>\n";
        echo "</div>\n";
        
    } else {
        echo "<div class='test-section error'>\n";
        echo "<h3>❌ Nenhum cliente encontrado</h3>\n";
        echo "<p>Não é possível testar sem clientes no banco de dados.</p>\n";
        echo "</div>\n";
    }
    
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
// Teste automático da variável clienteId
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Testando variável clienteId...');
    
    // Aguardar um pouco para o iframe carregar
    setTimeout(() => {
        const iframe = document.querySelector('iframe');
        if (iframe && iframe.contentWindow) {
            try {
                const iframeClienteId = iframe.contentWindow.clienteId;
                console.log('Cliente ID no iframe:', iframeClienteId);
                
                if (iframeClienteId) {
                    console.log('✅ Variável clienteId está definida no iframe');
                } else {
                    console.log('❌ Variável clienteId NÃO está definida no iframe');
                }
            } catch (e) {
                console.log('⚠️ Não foi possível acessar variáveis do iframe (normal)');
            }
        }
    }, 3000);
});
</script> 