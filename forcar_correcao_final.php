<?php
/**
 * 🔧 FORÇAR CORREÇÃO FINAL DO WEBHOOK
 * 
 * Este script força a correção do webhook usando a API
 */

echo "🔧 FORÇANDO CORREÇÃO FINAL DO WEBHOOK\n";
echo "=====================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url_correta = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';
$portas = [3000, 3001];

echo "🎯 CONFIGURAÇÃO A SER APLICADA:\n";
echo "- URL Correta: $webhook_url_correta\n";
echo "- Portas: " . implode(', ', $portas) . "\n\n";

foreach ($portas as $porta) {
    echo "🔧 Forçando correção na porta $porta...\n";
    
    $vps_url = "http://$vps_ip:$porta";
    
    // 1. Verificar configuração atual
    echo "1️⃣ Verificando configuração atual...\n";
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $config = json_decode($response, true);
        echo "📡 Webhook atual: " . ($config['webhook'] ?? 'N/A') . "\n";
        
        // 2. Forçar correção
        echo "2️⃣ Forçando correção...\n";
        
        $data = json_encode(['url' => $webhook_url_correta]);
        $ch = curl_init($vps_url . "/webhook/config");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $result_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "📡 Resultado: $result (HTTP: $result_http)\n";
        
        if ($result_http == 200) {
            echo "✅ Correção aplicada!\n";
        } else {
            echo "❌ Erro ao aplicar correção\n";
        }
    } else {
        echo "❌ Erro ao verificar webhook (HTTP: $http_code)\n";
    }
    
    echo "\n";
}

// 3. Verificar configuração final
echo "3️⃣ Verificando configuração final...\n";
foreach ($portas as $porta) {
    $vps_url = "http://$vps_ip:$porta";
    
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $config = json_decode($response, true);
        echo "📡 Porta $porta: " . ($config['webhook'] ?? 'N/A') . "\n";
        
        if (($config['webhook'] ?? '') === $webhook_url_correta) {
            echo "✅ Porta $porta: CORRETA\n";
        } else {
            echo "❌ Porta $porta: INCORRETA\n";
        }
    } else {
        echo "❌ Porta $porta: Erro (HTTP: $http_code)\n";
    }
}

echo "\n🎯 URL CORRETA DO WEBHOOK: $webhook_url_correta\n";

// 4. Teste final
echo "\n4️⃣ Teste final...\n";
$test_data = [
    'event' => 'test',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste correção final - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_url_correta);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Teste webhook: HTTP $http_code\n";
echo "📡 Resposta: $response\n";

if ($http_code === 200) {
    echo "✅ SUCESSO: Webhook está funcionando!\n";
} else {
    echo "❌ ERRO: Webhook não está funcionando (HTTP: $http_code)\n";
}

echo "\n🎯 CORREÇÃO FINAL CONCLUÍDA!\n";
?> 