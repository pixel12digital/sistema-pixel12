<?php
/**
 * 🧪 TESTE ESPECÍFICO DO WEBHOOK DE PRODUÇÃO
 * 
 * Testa diferentes endpoints do webhook para identificar qual está funcionando
 */

echo "=== 🧪 TESTE ESPECÍFICO DO WEBHOOK DE PRODUÇÃO ===\n\n";

// ===== 1. TESTAR DIFERENTES ENDPOINTS =====
$endpoints = [
    'receber_mensagem.php' => 'https://app.pixel12digital.com.br/painel/receber_mensagem.php',
    'receber_mensagem_ana.php' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana.php',
    'receber_mensagem_ana_local.php' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php',
    'webhook.php' => 'https://app.pixel12digital.com.br/webhook.php',
    'webhook_ana.php' => 'https://app.pixel12digital.com.br/webhook_ana.php'
];

$test_payload = json_encode([
    'from' => '554796164699@c.us',
    'body' => 'Teste de webhook - ' . date('Y-m-d H:i:s'),
    'timestamp' => time(),
    'to' => '554797146908@c.us'
]);

echo "1. 🔍 TESTANDO ENDPOINTS DO WEBHOOK:\n\n";

foreach ($endpoints as $nome => $url) {
    echo "   📡 $nome:\n";
    echo "      URL: $url\n";
    echo "      Status: ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $test_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Pixel12Digital-Webhook-Test/1.0');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    if (!$curl_error && $http_code === 200) {
        echo "✅ FUNCIONANDO (HTTP $http_code, {$total_time}s)\n";
        
        // Tentar decodificar resposta
        $response_data = json_decode($response, true);
        if ($response_data) {
            if (isset($response_data['success'])) {
                echo "      Resposta: " . ($response_data['success'] ? 'SUCCESS' : 'ERROR') . "\n";
            }
            if (isset($response_data['message'])) {
                echo "      Mensagem: " . substr($response_data['message'], 0, 100) . "\n";
            }
        } else {
            echo "      Resposta: " . substr($response, 0, 100) . "\n";
        }
    } else {
        echo "❌ ERRO (HTTP $http_code, {$total_time}s)\n";
        if ($curl_error) {
            echo "      Curl Error: $curl_error\n";
        }
        if ($response) {
            echo "      Resposta: " . substr($response, 0, 200) . "\n";
        }
    }
    echo "\n";
}

// ===== 2. TESTAR API DA ANA COM DIFERENTES URLS =====
echo "2. 🤖 TESTANDO API DA ANA:\n\n";

$ana_urls = [
    'URL Principal' => 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php',
    'URL Alternativa' => 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php',
    'URL Teste' => 'https://agentes.pixel12digital.com.br/api/test.php'
];

$ana_payload = json_encode([
    'question' => 'Teste de conexão',
    'agent_id' => '3'
]);

foreach ($ana_urls as $nome => $url) {
    echo "   📡 $nome:\n";
    echo "      URL: $url\n";
    echo "      Status: ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $ana_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    if (!$curl_error && $http_code === 200) {
        echo "✅ ONLINE (HTTP $http_code, {$total_time}s)\n";
        
        $ana_data = json_decode($response, true);
        if ($ana_data && isset($ana_data['response'])) {
            echo "      Resposta: " . substr($ana_data['response'], 0, 100) . "\n";
        } else {
            echo "      Resposta: " . substr($response, 0, 100) . "\n";
        }
    } else {
        echo "❌ OFFLINE (HTTP $http_code, {$total_time}s)\n";
        if ($curl_error) {
            echo "      Curl Error: $curl_error\n";
        }
    }
    echo "\n";
}

// ===== 3. TESTAR VPS WHATSAPP =====
echo "3. 📱 TESTANDO VPS WHATSAPP:\n\n";

$vps_endpoints = [
    'Status' => 'http://212.85.11.238:3000/status',
    'Info' => 'http://212.85.11.238:3000/info',
    'Health' => 'http://212.85.11.238:3000/health',
    'Root' => 'http://212.85.11.238:3000/'
];

foreach ($vps_endpoints as $nome => $url) {
    echo "   📡 $nome:\n";
    echo "      URL: $url\n";
    echo "      Status: ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    if (!$curl_error && $http_code === 200) {
        echo "✅ ONLINE (HTTP $http_code, {$total_time}s)\n";
        echo "      Resposta: " . substr($response, 0, 100) . "\n";
    } else {
        echo "❌ OFFLINE (HTTP $http_code, {$total_time}s)\n";
        if ($curl_error) {
            echo "      Curl Error: $curl_error\n";
        }
    }
    echo "\n";
}

// ===== 4. TESTE DE ENVIO DE MENSAGEM VIA VPS =====
echo "4. 📤 TESTE DE ENVIO VIA VPS:\n\n";

$vps_send_url = 'http://212.85.11.238:3000/send-message';
$send_payload = json_encode([
    'to' => '554796164699@c.us',
    'message' => 'Teste de envio via VPS - ' . date('Y-m-d H:i:s')
]);

echo "   📡 Enviando mensagem via VPS:\n";
echo "      URL: $vps_send_url\n";
echo "      Status: ";

$ch = curl_init($vps_send_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $send_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

if (!$curl_error && $http_code === 200) {
    echo "✅ ENVIADO (HTTP $http_code, {$total_time}s)\n";
    $send_data = json_decode($response, true);
    if ($send_data) {
        echo "      Resposta: " . json_encode($send_data, JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "❌ ERRO (HTTP $http_code, {$total_time}s)\n";
    if ($curl_error) {
        echo "      Curl Error: $curl_error\n";
    }
    if ($response) {
        echo "      Resposta: " . substr($response, 0, 200) . "\n";
    }
}

echo "\n";

// ===== 5. RESUMO E RECOMENDAÇÕES =====
echo "5. 📊 RESUMO E RECOMENDAÇÕES:\n\n";

echo "   🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Verificar se o VPS está rodando os processos PM2\n";
echo "   2. Confirmar se o webhook está configurado corretamente no VPS\n";
echo "   3. Testar envio de mensagem real para 554797146908\n";
echo "   4. Verificar logs do VPS para identificar problemas\n\n";

echo "   📋 COMANDOS PARA EXECUTAR NO VPS:\n";
echo "   ssh root@212.85.11.238\n";
echo "   pm2 status\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n";
echo "   curl -X POST http://212.85.11.238:3000/webhook/config -H 'Content-Type: application/json' -d '{\"url\":\"https://app.pixel12digital.com.br/painel/receber_mensagem.php\"}'\n\n";

echo "=== FIM DO TESTE ===\n";
?> 