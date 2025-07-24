<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
require_once 'db.php';

echo "📱 Monitorando mensagens WhatsApp\n\n";

// 1. Verificar mensagens recebidas hoje
echo "1. 📊 Mensagens recebidas hoje:\n";
$result = $mysqli->query("
    SELECT m.*, c.nome as cliente_nome, ch.nome_exibicao as canal_nome
    FROM mensagens_comunicacao m
    LEFT JOIN clientes c ON m.cliente_id = c.id
    LEFT JOIN canais_comunicacao ch ON m.canal_id = ch.id
    WHERE m.direcao = 'recebido' 
    AND DATE(m.data_hora) = CURDATE()
    ORDER BY m.data_hora DESC
    LIMIT 10
");

if ($result && $result->num_rows > 0) {
    while ($msg = $result->fetch_assoc()) {
        $cliente = $msg['cliente_nome'] ?? 'Cliente não identificado';
        $canal = $msg['canal_nome'] ?? 'Canal ' . $msg['canal_id'];
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        
        echo "   📥 [$hora] $cliente ($canal): " . substr($msg['mensagem'], 0, 50) . "...\n";
        echo "      ID: {$msg['id']} | Status: {$msg['status']}\n\n";
    }
} else {
    echo "   ⚠️ Nenhuma mensagem recebida hoje\n\n";
}

// 2. Verificar mensagens pendentes (sem cliente)
echo "2. 📋 Mensagens pendentes (sem cliente associado):\n";
$result = $mysqli->query("
    SELECT * FROM mensagens_pendentes
    WHERE DATE(data_hora) = CURDATE()
    ORDER BY data_hora DESC
    LIMIT 5
");

if ($result && $result->num_rows > 0) {
    while ($msg = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        echo "   📝 [$hora] {$msg['numero']}: " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
} else {
    echo "   ✅ Nenhuma mensagem pendente\n";
}

echo "\n";

// 3. Verificar status dos canais
echo "3. 📡 Status dos canais WhatsApp:\n";
$result = $mysqli->query("
    SELECT * FROM canais_comunicacao 
    WHERE tipo = 'whatsapp' 
    ORDER BY id
");

if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   $status_icon Canal {$canal['id']} ({$canal['nome_exibicao']}): {$canal['status']}\n";
        echo "      Identificador: {$canal['identificador']}\n";
        echo "      Porta: {$canal['porta']}\n";
        
        // Verificar mensagens deste canal hoje
        $msg_count = $mysqli->query("
            SELECT COUNT(*) as total 
            FROM mensagens_comunicacao 
            WHERE canal_id = {$canal['id']} 
            AND DATE(data_hora) = CURDATE()
        ")->fetch_assoc()['total'];
        
        echo "      Mensagens hoje: $msg_count\n\n";
    }
} else {
    echo "   ⚠️ Nenhum canal WhatsApp configurado\n\n";
}

// 4. Verificar últimas atividades do webhook
echo "4. 📋 Últimas atividades do webhook:\n";
$log_files = [
    'logs/webhook_whatsapp_' . date('Y-m-d') . '.log',
    '../api/debug_webhook.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "   📄 Analisando: $log_file\n";
        $content = file_get_contents($log_file);
        $lines = explode("\n", $content);
        $recent_lines = array_slice($lines, -10);
        
        foreach ($recent_lines as $line) {
            if (trim($line) && strpos($line, date('Y-m-d')) !== false) {
                echo "      " . trim($line) . "\n";
            }
        }
        echo "\n";
    }
}

// 5. Testar conectividade do WhatsApp
echo "5. 🔍 Testando conectividade WhatsApp:\n";
$vps_url = 'http://212.85.11.238:3000';

$ch = curl_init($vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $status = json_decode($response, true);
    $connected = $status['clients_status']['default']['status'] ?? 'unknown';
    $icon = $connected === 'connected' ? '🟢' : '🔴';
    echo "   $icon Status: $connected\n";
    
    if ($connected === 'connected') {
        echo "   ✅ WhatsApp está conectado e deve receber mensagens\n";
    } else {
        echo "   ⚠️ WhatsApp não está conectado - mensagens não serão recebidas\n";
    }
} else {
    echo "   ❌ Não foi possível conectar com o servidor WhatsApp\n";
}

echo "\n";

// 6. Diagnóstico e soluções
echo "6. 🔧 Diagnóstico e Soluções:\n";

// Verificar se há clientes sem celular
$clientes_sem_celular = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM clientes 
    WHERE (celular IS NULL OR celular = '' OR telefone IS NULL OR telefone = '')
")->fetch_assoc()['total'];

if ($clientes_sem_celular > 0) {
    echo "   ⚠️ $clientes_sem_celular clientes sem número de celular cadastrado\n";
}

// Verificar webhook
$webhook_working = file_exists('../api/webhook_whatsapp.php');
echo "   " . ($webhook_working ? '✅' : '❌') . " Webhook WhatsApp " . ($webhook_working ? 'existe' : 'não encontrado') . "\n";

// Verificar cache
$cache_files = glob('cache/*.cache');
if (count($cache_files) > 0) {
    echo "   🗄️ " . count($cache_files) . " arquivos de cache encontrados\n";
    echo "      Para limpar cache: rm cache/*.cache\n";
}

echo "\n";

echo "7. 📞 Como testar o recebimento de mensagens:\n";
echo "   1. Certifique-se de que o WhatsApp está conectado (status acima)\n";
echo "   2. Envie uma mensagem para: 554797146908\n";
echo "   3. Execute este script novamente para ver se a mensagem foi recebida\n";
echo "   4. Verifique o chat em: http://localhost:8080/loja-virtual-revenda/painel/chat.php\n";
echo "\n";

echo "🎯 Monitoramento concluído!\n";
?> 