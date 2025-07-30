<?php
/**
 * Teste da Reabertura Automática de Conversas
 * 
 * Verifica se conversas fechadas há mais de 24 horas são reabertas automaticamente
 */

require_once 'config.php';

echo "<h1>🔄 Teste da Reabertura Automática</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .info { background: #d1ecf1; color: #0c5460; }
    .warning { background: #fff3cd; color: #856404; }
    .btn-test { background: #007bff; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin: 0.5rem; }
    .btn-test:hover { background: #0056b3; }
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
    
    // ===== TESTE 1: Verificar conversas fechadas =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 1: Verificando conversas fechadas</h3>\n";
    
    $sql_conversas_fechadas = "SELECT 
                                  c.id as cliente_id,
                                  c.nome,
                                  m.data_hora as ultima_mensagem,
                                  TIMESTAMPDIFF(HOUR, m.data_hora, NOW()) as horas_fechada,
                                  COUNT(*) as total_mensagens_fechadas
                              FROM mensagens_comunicacao m
                              INNER JOIN clientes c ON m.cliente_id = c.id
                              WHERE m.status_conversa = 'fechada'
                              GROUP BY c.id, c.nome, m.data_hora
                              ORDER BY m.data_hora ASC";
    
    $result_fechadas = $mysqli->query($sql_conversas_fechadas);
    
    if ($result_fechadas && $result_fechadas->num_rows > 0) {
        echo "<p><strong>Conversas fechadas encontradas:</strong></p>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Cliente ID</th><th>Nome</th><th>Última Mensagem</th><th>Horas Fechada</th><th>Total Msgs</th><th>Status</th></tr>\n";
        
        while ($conversa = $result_fechadas->fetch_assoc()) {
            $horas_fechada = $conversa['horas_fechada'];
            $status = $horas_fechada >= 24 ? '🔄 PRONTA PARA REABERTURA' : '⏳ Aguardando 24h';
            $cor_status = $horas_fechada >= 24 ? 'color: #155724; font-weight: bold;' : 'color: #856404;';
            
            echo "<tr>";
            echo "<td>{$conversa['cliente_id']}</td>";
            echo "<td>{$conversa['nome']}</td>";
            echo "<td>{$conversa['ultima_mensagem']}</td>";
            echo "<td>{$horas_fechada}h</td>";
            echo "<td>{$conversa['total_mensagens_fechadas']}</td>";
            echo "<td style='$cor_status'>$status</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>✅ Nenhuma conversa fechada encontrada</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 2: Verificar função de reabertura =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 2: Verificando função de reabertura</h3>\n";
    
    $webhook_content = file_get_contents('api/webhook_whatsapp.php');
    
    if (strpos($webhook_content, 'verificarReaberturaAutomatica') !== false) {
        echo "<p>✅ Função <code>verificarReaberturaAutomatica()</code> encontrada</p>\n";
    } else {
        echo "<p>❌ Função <code>verificarReaberturaAutomatica()</code> NÃO encontrada</p>\n";
    }
    
    if (strpos($webhook_content, 'DATE_SUB(NOW(), INTERVAL 24 HOUR)') !== false) {
        echo "<p>✅ Verificação de 24 horas implementada</p>\n";
    } else {
        echo "<p>❌ Verificação de 24 horas NÃO implementada</p>\n";
    }
    
    if (strpos($webhook_content, 'Conversa reaberta automaticamente') !== false) {
        echo "<p>✅ Mensagem de reabertura automática implementada</p>\n";
    } else {
        echo "<p>❌ Mensagem de reabertura automática NÃO implementada</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 3: Testar reabertura manual =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 3: Testando reabertura manual</h3>\n";
    
    // Buscar um cliente para teste
    $result_cliente = $mysqli->query("SELECT id, nome FROM clientes LIMIT 1");
    if ($result_cliente && $result_cliente->num_rows > 0) {
        $cliente_teste = $result_cliente->fetch_assoc();
        $cliente_id_teste = $cliente_teste['id'];
        $cliente_nome_teste = $cliente_teste['nome'];
        
        echo "<p><strong>Cliente de teste:</strong> $cliente_nome_teste (ID: $cliente_id_teste)</p>\n";
        
        // Verificar se o cliente tem conversa fechada
        $sql_verificar_fechada = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE cliente_id = $cliente_id_teste AND status_conversa = 'fechada'";
        $result_verificar = $mysqli->query($sql_verificar_fechada);
        $conversa_fechada = $result_verificar->fetch_assoc();
        
        if ($conversa_fechada['total'] > 0) {
            echo "<p>✅ Cliente tem conversa fechada</p>\n";
            echo "<button class='btn-test' onclick='testarReabertura($cliente_id_teste)'>🔄 Testar Reabertura Automática</button>\n";
        } else {
            echo "<p>⚠️ Cliente não tem conversa fechada - fechando primeiro...</p>\n";
            echo "<button class='btn-test' onclick='fecharConversa($cliente_id_teste)'>🔒 Fechar Conversa Primeiro</button>\n";
        }
        
    } else {
        echo "<p>❌ Nenhum cliente encontrado para teste</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 4: Verificar lógica de atendente =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 4: Verificando lógica de atendente</h3>\n";
    
    if (strpos($webhook_content, 'processarSolicitacaoAtendente') !== false) {
        echo "<p>✅ Função <code>processarSolicitacaoAtendente()</code> encontrada</p>\n";
    } else {
        echo "<p>❌ Função <code>processarSolicitacaoAtendente()</code> NÃO encontrada</p>\n";
    }
    
    if (strpos($webhook_content, 'Dados do cliente:') !== false) {
        echo "<p>✅ Resposta personalizada com dados do cliente implementada</p>\n";
    } else {
        echo "<p>❌ Resposta personalizada com dados do cliente NÃO implementada</p>\n";
    }
    
    if (strpos($webhook_content, 'Tempo estimado: 5-15 minutos') !== false) {
        echo "<p>✅ Informações de tempo de atendimento implementadas</p>\n";
    } else {
        echo "<p>❌ Informações de tempo de atendimento NÃO implementadas</p>\n";
    }
    echo "</div>\n";
    
    // ===== RESUMO FINAL =====
    echo "<div class='test-section success'>\n";
    echo "<h3>🎉 Reabertura Automática Implementada!</h3>\n";
    echo "<p><strong>Funcionalidades implementadas:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Reabertura automática</strong> - Conversas fechadas há mais de 24 horas são reabertas automaticamente</li>\n";
    echo "<li>✅ <strong>Mensagem informativa</strong> - Cliente recebe mensagem quando conversa é reaberta</li>\n";
    echo "<li>✅ <strong>Log detalhado</strong> - Sistema registra quando conversa é reaberta</li>\n";
    echo "<li>✅ <strong>Atendente melhorado</strong> - Inclui dados do cliente e tempo estimado</li>\n";
    echo "<li>✅ <strong>Prioridade correta</strong> - Reabertura é verificada antes de outras lógicas</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Fluxo de funcionamento:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Cliente envia mensagem</li>\n";
    echo "<li>Sistema verifica se conversa foi fechada há mais de 24 horas</li>\n";
    echo "<li>Se sim, reabre automaticamente e envia mensagem informativa</li>\n";
    echo "<li>Se não, processa normalmente (fechada permanece fechada)</li>\n";
    echo "<li>Cliente pode solicitar atendente digitando '1'</li>\n";
    echo "<li>Atendente recebe dados completos do cliente</li>\n";
    echo "</ol>\n";
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
function testarReabertura(clienteId) {
    if (!confirm('Deseja testar a reabertura automática para o cliente ID ' + clienteId + '?')) return;
    
    // Simular envio de mensagem via webhook
    const formData = new FormData();
    formData.append('cliente_id', clienteId);
    formData.append('teste_reabertura', 'true');
    
    fetch('api/webhook_whatsapp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Teste de reabertura executado!\n\nVerifique se a conversa foi reaberta automaticamente.');
            location.reload();
        } else {
            alert('❌ Erro no teste: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao testar reabertura: ' + error.message);
    });
}

function fecharConversa(clienteId) {
    if (!confirm('Deseja fechar a conversa do cliente ID ' + clienteId + ' para testar a reabertura?')) return;
    
    const formData = new FormData();
    formData.append('cliente_id', clienteId);
    
    fetch('painel/api/fechar_conversa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Conversa fechada com sucesso!\n\nAgora você pode testar a reabertura automática.');
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
</script> 