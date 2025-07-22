<?php
header('Content-Type: application/json');
require_once 'config.php';

// Configurações
$vps_url = 'http://212.85.11.238:3000';
$webhook_url = 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php';

echo "🧪 Testando configuração do webhook WhatsApp\n\n";

// 1. Verificar se a VPS está acessível
echo "1. 🌐 Testando conectividade com VPS...\n";
$ch = curl_init($vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ VPS acessível (HTTP $http_code)\n";
    $status_data = json_decode($response, true);
    echo "   📊 Status: " . json_encode($status_data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   ❌ VPS não acessível (HTTP $http_code)\n";
    echo "   📝 Resposta: $response\n";
}

echo "\n";

// 2. Verificar configuração atual do webhook
echo "2. 🔗 Verificando configuração atual do webhook...\n";
$ch = curl_init($vps_url . '/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook config acessível\n";
    $webhook_data = json_decode($response, true);
    echo "   🔧 Config atual: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   ❌ Webhook config não acessível (HTTP $http_code)\n";
}

echo "\n";

// 3. Configurar webhook
echo "3. ⚙️ Configurando webhook...\n";
$ch = curl_init($vps_url . '/webhook/config');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook configurado com sucesso\n";
    $config_result = json_decode($response, true);
    echo "   📝 Resultado: " . json_encode($config_result, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   ❌ Erro ao configurar webhook (HTTP $http_code)\n";
    echo "   📝 Resposta: $response\n";
}

echo "\n";

// 4. Testar webhook
echo "4. 🧪 Testando webhook...\n";
$ch = curl_init($vps_url . '/webhook/test');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Teste do webhook executado\n";
    $test_result = json_decode($response, true);
    echo "   📝 Resultado: " . json_encode($test_result, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   ❌ Erro no teste do webhook (HTTP $http_code)\n";
    echo "   📝 Resposta: $response\n";
}

echo "\n";

// 5. Verificar logs
echo "5. 📋 Verificando logs de webhook...\n";
$log_files = [
    'logs/webhook_whatsapp_' . date('Y-m-d') . '.log',
    'api/debug_webhook.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "   📄 Log encontrado: $log_file\n";
        $log_content = file_get_contents($log_file);
        $lines = explode("\n", $log_content);
        $recent_lines = array_slice($lines, -5); // Últimas 5 linhas
        echo "   📝 Últimas linhas:\n";
        foreach ($recent_lines as $line) {
            if (trim($line)) {
                echo "      " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ⚠️ Log não encontrado: $log_file\n";
    }
}

echo "\n";

// 6. Verificar tabelas do banco
echo "6. 🗄️ Verificando tabelas do banco...\n";
require_once 'db.php';

$tables_to_check = [
    'mensagens_comunicacao',
    'canais_comunicacao',
    'clientes'
];

foreach ($tables_to_check as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "   ✅ Tabela '$table' existe\n";
        
        // Verificar últimas mensagens
        if ($table === 'mensagens_comunicacao') {
            $msg_result = $mysqli->query("SELECT COUNT(*) as total FROM $table WHERE direcao = 'recebido' AND DATE(data_hora) = CURDATE()");
            if ($msg_result) {
                $count = $msg_result->fetch_assoc()['total'];
                echo "      📊 Mensagens recebidas hoje: $count\n";
            }
        }
    } else {
        echo "   ❌ Tabela '$table' não existe\n";
    }
}

echo "\n";

// 7. Resumo e próximos passos
echo "7. 📋 Resumo e Próximos Passos\n";
echo "   🔧 Para corrigir problemas de recebimento de mensagens:\n";
echo "   \n";
echo "   1. Certifique-se de que o WhatsApp está conectado\n";
echo "   2. Execute: iniciarSessaoWhatsApp() no console\n";
echo "   3. Conecte o WhatsApp escaneando o QR Code\n";
echo "   4. Teste enviando uma mensagem para o número do robô\n";
echo "   5. Verifique os logs para mensagens recebidas\n";
echo "   \n";
echo "   🔍 URLs importantes:\n";
echo "   - VPS Status: $vps_url/status\n";
echo "   - Webhook Config: $vps_url/webhook/config\n";
echo "   - Webhook URL: $webhook_url\n";
echo "   \n";
echo "   📞 Para testar o recebimento:\n";
echo "   1. Envie uma mensagem WhatsApp para: 554797146908\n";
echo "   2. Verifique se aparece nos logs\n";
echo "   3. Verifique se salva no banco de dados\n";
echo "\n";

echo "🎯 Teste concluído!\n";
?> 