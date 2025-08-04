<?php
/**
 * 🔍 INVESTIGAÇÃO WEBHOOK REAL
 * 
 * Investiga por que mensagens reais do WhatsApp não estão chegando ao webhook
 * enquanto mensagens de teste funcionam
 */

echo "🔍 INVESTIGAÇÃO WEBHOOK REAL\n";
echo "============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. VERIFICAR STATUS DOS CANAIS WHATSAPP
echo "1️⃣ VERIFICANDO STATUS DOS CANAIS WHATSAPP\n";
echo "-----------------------------------------\n";

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
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        echo "  ✅ Conectado (HTTP $http_code)\n";
        if ($data) {
            echo "  📊 Status: " . ($data['status'] ?? 'N/A') . "\n";
            echo "  📱 Sessão: " . ($data['session'] ?? 'N/A') . "\n";
            echo "  🔗 Conectado: " . (isset($data['connected']) && $data['connected'] ? '✅ SIM' : '❌ NÃO') . "\n";
            
            // Verificar se está realmente conectado ao WhatsApp
            if (isset($data['connected']) && $data['connected']) {
                echo "  ✅ WhatsApp conectado\n";
            } else {
                echo "  ❌ WhatsApp NÃO conectado - Este é o problema!\n";
            }
        }
    } else {
        echo "  ❌ Erro (HTTP $http_code): $error\n";
    }
    echo "\n";
}

// 2. VERIFICAR CONFIGURAÇÃO DE WEBHOOK NO VPS
echo "2️⃣ VERIFICANDO CONFIGURAÇÃO DE WEBHOOK NO VPS\n";
echo "---------------------------------------------\n";

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
            echo "  🔗 URL configurada: " . ($config['url'] ?? 'N/A') . "\n";
            echo "  📊 Ativo: " . (isset($config['active']) && $config['active'] ? '✅ SIM' : '❌ NÃO') . "\n";
            
            // Verificar se a URL está correta
            if (($config['url'] ?? '') === $webhook_url) {
                echo "  ✅ URL correta configurada\n";
            } else {
                echo "  ❌ URL incorreta! Configurada: " . ($config['url'] ?? 'N/A') . "\n";
            }
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
    }
    echo "\n";
}

// 3. TESTAR WEBHOOK COM DADOS REAIS DO WHATSAPP
echo "3️⃣ TESTANDO WEBHOOK COM DADOS REAIS DO WHATSAPP\n";
echo "------------------------------------------------\n";

// Dados reais que o WhatsApp envia
$whatsapp_real_data = [
    'event' => 'message',
    'data' => [
        'from' => '554796164699@c.us',
        'to' => '554797146908@c.us',
        'body' => 'TESTE MENSAGEM REAL WHATSAPP - ' . date('Y-m-d H:i:s'),
        'type' => 'text',
        'timestamp' => time(),
        'messageId' => 'test_' . time(),
        'chatId' => '554796164699@c.us',
        'quotedMessageId' => null,
        'isForwarded' => false,
        'isStatus' => false,
        'isGroup' => false
    ]
];

echo "📤 Enviando dados reais do WhatsApp...\n";
echo "  Dados: " . json_encode($whatsapp_real_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($whatsapp_real_data));
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
echo "  Response: $response\n";
if ($error) {
    echo "  Error: $error\n";
}

// 4. VERIFICAR LOGS DO VPS
echo "\n4️⃣ VERIFICANDO LOGS DO VPS\n";
echo "---------------------------\n";

echo "🔍 Para verificar logs do VPS, execute:\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 logs whatsapp-3000 --lines 50\n";
echo "   pm2 logs whatsapp-3001 --lines 50\n\n";

// 5. TESTAR CONECTIVIDADE DO VPS PARA O WEBHOOK
echo "5️⃣ TESTANDO CONECTIVIDADE DO VPS PARA O WEBHOOK\n";
echo "------------------------------------------------\n";

echo "🔍 Testando se o VPS consegue acessar o webhook...\n";

// Simular teste do VPS para o webhook
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

// 6. VERIFICAR SE HÁ PROBLEMAS DE DNS
echo "\n6️⃣ VERIFICANDO PROBLEMAS DE DNS\n";
echo "--------------------------------\n";

$domain = 'app.pixel12digital.com.br';
echo "🔍 Verificando DNS para: $domain\n";

$ip = gethostbyname($domain);
if ($ip !== $domain) {
    echo "  ✅ DNS resolvido: $domain -> $ip\n";
} else {
    echo "  ❌ DNS não resolvido para: $domain\n";
}

// 7. ANÁLISE E RECOMENDAÇÕES
echo "\n7️⃣ ANÁLISE E RECOMENDAÇÕES\n";
echo "---------------------------\n";

echo "🔍 POSSÍVEIS CAUSAS DO PROBLEMA:\n\n";

echo "1. 📱 **WhatsApp não conectado:**\n";
echo "   - Os canais podem estar desconectados do WhatsApp\n";
echo "   - Verificar se há QR Code pendente\n";
echo "   - Verificar se as sessões estão ativas\n\n";

echo "2. 🔗 **Webhook não configurado corretamente:**\n";
echo "   - URL incorreta no VPS\n";
echo "   - Webhook desativado\n";
echo "   - Problema de conectividade\n\n";

echo "3. 🌐 **Problemas de rede:**\n";
echo "   - Firewall bloqueando conexões\n";
echo "   - DNS não resolvendo\n";
echo "   - Timeout nas requisições\n\n";

echo "4. ⚙️ **Problemas de configuração:**\n";
echo "   - Formato de dados incorreto\n";
echo "   - Headers HTTP incorretos\n";
echo "   - Problemas de SSL/TLS\n\n";

echo "🎯 PRÓXIMOS PASSOS RECOMENDADOS:\n\n";

echo "1. 🔄 **Reconectar WhatsApp:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 restart whatsapp-3000 whatsapp-3001\n";
echo "   curl -s http://{$vps_ip}:3000/qr?session=default\n";
echo "   curl -s http://{$vps_ip}:3001/qr?session=comercial\n\n";

echo "2. 🔧 **Reconfigurar webhook:**\n";
echo "   curl -X POST \"http://{$vps_ip}:3000/webhook/config\" \\\n";
echo "        -H \"Content-Type: application/json\" \\\n";
echo "        -d '{\"url\": \"{$webhook_url}\"}'\n\n";

echo "3. 📊 **Verificar logs:**\n";
echo "   ssh root@{$vps_ip} 'pm2 logs whatsapp-3000 --lines 100'\n\n";

echo "4. 🧪 **Testar envio real:**\n";
echo "   Envie uma mensagem para 554797146908 via WhatsApp\n";
echo "   Verifique se aparece nos logs do VPS\n\n";

echo "5. 🌐 **Verificar conectividade:**\n";
echo "   ssh root@{$vps_ip} 'curl -I {$webhook_url}'\n\n";

echo "✅ INVESTIGAÇÃO CONCLUÍDA!\n";
echo "Execute os passos recomendados para resolver o problema.\n";
?> 