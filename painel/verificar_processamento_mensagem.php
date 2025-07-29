<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>🔍 Verificação do Processamento da Mensagem</h1>";
echo "<h2>Cliente: JP TRASLADOS LTDA | Nicelio Salustiano dos santos (ID: 145)</h2>";

// 1. Verificar mensagem agendada #4
echo "<h3>1. Mensagem Agendada #4</h3>";
$sql_agendada = "SELECT * FROM mensagens_agendadas WHERE id = 4";
$result_agendada = $mysqli->query($sql_agendada);

if ($result_agendada && $result_agendada->num_rows > 0) {
    $msg_agendada = $result_agendada->fetch_assoc();
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>ID:</strong> {$msg_agendada['id']}</p>";
    echo "<p><strong>Cliente ID:</strong> {$msg_agendada['cliente_id']}</p>";
    echo "<p><strong>Tipo:</strong> {$msg_agendada['tipo']}</p>";
    echo "<p><strong>Status:</strong> {$msg_agendada['status']}</p>";
    echo "<p><strong>Data Agendada:</strong> {$msg_agendada['data_agendada']}</p>";
    echo "<p><strong>Data Criação:</strong> {$msg_agendada['data_criacao']}</p>";
    echo "<p><strong>Data Atualização:</strong> {$msg_agendada['data_atualizacao']}</p>";
    echo "<p><strong>Prioridade:</strong> {$msg_agendada['prioridade']}</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Mensagem agendada #4 não encontrada!</p>";
}

// 2. Verificar logs de processamento
echo "<h3>2. Logs de Processamento</h3>";
$log_file = '../logs/processamento_agendadas.log';
if (file_exists($log_file)) {
    echo "<p><strong>Arquivo:</strong> $log_file</p>";
    
    $content = file_get_contents($log_file);
    $lines = explode("\n", $content);
    $today = date('Y-m-d');
    $found = false;
    
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    foreach ($lines as $line) {
        if (strpos($line, $today) !== false && 
            (strpos($line, '4') !== false || strpos($line, '145') !== false || strpos($line, 'Nicelio') !== false || strpos($line, 'JP TRASLADOS') !== false)) {
            echo "<p>" . htmlspecialchars($line) . "</p>";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "<p style='color: orange;'>⚠️ Nenhum log de processamento encontrado para hoje relacionado ao Nicelio</p>";
    }
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Arquivo de log não encontrado!</p>";
}

// 3. Verificar se a mensagem foi salva no histórico
echo "<h3>3. Histórico de Mensagens (mensagens_comunicacao)</h3>";
$sql_historico = "SELECT * FROM mensagens_comunicacao 
                  WHERE cliente_id = 145 
                  AND tipo = 'cobranca_vencida'
                  AND data_hora >= CURDATE()
                  ORDER BY data_hora DESC";
$result_historico = $mysqli->query($sql_historico);

if ($result_historico && $result_historico->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Data/Hora</th><th>Canal</th><th>Direção</th><th>Status</th><th>Mensagem (primeiros 50 chars)</th></tr>";
    
    while ($msg = $result_historico->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['data_hora']}</td>";
        echo "<td>{$msg['canal_id']}</td>";
        echo "<td>{$msg['direcao']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "<td>" . htmlspecialchars(substr($msg['mensagem'], 0, 50)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem encontrada no histórico!</p>";
}

// 4. Verificar se o cron job foi executado hoje
echo "<h3>4. Execução do Cron Job</h3>";
$sql_cron = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
             WHERE tipo = 'cobranca_vencida' 
             AND data_hora >= CURDATE()";
$result_cron = $mysqli->query($sql_cron);
$total_cron = $result_cron->fetch_assoc()['total'];

echo "<p><strong>Total de mensagens de cobrança vencida enviadas hoje:</strong> $total_cron</p>";

// 5. Verificar última execução do cron
$sql_ultima = "SELECT MAX(data_hora) as ultima FROM mensagens_comunicacao 
               WHERE tipo = 'cobranca_vencida'";
$result_ultima = $mysqli->query($sql_ultima);
$ultima = $result_ultima->fetch_assoc()['ultima'];

if ($ultima) {
    echo "<p><strong>Última execução:</strong> $ultima</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma execução encontrada</p>";
}

// 6. Verificar se há mensagens agendadas para hoje
echo "<h3>5. Mensagens Agendadas para Hoje</h3>";
$sql_hoje = "SELECT * FROM mensagens_agendadas 
             WHERE DATE(data_agendada) = CURDATE()
             ORDER BY data_agendada ASC";
$result_hoje = $mysqli->query($sql_hoje);

if ($result_hoje && $result_hoje->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Cliente ID</th><th>Tipo</th><th>Status</th><th>Data Agendada</th><th>Prioridade</th></tr>";
    
    while ($msg = $result_hoje->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['cliente_id']}</td>";
        echo "<td>{$msg['tipo']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "<td>{$msg['data_agendada']}</td>";
        echo "<td>{$msg['prioridade']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma mensagem agendada para hoje</p>";
}

// 7. Verificar se o cron job está funcionando
echo "<h3>6. Status do Cron Job</h3>";
$cron_file = '../cron/processar_mensagens_agendadas.php';
if (file_exists($cron_file)) {
    echo "<p style='color: green;'>✅ Arquivo do cron job existe</p>";
    
    // Verificar se foi executado recentemente
    $file_time = filemtime($cron_file);
    $file_date = date('Y-m-d H:i:s', $file_time);
    echo "<p><strong>Última modificação do arquivo:</strong> $file_date</p>";
} else {
    echo "<p style='color: red;'>❌ Arquivo do cron job não encontrado</p>";
}

echo "<hr>";
echo "<h3>🎯 Análise do Problema</h3>";

if ($msg_agendada['status'] === 'enviada' && $total_cron == 0) {
    echo "<p style='color: red;'><strong>PROBLEMA IDENTIFICADO:</strong> A mensagem foi marcada como 'enviada' na tabela de agendamento, mas não foi salva no histórico de mensagens.</p>";
    echo "<p><strong>Causa provável:</strong> O cron job processou a mensagem e enviou via WhatsApp, mas falhou ao salvar no histórico.</p>";
} elseif ($msg_agendada['status'] === 'agendada') {
    echo "<p style='color: orange;'><strong>PROBLEMA IDENTIFICADO:</strong> A mensagem ainda está agendada e não foi processada pelo cron job.</p>";
    echo "<p><strong>Causa provável:</strong> O cron job não está sendo executado ou não está processando mensagens agendadas.</p>";
} else {
    echo "<p style='color: green;'><strong>STATUS:</strong> Sistema funcionando normalmente.</p>";
}

echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 