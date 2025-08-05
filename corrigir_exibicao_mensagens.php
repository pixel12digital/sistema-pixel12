<?php
require_once 'config.php';
require_once 'painel/db.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔧 Correção da Exibição de Mensagens</h2>";
echo "<p><strong>Data/Hora da correção:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Cliente ID para teste
$cliente_id = 4296;

echo "<h3>🎯 Corrigindo Exibição para Cliente ID: $cliente_id</h3>";

// 1. Limpar cache das mensagens
echo "<h4>1. Limpando Cache das Mensagens</h4>";
$cache_file = __DIR__ . '/cache/' . md5("mensagens_{$cliente_id}") . '.cache';
if (file_exists($cache_file)) {
    unlink($cache_file);
    echo "✅ Cache das mensagens removido: $cache_file<br>";
} else {
    echo "ℹ️ Cache das mensagens não encontrado<br>";
}

// 2. Verificar se as mensagens estão no banco
echo "<h4>2. Verificando Mensagens no Banco</h4>";
$sql_check = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE cliente_id = ?";
$stmt = $mysqli->prepare($sql_check);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc()['total'];
$stmt->close();

echo "✅ Total de mensagens encontradas: $total<br>";

// 3. Verificar mensagens específicas dos canais 3000 e 3001
echo "<h4>3. Verificando Mensagens dos Canais 3000 e 3001</h4>";
$sql_especificas = "SELECT id, mensagem, direcao, status, data_hora, canal_id 
                    FROM mensagens_comunicacao 
                    WHERE cliente_id = ? 
                    AND (mensagem LIKE '%Teste mensagem enviada de canal 3000%' 
                         OR mensagem LIKE '%Teste mensagem enviada de canal 3001%'
                         OR canal_id IN (36, 37))
                    ORDER BY data_hora DESC";

