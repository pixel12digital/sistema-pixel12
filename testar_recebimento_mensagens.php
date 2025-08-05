<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔍 Teste de Recebimento de Mensagens</h2>";
echo "<p><strong>Data/Hora do teste:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Cliente ID para teste
$cliente_id = 4296;

echo "<h3>🎯 Testando Recebimento para Cliente ID: $cliente_id</h3>";

// 1. Verificar mensagens recebidas dos canais 3000 e 3001
echo "<h4>1. Verificando Mensagens Recebidas dos Canais 3000 e 3001</h4>";

$sql_recebidas = "SELECT m.id, m.mensagem, m.direcao, m.status, m.data_hora, m.canal_id, m.numero_whatsapp,
                         c.nome_exibicao as canal_nome
                  FROM mensagens_comunicacao m
                  LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                  WHERE m.cliente_id = ? 
                  AND m.direcao = 'recebido'
                  AND (m.canal_id IN (36, 37) OR m.mensagem LIKE '%Teste mensagem recebida%')
                  ORDER BY m.data_hora DESC
                  LIMIT 20";

$stmt = $mysqli->prepare($sql_recebidas);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Canal Nome</th><th>Número WhatsApp</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . (strlen($msg['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: blue;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['canal_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem recebida encontrada!</p>";
}
$stmt->close();

// 2. Verificar todas as mensagens dos canais 3000 e 3001
echo "<h4>2. Verificando Todas as Mensagens dos Canais 3000 e 3001</h4>";

$sql_todas = "SELECT m.id, m.mensagem, m.direcao, m.status, m.data_hora, m.canal_id, m.numero_whatsapp,
                     c.nome_exibicao as canal_nome
              FROM mensagens_comunicacao m
              LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
              WHERE m.cliente_id = ? 
              AND m.canal_id IN (36, 37)
              ORDER BY m.data_hora DESC
              LIMIT 20";

$stmt = $mysqli->prepare($sql_todas);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #FF9800; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Canal Nome</th><th>Número WhatsApp</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        $cor = $msg['direcao'] == 'enviado' ? 'green' : 'blue';
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . (strlen($msg['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: $cor;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['canal_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem dos canais 3000/3001 encontrada!</p>";
}
$stmt->close();

// 3. Verificar se há mensagens específicas que você enviou
echo "<h4>3. Verificando Mensagens Específicas Enviadas</h4>";

$sql_especificas = "SELECT m.id, m.mensagem, m.direcao, m.status, m.data_hora, m.canal_id, m.numero_whatsapp,
                           c.nome_exibicao as canal_nome
                    FROM mensagens_comunicacao m
                    LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                    WHERE m.cliente_id = ? 
                    AND (m.mensagem LIKE '%Teste mensagem recebida de canal 3001%' 
                         OR m.mensagem LIKE '%Teste mensagem recebida de canal 3000%'
                         OR m.mensagem LIKE '%17:45%')
                    ORDER BY m.data_hora DESC";

$stmt = $mysqli->prepare($sql_especificas);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #2196F3; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Canal Nome</th><th>Número WhatsApp</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        $cor = $msg['direcao'] == 'enviado' ? 'green' : 'blue';
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 150)) . (strlen($msg['mensagem']) > 150 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: $cor;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['canal_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem específica encontrada!</p>";
}
$stmt->close();

// 4. Verificar status dos canais
echo "<h4>4. Verificando Status dos Canais</h4>";

$sql_canais = "SELECT id, nome_exibicao, porta, identificador, status, tipo FROM canais_comunicacao WHERE id IN (36, 37)";
$result_canais = $mysqli->query($sql_canais);

if ($result_canais && $result_canais->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #9C27B0; color: white;'>";
    echo "<th>ID</th><th>Nome Exibição</th><th>Porta</th><th>Identificador</th><th>Status</th><th>Tipo</th>";
    echo "</tr>";
    
    while ($canal = $result_canais->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $canal['id'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['nome_exibicao']) . "</td>";
        echo "<td>" . $canal['porta'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['identificador']) . "</td>";
        echo "<td>" . $canal['status'] . "</td>";
        echo "<td>" . $canal['tipo'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Canais não encontrados!</p>";
}

// 5. Criar script para simular recebimento de mensagem
echo "<h4>5. Script para Simular Recebimento de Mensagem</h4>";

$simular_script = "<?php
require_once 'config.php';

// Conectar ao banco
\$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
\$mysqli->set_charset('utf8mb4');

// Simular recebimento de mensagem do canal 3001
\$cliente_id = $cliente_id;
\$numero = '554796164699';
\$mensagem = 'Teste mensagem recebida de canal 3001 554797309525 17:45 - SIMULADA';
\$canal_id = 37; // Canal 3001
\$canal_nome = 'Pixel - Comercial';

// Inserir mensagem recebida
\$sql = \"INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, direcao, data_hora, status, numero_whatsapp, canal_id, canal_nome) 
         VALUES (?, ?, 'text', 'recebido', NOW(), 'nao_lido', ?, ?, ?)\";

\$stmt = \$mysqli->prepare(\$sql);
\$stmt->bind_param('issss', \$cliente_id, \$mensagem, \$numero, \$canal_id, \$canal_nome);

if (\$stmt->execute()) {
    \$mensagem_id = \$mysqli->insert_id;
    echo \"✅ Mensagem recebida simulada criada - ID: \$mensagem_id<br>\";
    
    // Limpar cache
    \$cache_file = __DIR__ . '/cache/' . md5(\"mensagens_{\$cliente_id}\") . '.cache';
    if (file_exists(\$cache_file)) {
        unlink(\$cache_file);
        echo \"✅ Cache limpo<br>\";
    }
} else {
    echo \"❌ Erro ao criar mensagem simulada: \" . \$stmt->error . \"<br>\";
}

\$stmt->close();
\$mysqli->close();

echo \"<p><strong>🎯 Próximos passos:</strong></p>\";
echo \"<ol>\";
echo \"<li>Acesse o chat: <a href='painel/chat.php?cliente_id=\$cliente_id' target='_blank'>Chat do Cliente</a></li>\";
echo \"<li>Recarregue a página (F5)</li>\";
echo \"<li>Verifique se a mensagem simulada aparece</li>\";
echo \"</ol>\";
?>";

file_put_contents('simular_mensagem_recebida.php', $simular_script);
echo "✅ Script de simulação criado: <a href='simular_mensagem_recebida.php' target='_blank'>simular_mensagem_recebida.php</a><br>";

// 6. Verificar se há problemas no webhook
echo "<h4>6. Verificando Webhook</h4>";
echo "<p>ℹ️ Para verificar se o webhook está funcionando:</p>";
echo "<ol>";
echo "<li>Acesse: <a href='api/webhook_whatsapp.php' target='_blank'>Webhook WhatsApp</a></li>";
echo "<li>Verifique se há erros no log</li>";
echo "<li>Teste enviando uma mensagem para o canal 3001</li>";
echo "</ol>";

$mysqli->close();

echo "<h3>🎯 Resumo do Teste de Recebimento</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>🔍 Problema Identificado:</strong></p>";
echo "<p>As mensagens que você está enviando <strong>para</strong> os canais 3000 e 3001 não estão sendo registradas como <strong>recebidas</strong> no sistema.</p>";
echo "<p><strong>🔧 Solução:</strong></p>";
echo "<ol>";
echo "<li>Execute o script de simulação: <a href='simular_mensagem_recebida.php' target='_blank'>simular_mensagem_recebida.php</a></li>";
echo "<li>Verifique se a mensagem simulada aparece no chat</li>";
echo "<li>Se aparecer, o problema está no webhook de recebimento</li>";
echo "<li>Se não aparecer, o problema está na exibição do chat</li>";
echo "</ol>";
echo "</div>";
?> 