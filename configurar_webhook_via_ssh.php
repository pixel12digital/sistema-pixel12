<?php
/**
 * 🔧 CONFIGURAR WEBHOOK VIA SSH
 * 
 * Instruções para configurar webhook no VPS canal 3000 via SSH
 */

echo "🔧 CONFIGURAÇÃO WEBHOOK VIA SSH\n";
echo "===============================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 DIAGNÓSTICO CONFIRMADO:\n";
echo "==========================\n";
echo "✅ VPS Canal 3000: CONECTADO\n";
echo "✅ WhatsApp: FUNCIONANDO\n";
echo "✅ Nosso Webhook: OPERACIONAL\n";
echo "❌ Problema: Webhook não configurado no VPS\n\n";

echo "🚀 SOLUÇÃO VIA SSH:\n";
echo "===================\n\n";

echo "1️⃣ CONECTAR VIA SSH:\n";
echo "ssh root@$vps_ip\n\n";

echo "2️⃣ VERIFICAR PROCESSO:\n";
echo "pm2 list\n";
echo "pm2 show whatsapp-3000\n";
echo "pm2 logs whatsapp-3000 --lines 20\n\n";

echo "3️⃣ LOCALIZAR ARQUIVO DE CONFIGURAÇÃO:\n";
echo "find /root -name '*.json' -o -name '*.js' -o -name '*.env' | grep -i whatsapp\n";
echo "find /home -name '*.json' -o -name '*.js' -o -name '*.env' | grep -i whatsapp\n";
echo "find /opt -name '*.json' -o -name '*.js' -o -name '*.env' | grep -i whatsapp\n\n";

echo "4️⃣ VERIFICAR VARIÁVEIS DE AMBIENTE:\n";
echo "pm2 env whatsapp-3000\n";
echo "cat ~/.bashrc | grep -i webhook\n";
echo "cat /etc/environment | grep -i webhook\n\n";

echo "5️⃣ CONFIGURAÇÕES POSSÍVEIS:\n";
echo "============================\n";
echo "A) ARQUIVO .ENV:\n";
echo "echo 'WEBHOOK_URL=$webhook_url' >> /path/to/.env\n\n";

echo "B) ARQUIVO CONFIG.JSON:\n";
echo "{\n";
echo "  \"webhook\": \"$webhook_url\",\n";
echo "  \"webhookEnabled\": true\n";
echo "}\n\n";

echo "C) VARIÁVEL PM2:\n";
echo "pm2 set whatsapp-3000:WEBHOOK_URL $webhook_url\n";
echo "pm2 restart whatsapp-3000\n\n";

echo "6️⃣ TESTAR CONFIGURAÇÃO:\n";
echo "========================\n";
echo "pm2 restart whatsapp-3000\n";
echo "pm2 logs whatsapp-3000 --lines 10\n";
echo "curl http://localhost:3000/status\n\n";

echo "🔍 COMANDOS DE DEPURAÇÃO:\n";
echo "=========================\n";
echo "# Verificar porta em uso:\n";
echo "netstat -tulpn | grep 3000\n\n";
echo "# Verificar logs de sistema:\n";
echo "journalctl -u whatsapp-3000 -f\n\n";
echo "# Verificar processos:\n";
echo "ps aux | grep whatsapp\n\n";

echo "📱 APÓS CONFIGURAÇÃO:\n";
echo "=====================\n";
echo "1. Execute: php verificar_mensagens_recentes.php\n";
echo "2. Envie mensagem do WhatsApp para canal 3000\n";
echo "3. Aguarde 30 segundos\n";
echo "4. Execute novamente: php verificar_mensagens_recentes.php\n";
echo "5. Verifique se mensagem aparece no banco\n\n";

echo "🆘 SE PRECISAR DE AJUDA:\n";
echo "========================\n";
echo "1. Copie e cole o resultado de:\n";
echo "   pm2 show whatsapp-3000\n\n";
echo "2. Procure por arquivos de configuração:\n";
echo "   find / -name '*whatsapp*' -type f 2>/dev/null\n\n";
echo "3. Verifique se há arquivo package.json:\n";
echo "   cat package.json (no diretório do projeto)\n\n";

echo "💡 DICA IMPORTANTE:\n";
echo "===================\n";
echo "O webhook deve apontar para:\n";
echo "→ $webhook_url\n\n";
echo "Testamos e confirmamos que nosso webhook:\n";
echo "✅ Recebe mensagens\n";
echo "✅ Processa com Ana\n";
echo "✅ Salva no banco\n";
echo "✅ Retorna resposta válida\n\n";

echo "🎯 OBJETIVO:\n";
echo "Fazer o VPS enviar mensagens do WhatsApp para nosso webhook!\n";

?> 