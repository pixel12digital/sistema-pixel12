<?php
/**
 * 🔍 VERIFICAR CONFIGURAÇÃO DO WEBHOOK NO VPS
 * 
 * Este script verifica se o webhook está configurado corretamente no VPS
 */

echo "🔍 VERIFICANDO CONFIGURAÇÃO DO WEBHOOK NO VPS\n";
echo "=============================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_correto = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

// ===== 1. VERIFICAR CANAL 3000 =====
echo "1️⃣ VERIFICANDO CANAL 3000:\n";
echo "============================\n";

$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_3000 = curl_error($ch);
curl_close($ch);

if ($curl_error_3000) {
    echo "❌ Erro cURL canal 3000: $curl_error_3000\n";
} elseif ($http_code_3000 === 200) {
    $config_3000 = json_decode($response_3000, true);
    if ($config_3000) {
        $webhook_3000 = $config_3000['webhook'] ?? $config_3000['webhook_url'] ?? 'N/A';
        echo "📡 Canal 3000 - Webhook atual: $webhook_3000\n";
        
        if ($webhook_3000 === $webhook_correto) {
            echo "✅ Canal 3000 - Webhook CORRETO!\n";
        } else {
            echo "❌ Canal 3000 - Webhook INCORRETO!\n";
            echo "🔧 Precisa ser corrigido para: $webhook_correto\n";
        }
    } else {
        echo "⚠️ Canal 3000 - Resposta inválida: $response_3000\n";
    }
} else {
    echo "❌ Canal 3000 - HTTP $http_code_3000: $response_3000\n";
}

echo "\n";

// ===== 2. VERIFICAR CANAL 3001 =====
echo "2️⃣ VERIFICANDO CANAL 3001:\n";
echo "============================\n";

$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_3001 = curl_error($ch);
curl_close($ch);

if ($curl_error_3001) {
    echo "❌ Erro cURL canal 3001: $curl_error_3001\n";
} elseif ($http_code_3001 === 200) {
    $config_3001 = json_decode($response_3001, true);
    if ($config_3001) {
        $webhook_3001 = $config_3001['webhook'] ?? $config_3001['webhook_url'] ?? 'N/A';
        echo "📡 Canal 3001 - Webhook atual: $webhook_3001\n";
        
        if ($webhook_3001 === $webhook_correto) {
            echo "✅ Canal 3001 - Webhook CORRETO!\n";
        } else {
            echo "❌ Canal 3001 - Webhook INCORRETO!\n";
            echo "🔧 Precisa ser corrigido para: $webhook_correto\n";
        }
    } else {
        echo "⚠️ Canal 3001 - Resposta inválida: $response_3001\n";
    }
} else {
    echo "❌ Canal 3001 - HTTP $http_code_3001: $response_3001\n";
}

echo "\n";

// ===== 3. CORRIGIR SE NECESSÁRIO =====
echo "3️⃣ CORRIGINDO WEBHOOKS SE NECESSÁRIO:\n";
echo "=====================================\n";

$corrigidos = 0;

// Corrigir canal 3000 se necessário
if (isset($webhook_3000) && $webhook_3000 !== $webhook_correto) {
    echo "🔧 Corrigindo webhook do canal 3000...\n";
    
    $ch = curl_init("http://$vps_ip:3000/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_correto]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $correcao_response = curl_exec($ch);
    $correcao_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($correcao_http === 200) {
        echo "✅ Canal 3000 corrigido com sucesso!\n";
        $corrigidos++;
    } else {
        echo "❌ Erro ao corrigir canal 3000 (HTTP $correcao_http)\n";
    }
}

// Corrigir canal 3001 se necessário
if (isset($webhook_3001) && $webhook_3001 !== $webhook_correto) {
    echo "🔧 Corrigindo webhook do canal 3001...\n";
    
    $ch = curl_init("http://$vps_ip:3001/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_correto]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $correcao_response = curl_exec($ch);
    $correcao_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($correcao_http === 200) {
        echo "✅ Canal 3001 corrigido com sucesso!\n";
        $corrigidos++;
    } else {
        echo "❌ Erro ao corrigir canal 3001 (HTTP $correcao_http)\n";
    }
}

if ($corrigidos === 0) {
    echo "✅ Todos os webhooks já estão corretos!\n";
}

echo "\n";

// ===== 4. VERIFICAÇÃO FINAL =====
echo "4️⃣ VERIFICAÇÃO FINAL:\n";
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

echo "\n🎯 CONCLUSÃO:\n";
echo "=============\n";

if ((isset($final_webhook_3000) && $final_webhook_3000 === $webhook_correto) && 
    (isset($final_webhook_3001) && $final_webhook_3001 === $webhook_correto)) {
    echo "✅ TODOS OS WEBHOOKS ESTÃO CORRETOS!\n";
    echo "🎉 As mensagens do WhatsApp devem chegar ao chat agora.\n";
    echo "\n📋 PRÓXIMOS PASSOS:\n";
    echo "1. Envie uma mensagem real para o WhatsApp\n";
    echo "2. Verifique se aparece no chat\n";
    echo "3. Verifique se a Ana responde\n";
} else {
    echo "⚠️ AINDA HÁ PROBLEMAS COM OS WEBHOOKS!\n";
    echo "🔧 Verificar se os serviços estão rodando no VPS\n";
    echo "🔧 Verificar se as portas 3000 e 3001 estão acessíveis\n";
}
?> 