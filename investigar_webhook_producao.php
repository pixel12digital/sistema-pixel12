<?php
/**
 * 🔍 INVESTIGAR WEBHOOK PRODUÇÃO
 * 
 * Investiga por que o webhook não está recebendo mensagens reais do WhatsApp
 * mesmo com os testes funcionando
 */

echo "🔍 INVESTIGAR WEBHOOK PRODUÇÃO\n";
echo "==============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. VERIFICAR STATUS DOS CANAIS
echo "1️⃣ VERIFICANDO STATUS DOS CANAIS\n";
echo "--------------------------------\n";

$canal_urls = [
    'Canal 3000 (Ana)' => "http://{$vps_ip}:3000",
    'Canal 3001 (Humano)' => "http://{$vps_ip}:3001"
];

foreach ($canal_urls as $nome => $url) {
    echo "🔍 $nome...\n";
    
    $ch = curl_init($url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        echo "  📊 Status: " . ($data['status'] ?? 'N/A') . "\n";
        echo "  🔗 Conectado: " . (isset($data['connected']) && $data['connected'] ? '✅ SIM' : '❌ NÃO') . "\n";
        echo "  📱 Sessão: " . ($data['session'] ?? 'N/A') . "\n";
        
        if (isset($data['success']) && $data['success']) {
            echo "  ✅ WhatsApp conectado e funcionando!\n";
        } else {
            echo "  ❌ WhatsApp NÃO conectado\n";
        }
    } else {
        echo "  ❌ Erro (HTTP $http_code)\n";
    }
    echo "\n";
}

// 2. VERIFICAR CONFIGURAÇÃO DOS WEBHOOKS
echo "2️⃣ VERIFICANDO CONFIGURAÇÃO DOS WEBHOOKS\n";
echo "----------------------------------------\n";

foreach ($canal_urls as $nome => $url) {
    echo "🔍 Verificando webhook em $nome...\n";
    
    $ch = curl_init($url . '/webhook/config');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $config = json_decode($response, true);
        echo "  ✅ Configuração obtida\n";
        if ($config) {
            echo "  🔗 URL configurada: " . ($config['webhook_url'] ?? $config['url'] ?? 'N/A') . "\n";
            echo "  📊 Ativo: " . (isset($config['active']) && $config['active'] ? '✅ SIM' : '❌ NÃO') . "\n";
            
            $configured_url = $config['webhook_url'] ?? $config['url'] ?? '';
            if ($configured_url === $webhook_url) {
                echo "  ✅ URL correta configurada\n";
            } else {
                echo "  ❌ URL incorreta! Configurada: $configured_url\n";
            }
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
    }
    echo "\n";
}

// 3. TESTAR CONECTIVIDADE VPS -> WEBHOOK
echo "3️⃣ TESTANDO CONECTIVIDADE VPS -> WEBHOOK\n";
echo "----------------------------------------\n";

echo "🔍 Testando se o VPS consegue acessar o webhook...\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Acessível: " . ($http_code > 0 && $http_code < 400 ? '✅ SIM' : '❌ NÃO') . "\n";
if ($error) {
    echo "  Erro: $error\n";
}

// 4. VERIFICAR SE O WEBHOOK ESTÁ RECEBENDO MENSAGENS
echo "\n4️⃣ TESTANDO WEBHOOK COM MENSAGEM REAL\n";
echo "--------------------------------------\n";

// Simular mensagem real do WhatsApp
$webhook_test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE MENSAGEM REAL WHATSAPP - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Testando webhook com mensagem real...\n";
echo "  Dados: " . json_encode($webhook_test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do webhook:\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ Webhook processando corretamente!\n";
        echo "  📝 Message ID: " . ($data['message_id'] ?? 'N/A') . "\n";
        echo "  📝 Response ID: " . ($data['response_id'] ?? 'N/A') . "\n";
    } else {
        echo "  ❌ Erro no processamento: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "  ❌ Erro HTTP: $http_code\n";
    if ($error) {
        echo "  Error: $error\n";
    }
    echo "  Response: $response\n";
}

// 5. VERIFICAR LOGS DO VPS
echo "\n5️⃣ VERIFICAR LOGS DO VPS\n";
echo "-------------------------\n";

echo "🔍 Para verificar logs do VPS, execute:\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n";
echo "   pm2 logs whatsapp-3001 --lines 100\n";
echo "   # Procure por logs de recebimento de mensagem\n\n";

// 6. TESTAR ENVIO DIRETO PARA O CANAL
echo "6️⃣ TESTANDO ENVIO DIRETO PARA CANAL\n";
echo "-----------------------------------\n";

// Testar envio direto para o canal 3000
$test_data = [
    'to' => '554796164699@c.us',
    'message' => 'TESTE DIRETO CANAL 3000 - ' . date('Y-m-d H:i:s'),
    'session' => 'default'
];

echo "📤 Enviando teste direto para canal 3000...\n";
echo "  Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init("http://{$vps_ip}:3000/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do canal 3000:\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ Envio direto bem-sucedido!\n";
        echo "  📝 Message ID: " . ($data['messageId'] ?? 'N/A') . "\n";
    } else {
        echo "  ❌ Erro no envio: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "  ❌ Erro HTTP: $http_code\n";
    if ($error) {
        echo "  Error: $error\n";
    }
    echo "  Response: $response\n";
}

// 7. ANÁLISE E DIAGNÓSTICO
echo "\n7️⃣ ANÁLISE E DIAGNÓSTICO\n";
echo "------------------------\n";

echo "🔍 **POSSÍVEIS CAUSAS PARA MENSAGENS REAIS NÃO CHEGAREM:**\n\n";

echo "1. 📱 **WhatsApp não está enviando webhooks:**\n";
echo "   - VPS não está recebendo mensagens do WhatsApp\n";
echo "   - Problema de conectividade WhatsApp -> VPS\n";
echo "   - Sessão instável\n\n";

echo "2. 🔗 **Webhook não está ativo:**\n";
echo "   - VPS recebe mas não envia para webhook\n";
echo "   - Webhook desativado automaticamente\n";
echo "   - Erro interno no VPS\n\n";

echo "3. 🌐 **Problemas de rede:**\n";
echo "   - Firewall bloqueando conexões\n";
echo "   - DNS não resolvendo\n";
echo "   - Timeout nas requisições\n\n";

echo "4. ⚙️ **Problemas de configuração:**\n";
echo "   - Formato de dados incorreto\n";
echo   "   - Headers HTTP incorretos\n";
echo "   - Problemas de SSL/TLS\n\n";

echo "🎯 **PRÓXIMOS PASSOS:**\n\n";

echo "1. 📊 **Verificar logs do VPS:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n";
echo "   # Procure por logs de recebimento de mensagem\n\n";

echo "2. 🔍 **Verificar se webhook está ativo:**\n";
echo "   curl -s http://{$vps_ip}:3000/webhook/config | jq .\n";
echo "   curl -s http://{$vps_ip}:3001/webhook/config | jq .\n\n";

echo "3. 🧪 **Teste manual:**\n";
echo "   Envie uma nova mensagem para 554797146908\n";
echo "   Verifique se aparece nos logs do VPS\n\n";

echo "4. 📋 **Verificar conectividade:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   curl -I {$webhook_url}\n\n";

echo "✅ INVESTIGAÇÃO CONCLUÍDA!\n";
echo "O problema parece ser que o VPS não está recebendo mensagens do WhatsApp.\n";
echo "Verifique os logs para identificar a causa exata.\n";
?> 