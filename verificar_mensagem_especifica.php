<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔍 Verificando Mensagem Específica</h2>";
echo "<p><strong>Data/Hora da verificação:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Mensagem específica que você enviou
$mensagem_teste = "Teste de mensagem enviada para canal 3000 554797146908 - 18:04";

echo "<h3>🎯 Procurando mensagem: \"$mensagem_teste\"</h3>";

// 1. Buscar mensagem exata
echo "<h4>1. Buscando Mensagem Exata</h4>";
$sql_exata = "SELECT id, mensagem, direcao, status, data_hora, canal_id, numero_whatsapp, cliente_id 
              FROM mensagens_comunicacao 
              WHERE mensagem = ? 
              ORDER BY data_hora DESC";

$stmt = $mysqli->prepare($sql_exata);
$stmt->bind_param('s', $mensagem_teste);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ <strong>MENSAGEM ENCONTRADA!</strong></p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Número WhatsApp</th><th>Cliente ID</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        $cor = $msg['direcao'] == 'enviado' ? 'green' : 'blue';
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars($msg['mensagem']) . "</td>";
        echo "<td style='font-weight: bold; color: $cor;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "<td>" . $msg['cliente_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ <strong>MENSAGEM NÃO ENCONTRADA!</strong></p>";
}
$stmt->close();

// 2. Buscar mensagens similares (com 18:04)
echo "<h4>2. Buscando Mensagens com '18:04'</h4>";
$sql_similar = "SELECT id, mensagem, direcao, status, data_hora, canal_id, numero_whatsapp, cliente_id 
                FROM mensagens_comunicacao 
                WHERE mensagem LIKE '%18:04%' 
                ORDER BY data_hora DESC 
                LIMIT 10";

$result = $mysqli->query($sql_similar);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #FF9800; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Número WhatsApp</th><th>Cliente ID</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        $cor = $msg['direcao'] == 'enviado' ? 'green' : 'blue';
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars($msg['mensagem']) . "</td>";
        echo "<td style='font-weight: bold; color: $cor;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "<td>" . $msg['cliente_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem com '18:04' encontrada!</p>";
}

// 3. Buscar mensagens recebidas recentes do canal 3000
echo "<h4>3. Buscando Mensagens Recebidas Recentes do Canal 3000</h4>";
$sql_recentes = "SELECT id, mensagem, direcao, status, data_hora, canal_id, numero_whatsapp, cliente_id 
                 FROM mensagens_comunicacao 
                 WHERE canal_id = 36 
                 AND direcao = 'recebido' 
                 AND data_hora >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                 ORDER BY data_hora DESC 
                 LIMIT 10";

$result = $mysqli->query($sql_recentes);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #2196F3; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Número WhatsApp</th><th>Cliente ID</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars($msg['mensagem']) . "</td>";
        echo "<td style='font-weight: bold; color: blue;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "<td>" . $msg['cliente_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem recebida recente do canal 3000!</p>";
}

// 4. Verificar se há problemas no webhook
echo "<h4>4. Verificando Webhook</h4>";
echo "<p>ℹ️ Para verificar se o webhook está funcionando:</p>";
echo "<ol>";
echo "<li>Acesse: <a href='api/webhook_whatsapp.php' target='_blank'>Webhook WhatsApp</a></li>";
echo "<li>Verifique se há erros no log</li>";
echo "<li>Teste enviando uma mensagem para o canal 3000</li>";
echo "</ol>";

// 5. Criar script para forçar recebimento da mensagem específica
echo "<h4>5. Script para Forçar Recebimento da Mensagem Específica</h4>";

$forcar_mensagem_script = "<?php
require_once 'config.php';

// Conectar ao banco
\$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
\$mysqli->set_charset('utf8mb4');

// Forçar recebimento da mensagem específica
\$cliente_id = 4296;
\$numero = '554796164699';
\$mensagem = '$mensagem_teste';
\$canal_id = 36; // Canal 3000
\$canal_nome = 'Pixel12Digital';

// Verificar se já existe
\$sql_check = \"SELECT id FROM mensagens_comunicacao WHERE mensagem = ? AND direcao = 'recebido'\";
\$stmt_check = \$mysqli->prepare(\$sql_check);
\$stmt_check->bind_param('s', \$mensagem);
\$stmt_check->execute();
\$result_check = \$stmt_check->get_result();

if (\$result_check->num_rows > 0) {
    echo \"✅ Mensagem já existe no banco!<br>\";
    \$msg_existente = \$result_check->fetch_assoc();
    echo \"ID: {\$msg_existente['id']}<br>\";
} else {
    // Inserir mensagem recebida
    \$sql = \"INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, direcao, data_hora, status, numero_whatsapp, canal_id, canal_nome) 
             VALUES (?, ?, 'text', 'recebido', NOW(), 'nao_lido', ?, ?, ?)\";
    
    \$stmt = \$mysqli->prepare(\$sql);
    \$stmt->bind_param('issss', \$cliente_id, \$mensagem, \$numero, \$canal_id, \$canal_nome);
    
    if (\$stmt->execute()) {
        \$mensagem_id = \$mysqli->insert_id;
        echo \"✅ Mensagem recebida forçada criada - ID: \$mensagem_id<br>\";
        
        // Limpar cache
        \$cache_file = __DIR__ . '/cache/' . md5(\"mensagens_{\$cliente_id}\") . '.cache';
        if (file_exists(\$cache_file)) {
            unlink(\$cache_file);
            echo \"✅ Cache limpo<br>\";
        }
    } else {
        echo \"❌ Erro ao criar mensagem forçada: \" . \$stmt->error . \"<br>\";
    }
    
    \$stmt->close();
}

\$stmt_check->close();
\$mysqli->close();

echo \"<p><strong>🎯 Próximos passos:</strong></p>\";
echo \"<ol>\";
echo \"<li>Acesse o chat: <a href='painel/chat.php?cliente_id=4296' target='_blank'>Chat do Cliente</a></li>\";
echo \"<li>Recarregue a página (F5)</li>\";
echo \"<li>Verifique se a mensagem específica aparece</li>\";
echo \"</ol>\";
?>";

file_put_contents('forcar_mensagem_especifica.php', $forcar_mensagem_script);
echo "✅ Script para forçar recebimento criado: <a href='forcar_mensagem_especifica.php' target='_blank'>forcar_mensagem_especifica.php</a><br>";

$mysqli->close();

echo "<h3>🎯 Resumo da Verificação</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>🔍 Status da Mensagem:</strong></p>";
echo "<p>Mensagem: \"$mensagem_teste\"</p>";
echo "<p><strong>🎯 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Se a mensagem não foi encontrada, execute: <a href='forcar_mensagem_especifica.php' target='_blank'>forcar_mensagem_especifica.php</a></li>";
echo "<li>Verifique se a mensagem aparece no chat</li>";
echo "<li>Se não aparecer, o problema está no webhook de recebimento</li>";
echo "</ol>";
echo "</div>";
?> 