$stmt = $mysqli->prepare($sql_especificas);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . (strlen($msg['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: " . ($msg['direcao'] == 'enviado' ? 'green' : 'blue') . ";'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Nenhuma mensagem específica encontrada<br>";
}
$stmt->close();

// 4. Forçar atualização do cache
echo "<h4>4. Forçando Atualização do Cache</h4>";

// Simular a função cache_remember para mensagens
$sql_mensagens = "SELECT m.*, 'WhatsApp' as canal_nome
                  FROM mensagens_comunicacao m
                  WHERE m.cliente_id = ?
                  ORDER BY m.data_hora ASC";

$stmt = $mysqli->prepare($sql_mensagens);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

$mensagens = [];
while ($msg = $result->fetch_assoc()) {
    $mensagens[] = $msg;
}
$stmt->close();

// Salvar no cache
$cache_data = [
    'data' => $mensagens,
    'timestamp' => time()
];

$cache_file = __DIR__ . '/cache/' . md5("mensagens_{$cliente_id}") . '.cache';
file_put_contents($cache_file, serialize($cache_data));

echo "✅ Cache atualizado com " . count($mensagens) . " mensagens<br>";

// 5. Verificar se há problemas na tabela canais_comunicacao
echo "<h4>5. Verificando Tabela canais_comunicacao</h4>";
$sql_canais = "SELECT id, nome_exibicao, porta, identificador, status FROM canais_comunicacao WHERE id IN (36, 37)";
$result_canais = $mysqli->query($sql_canais);

if ($result_canais && $result_canais->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #FF9800; color: white;'>";
    echo "<th>ID</th><th>Nome Exibição</th><th>Porta</th><th>Identificador</th><th>Status</th>";
    echo "</tr>";
    
    while ($canal = $result_canais->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $canal['id'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['nome_exibicao']) . "</td>";
        echo "<td>" . $canal['porta'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['identificador']) . "</td>";
        echo "<td>" . $canal['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Canais não encontrados<br>";
}

// 6. Criar script de teste para verificar se as mensagens aparecem
echo "<h4>6. Script de Teste Criado</h4>";
$test_script = "<?php
require_once 'config.php';

// Conectar ao banco
\$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
\$mysqli->set_charset('utf8mb4');

// Cliente ID para teste
\$cliente_id = $cliente_id;

// Consulta exata do api/mensagens_cliente.php
\$sql = \"SELECT m.*, 
               c.nome_exibicao as canal_nome,
               c.porta as canal_porta,
               c.identificador as canal_identificador,
               CASE 
                   WHEN m.direcao = 'enviado' THEN 'Você'
                   WHEN m.direcao = 'recebido' THEN c.nome_exibicao
                   ELSE 'Sistema'
               END as contato_interagiu
        FROM mensagens_comunicacao m
        LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
        WHERE m.cliente_id = ?
        ORDER BY m.data_hora ASC\";

\$stmt = \$mysqli->prepare(\$sql);
\$stmt->bind_param('i', \$cliente_id);
\$stmt->execute();
\$result = \$stmt->get_result();

\$mensagens = [];
while (\$msg = \$result->fetch_assoc()) {
    \$mensagens[] = [
        'id' => \$msg['id'],
        'mensagem' => \$msg['mensagem'],
        'direcao' => \$msg['direcao'],
        'status' => \$msg['status'],
        'data_hora' => \$msg['data_hora'],
        'canal_nome' => \$msg['canal_nome'] ?: 'WhatsApp',
        'contato_interagiu' => \$msg['contato_interagiu'] ?: 'Sistema'
    ];
}
\$stmt->close();

echo \"<h3>Teste de Consulta - Cliente ID: \$cliente_id</h3>\";
echo \"<p>Total de mensagens: \" . count(\$mensagens) . \"</p>\";

if (!empty(\$mensagens)) {
    echo \"<table border='1' style='border-collapse: collapse; width: 100%;'>\";
    echo \"<tr style='background-color: #4CAF50; color: white;'>\";
    echo \"<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal</th>\";
    echo \"</tr>\";
    
    foreach (\$mensagens as \$msg) {
        echo \"<tr>\";
        echo \"<td>\" . \$msg['id'] . \"</td>\";
        echo \"<td>\" . htmlspecialchars(substr(\$msg['mensagem'], 0, 100)) . (strlen(\$msg['mensagem']) > 100 ? '...' : '') . \"</td>\";
        echo \"<td>\" . \$msg['direcao'] . \"</td>\";
        echo \"<td>\" . \$msg['status'] . \"</td>\";
        echo \"<td>\" . \$msg['data_hora'] . \"</td>\";
        echo \"<td>\" . htmlspecialchars(\$msg['canal_nome']) . \"</td>\";
        echo \"</tr>\";
    }
    echo \"</table>\";
} else {
    echo \"<p style='color: red;'>Nenhuma mensagem encontrada!</p>\";
}

\$mysqli->close();
?>";

file_put_contents('teste_mensagens_final.php', $test_script);
echo "✅ Script de teste criado: teste_mensagens_final.php<br>";

// 7. Verificar se há problemas no JavaScript
echo "<h4>7. Verificando JavaScript do Chat</h4>";
echo "ℹ️ Para verificar se as mensagens aparecem no chat:<br>";
echo "1. Acesse: <a href='painel/chat.php?cliente_id=$cliente_id' target='_blank'>Chat do Cliente</a><br>";
echo "2. Abra o console do navegador (F12)<br>";
echo "3. Verifique se há erros JavaScript<br>";
echo "4. Recarregue a página (F5)<br>";

// 8. Criar script para forçar atualização
echo "<h4>8. Script de Força Atualização Criado</h4>";
$force_update_script = "<?php
// Script para forçar atualização das mensagens
require_once 'config.php';

\$cliente_id = $cliente_id;

// Limpar cache
\$cache_file = __DIR__ . '/cache/' . md5(\"mensagens_{\$cliente_id}\") . '.cache';
if (file_exists(\$cache_file)) {
    unlink(\$cache_file);
    echo \"Cache removido<br>\";
}

// Forçar atualização
echo \"<script>
    if (typeof carregarMensagensCliente === 'function') {
        carregarMensagensCliente($cliente_id, true);
        console.log('Mensagens recarregadas forçadamente');
    } else {
        console.log('Função carregarMensagensCliente não encontrada');
    }
</script>\";

echo \"✅ Força atualização aplicada para cliente ID: \$cliente_id<br>\";
?>";

file_put_contents('forcar_atualizacao_mensagens.php', $force_update_script);
echo "✅ Script de força atualização criado: forcar_atualizacao_mensagens.php<br>";

$mysqli->close();

echo "<h3>🎯 Resumo da Correção</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>✅ Cache limpo e atualizado</strong></p>";
echo "<p><strong>✅ Mensagens verificadas no banco</strong></p>";
echo "<p><strong>✅ Scripts de teste criados</strong></p>";
echo "<p><strong>✅ Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Acesse o chat: <a href='painel/chat.php?cliente_id=$cliente_id' target='_blank'>Chat do Cliente</a></li>";
echo "<li>Recarregue a página (F5)</li>";
echo "<li>Verifique se as mensagens aparecem</li>";
echo "<li>Se não aparecerem, execute: <a href='forcar_atualizacao_mensagens.php' target='_blank'>Força Atualização</a></li>";
echo "</ol>";
echo "</div>";
?> 