<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>🔍 Verificação da Mensagem do Nicelio</h1>";
echo "<h2>Cliente: JP TRASLADOS LTDA | Nicelio Salustiano dos santos (ID: 145)</h2>";

// 1. Verificar se o cliente existe e está monitorado
echo "<h3>1. Status do Cliente</h3>";
$sql_cliente = "SELECT c.*, cm.monitorado 
                FROM clientes c 
                LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id 
                WHERE c.id = 145";
$result_cliente = $mysqli->query($sql_cliente);

if ($result_cliente && $result_cliente->num_rows > 0) {
    $cliente = $result_cliente->fetch_assoc();
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>ID:</strong> {$cliente['id']}</p>";
    echo "<p><strong>Nome:</strong> {$cliente['nome']}</p>";
    echo "<p><strong>Celular:</strong> {$cliente['celular']}</p>";
    echo "<p><strong>Monitorado:</strong> " . ($cliente['monitorado'] ? '✅ SIM' : '❌ NÃO') . "</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Cliente não encontrado!</p>";
    exit;
}

// 2. Verificar mensagens de cobrança vencida para este cliente
echo "<h3>2. Mensagens de Cobrança Vencida</h3>";
$sql_mensagens = "SELECT * FROM mensagens_comunicacao 
                  WHERE cliente_id = 145 
                  AND tipo = 'cobranca_vencida'
                  ORDER BY data_hora DESC";
$result_mensagens = $mysqli->query($sql_mensagens);

if ($result_mensagens && $result_mensagens->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Data/Hora</th><th>Direção</th><th>Status</th><th>Mensagem (primeiros 100 chars)</th></tr>";
    
    while ($msg = $result_mensagens->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['data_hora']}</td>";
        echo "<td>{$msg['direcao']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "<td>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma mensagem de cobrança vencida encontrada!</p>";
}

// 3. Verificar TODAS as mensagens para este cliente
echo "<h3>3. Todas as Mensagens do Cliente</h3>";
$sql_todas = "SELECT * FROM mensagens_comunicacao 
              WHERE cliente_id = 145 
              ORDER BY data_hora DESC 
              LIMIT 10";
$result_todas = $mysqli->query($sql_todas);

if ($result_todas && $result_todas->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Data/Hora</th><th>Tipo</th><th>Direção</th><th>Status</th><th>Mensagem (primeiros 50 chars)</th></tr>";
    
    while ($msg = $result_todas->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['data_hora']}</td>";
        echo "<td>{$msg['tipo']}</td>";
        echo "<td>{$msg['direcao']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "<td>" . htmlspecialchars(substr($msg['mensagem'], 0, 50)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem encontrada para este cliente!</p>";
}

// 4. Verificar mensagens agendadas
echo "<h3>4. Mensagens Agendadas</h3>";
$sql_agendadas = "SELECT * FROM mensagens_agendadas 
                  WHERE cliente_id = 145 
                  ORDER BY data_agendada DESC";
$result_agendadas = $mysqli->query($sql_agendadas);

if ($result_agendadas && $result_agendadas->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Data Agendada</th><th>Tipo</th><th>Status</th><th>Prioridade</th></tr>";
    
    while ($msg = $result_agendadas->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['data_agendada']}</td>";
        echo "<td>{$msg['tipo']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "<td>{$msg['prioridade']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma mensagem agendada encontrada!</p>";
}

// 5. Verificar logs de hoje
echo "<h3>5. Logs de Hoje</h3>";
$log_file = 'logs/monitoramento_clientes.log';
if (file_exists($log_file)) {
    echo "<p><strong>Arquivo:</strong> $log_file</p>";
    
    $content = file_get_contents($log_file);
    $lines = explode("\n", $content);
    $today = date('Y-m-d');
    $found = false;
    
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    foreach ($lines as $line) {
        if (strpos($line, $today) !== false && 
            (strpos($line, '145') !== false || strpos($line, 'Nicelio') !== false || strpos($line, 'JP TRASLADOS') !== false)) {
            echo "<p>" . htmlspecialchars($line) . "</p>";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "<p style='color: orange;'>⚠️ Nenhum log encontrado para hoje relacionado ao Nicelio</p>";
    }
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Arquivo de log não encontrado!</p>";
}

// 6. Verificar se a mensagem foi enviada via WhatsApp
echo "<h3>6. Verificação de Envio WhatsApp</h3>";
$sql_whatsapp = "SELECT * FROM mensagens_comunicacao 
                 WHERE cliente_id = 145 
                 AND direcao = 'enviado'
                 AND data_hora >= CURDATE()
                 ORDER BY data_hora DESC";
$result_whatsapp = $mysqli->query($sql_whatsapp);

if ($result_whatsapp && $result_whatsapp->num_rows > 0) {
    echo "<p style='color: green;'>✅ Mensagens enviadas hoje encontradas:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Data/Hora</th><th>Tipo</th><th>Status</th></tr>";
    
    while ($msg = $result_whatsapp->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['data_hora']}</td>";
        echo "<td>{$msg['tipo']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem enviada hoje encontrada no banco!</p>";
}

// 7. Verificar se há mensagens com tipo diferente
echo "<h3>7. Verificação de Tipos de Mensagem</h3>";
$sql_tipos = "SELECT tipo, COUNT(*) as total 
              FROM mensagens_comunicacao 
              WHERE cliente_id = 145 
              GROUP BY tipo";
$result_tipos = $mysqli->query($sql_tipos);

if ($result_tipos && $result_tipos->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tipo</th><th>Total</th></tr>";
    
    while ($tipo = $result_tipos->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$tipo['tipo']}</td>";
        echo "<td>{$tipo['total']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem encontrada!</p>";
}

echo "<hr>";
echo "<h3>🎯 Conclusão</h3>";
echo "<p><strong>Problema identificado:</strong> A mensagem foi enviada pelo WhatsApp Web, mas não foi registrada no banco de dados com o tipo 'cobranca_vencida'.</p>";
echo "<p><strong>Solução:</strong> Verificar se o sistema de monitoramento está salvando as mensagens corretamente.</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 