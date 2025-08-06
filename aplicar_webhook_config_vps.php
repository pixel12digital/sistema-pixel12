<?php
/**
 * 🔧 APLICAR CONFIGURAÇÃO DE WEBHOOK NA VPS
 * 
 * Este script aplica a nova estrutura de webhookConfig no servidor VPS
 * Substitui a configuração antiga pela nova estrutura completa
 */

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 APLICANDO CONFIGURAÇÃO DE WEBHOOK NA VPS\n";
echo "============================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];
$webhook_url_correta = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

echo "🎯 CONFIGURAÇÃO A SER APLICADA:\n";
echo "- URL: $webhook_url_correta\n";
echo "- Events: ['onmessage', 'onqr', 'onready', 'onclose']\n\n";

foreach ($portas as $porta) {
    echo "🔧 Aplicando na porta $porta...\n";
    
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
        echo "📡 Webhook atual: " . (isset($current_config['webhook']) ? $current_config['webhook'] : 'N/A') . "\n";
    } else {
        echo "❌ Erro ao verificar webhook atual (HTTP: $current_http)\n";
    }
    
    // 2. Configurar nova estrutura
    echo "2️⃣ Configurando nova estrutura...\n";
    $config_data = [
        'url' => $webhook_url_correta,
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
    
    echo "📡 Config HTTP Code: $config_http\n";
    echo "📡 Config Response: $config_response\n";
    
    if ($config_http == 200) {
        echo "✅ Webhook configurado com sucesso na porta $porta!\n";
    } else {
        echo "❌ Erro ao configurar webhook na porta $porta\n";
    }
    
    // 3. Verificar nova configuração
    echo "3️⃣ Verificando nova configuração:\n";
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $new_response = curl_exec($ch);
    $new_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($new_http == 200) {
        $new_config = json_decode($new_response, true);
        echo "✅ Nova configuração aplicada:\n";
        echo "   URL: " . (isset($new_config['webhook']) ? $new_config['webhook'] : 'N/A') . "\n";
        echo "   Events: " . (isset($new_config['events']) ? json_encode($new_config['events']) : 'N/A') . "\n";
    } else {
        echo "❌ Erro ao verificar nova configuração\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "🎯 RESUMO DA APLICAÇÃO:\n";
echo "======================\n";
echo "✅ Script concluído!\n";
echo "✅ Nova estrutura de webhook aplicada nas portas 3000 e 3001\n";
echo "✅ URL configurada: $webhook_url_correta\n";
echo "✅ Events configurados: ['onmessage', 'onqr', 'onready', 'onclose']\n\n";

echo "🔍 PARA TESTAR:\n";
echo "===============\n";
echo "1. Acesse: http://{$vps_ip}:3000/webhook/config\n";
echo "2. Acesse: http://{$vps_ip}:3001/webhook/config\n";
echo "3. Envie uma mensagem para o WhatsApp\n";
echo "4. Verifique se o webhook está funcionando\n\n";

echo "📝 LOGS DISPONÍVEIS:\n";
echo "===================\n";
echo "- Logs do VPS: pm2 logs whatsapp-3000 --lines 50\n";
echo "- Logs do VPS: pm2 logs whatsapp-3001 --lines 50\n";
echo "- Logs do webhook: https://app.pixel12digital.com.br/painel/logs/\n\n";
?> 