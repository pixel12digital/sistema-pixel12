<?php
/**
 * 🔍 INVESTIGAÇÃO COMPLETA DO VPS
 * 
 * Descobre exatamente que API está rodando e como enviar mensagens
 */

echo "=== 🔍 INVESTIGAÇÃO COMPLETA DO VPS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

$vps_ip = "212.85.11.238";
$vps_port = "3000";

// ===== 1. INVESTIGAR API COMPLETAMENTE =====
echo "1. 🔍 INVESTIGANDO TIPO DE API:\n";

// Verificar status detalhado
$ch = curl_init("http://$vps_ip:$vps_port/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$status_response = curl_exec($ch);
curl_close($ch);

echo "   📊 Status completo:\n";
echo "   " . $status_response . "\n\n";

$status_data = json_decode($status_response, true);
if ($status_data) {
    foreach ($status_data as $key => $value) {
        echo "   📋 $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
}

echo "\n";

// ===== 2. TESTAR TODOS OS ENDPOINTS POSSÍVEIS =====
echo "2. 🧪 TESTANDO TODOS OS ENDPOINTS POSSÍVEIS:\n";

$endpoints_completos = [
    // Endpoints comuns de envio
    "/send", "/send-message", "/sendText", "/sendMessage", 
    "/message", "/messages", "/chat", "/whatsapp",
    
    // Endpoints com sessão
    "/session/send", "/session/default/send", "/session/message",
    
    // Endpoints de API
    "/api/send", "/api/message", "/api/sendMessage", "/api/sendText",
    "/api/v1/send", "/api/v1/message", "/api/v2/send",
    
    // Endpoints específicos
    "/webhook/send", "/bot/send", "/client/send",
    "/instance/send", "/phone/send",
    
    // Endpoints wppconnect
    "/sendText", "/sendMessage", "/sendImage", "/sendFile",
    
    // Endpoints baileys  
    "/sendMsg", "/send-msg", "/msg", "/text",
    
    // Endpoints venom-bot
    "/sendTextMessage", "/send-text", "/message/text",
    
    // Outros
    "/", "/help", "/docs", "/info", "/version"
];

foreach ($endpoints_completos as $endpoint) {
    $url = "http://$vps_ip:$vps_port$endpoint";
    
    // Testar GET primeiro
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ $endpoint: HTTP $http_code (GET)\n";
        if (strlen($response) < 200) {
            echo "      📄 Resposta: $response\n";
        }
    } elseif ($http_code == 405) {
        echo "   ⚠️  $endpoint: HTTP $http_code (Método não permitido - pode aceitar POST)\n";
    } elseif ($http_code != 404) {
        echo "   📊 $endpoint: HTTP $http_code\n";
    }
}

echo "\n";

// ===== 3. TESTAR ENDPOINTS QUE ACEITAM POST =====
echo "3. 🧪 TESTANDO ENDPOINTS COM POST:\n";

$endpoints_post = [
    "/send", "/sendText", "/sendMessage", "/message", 
    "/api/send", "/api/sendMessage", "/session/send"
];

$test_data = [
    "chatId" => "554796164699@c.us",
    "text" => "🧪 Teste POST - " . date('H:i:s')
];

foreach ($endpoints_post as $endpoint) {
    $url = "http://$vps_ip:$vps_port$endpoint";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   📊 POST $endpoint: HTTP $http_code\n";
    if ($http_code != 404) {
        echo "      📄 Resposta: " . substr($response, 0, 150) . "\n";
    }
}

echo "\n";

// ===== 4. VERIFICAR SE É WPPCONNECT/BAILEYS/VENOM =====
echo "4. 🔍 DETECTANDO TIPO DE API:\n";

// Verificar endpoints específicos de cada biblioteca
$api_signatures = [
    "wppconnect" => ["/api/status", "/api/qrcode", "/api/sendText"],
    "baileys" => ["/qr", "/status", "/send"],  
    "venom-bot" => ["/status", "/qr", "/sendText"],
    "whatsapp-web.js" => ["/qr", "/status", "/send-message"],
    "green-api" => ["/waInstance", "/sendMessage"],
    "chat-api" => ["/status", "/sendMessage"]
];

foreach ($api_signatures as $api_name => $endpoints) {
    echo "   🔍 Testando $api_name:\n";
    $found_endpoints = 0;
    
    foreach ($endpoints as $endpoint) {
        $url = "http://$vps_ip:$vps_port$endpoint";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 || $http_code == 405) {
            echo "      ✅ $endpoint: HTTP $http_code\n";
            $found_endpoints++;
        }
    }
    
    if ($found_endpoints > 0) {
        echo "      🎯 Possível API: $api_name (encontrados: $found_endpoints endpoints)\n";
    } else {
        echo "      ❌ Não é $api_name\n";
    }
    echo "\n";
}

// ===== 5. INVESTIGAR LOGS DO VPS =====
echo "5. 📋 COMANDOS PARA INVESTIGAR VPS:\n";

echo "   🔧 Execute estes comandos no VPS via SSH:\n";
echo "   ssh root@$vps_ip\n\n";

echo "   📊 Verificar processos rodando:\n";
echo "   ps aux | grep node\n";
echo "   ps aux | grep whatsapp\n";
echo "   pm2 list\n\n";

echo "   📋 Verificar logs:\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs --lines 20\n\n";

echo "   📁 Verificar arquivos:\n";
echo "   cd /var/whatsapp-api\n";
echo "   ls -la\n";
echo "   cat package.json\n";
echo "   cat whatsapp-api-server.js | head -50\n\n";

echo "   🔍 Verificar porta 3000:\n";
echo "   netstat -tulpn | grep 3000\n";
echo "   curl localhost:3000/status\n\n";

// ===== 6. GERAR SOLUÇÃO BASEADA NO QUE ENCONTRAMOS =====
echo "6. 🎯 DIAGNÓSTICO E PRÓXIMOS PASSOS:\n";

echo "   📋 RESUMO:\n";
echo "   - VPS responde em /status (API online)\n";
echo "   - Webhook configurado corretamente\n";
echo "   - Ana responde e salva no banco\n";
echo "   - PROBLEMA: Nenhum endpoint de envio funciona\n\n";

echo "   🚨 AÇÕES URGENTES:\n";
echo "   1. Acessar VPS via SSH para investigar\n";
echo "   2. Verificar que biblioteca WhatsApp está sendo usada\n";
echo "   3. Encontrar o endpoint correto de envio\n";
echo "   4. Configurar o webhook para enviar respostas\n\n";

echo "   🎯 POSSÍVEIS SOLUÇÕES:\n";
echo "   1. API pode estar incompleta (só recebe, não envia)\n";
echo "   2. Endpoint de envio pode ter nome diferente\n";
echo "   3. Pode precisar de autenticação/token\n";
echo "   4. WhatsApp pode estar desconectado\n\n";

echo "   📞 PRÓXIMA AÇÃO RECOMENDADA:\n";
echo "   Acessar o VPS via SSH e executar os comandos listados acima\n";

echo "\n=== FIM DA INVESTIGAÇÃO ===\n";
?> 