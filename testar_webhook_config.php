<?php
/**
 * 🧪 TESTAR CONFIGURAÇÃO DE WEBHOOK
 * 
 * Este script testa se a nova configuração de webhook foi aplicada corretamente
 */

header('Content-Type: text/plain; charset=utf-8');

echo "🧪 TESTANDO CONFIGURAÇÃO DE WEBHOOK\n";
echo "===================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];
$webhook_url_esperada = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

foreach ($portas as $porta) {
    echo "🔍 Testando porta $porta...\n";
    echo str_repeat("-", 40) . "\n";
    
    $vps_url = "http://{$vps_ip}:{$porta}";
    
    // 1. Verificar configuração atual
    echo "1️⃣ Verificando configuração atual:\n";
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $current_response = curl_exec($ch);
    $current_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($current_http == 200) {
        $current_config = json_decode($current_response, true);
        echo "📡 Status: OK (HTTP $current_http)\n";
        echo "📡 Webhook atual: " . (isset($current_config['webhook']) ? $current_config['webhook'] : 'N/A') . "\n";
        echo "📡 Events: " . (isset($current_config['events']) ? json_encode($current_config['events']) : 'N/A') . "\n";
        echo "📡 Message: " . (isset($current_config['message']) ? $current_config['message'] : 'N/A') . "\n";
        
        // Verificar se a URL está correta
        if (isset($current_config['webhook']) && $current_config['webhook'] === $webhook_url_esperada) {
            echo "✅ URL do webhook está CORRETA!\n";
        } else {
            echo "❌ URL do webhook está INCORRETA!\n";
            echo "   Esperada: $webhook_url_esperada\n";
            echo "   Atual: " . (isset($current_config['webhook']) ? $current_config['webhook'] : 'N/A') . "\n";
        }
    } else {
        echo "❌ Erro ao verificar webhook (HTTP: $current_http)\n";
        echo "❌ Response: $current_response\n";
    }
    
    // 2. Testar configuração via POST
    echo "\n2️⃣ Testando configuração via POST:\n";
    $config_data = [
        'url' => $webhook_url_esperada,
        'events' => ['onmessage', 'onqr', 'onready', 'onclose']
    ];
    
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($ch);
    $config_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📡 POST Status: $config_http\n";
    echo "📡 POST Response: $config_response\n";
    
    if ($config_http == 200) {
        $post_config = json_decode($config_response, true);
        if (isset($post_config['webhook']) && $post_config['webhook'] === $webhook_url_esperada) {
            echo "✅ POST configurado com sucesso!\n";
        } else {
            echo "❌ POST não configurou corretamente\n";
        }
    } else {
        echo "❌ Erro no POST\n";
    }
    
    // 3. Verificar novamente após POST
    echo "\n3️⃣ Verificando após POST:\n";
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $new_response = curl_exec($ch);
    $new_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($new_http == 200) {
        $new_config = json_decode($new_response, true);
        echo "📡 Webhook após POST: " . (isset($new_config['webhook']) ? $new_config['webhook'] : 'N/A') . "\n";
        echo "📡 Events após POST: " . (isset($new_config['events']) ? json_encode($new_config['events']) : 'N/A') . "\n";
        
        if (isset($new_config['webhook']) && $new_config['webhook'] === $webhook_url_esperada) {
            echo "✅ Configuração aplicada com sucesso!\n";
        } else {
            echo "❌ Configuração não foi aplicada corretamente\n";
        }
    } else {
        echo "❌ Erro ao verificar após POST\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

echo "🎯 RESUMO DOS TESTES:\n";
echo "====================\n";
echo "✅ Testes concluídos!\n";
echo "✅ Verifique se as URLs estão corretas\n";
echo "✅ Verifique se os events estão configurados\n";
echo "✅ Verifique se os endpoints estão funcionando\n\n";

echo "🔍 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Se as URLs estão incorretas, aplique as mudanças no arquivo\n";
echo "2. Se os endpoints não funcionam, reinicie os serviços\n";
echo "3. Teste com uma mensagem real do WhatsApp\n";
echo "4. Verifique os logs para confirmar funcionamento\n\n";
?> 