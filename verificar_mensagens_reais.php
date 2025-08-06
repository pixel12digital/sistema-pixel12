<?php
require_once 'config.php';

// Conectar ao banco de dados
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("❌ Erro na conexão com o banco: " . $mysqli->connect_error);
}

echo "🔍 VERIFICANDO MENSAGENS REAIS NO BANCO\n";
echo "=======================================\n\n";

// 1. Verificar mensagens das últimas 2 horas
echo "1️⃣ Mensagens das últimas 2 horas:\n";
$sql = "SELECT 
    id,
    canal_id,
    cliente_id,
    numero_whatsapp,
    mensagem,
    tipo,
    data_hora,
    direcao,
    status
    FROM mensagens_comunicacao 
    WHERE data_hora > DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ORDER BY data_hora DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "📥 Total de mensagens: " . $result->num_rows . "\n\n";
    while ($row = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($row['data_hora']));
        $msg = substr($row['mensagem'], 0, 60) . (strlen($row['mensagem']) > 60 ? '...' : '');
        $direcao_icon = $row['direcao'] == 'recebido' ? '📥' : '📤';
        echo "   $hora | $direcao_icon | {$row['numero_whatsapp']} | {$row['status']} | $msg\n";
    }
} else {
    echo "❌ Nenhuma mensagem nas últimas 2 horas\n";
}
echo "\n";

// 2. Verificar logs da Ana das últimas 2 horas
echo "2️⃣ Logs da Ana das últimas 2 horas:\n";
$sql_logs = "SELECT 
    id,
    numero_cliente,
    mensagem_enviada,
    resposta_ana,
    acao_sistema,
    data_log
    FROM logs_integracao_ana 
    WHERE data_log > DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ORDER BY data_log DESC";

$logs_result = $mysqli->query($sql_logs);

if ($logs_result && $logs_result->num_rows > 0) {
    echo "🤖 Total de logs: " . $logs_result->num_rows . "\n\n";
    while ($log = $logs_result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($log['data_log']));
        $acao = $log['acao_sistema'];
        $msg = substr($log['mensagem_enviada'], 0, 40) . (strlen($log['mensagem_enviada']) > 40 ? '...' : '');
        echo "   $hora | $acao | {$log['numero_cliente']} | $msg\n";
    }
} else {
    echo "❌ Nenhum log da Ana nas últimas 2 horas\n";
}
echo "\n";

// 3. Estatísticas de hoje
echo "3️⃣ Estatísticas de hoje:\n";
$sql_hoje = "SELECT 
    direcao,
    COUNT(*) as total
    FROM mensagens_comunicacao 
    WHERE DATE(data_hora) = CURDATE()
    GROUP BY direcao";

$hoje_result = $mysqli->query($sql_hoje);

if ($hoje_result && $hoje_result->num_rows > 0) {
    while ($stat = $hoje_result->fetch_assoc()) {
        $icon = $stat['direcao'] == 'recebido' ? '📥' : '📤';
        echo "   $icon {$stat['direcao']}: {$stat['total']} mensagens\n";
    }
} else {
    echo "❌ Nenhuma mensagem hoje\n";
}
echo "\n";

// 4. Verificar webhook atual no VPS
echo "4️⃣ Verificando webhook no VPS:\n";
$vps_url = "http://212.85.11.238:3000";

$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_response = curl_exec($ch);
$webhook_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($webhook_http == 200) {
    $webhook_config = json_decode($webhook_response, true);
    echo "📡 Webhook atual: " . (isset($webhook_config['webhook']) ? $webhook_config['webhook'] : 'N/A') . "\n";
    
    if (isset($webhook_config['webhook']) && strpos($webhook_config['webhook'], 'webhook_sem_redirect') !== false) {
        echo "✅ Webhook configurado corretamente!\n";
    } else {
        echo "❌ Webhook ainda incorreto!\n";
    }
} else {
    echo "❌ Erro ao verificar webhook\n";
}

echo "\n🎯 DIAGNÓSTICO:\n";
echo "1. Se há mensagens no banco: Sistema funcionando perfeitamente\n";
echo "2. Se não há mensagens: VPS não está enviando webhooks\n";
echo "3. Se Ana responde mas não salva: Problema no webhook\n";

$mysqli->close();
?> 