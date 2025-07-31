<?php
/**
 * DEPLOY HOSTINGER CANAL COMERCIAL
 * 
 * Este script prepara os arquivos para deploy na Hostinger compartilhada
 * onde está o sistema principal (app.pixel12digital.com.br)
 */

echo "🚀 DEPLOY HOSTINGER CANAL COMERCIAL\n";
echo "===================================\n\n";

echo "📊 ARQUITETURA:\n";
echo "  🏠 Hostinger Compartilhada: app.pixel12digital.com.br (Sistema Principal)\n";
echo "  🖥️ VPS: 212.85.11.238:3001 (Apenas WhatsApp API)\n";
echo "  💾 Banco: u342734079_wts_com_pixel (Canal Comercial)\n\n";

// 1. Verificar arquivos locais
echo "🔍 VERIFICANDO ARQUIVOS LOCAIS:\n";
$arquivos_necessarios = [
    'api/webhook_canal_37.php' => 'Webhook específico do canal comercial',
    'canais/comercial/canal_config.php' => 'Configuração do canal',
    'canais/comercial/teste_final.php' => 'Script de teste',
    'painel/receber_mensagem.php' => 'Processamento de mensagens (atualizado)',
    'painel/api/mensagens_cliente.php' => 'API de mensagens (atualizada)'
];

foreach ($arquivos_necessarios as $arquivo => $descricao) {
    if (file_exists(__DIR__ . '/' . $arquivo)) {
        $tamanho = filesize(__DIR__ . '/' . $arquivo);
        echo "  ✅ $arquivo - $descricao ($tamanho bytes)\n";
    } else {
        echo "  ❌ $arquivo - $descricao (não encontrado)\n";
    }
}

// 2. Gerar comandos para deploy na Hostinger
echo "\n📋 DEPLOY NA HOSTINGER COMPARTILHADA:\n";
echo "Execute os seguintes passos:\n\n";

echo "1️⃣ FAZER COMMIT E PUSH:\n";
echo "git add .\n";
echo "git commit -m 'Canal comercial configurado com banco separado'\n";
echo "git push origin master\n\n";

echo "2️⃣ NA HOSTINGER (cPanel ou SSH):\n";
echo "cd /home/u342734079/domains/app.pixel12digital.com.br/public_html/\n";
echo "git pull origin master\n\n";

echo "3️⃣ VERIFICAR ARQUIVOS NA HOSTINGER:\n";
echo "ls -la api/webhook_canal_37.php\n";
echo "ls -la canais/comercial/\n\n";

echo "4️⃣ TESTAR WEBHOOK NA HOSTINGER:\n";
echo "curl -X POST https://app.pixel12digital.com.br/api/webhook_canal_37.php \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"from\":\"554797146908@c.us\",\"to\":\"4797309525@c.us\",\"body\":\"Teste deploy hostinger\"}'\n\n";

// 3. Configurar VPS para usar webhook correto
echo "🔧 CONFIGURAR VPS PARA CANAL COMERCIAL:\n";
echo "Execute no VPS (212.85.11.238):\n\n";

echo "1️⃣ CONECTAR AO VPS:\n";
echo "ssh root@212.85.11.238\n\n";

echo "2️⃣ VERIFICAR CONFIGURAÇÃO ATUAL:\n";
echo "cd /var/whatsapp-api\n";
echo "cat package.json | grep webhook\n";
echo "cat .env | grep WEBHOOK\n\n";

echo "3️⃣ CONFIGURAR WEBHOOK PARA CANAL COMERCIAL:\n";
echo "# Editar arquivo de configuração do servidor WhatsApp\n";
echo "nano package.json\n";
echo "# Ou\n";
echo "nano .env\n\n";

echo "4️⃣ ALTERAR WEBHOOK PARA:\n";
echo "WEBHOOK_URL=https://app.pixel12digital.com.br/api/webhook_canal_37.php\n\n";

echo "5️⃣ REINICIAR SERVIDOR WHATSAPP:\n";
echo "pm2 restart whatsapp-api\n";
echo "pm2 logs whatsapp-api\n\n";

// 4. Testes finais
echo "🧪 TESTES FINAIS:\n";
echo "Após o deploy, execute:\n\n";

echo "1️⃣ TESTAR VPS:\n";
echo "curl http://212.85.11.238:3001/status\n\n";

echo "2️⃣ TESTAR WEBHOOK:\n";
echo "curl -X POST https://app.pixel12digital.com.br/api/webhook_canal_37.php \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"from\":\"554797146908@c.us\",\"to\":\"4797309525@c.us\",\"body\":\"Teste final\"}'\n\n";

echo "3️⃣ VERIFICAR BANCO:\n";
echo "Acesse: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel&table=mensagens_comunicacao\n\n";

echo "4️⃣ TESTAR CHAT DO PAINEL:\n";
echo "Acesse: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "Verifique se as mensagens do canal comercial aparecem\n\n";

// 5. Script de deploy automático
echo "📄 SCRIPT DE DEPLOY AUTOMÁTICO:\n";
echo "Crie um arquivo 'deploy_hostinger.sh' com:\n\n";

echo "#!/bin/bash\n";
echo "echo '🚀 Deploy Canal Comercial - Hostinger'\n";
echo "cd /home/u342734079/domains/app.pixel12digital.com.br/public_html/\n\n";
echo "# Backup\n";
echo "cp api/webhook_whatsapp.php api/webhook_whatsapp.php.backup\n";
echo "cp painel/receber_mensagem.php painel/receber_mensagem.php.backup\n";
echo "cp painel/api/mensagens_cliente.php painel/api/mensagens_cliente.php.backup\n\n";
echo "# Pull do git\n";
echo "git pull origin master\n\n";
echo "# Verificar arquivos\n";
echo "ls -la api/webhook_canal_37.php\n";
echo "ls -la canais/comercial/\n\n";
echo "# Testar sintaxe\n";
echo "php -l api/webhook_canal_37.php\n";
echo "php -l canais/comercial/canal_config.php\n\n";
echo "echo '✅ Deploy concluído!'\n";

echo "\n🎯 RESUMO DO QUE FOI CONFIGURADO:\n";
echo "✅ Canal Comercial (ID 37) configurado\n";
echo "✅ Banco separado: u342734079_wts_com_pixel\n";
echo "✅ Webhook específico: /api/webhook_canal_37.php\n";
echo "✅ VPS porta 3001 funcionando\n";
echo "✅ Configurações centralizadas em canais/comercial/\n";

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Fazer git push dos arquivos\n";
echo "2. Fazer git pull na Hostinger\n";
echo "3. Configurar VPS para usar webhook correto\n";
echo "4. Testar recebimento de mensagens\n";
echo "5. Verificar chat do painel\n";

echo "\n🌐 LINKS IMPORTANTES:\n";
echo "• Sistema: https://app.pixel12digital.com.br/\n";
echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "• VPS Status: http://212.85.11.238:3001/status\n";
echo "• Webhook: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "• phpMyAdmin: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel&table=mensagens_comunicacao\n";
?> 