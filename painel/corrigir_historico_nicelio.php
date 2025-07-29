<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>🔧 Correção do Histórico da Mensagem</h1>";
echo "<h2>Cliente: JP TRASLADOS LTDA | Nicelio Salustiano dos santos (ID: 145)</h2>";

// 1. Buscar a mensagem agendada #4
echo "<h3>1. Recuperando Mensagem Agendada</h3>";
$sql_agendada = "SELECT * FROM mensagens_agendadas WHERE id = 4";
$result_agendada = $mysqli->query($sql_agendada);

if (!$result_agendada || $result_agendada->num_rows === 0) {
    echo "<p style='color: red;'>❌ Mensagem agendada #4 não encontrada!</p>";
    exit;
}

$msg_agendada = $result_agendada->fetch_assoc();

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
echo "<p><strong>ID:</strong> {$msg_agendada['id']}</p>";
echo "<p><strong>Cliente ID:</strong> {$msg_agendada['cliente_id']}</p>";
echo "<p><strong>Tipo:</strong> {$msg_agendada['tipo']}</p>";
echo "<p><strong>Status:</strong> {$msg_agendada['status']}</p>";
echo "<p><strong>Data Agendada:</strong> {$msg_agendada['data_agendada']}</p>";
echo "<p><strong>Data Atualização:</strong> {$msg_agendada['data_atualizacao']}</p>";
echo "</div>";

// 2. Verificar se já existe no histórico
echo "<h3>2. Verificando Histórico Existente</h3>";
$sql_existe = "SELECT * FROM mensagens_comunicacao 
               WHERE cliente_id = 145 
               AND tipo = 'cobranca_vencida'
               AND data_hora >= '2025-07-29 17:30:00'
               AND data_hora <= '2025-07-29 17:40:00'";
$result_existe = $mysqli->query($sql_existe);

if ($result_existe && $result_existe->num_rows > 0) {
    echo "<p style='color: green;'>✅ Mensagem já existe no histórico!</p>";
    $msg_existente = $result_existe->fetch_assoc();
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>ID:</strong> {$msg_existente['id']}</p>";
    echo "<p><strong>Data/Hora:</strong> {$msg_existente['data_hora']}</p>";
    echo "<p><strong>Status:</strong> {$msg_existente['status']}</p>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ Mensagem não encontrada no histórico. Vou criar o registro...</p>";
    
    // 3. Inserir no histórico
    echo "<h3>3. Inserindo no Histórico</h3>";
    
    $mensagem_escaped = $mysqli->real_escape_string($msg_agendada['mensagem']);
    $tipo_escaped = $mysqli->real_escape_string($msg_agendada['tipo']);
    $data_envio = $msg_agendada['data_atualizacao']; // Usar a data de atualização como data de envio
    
    // Usar canal_id = 36 (Financeiro) em vez de 1
    $sql_insert = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                   VALUES (36, 145, '$mensagem_escaped', '$tipo_escaped', '$data_envio', 'enviado', 'enviado')";
    
    if ($mysqli->query($sql_insert)) {
        $msg_id = $mysqli->insert_id;
        echo "<p style='color: green;'>✅ Mensagem inserida no histórico com sucesso!</p>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px;'>";
        echo "<p><strong>ID da mensagem:</strong> $msg_id</p>";
        echo "<p><strong>Data/Hora:</strong> $data_envio</p>";
        echo "<p><strong>Tipo:</strong> $tipo_escaped</p>";
        echo "<p><strong>Canal:</strong> 36 (Financeiro)</p>";
        echo "<p><strong>Status:</strong> enviado</p>";
        echo "</div>";
        
        // 4. Log da correção
        $log_data = date('Y-m-d H:i:s') . " - CORREÇÃO: Mensagem do Nicelio (ID: 145) inserida no histórico. Mensagem agendada #4, Histórico ID: $msg_id\n";
        file_put_contents('logs/correcao_historico.log', $log_data, FILE_APPEND);
        
    } else {
        echo "<p style='color: red;'>❌ Erro ao inserir no histórico: " . $mysqli->error . "</p>";
    }
}

// 5. Verificar se agora aparece no dashboard
echo "<h3>4. Verificando Dashboard</h3>";
$sql_dashboard = "SELECT 
                    (
                        SELECT MAX(data_hora) 
                        FROM mensagens_comunicacao mc 
                        WHERE mc.cliente_id = 145 
                        AND mc.tipo = 'cobranca_vencida'
                    ) as ultima_mensagem
                  FROM clientes c
                  WHERE c.id = 145";
$result_dashboard = $mysqli->query($sql_dashboard);

if ($result_dashboard && $result_dashboard->num_rows > 0) {
    $dashboard = $result_dashboard->fetch_assoc();
    
    if ($dashboard['ultima_mensagem']) {
        echo "<p style='color: green;'>✅ Última mensagem no dashboard: " . date('d/m/Y H:i', strtotime($dashboard['ultima_mensagem'])) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Ainda não aparece no dashboard</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Erro ao verificar dashboard</p>";
}

// 6. Testar consulta do dashboard
echo "<h3>5. Testando Consulta do Dashboard</h3>";
$sql_test = "SELECT DISTINCT 
                c.id,
                c.nome,
                c.celular,
                c.contact_name,
                cm.monitorado,
                (
                    SELECT MAX(data_hora) 
                    FROM mensagens_comunicacao mc 
                    WHERE mc.cliente_id = c.id 
                    AND mc.tipo = 'cobranca_vencida'
                ) as ultima_mensagem
            FROM clientes c
            JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            WHERE c.id = 145
            AND cm.monitorado = 1";
$result_test = $mysqli->query($sql_test);

if ($result_test && $result_test->num_rows > 0) {
    $test = $result_test->fetch_assoc();
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>Cliente:</strong> {$test['nome']}</p>";
    echo "<p><strong>Monitorado:</strong> " . ($test['monitorado'] ? 'SIM' : 'NÃO') . "</p>";
    echo "<p><strong>Última Mensagem:</strong> " . ($test['ultima_mensagem'] ? date('d/m/Y H:i', strtotime($test['ultima_mensagem'])) : 'Nunca') . "</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Erro na consulta de teste</p>";
}

echo "<hr>";
echo "<h3>🎯 Resumo da Correção</h3>";

if (isset($msg_id)) {
    echo "<p style='color: green;'><strong>✅ CORREÇÃO APLICADA COM SUCESSO!</strong></p>";
    echo "<p><strong>Problema:</strong> Mensagem enviada via WhatsApp mas não registrada no histórico</p>";
    echo "<p><strong>Solução:</strong> Registro criado manualmente no histórico</p>";
    echo "<p><strong>ID da mensagem:</strong> $msg_id</p>";
    echo "<p><strong>Data/Hora:</strong> $data_envio</p>";
    echo "<p><strong>Canal:</strong> 36 (Financeiro)</p>";
    echo "<p><strong>Status:</strong> A mensagem agora deve aparecer corretamente no dashboard</p>";
} else {
    echo "<p style='color: orange;'><strong>ℹ️ VERIFICAÇÃO CONCLUÍDA</strong></p>";
    echo "<p><strong>Status:</strong> Mensagem já estava no histórico ou não foi necessário criar</p>";
}

echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 