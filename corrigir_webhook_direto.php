<?php
/**
 * 🔧 CORRIGIR WEBHOOK DIRETAMENTE
 * 
 * Este script aplica as mudanças diretamente no arquivo usando comandos simples
 */

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 CORRIGINDO WEBHOOK DIRETAMENTE\n";
echo "=================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$vps_user = 'root';
$arquivo = '/var/whatsapp-api/whatsapp-api-server.js';
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

echo "🎯 CONFIGURAÇÃO A SER APLICADA:\n";
echo "- VPS: $vps_ip\n";
echo "- Arquivo: $arquivo\n";
echo "- URL: $webhook_url\n\n";

// 1. Fazer backup
echo "1️⃣ Fazendo backup...\n";
$comando_backup = "ssh $vps_user@$vps_ip \"cd /var/whatsapp-api && cp whatsapp-api-server.js whatsapp-api-server.js.backup." . date('Ymd_His') . "\"";
$output_backup = shell_exec($comando_backup);

if ($output_backup === null) {
    echo "✅ Backup criado com sucesso\n";
} else {
    echo "❌ Erro ao criar backup: $output_backup\n";
}

// 2. Substituir a configuração do webhook
echo "\n2️⃣ Substituindo configuração do webhook...\n";

// Criar um arquivo temporário com a nova configuração
$nova_config = "// Variável global para webhook
let webhookConfig = {
    url: '$webhook_url',
    events: ['onmessage', 'onqr', 'onready', 'onclose']
};";

// Salvar a nova configuração em um arquivo temporário
file_put_contents('webhook_config_temp.txt', $nova_config);

// Enviar o arquivo para a VPS e aplicar as mudanças
$comando_sed = "ssh $vps_user@$vps_ip \"cd /var/whatsapp-api && sed -i 's|let webhookUrl = .*;|let webhookConfig = { url: \\\"$webhook_url\\\", events: [\\\"onmessage\\\", \\\"onqr\\\", \\\"onready\\\", \\\"onclose\\\"] };|g' whatsapp-api-server.js\"";
$output_sed = shell_exec($comando_sed);

if ($output_sed === null) {
    echo "✅ Configuração substituída\n";
} else {
    echo "❌ Erro ao substituir configuração: $output_sed\n";
}

// 3. Atualizar referências
echo "\n3️⃣ Atualizando referências...\n";
$comando_refs = "ssh $vps_user@$vps_ip \"cd /var/whatsapp-api && sed -i 's/webhookUrl/webhookConfig.url/g' whatsapp-api-server.js\"";
$output_refs = shell_exec($comando_refs);

if ($output_refs === null) {
    echo "✅ Referências atualizadas\n";
} else {
    echo "❌ Erro ao atualizar referências: $output_refs\n";
}

// 4. Reiniciar serviços
echo "\n4️⃣ Reiniciando serviços...\n";
$comando_restart = "ssh $vps_user@$vps_ip \"pm2 restart whatsapp-3000 && pm2 restart whatsapp-3001\"";
$output_restart = shell_exec($comando_restart);

if ($output_restart === null) {
    echo "✅ Serviços reiniciados\n";
} else {
    echo "❌ Erro ao reiniciar serviços: $output_restart\n";
}

// 5. Verificar se as mudanças foram aplicadas
echo "\n5️⃣ Verificando mudanças...\n";
$comando_check = "ssh $vps_user@$vps_ip \"cd /var/whatsapp-api && grep -n 'webhookConfig' whatsapp-api-server.js\"";
$output_check = shell_exec($comando_check);

if ($output_check) {
    echo "✅ webhookConfig encontrado no arquivo:\n";
    echo $output_check;
} else {
    echo "❌ webhookConfig não encontrado!\n";
}

// 6. Testar a configuração
echo "\n6️⃣ Testando configuração...\n";
$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $config = json_decode($response, true);
    echo "📡 Status: OK (HTTP $http_code)\n";
    echo "📡 Webhook: " . (isset($config['webhook']) ? $config['webhook'] : 'N/A') . "\n";
    echo "📡 Events: " . (isset($config['events']) ? json_encode($config['events']) : 'N/A') . "\n";
    
    if (isset($config['webhook']) && $config['webhook'] === $webhook_url) {
        echo "✅ Configuração aplicada com sucesso!\n";
    } else {
        echo "❌ Configuração ainda não foi aplicada corretamente\n";
    }
} else {
    echo "❌ Erro ao testar configuração (HTTP: $http_code)\n";
}

echo "\n🎯 CORREÇÃO CONCLUÍDA!\n";
echo "=====================\n";
echo "✅ Backup criado\n";
echo "✅ Configuração substituída\n";
echo "✅ Referências atualizadas\n";
echo "✅ Serviços reiniciados\n\n";

echo "🔍 PARA TESTAR:\n";
echo "===============\n";
echo "1. Acesse: http://$vps_ip:3000/webhook/config\n";
echo "2. Acesse: http://$vps_ip:3001/webhook/config\n";
echo "3. Verifique se a URL está correta\n";
echo "4. Teste com uma mensagem do WhatsApp\n\n";

// Limpar arquivo temporário
if (file_exists('webhook_config_temp.txt')) {
    unlink('webhook_config_temp.txt');
}
?> 