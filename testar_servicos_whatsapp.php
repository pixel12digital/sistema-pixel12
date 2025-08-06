<?php
/**
 * 🔧 TESTE COMPLETO DOS SERVIÇOS WHATSAPP
 * 
 * Este script testa todos os serviços WhatsApp e identifica problemas
 */

echo "🔧 TESTE COMPLETO DOS SERVIÇOS WHATSAPP\n";
echo "=======================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

foreach ($portas as $porta) {
    echo "🔍 TESTANDO PORTA $porta\n";
    echo "========================\n";
    
    $vps_url = "http://$vps_ip:$porta";
    
    // 1. Testar status geral
    echo "1️⃣ Testando status geral...\n";
    $ch = curl_init($vps_url . "/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        echo "✅ Status: " . ($data['status'] ?? 'N/A') . "\n";
        echo "✅ Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
        echo "✅ Port: " . ($data['port'] ?? 'N/A') . "\n";
        
        if (isset($data['clients_status'])) {
            foreach ($data['clients_status'] as $session => $status) {
                echo "   📱 Sessão $session:\n";
                echo "      - Ready: " . ($status['ready'] ? 'SIM' : 'NÃO') . "\n";
                echo "      - HasQR: " . ($status['hasQR'] ? 'SIM' : 'NÃO') . "\n";
                echo "      - QR: " . ($status['qr'] ? 'DISPONÍVEL' : 'NÃO') . "\n";
            }
        }
    } else {
        echo "❌ Erro ao verificar status (HTTP: $http_code)\n";
        if ($curl_error) {
            echo "   Curl Error: $curl_error\n";
        }
    }
    
    // 2. Testar endpoint QR
    echo "\n2️⃣ Testando endpoint QR...\n";
    $ch = curl_init($vps_url . "/qr?session=default");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        echo "✅ QR Endpoint: OK\n";
        echo "✅ Success: " . ($data['success'] ? 'SIM' : 'NÃO') . "\n";
        echo "✅ Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
        echo "✅ QR: " . ($data['qr'] ? 'DISPONÍVEL' : 'NÃO') . "\n";
        echo "✅ Message: " . ($data['message'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Erro no endpoint QR (HTTP: $http_code)\n";
        if ($curl_error) {
            echo "   Curl Error: $curl_error\n";
        }
    }
    
    // 3. Testar endpoint de sessões
    echo "\n3️⃣ Testando endpoint de sessões...\n";
    $ch = curl_init($vps_url . "/sessions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        echo "✅ Sessions Endpoint: OK\n";
        echo "✅ Total Sessions: " . ($data['total'] ?? 'N/A') . "\n";
        if (isset($data['sessions'])) {
            foreach ($data['sessions'] as $session) {
                echo "   📱 Sessão: $session\n";
            }
        }
    } else {
        echo "❌ Erro no endpoint sessions (HTTP: $http_code)\n";
        if ($curl_error) {
            echo "   Curl Error: $curl_error\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

// 4. Teste de conexão com webhook
echo "4️⃣ Testando webhook...\n";
$webhook_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";
$test_data = [
    'event' => 'test',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste de conexão - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "✅ Webhook Test: HTTP $http_code\n";
echo "✅ Response: $response\n";

echo "\n🎯 DIAGNÓSTICO FINAL:\n";
echo "====================\n";

// Verificar se há problemas
$problemas = [];

if ($http_code !== 200) {
    $problemas[] = "❌ Webhook não está respondendo corretamente (HTTP: $http_code)";
}

echo "📋 RESUMO DOS PROBLEMAS IDENTIFICADOS:\n";
if (empty($problemas)) {
    echo "✅ Nenhum problema crítico identificado\n";
} else {
    foreach ($problemas as $problema) {
        echo "$problema\n";
    }
}

echo "\n🔧 RECOMENDAÇÕES:\n";
echo "================\n";
echo "1. Verificar se os serviços estão rodando na VPS\n";
echo "2. Reiniciar os serviços se necessário\n";
echo "3. Verificar logs dos serviços\n";
echo "4. Testar conexão manual com o WhatsApp\n";

echo "\n🎯 TESTE CONCLUÍDO!\n";
?> 