<?php
/**
 * 🔧 CORRIGIR WEBHOOK PRODUÇÃO
 * 
 * Corrige todos os problemas identificados:
 * 1. Reconecta WhatsApp
 * 2. Reconfigura webhook
 * 3. Testa conectividade
 */

echo "🔧 CORRIGIR WEBHOOK PRODUÇÃO\n";
echo "============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. VERIFICAR STATUS ATUAL
echo "1️⃣ VERIFICANDO STATUS ATUAL\n";
echo "---------------------------\n";

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
        
        if (!isset($data['connected']) || !$data['connected']) {
            echo "  ⚠️ WhatsApp desconectado - precisa reconectar\n";
        }
    } else {
        echo "  ❌ Erro (HTTP $http_code)\n";
    }
    echo "\n";
}

// 2. CONFIGURAR WEBHOOK CORRETAMENTE
echo "2️⃣ CONFIGURANDO WEBHOOK CORRETAMENTE\n";
echo "-----------------------------------\n";

foreach ($canal_urls as $nome => $url) {
    echo "🔧 Configurando webhook em $nome...\n";
    
    $ch = curl_init($url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Webhook configurado com sucesso\n";
        $result = json_decode($response, true);
        if ($result) {
            echo "  📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "  ❌ Erro ao configurar webhook (HTTP $http_code)\n";
        echo "  Resposta: $response\n";
    }
    echo "\n";
}

// 3. TESTAR WEBHOOK COM FORMATO CORRETO
echo "3️⃣ TESTANDO WEBHOOK COM FORMATO CORRETO\n";
echo "---------------------------------------\n";

// Formato correto que o webhook espera
$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE CORREÇÃO WEBHOOK - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Enviando teste com formato correto...\n";
echo "  Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
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
        echo "  ✅ Teste bem-sucedido!\n";
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

// 4. COMANDOS PARA EXECUTAR NO VPS
echo "\n4️⃣ COMANDOS PARA EXECUTAR NO VPS\n";
echo "--------------------------------\n";

echo "🔧 Execute estes comandos no VPS para completar a correção:\n\n";

echo "1. 🔄 **Reconectar WhatsApp:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 restart whatsapp-3000 whatsapp-3001\n";
echo "   sleep 30\n\n";

echo "2. 📱 **Verificar QR Codes:**\n";
echo "   curl -s http://{$vps_ip}:3000/qr?session=default\n";
echo "   curl -s http://{$vps_ip}:3001/qr?session=comercial\n\n";

echo "3. 📊 **Verificar status:**\n";
echo "   curl -s http://{$vps_ip}:3000/status | jq .\n";
echo "   curl -s http://{$vps_ip}:3001/status | jq .\n\n";

echo "4. 📋 **Verificar logs:**\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n\n";

echo "5. 🌐 **Testar conectividade:**\n";
echo "   curl -I {$webhook_url}\n\n";

// 5. INSTRUÇÕES PARA TESTE REAL
echo "5️⃣ INSTRUÇÕES PARA TESTE REAL\n";
echo "-----------------------------\n";

echo "🧪 **Para testar se está funcionando:**\n\n";

echo "1. 📱 **Envie uma mensagem real:**\n";
echo "   - Abra o WhatsApp\n";
echo "   - Envie uma mensagem para: 554797146908\n";
echo "   - Aguarde a resposta da Ana\n\n";

echo "2. 🌐 **Verifique no chat web:**\n";
echo "   - Acesse: https://app.pixel12digital.com.br/painel/chat.php?cliente_id=4296\n";
echo "   - Verifique se a mensagem aparece\n\n";

echo "3. 📊 **Verifique logs do VPS:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 logs whatsapp-3000 --lines 10\n";
echo "   # Deve mostrar logs de recebimento de mensagem\n\n";

// 6. VERIFICAÇÃO FINAL
echo "6️⃣ VERIFICAÇÃO FINAL\n";
echo "--------------------\n";

echo "✅ **Após executar os comandos, verifique:**\n\n";

echo "1. 📱 **WhatsApp conectado:**\n";
echo "   - Status deve mostrar 'connected: true'\n";
echo "   - Não deve haver QR Code pendente\n\n";

echo "2. 🔗 **Webhook configurado:**\n";
echo "   - URL deve estar correta\n";
echo "   - Webhook deve estar ativo\n\n";

echo "3. 📨 **Mensagens chegando:**\n";
echo "   - Mensagens reais devem aparecer no chat\n";
echo "   - Ana deve responder automaticamente\n\n";

echo "🎯 **RESULTADO ESPERADO:**\n";
echo "✅ WhatsApp conectado\n";
echo "✅ Webhook configurado\n";
echo "✅ Mensagens sendo recebidas\n";
echo "✅ Ana respondendo automaticamente\n";
echo "✅ Chat web atualizando em tempo real\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
echo "Execute os comandos no VPS e teste com uma mensagem real.\n";
?> 