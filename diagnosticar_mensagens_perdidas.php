<?php
/**
 * DIAGNÓSTICO DE MENSAGENS PERDIDAS
 * 
 * Verifica por que algumas mensagens não aparecem no chat
 */

echo "🔍 DIAGNÓSTICO DE MENSAGENS PERDIDAS\n";
echo "=====================================\n\n";

// Conectar ao banco
$host = 'localhost';
$username = 'u342734079_revendaweb';
$password = 'Revenda@2024';
$database = 'u342734079_revendaweb';

$mysqli = new mysqli($host, $username, $password, $database);
if ($mysqli->connect_error) {
    die("❌ Erro na conexão: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

echo "📊 1. VERIFICANDO MENSAGENS ESPECÍFICAS\n";
echo "----------------------------------------\n";

// Verificar mensagens específicas que não apareceram
$mensagens_problema = [
    'boa tarde' => ['17:03', '17:44'],
    'oi' => ['17:42'],
    'oie' => ['16:06'] // Esta deveria estar funcionando
];

foreach ($mensagens_problema as $texto => $horarios) {
    foreach ($horarios as $horario) {
        $data = '2025-07-28 ' . $horario . ':00';
        $sql = "SELECT * FROM mensagens_comunicacao 
                WHERE texto LIKE '%$texto%' 
                AND data_hora >= '$data' 
                AND data_hora <= DATE_ADD('$data', INTERVAL 1 MINUTE)";
        
        $result = $mysqli->query($sql);
        if ($result->num_rows > 0) {
            echo "✅ '$texto' ($horario): ENCONTRADA no banco\n";
        } else {
            echo "❌ '$texto' ($horario): NÃO ENCONTRADA no banco\n";
        }
    }
}

echo "\n📋 2. VERIFICANDO LOGS DO WEBHOOK\n";
echo "-----------------------------------\n";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    echo "📁 Log encontrado: $log_file\n";
    echo "📏 Tamanho: " . formatBytes(filesize($log_file)) . "\n\n";
    
    // Procurar por mensagens específicas nos logs
    $logs = file($log_file);
    $total_logs = count($logs);
    echo "📊 Total de linhas no log: $total_logs\n\n";
    
    // Procurar por mensagens perdidas
    $mensagens_encontradas = [];
    foreach ($logs as $linha) {
        if (strpos($linha, 'boa tarde') !== false || strpos($linha, 'oi') !== false) {
            $mensagens_encontradas[] = trim($linha);
        }
    }
    
    if (count($mensagens_encontradas) > 0) {
        echo "🔍 Mensagens encontradas nos logs:\n";
        foreach ($mensagens_encontradas as $msg) {
            echo "   • $msg\n";
        }
    } else {
        echo "❌ Nenhuma mensagem 'boa tarde' ou 'oi' encontrada nos logs\n";
    }
    
    // Verificar últimas 10 linhas do log
    echo "\n📝 Últimas 10 linhas do log:\n";
    $ultimas_linhas = array_slice($logs, -10);
    foreach ($ultimas_linhas as $linha) {
        echo "   " . trim($linha) . "\n";
    }
    
} else {
    echo "❌ Arquivo de log não encontrado: $log_file\n";
}

echo "\n🌐 3. TESTANDO WEBHOOK\n";
echo "----------------------\n";

$webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';

// Teste simples de conectividade
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erro na conexão: $error\n";
} else {
    echo "✅ Webhook responde: HTTP $http_code\n";
}

// Teste com mensagem real
echo "\n🧪 Enviando mensagem de teste...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste diagnóstico às ' . date('H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📤 Resposta do webhook: HTTP $http_code\n";
echo "📄 Conteúdo: " . substr($response, 0, 200) . "...\n";

// Verificar se a mensagem de teste foi salva
sleep(2); // Aguarda 2 segundos
$sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE texto LIKE '%Teste diagnóstico%' AND data_hora >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo "✅ Mensagem de teste salva no banco\n";
} else {
    echo "❌ Mensagem de teste NÃO foi salva no banco\n";
}

echo "\n📈 4. ESTATÍSTICAS GERAIS\n";
echo "--------------------------\n";

// Mensagens hoje
$sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
echo "📊 Mensagens hoje: {$row['total']}\n";

// Últimas mensagens
$sql = "SELECT texto, data_hora, numero_whatsapp FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5";
$result = $mysqli->query($sql);
echo "📝 Últimas 5 mensagens:\n";
while ($row = $result->fetch_assoc()) {
    echo "   • " . date('H:i', strtotime($row['data_hora'])) . " - {$row['texto']} ({$row['numero_whatsapp']})\n";
}

// Verificar mensagens com numero_whatsapp N/A
$sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = 'N/A' OR numero_whatsapp IS NULL";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
echo "⚠️ Mensagens com numero_whatsapp N/A: {$row['total']}\n";

echo "\n🔧 5. RECOMENDAÇÕES\n";
echo "-------------------\n";

echo "1. 📡 Verificar configuração do webhook no WhatsApp Business API\n";
echo "2. 🔄 Testar conectividade com o servidor WhatsApp\n";
echo "3. 📊 Monitorar logs em tempo real\n";
echo "4. ⚡ Usar o sistema de ações rápidas nas configurações\n";
echo "5. 🔍 Verificar se há rate limiting ou timeouts\n";

echo "\n✅ DIAGNÓSTICO CONCLUÍDO\n";
echo "========================\n";

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 