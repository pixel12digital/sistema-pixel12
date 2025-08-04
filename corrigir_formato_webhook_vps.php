<?php
/**
 * 🔧 CORRIGIR FORMATO WEBHOOK VPS
 * 
 * Corrige o formato de dados enviado pelo VPS para o webhook
 */

echo "🔧 CORRIGIR FORMATO WEBHOOK VPS\n";
echo "================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. VERIFICAR CONFIGURAÇÃO ATUAL
echo "1️⃣ VERIFICANDO CONFIGURAÇÃO ATUAL\n";
echo "---------------------------------\n";

$canal_urls = [
    'Canal 3000 (Ana)' => "http://{$vps_ip}:3000",
    'Canal 3001 (Humano)' => "http://{$vps_ip}:3001"
];

foreach ($canal_urls as $nome => $url) {
    echo "🔍 $nome...\n";
    
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
            echo "  🔗 URL configurada: " . ($config['webhook_url'] ?? 'N/A') . "\n";
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
    }
    echo "\n";
}

// 2. TESTAR FORMATO CORRETO
echo "2️⃣ TESTANDO FORMATO CORRETO\n";
echo "---------------------------\n";

// Formato CORRETO que o webhook espera
$dados_corretos = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE FORMATO CORRETO - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Testando webhook com formato CORRETO...\n";
echo "  Dados: " . json_encode($dados_corretos, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_corretos));
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

// 3. INSTRUÇÕES PARA CORREÇÃO NO VPS
echo "\n3️⃣ INSTRUÇÕES PARA CORREÇÃO NO VPS\n";
echo "-----------------------------------\n";

echo "🔧 **PROBLEMAS IDENTIFICADOS NO VPS:**\n\n";

echo "1. ❌ **Formato de dados incorreto:**\n";
echo "   - VPS envia: {\"from\": \"554796164699\", \"text\": \"msg\"}\n";
echo "   - Webhook espera: {\"from\": \"554796164699@c.us\", \"body\": \"msg\"}\n\n";

echo "2. ❌ **URL inválida:**\n";
echo "   - VPS tenta: api/webhook.php (URL relativa)\n";
echo "   - Deveria: URL completa configurada\n\n";

echo "3. ❌ **Timeout de conexão:**\n";
echo "   - Tentativa de conexão com porta 8443\n";
echo "   - Timeout de 10 segundos\n\n";

echo "🔧 **CORREÇÕES NECESSÁRIAS NO CÓDIGO DO VPS:**\n\n";

echo "**Arquivo:** /var/whatsapp-api/whatsapp-api-server.js\n";
echo "**Linha:** ~180 (função de envio de webhook)\n\n";

echo "**ANTES (INCORRETO):**\n";
echo "```javascript\n";
echo "const webhookData = {\n";
echo "  event: 'onmessage',\n";
echo "  data: {\n";
echo "    from: message.from.split('@')[0],\n";
echo "    text: message.body,\n";
echo "    type: 'chat',\n";
echo "    timestamp: message.timestamp,\n";
echo "    session: sessionName\n";
echo "  }\n";
echo "};\n";
echo "```\n\n";

echo "**DEPOIS (CORRETO):**\n";
echo "```javascript\n";
echo "const webhookData = {\n";
echo "  from: message.from,\n";
echo "  to: message.to || '554797146908@c.us',\n";
echo "  body: message.body,\n";
echo "  type: 'text',\n";
echo "  timestamp: message.timestamp\n";
echo "};\n";
echo "```\n\n";

echo "**CORREÇÃO DA URL:**\n";
echo "```javascript\n";
echo "// Usar a URL configurada em vez de URL relativa\n";
echo "const webhookUrl = webhookConfig.webhook_url || webhookConfig.url;\n";
echo "if (!webhookUrl) {\n";
echo "  console.log('❌ Webhook não configurado');\n";
echo "  return;\n";
echo "}\n";
echo "```\n\n";

echo "**CORREÇÃO DO TIMEOUT:**\n";
echo "```javascript\n";
echo "const response = await fetch(webhookUrl, {\n";
echo "  method: 'POST',\n";
echo "  headers: {\n";
echo "    'Content-Type': 'application/json'\n";
echo "  },\n";
echo "  body: JSON.stringify(webhookData),\n";
echo "  timeout: 30000 // 30 segundos\n";
echo "});\n";
echo "```\n\n";

echo "4️⃣ COMANDOS PARA APLICAR CORREÇÕES\n";
echo "----------------------------------\n";

echo "🔧 **Execute no VPS:**\n\n";

echo "1. **Fazer backup do arquivo atual:**\n";
echo "   cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup\n\n";

echo "2. **Editar o arquivo:**\n";
echo "   nano /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "3. **Localizar a função de webhook (linha ~180):**\n";
echo "   - Procurar por 'webhook' ou 'fetch'\n";
echo "   - Corrigir o formato de dados\n";
echo "   - Corrigir a URL\n";
echo "   - Aumentar timeout\n\n";

echo "4. **Reiniciar os serviços:**\n";
echo "   pm2 restart whatsapp-3000\n";
echo "   pm2 restart whatsapp-3001\n\n";

echo "5. **Verificar logs:**\n";
echo "   pm2 logs whatsapp-3000 --lines 50\n";
echo "   pm2 logs whatsapp-3001 --lines 50\n\n";

echo "✅ **RESULTADO ESPERADO:**\n";
echo "- Webhook recebendo dados no formato correto\n";
echo "- Mensagens sendo salvas no banco\n";
echo "- Ana respondendo automaticamente\n";
echo "- Chat funcionando normalmente\n\n";

echo "🎯 **PRÓXIMOS PASSOS:**\n";
echo "1. Aplicar correções no código do VPS\n";
echo "2. Reiniciar serviços\n";
echo "3. Testar com mensagem real\n";
echo "4. Verificar se Ana responde\n\n";

echo "✅ ANÁLISE CONCLUÍDA!\n";
echo "O problema está no formato de dados enviado pelo VPS.\n";
echo "Aplique as correções sugeridas para resolver o problema.\n";
?> 