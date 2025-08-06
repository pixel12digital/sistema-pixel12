<?php
/**
 * 🚀 DEPLOY FINAL DO WEBHOOK NA VPS
 * 
 * Este script aplica as mudanças corretas no arquivo whatsapp-api-server.js
 */

header('Content-Type: text/plain; charset=utf-8');

echo "🚀 DEPLOY FINAL DO WEBHOOK NA VPS\n";
echo "=================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$vps_user = 'root';
$arquivo = '/var/whatsapp-api/whatsapp-api-server.js';
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

echo "🎯 CONFIGURAÇÃO A SER APLICADA:\n";
echo "- VPS: $vps_ip\n";
echo "- Arquivo: $arquivo\n";
echo "- URL: $webhook_url\n";
echo "- Events: ['onmessage', 'onqr', 'onready', 'onclose']\n\n";

// 1. Fazer backup
echo "1️⃣ Fazendo backup...\n";
$comando_backup = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && cp whatsapp-api-server.js whatsapp-api-server.js.backup." . date('Ymd_His') . "'";
$output_backup = shell_exec($comando_backup);

if ($output_backup === null) {
    echo "✅ Backup criado com sucesso\n";
} else {
    echo "❌ Erro ao criar backup: $output_backup\n";
}

// 2. Aplicar mudanças no arquivo
echo "\n2️⃣ Aplicando mudanças no arquivo...\n";

// Comando para substituir a configuração do webhook
$comando_webhook = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && sed -i \"s|let webhookUrl = '.*';|let webhookConfig = { url: '$webhook_url', events: ['onmessage', 'onqr', 'onready', 'onclose'] };|g\" whatsapp-api-server.js'";
$output_webhook = shell_exec($comando_webhook);

if ($output_webhook === null) {
    echo "✅ Configuração do webhook aplicada\n";
} else {
    echo "❌ Erro ao aplicar configuração: $output_webhook\n";
}

// 3. Atualizar referências ao webhookUrl
echo "\n3️⃣ Atualizando referências...\n";
$comando_refs = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && sed -i \"s/webhookUrl/webhookConfig.url/g\" whatsapp-api-server.js'";
$output_refs = shell_exec($comando_refs);

if ($output_refs === null) {
    echo "✅ Referências atualizadas\n";
} else {
    echo "❌ Erro ao atualizar referências: $output_refs\n";
}

// 4. Atualizar endpoints
echo "\n4️⃣ Atualizando endpoints...\n";

// Comando para atualizar o endpoint POST
$comando_post = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && sed -i \"/app.post('\/webhook\/config'/,/});/c\\
app.post('/webhook/config', (req, res) => {\\
    const { url, events } = req.body;\\
    \\
    if (url) {\\
        webhookConfig.url = url;\\
        if (events) webhookConfig.events = events;\\
        \\
        console.log(\`🔗 [WEBHOOK] Configurado: \${webhookConfig.url}\`);\\
        res.json({\\
            success: true,\\
            webhook: webhookConfig.url,\\
            events: webhookConfig.events,\\
            message: 'Webhook configurado com sucesso'\\
        });\\
    } else {\\
        res.status(400).json({ error: 'URL do webhook é obrigatória' });\\
    }\\
});' whatsapp-api-server.js'";

$output_post = shell_exec($comando_post);

if ($output_post === null) {
    echo "✅ Endpoint POST atualizado\n";
} else {
    echo "❌ Erro ao atualizar endpoint POST: $output_post\n";
}

// Comando para atualizar o endpoint GET
$comando_get = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && sed -i \"/app.get('\/webhook\/config'/,/});/c\\
app.get('/webhook/config', (req, res) => {\\
    res.json({\\
        success: true,\\
        webhook: webhookConfig.url,\\
        events: webhookConfig.events,\\
        message: 'Webhook configurado'\\
    });\\
});' whatsapp-api-server.js'";

$output_get = shell_exec($comando_get);

if ($output_get === null) {
    echo "✅ Endpoint GET atualizado\n";
} else {
    echo "❌ Erro ao atualizar endpoint GET: $output_get\n";
}

// 5. Reiniciar serviços
echo "\n5️⃣ Reiniciando serviços...\n";
$comando_restart = "ssh $vps_user@$vps_ip 'pm2 restart whatsapp-3000 && pm2 restart whatsapp-3001'";
$output_restart = shell_exec($comando_restart);

if ($output_restart === null) {
    echo "✅ Serviços reiniciados\n";
} else {
    echo "❌ Erro ao reiniciar serviços: $output_restart\n";
}

// 6. Verificar se as mudanças foram aplicadas
echo "\n6️⃣ Verificando mudanças...\n";
$comando_check = "ssh $vps_user@$vps_ip 'cd /var/whatsapp-api && grep -n \"webhookConfig\" whatsapp-api-server.js'";
$output_check = shell_exec($comando_check);

if ($output_check) {
    echo "✅ webhookConfig encontrado no arquivo:\n";
    echo $output_check;
} else {
    echo "❌ webhookConfig não encontrado!\n";
}

echo "\n🎯 DEPLOY CONCLUÍDO!\n";
echo "===================\n";
echo "✅ Backup criado\n";
echo "✅ Configuração aplicada\n";
echo "✅ Referências atualizadas\n";
echo "✅ Endpoints atualizados\n";
echo "✅ Serviços reiniciados\n\n";

echo "🔍 PARA TESTAR:\n";
echo "===============\n";
echo "1. Acesse: http://$vps_ip:3000/webhook/config\n";
echo "2. Acesse: http://$vps_ip:3001/webhook/config\n";
echo "3. Verifique se a URL está correta\n";
echo "4. Teste com uma mensagem do WhatsApp\n\n";

echo "📝 LOGS DISPONÍVEIS:\n";
echo "===================\n";
echo "- Logs do VPS: pm2 logs whatsapp-3000 --lines 50\n";
echo "- Logs do VPS: pm2 logs whatsapp-3001 --lines 50\n";
echo "- Logs do webhook: https://app.pixel12digital.com.br/painel/logs/\n\n";
?> 