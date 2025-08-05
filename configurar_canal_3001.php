<?php
/**
 * 🔧 CONFIGURAÇÃO ESPECÍFICA DO CANAL 3001
 * 
 * O canal 3001 precisa de configuração especial
 */

echo "🔧 CONFIGURAÇÃO ESPECÍFICA DO CANAL 3001\n";
echo "========================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

// Tentar diferentes endpoints para o canal 3001
$endpoints = [
    '/webhook/config',
    '/webhook',
    '/hook/config',
    '/hook',
    '/config',
    '/setup'
];

echo "🎯 Tentando configurar webhook para canal 3001...\n";
echo "URL: $webhook_url\n\n";

$configurado = false;

foreach ($endpoints as $endpoint) {
    echo "🔄 Tentando endpoint: $endpoint\n";
    
    $ch = curl_init("http://$vps_ip:3001$endpoint");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "  HTTP Code: $http_code\n";
    
    if ($http_code === 200) {
        $result = json_decode($response, true);
        if ($result && (isset($result['success']) || isset($result['webhook']))) {
            echo "  ✅ Sucesso! Webhook configurado via $endpoint\n";
            echo "  📝 Resposta: " . json_encode($result) . "\n";
            $configurado = true;
            break;
        } else {
            echo "  ⚠️ Resposta inesperada: " . substr($response, 0, 100) . "\n";
        }
    } else {
        echo "  ❌ Falhou (HTTP $http_code)\n";
        if ($error) {
            echo "  🚫 Erro cURL: $error\n";
        }
    }
    echo "\n";
}

if (!$configurado) {
    echo "❌ Não foi possível configurar webhook no canal 3001 via API\n";
    echo "💡 Tentando métodos alternativos...\n\n";
    
    // Método alternativo: tentar com diferentes payloads
    $payloads = [
        ['url' => $webhook_url],
        ['webhook_url' => $webhook_url],
        ['webhook' => $webhook_url],
        ['config' => ['webhook_url' => $webhook_url]],
        ['settings' => ['webhook' => $webhook_url]]
    ];
    
    foreach ($payloads as $i => $payload) {
        echo "🔄 Tentando payload " . ($i + 1) . ": " . json_encode($payload) . "\n";
        
        $ch = curl_init("http://$vps_ip:3001/webhook/config");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            if ($result && (isset($result['success']) || isset($result['webhook']))) {
                echo "  ✅ Sucesso com payload " . ($i + 1) . "!\n";
                echo "  📝 Resposta: " . json_encode($result) . "\n";
                $configurado = true;
                break;
            }
        }
        echo "  ❌ Falhou (HTTP $http_code)\n";
    }
}

// Verificar se foi configurado
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO FINAL\n";
echo "==================================\n";

$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $config = json_decode($response, true);
    if ($config && isset($config['webhook_url'])) {
        echo "✅ Webhook configurado com sucesso: {$config['webhook_url']}\n";
    } else {
        echo "⚠️ Configuração incerta. Resposta: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "❌ Erro ao verificar configuração (HTTP $http_code)\n";
}

// Testar envio de mensagem para canal 3001
echo "\n🧪 TESTANDO MENSAGEM PARA CANAL 3001\n";
echo "====================================\n";

$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797309525@c.us',  // Número do canal 3001
    'body' => 'TESTE CANAL 3001 - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time(),
    'session' => 'comercial'
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
    echo "✅ Webhook processou mensagem do canal 3001\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro ao processar mensagem (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}

// Verificar se foi salva no banco
require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

$check_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE mensagem LIKE '%TESTE CANAL 3001%' ORDER BY data_hora DESC LIMIT 1");

if ($check_msg && $check_msg->num_rows > 0) {
    $msg = $check_msg->fetch_assoc();
    echo "\n✅ Mensagem salva no banco - Canal: {$msg['canal_id']}\n";
} else {
    echo "\n❌ Mensagem não foi salva no banco\n";
}

echo "\n📋 RESUMO DA CONFIGURAÇÃO\n";
echo "=========================\n";
echo "Canal 3000 (554797146908): ✅ Configurado\n";
echo "Canal 3001 (554797309525): " . ($configurado ? "✅ Configurado" : "❌ Precisa configuração manual") . "\n";

if (!$configurado) {
    echo "\n🔧 CONFIGURAÇÃO MANUAL NECESSÁRIA\n";
    echo "==================================\n";
    echo "1. Acesse o painel do WhatsApp API na porta 3001\n";
    echo "2. Procure por 'Webhook' ou 'Configurações'\n";
    echo "3. Configure a URL: $webhook_url\n";
    echo "4. Teste enviando mensagem para 554797309525\n";
}

echo "\n✅ SCRIPT CONCLUÍDO!\n";
?> 