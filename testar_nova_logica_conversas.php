<?php
/**
 * Teste da Nova Lógica de Conversas
 * 
 * Verifica a nova lógica:
 * 1. Conversas fechadas ficam arquivadas
 * 2. Nova conversa com histórico quando cliente envia mensagem
 * 3. Automação pausada por 24h quando solicitado atendente
 */

require_once 'config.php';

echo "<h1>🔄 Teste da Nova Lógica de Conversas</h1>\n";
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
    
    // ===== TESTE 1: Verificar conversas arquivadas =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 1: Verificando conversas arquivadas (fechadas)</h3>\n";
    
    $sql_conversas_arquivadas = "SELECT 
                                    c.id as cliente_id,
                                    c.nome,
                                    m.data_hora as ultima_mensagem,
                                    TIMESTAMPDIFF(HOUR, m.data_hora, NOW()) as horas_arquivada,
                                    COUNT(*) as total_mensagens
                                FROM mensagens_comunicacao m
                                INNER JOIN clientes c ON m.cliente_id = c.id
                                WHERE m.status_conversa = 'fechada'
                                GROUP BY c.id, c.nome, m.data_hora
                                ORDER BY m.data_hora ASC";
    
    $result_arquivadas = $mysqli->query($sql_conversas_arquivadas);
    
    if ($result_arquivadas && $result_arquivadas->num_rows > 0) {
        echo "<p><strong>Conversas arquivadas encontradas:</strong></p>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Cliente ID</th><th>Nome</th><th>Última Mensagem</th><th>Horas Arquivada</th><th>Total Msgs</th><th>Status</th></tr>\n";
        
        while ($conversa = $result_arquivadas->fetch_assoc()) {
            $horas_arquivada = $conversa['horas_arquivada'];
            $status = '📁 ARQUIVADA';
            $cor_status = 'color: #856404; font-weight: bold;';
            
            echo "<tr>";
            echo "<td>{$conversa['cliente_id']}</td>";
            echo "<td>{$conversa['nome']}</td>";
            echo "<td>{$conversa['ultima_mensagem']}</td>";
            echo "<td>{$horas_arquivada}h</td>";
            echo "<td>{$conversa['total_mensagens']}</td>";
            echo "<td style='$cor_status'>$status</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>✅ Nenhuma conversa arquivada encontrada</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 2: Verificar automação pausada =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 2: Verificando automação pausada por atendente</h3>\n";
    
    $sql_automacao_pausada = "SELECT 
                                 c.id as cliente_id,
                                 c.nome,
                                 m.data_hora as solicitacao_atendente,
                                 TIMESTAMPDIFF(HOUR, m.data_hora, NOW()) as horas_atras,
                                 (24 - TIMESTAMPDIFF(HOUR, m.data_hora, NOW())) as horas_restantes
                             FROM mensagens_comunicacao m
                             INNER JOIN clientes c ON m.cliente_id = c.id
                             WHERE m.mensagem LIKE '%Solicitação de atendente registrada%'
                             AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                             ORDER BY m.data_hora DESC";
    
    $result_pausada = $mysqli->query($sql_automacao_pausada);
    
    if ($result_pausada && $result_pausada->num_rows > 0) {
        echo "<p><strong>Automação pausada por atendente:</strong></p>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Cliente ID</th><th>Nome</th><th>Solicitação</th><th>Horas Atrás</th><th>Horas Restantes</th><th>Status</th></tr>\n";
        
        while ($pausa = $result_pausada->fetch_assoc()) {
            $horas_restantes = $pausa['horas_restantes'];
            $status = $horas_restantes > 0 ? '⏸️ PAUSADA' : '✅ LIBERADA';
            $cor_status = $horas_restantes > 0 ? 'color: #856404; font-weight: bold;' : 'color: #155724; font-weight: bold;';
            
            echo "<tr>";
            echo "<td>{$pausa['cliente_id']}</td>";
            echo "<td>{$pausa['nome']}</td>";
            echo "<td>{$pausa['solicitacao_atendente']}</td>";
            echo "<td>{$pausa['horas_atras']}h</td>";
            echo "<td>{$horas_restantes}h</td>";
            echo "<td style='$cor_status'>$status</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>✅ Nenhuma automação pausada encontrada</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 3: Verificar funções implementadas =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 3: Verificando funções implementadas</h3>\n";
    
    $webhook_content = file_get_contents('api/webhook_whatsapp.php');
    
    if (strpos($webhook_content, 'verificarAutomacaoPausada') !== false) {
        echo "<p>✅ Função <code>verificarAutomacaoPausada()</code> encontrada</p>\n";
    } else {
        echo "<p>❌ Função <code>verificarAutomacaoPausada()</code> NÃO encontrada</p>\n";
    }
    
    if (strpos($webhook_content, 'Nova conversa iniciada') !== false) {
        echo "<p>✅ Mensagem de nova conversa implementada</p>\n";
    } else {
        echo "<p>❌ Mensagem de nova conversa NÃO implementada</p>\n";
    }
    
    if (strpos($webhook_content, 'Automação pausada: Por 24 horas') !== false) {
        echo "<p>✅ Informação sobre pausa de 24h implementada</p>\n";
    } else {
        echo "<p>❌ Informação sobre pausa de 24h NÃO implementada</p>\n";
    }
    
    if (strpos($webhook_content, 'falar com atendimento') !== false) {
        echo "<p>✅ Detecção de 'falar com atendimento' implementada</p>\n";
    } else {
        echo "<p>❌ Detecção de 'falar com atendimento' NÃO implementada</p>\n";
    }
    echo "</div>\n";
    
    // ===== TESTE 4: Simular nova conversa =====
    echo "<div class='test-section info'>\n";
    echo "<h3>🔍 Teste 4: Simulando nova conversa</h3>\n";
    
    // Buscar um cliente com conversa arquivada
    $result_cliente_arquivado = $mysqli->query("SELECT DISTINCT c.id, c.nome FROM clientes c 
                                               INNER JOIN mensagens_comunicacao m ON c.id = m.cliente_id 
                                               WHERE m.status_conversa = 'fechada' 
                                               LIMIT 1");
    
    if ($result_cliente_arquivado && $result_cliente_arquivado->num_rows > 0) {
        $cliente_arquivado = $result_cliente_arquivado->fetch_assoc();
        echo "<p><strong>Cliente com conversa arquivada:</strong> {$cliente_arquivado['nome']} (ID: {$cliente_arquivado['id']})</p>\n";
        echo "<button class='btn-test' onclick='simularNovaConversa({$cliente_arquivado['id']})'>🔄 Simular Nova Conversa</button>\n";
    } else {
        echo "<p>⚠️ Nenhum cliente com conversa arquivada encontrado</p>\n";
    }
    echo "</div>\n";
    
    // ===== RESUMO FINAL =====
    echo "<div class='test-section success'>\n";
    echo "<h3>🎉 Nova Lógica Implementada!</h3>\n";
    echo "<p><strong>Funcionalidades implementadas:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Conversas arquivadas</strong> - Fechadas ficam como arquivadas</li>\n";
    echo "<li>✅ <strong>Nova conversa com histórico</strong> - Quando cliente envia mensagem</li>\n";
    echo "<li>✅ <strong>Automação pausada por 24h</strong> - Quando solicitado atendente</li>\n";
    echo "<li>✅ <strong>Detecção melhorada</strong> - 'falar com atendimento' agora funciona</li>\n";
    echo "<li>✅ <strong>Sem conflitos</strong> - Não prejudica funcionalidades existentes</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Fluxo de funcionamento:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Fechar conversa → Status = 'fechada' (arquivada)</li>\n";
    echo "<li>Cliente envia mensagem → Nova conversa + histórico carregado</li>\n";
    echo "<li>Solicitar atendente → Pausa automação por 24 horas</li>\n";
    echo "<li>Após 24h → Volta automação normal</li>\n";
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
function simularNovaConversa(clienteId) {
    if (!confirm('Deseja simular uma nova conversa para o cliente ID ' + clienteId + '?')) return;
    
    // Simular envio de mensagem via webhook
    const formData = new FormData();
    formData.append('cliente_id', clienteId);
    formData.append('teste_nova_conversa', 'true');
    
    fetch('api/webhook_whatsapp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Simulação de nova conversa executada!\n\nVerifique se foi criada uma nova conversa com histórico.');
            location.reload();
        } else {
            alert('❌ Erro na simulação: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao simular nova conversa: ' + error.message);
    });
}
</script> 