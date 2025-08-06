<?php
/**
 * 🔧 CORREÇÃO DIRETA DO WEBHOOK NO SERVIDOR VPS
 * 
 * Este script conecta diretamente no servidor VPS e corrige o arquivo whatsapp-api-server.js
 */

echo "🔧 CORREÇÃO DIRETA DO WEBHOOK NO SERVIDOR VPS\n";
echo "==============================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$vps_user = 'root';
$arquivo = '/var/whatsapp-api/whatsapp-api-server.js';
$webhook_url_correta = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

echo "🎯 CONFIGURAÇÃO A SER APLICADA:\n";
echo "- VPS: $vps_ip\n";
echo "- Arquivo: $arquivo\n";
echo "- URL Correta: $webhook_url_correta\n\n";

// 1. Fazer backup
echo "1️⃣ Fazendo backup do arquivo...\n";
$comando_backup = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && cp whatsapp-api-server.js whatsapp-api-server.js.backup." . date('Ymd_His') . "'";
$output_backup = shell_exec($comando_backup);

if ($output_backup === null) {
    echo "✅ Backup criado com sucesso\n";
} else {
    echo "❌ Erro ao criar backup: $output_backup\n";
}

// 2. Verificar configuração atual
echo "\n2️⃣ Verificando configuração atual...\n";
$comando_check = "ssh $vps_user@$vps_ip 'grep -n \"webhookConfig\" $arquivo'";
$output_check = shell_exec($comando_check);

if ($output_check) {
    echo "📝 Configuração atual encontrada:\n$output_check\n";
} else {
    echo "❌ Configuração webhookConfig não encontrada\n";
}

// 3. Aplicar correção direta
echo "\n3️⃣ Aplicando correção direta...\n";

// Comando para substituir a URL do webhook
$comando_correcao = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && sed -i \"s|url: '.*'|url: '$webhook_url_correta'|g\" whatsapp-api-server.js'";
$output_correcao = shell_exec($comando_correcao);

if ($output_correcao === null) {
    echo "✅ Correção aplicada com sucesso\n";
} else {
    echo "❌ Erro ao aplicar correção: $output_correcao\n";
}

// 4. Verificar se a correção foi aplicada
echo "\n4️⃣ Verificando se a correção foi aplicada...\n";
$comando_verificar = "ssh $vps_user@$vps_ip 'grep -n \"webhookConfig\" $arquivo'";
$output_verificar = shell_exec($comando_verificar);

if ($output_verificar) {
    echo "📝 Configuração após correção:\n$output_verificar\n";
    
    if (strpos($output_verificar, $webhook_url_correta) !== false) {
        echo "✅ SUCESSO: Webhook corrigido para a URL correta!\n";
    } else {
        echo "❌ ERRO: Webhook ainda não foi corrigido\n";
    }
} else {
    echo "❌ Erro ao verificar correção\n";
}

// 5. Reiniciar o serviço
echo "\n5️⃣ Reiniciando serviço...\n";
$comando_restart = "ssh $vps_user@$vps_ip 'pm2 restart whatsapp-3000'";
$output_restart = shell_exec($comando_restart);

if ($output_restart === null) {
    echo "✅ Serviço reiniciado com sucesso\n";
} else {
    echo "❌ Erro ao reiniciar serviço: $output_restart\n";
}

// 6. Testar configuração
echo "\n6️⃣ Testando configuração...\n";
sleep(5); // Aguardar o serviço reiniciar

$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $config = json_decode($response, true);
    echo "📡 Webhook atual: " . ($config['webhook'] ?? 'N/A') . "\n";
    
    if (($config['webhook'] ?? '') === $webhook_url_correta) {
        echo "✅ SUCESSO FINAL: Webhook corrigido e funcionando!\n";
    } else {
        echo "❌ ERRO: Webhook ainda não foi corrigido corretamente\n";
    }
} else {
    echo "❌ Erro ao testar configuração (HTTP: $http_code)\n";
}

echo "\n🎯 URL CORRETA DO WEBHOOK: $webhook_url_correta\n";
?> 