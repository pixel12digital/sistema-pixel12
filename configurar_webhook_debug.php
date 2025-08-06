<?php
/**
 * 🔧 CONFIGURAR WEBHOOK PARA DEBUG
 * 
 * Este script configura temporariamente o webhook para usar o debug
 */

echo "🔧 CONFIGURANDO WEBHOOK PARA DEBUG\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_debug = 'https://app.pixel12digital.com.br/webhook_sem_redirect/debug_webhook_real.php';

// ===== 1. CONFIGURAR CANAL 3000 PARA DEBUG =====
echo "1️⃣ CONFIGURANDO CANAL 3000 PARA DEBUG:\n";
echo "=======================================\n";

$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_debug]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_3000 = curl_error($ch);
curl_close($ch);

if ($curl_error_3000) {
    echo "❌ Erro cURL canal 3000: $curl_error_3000\n";
} elseif ($http_code_3000 === 200) {
    $config_3000 = json_decode($response_3000, true);
    if ($config_3000) {
        echo "✅ Canal 3000 configurado para debug!\n";
        echo "📡 URL: $webhook_debug\n";
    } else {
        echo "⚠️ Canal 3000 - Resposta inválida: $response_3000\n";
    }
} else {
    echo "❌ Canal 3000 - HTTP $http_code_3000: $response_3000\n";
}

echo "\n";

// ===== 2. CONFIGURAR CANAL 3001 PARA DEBUG =====
echo "2️⃣ CONFIGURANDO CANAL 3001 PARA DEBUG:\n";
echo "=======================================\n";

$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_debug]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_3001 = curl_error($ch);
curl_close($ch);

if ($curl_error_3001) {
    echo "❌ Erro cURL canal 3001: $curl_error_3001\n";
} elseif ($http_code_3001 === 200) {
    $config_3001 = json_decode($response_3001, true);
    if ($config_3001) {
        echo "✅ Canal 3001 configurado para debug!\n";
        echo "📡 URL: $webhook_debug\n";
    } else {
        echo "⚠️ Canal 3001 - Resposta inválida: $response_3001\n";
    }
} else {
    echo "❌ Canal 3001 - HTTP $http_code_3001: $response_3001\n";
}

echo "\n";

// ===== 3. VERIFICAÇÃO FINAL =====
echo "3️⃣ VERIFICAÇÃO FINAL:\n";
echo "=====================\n";

echo "🔍 Verificando configuração final...\n";

// Verificar canal 3000
$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$final_3000 = curl_exec($ch);
$final_http_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($final_http_3000 === 200) {
    $final_config_3000 = json_decode($final_3000, true);
    $final_webhook_3000 = $final_config_3000['webhook'] ?? $final_config_3000['webhook_url'] ?? 'N/A';
    echo "📡 Canal 3000 final: $final_webhook_3000\n";
}

// Verificar canal 3001
$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$final_3001 = curl_exec($ch);
$final_http_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($final_http_3001 === 200) {
    $final_config_3001 = json_decode($final_3001, true);
    $final_webhook_3001 = $final_config_3001['webhook'] ?? $final_config_3001['webhook_url'] ?? 'N/A';
    echo "📡 Canal 3001 final: $final_webhook_3001\n";
}

echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n";
echo "==========================\n";

if ((isset($final_webhook_3000) && $final_webhook_3000 === $webhook_debug) && 
    (isset($final_webhook_3001) && $final_webhook_3001 === $webhook_debug)) {
    echo "✅ TODOS OS CANAIS CONFIGURADOS PARA DEBUG!\n";
    echo "🎉 Agora envie uma mensagem real para o WhatsApp.\n";
    echo "\n📋 PRÓXIMOS PASSOS:\n";
    echo "1. Envie uma mensagem real para o WhatsApp\n";
    echo "2. Acesse: $webhook_debug\n";
    echo "3. Você verá exatamente os dados que chegam\n";
    echo "4. Verifique o arquivo: logs/debug_webhook_" . date('Y-m-d') . ".log\n";
    echo "\n🔧 PARA VOLTAR AO NORMAL:\n";
    echo "Execute: php configurar_webhook_normal.php\n";
} else {
    echo "⚠️ AINDA HÁ PROBLEMAS COM A CONFIGURAÇÃO!\n";
    echo "🔧 Verificar se os serviços estão rodando no VPS\n";
    echo "🔧 Verificar se as portas 3000 e 3001 estão acessíveis\n";
}
?> 