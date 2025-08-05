<?php
/**
 * 🔧 CORREÇÃO DOS PROBLEMAS DE WEBHOOK - MENSAGENS WHATSAPP
 * 
 * Este script corrige os problemas identificados no diagnóstico
 */

echo "🔧 CORREÇÃO DOS PROBLEMAS DE WEBHOOK\n";
echo "====================================\n\n";

// 1. CONFIGURAR WEBHOOKS NAS VPS
echo "1️⃣ CONFIGURANDO WEBHOOKS NAS VPS\n";
echo "=================================\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

echo "🎯 URL do webhook que está funcionando: $webhook_url\n\n";

// Configurar webhook no canal 3000
echo "🔧 Configurando webhook no canal 3000...\n";
$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook configurado no canal 3000\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro ao configurar webhook no canal 3000 (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}
echo "\n";

// Configurar webhook no canal 3001
echo "🔧 Configurando webhook no canal 3001...\n";
$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook configurado no canal 3001\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro ao configurar webhook no canal 3001 (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}
echo "\n";

// 2. VERIFICAR CONFIGURAÇÕES
echo "2️⃣ VERIFICANDO CONFIGURAÇÕES\n";
echo "=============================\n";

foreach ([3000, 3001] as $porta) {
    echo "🔄 Verificando configuração da porta $porta...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $config = json_decode($response, true);
        if ($config && isset($config['webhook_url'])) {
            echo "  ✅ Webhook configurado: {$config['webhook_url']}\n";
        } else {
            echo "  ⚠️ Webhook não configurado ou resposta inválida\n";
        }
    } else {
        echo "  ❌ Erro ao verificar configuração (HTTP $http_code)\n";
    }
}
echo "\n";

// 3. TESTAR ENVIO DE MENSAGEM DE TESTE
echo "3️⃣ TESTANDO ENVIO DE MENSAGEM DE TESTE\n";
echo "======================================\n";

echo "📤 Simulando mensagem recebida via webhook...\n";

$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE CORREÇÃO WEBHOOK - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time(),
    'session' => 'default'
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

if ($http_code === 200) {
    echo "✅ Webhook processou mensagem com sucesso\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro ao processar mensagem no webhook (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}
echo "\n";

// 4. VERIFICAR SE A MENSAGEM FOI SALVA NO BANCO
echo "4️⃣ VERIFICANDO SE A MENSAGEM FOI SALVA\n";
echo "======================================\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

$check_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE mensagem LIKE '%TESTE CORREÇÃO WEBHOOK%' ORDER BY data_hora DESC LIMIT 1");

if ($check_msg && $check_msg->num_rows > 0) {
    $msg = $check_msg->fetch_assoc();
    echo "✅ Mensagem encontrada no banco de dados:\n";
    echo "   ID: {$msg['id']}\n";
    echo "   Canal: {$msg['canal_id']}\n";
    echo "   Número: {$msg['numero_whatsapp']}\n";
    echo "   Mensagem: {$msg['mensagem']}\n";
    echo "   Data/Hora: {$msg['data_hora']}\n";
    echo "   Status: {$msg['status']}\n";
} else {
    echo "❌ Mensagem não foi salva no banco de dados\n";
    echo "💡 Verificando problemas no webhook...\n";
}
echo "\n";

// 5. COMANDOS PARA TESTE MANUAL
echo "5️⃣ COMANDOS PARA TESTE MANUAL\n";
echo "==============================\n";

echo "🧪 Para testar manualmente, execute estes comandos:\n\n";

echo "1. Testar webhook diretamente:\n";
echo "curl -X POST $webhook_url \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\n";
echo "    \"from\": \"554796164699@c.us\",\n";
echo "    \"to\": \"554797146908@c.us\",\n";
echo "    \"body\": \"Teste manual webhook\",\n";
echo "    \"type\": \"text\",\n";
echo "    \"timestamp\": " . time() . "\n";
echo "  }'\n\n";

echo "2. Verificar configuração webhook VPS 3000:\n";
echo "curl http://212.85.11.238:3000/webhook/config\n\n";

echo "3. Verificar configuração webhook VPS 3001:\n";
echo "curl http://212.85.11.238:3001/webhook/config\n\n";

echo "4. Enviar mensagem de teste via VPS:\n";
echo "curl -X POST http://212.85.11.238:3000/send/text \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\n";
echo "    \"sessionName\": \"default\",\n";
echo "    \"number\": \"554796164699\",\n";
echo "    \"message\": \"Teste de envio VPS\"\n";
echo "  }'\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Envie uma mensagem real do WhatsApp para 554797146908\n";
echo "2. Verifique se aparece no painel em: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "3. Se não aparecer, verifique os logs de erro\n";
echo "4. Teste também o canal 3001 enviando para 554797309525\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
?> 