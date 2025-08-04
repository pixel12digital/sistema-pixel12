<?php
/**
 * 📋 GUIA SSH - CONFIGURAÇÃO WEBHOOK CANAL 3000
 * 
 * Comandos detalhados para executar no SSH root@212.85.11.238
 */

echo "📋 GUIA SSH - CONFIGURAÇÃO WEBHOOK\n";
echo "==================================\n\n";

echo "🔗 CONECTE-SE AO SSH:\n";
echo "ssh root@212.85.11.238\n\n";

echo "📝 COMANDOS PARA EXECUTAR NO SSH:\n";
echo "=================================\n\n";

echo "1️⃣ VERIFICAR PROCESSOS PM2:\n";
echo "----------------------------\n";
echo "pm2 list\n";
echo "pm2 show whatsapp-3000\n";
echo "pm2 logs whatsapp-3000 --lines 20\n\n";

echo "2️⃣ LOCALIZAR DIRETÓRIO DO PROJETO:\n";
echo "-----------------------------------\n";
echo "pwd\n";
echo "find /root -name '*whatsapp*' -type d 2>/dev/null\n";
echo "find /home -name '*whatsapp*' -type d 2>/dev/null\n";
echo "find /opt -name '*whatsapp*' -type d 2>/dev/null\n\n";

echo "3️⃣ PROCURAR ARQUIVOS DE CONFIGURAÇÃO:\n";
echo "--------------------------------------\n";
echo "find /root -name '*.json' -o -name '*.js' -o -name '*.env' | grep -i whatsapp\n";
echo "find / -name 'package.json' 2>/dev/null | head -10\n";
echo "find / -name 'config.json' 2>/dev/null | head -10\n\n";

echo "4️⃣ VERIFICAR VARIÁVEIS DE AMBIENTE PM2:\n";
echo "---------------------------------------\n";
echo "pm2 env whatsapp-3000\n";
echo "pm2 describe whatsapp-3000\n\n";

echo "5️⃣ VERIFICAR PORTA E PROCESSO:\n";
echo "-------------------------------\n";
echo "netstat -tulpn | grep 3000\n";
echo "ps aux | grep whatsapp\n";
echo "lsof -i :3000\n\n";

echo "6️⃣ TESTAR STATUS ATUAL:\n";
echo "------------------------\n";
echo "curl http://localhost:3000/status\n";
echo "curl http://localhost:3000/qr\n\n";

echo "🔧 COMANDOS DE CONFIGURAÇÃO:\n";
echo "============================\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "OPÇÃO A - CONFIGURAR VIA PM2 ENV:\n";
echo "---------------------------------\n";
echo "pm2 set whatsapp-3000:WEBHOOK_URL \"$webhook_url\"\n";
echo "pm2 restart whatsapp-3000\n";
echo "pm2 logs whatsapp-3000 --lines 5\n\n";

echo "OPÇÃO B - ARQUIVO .ENV (se existir):\n";
echo "------------------------------------\n";
echo "# Primeiro, localizar arquivo .env:\n";
echo "find /root -name '.env' 2>/dev/null\n";
echo "find /home -name '.env' 2>/dev/null\n";
echo "# Se encontrar, editar:\n";
echo "echo 'WEBHOOK_URL=$webhook_url' >> /caminho/para/.env\n\n";

echo "OPÇÃO C - ARQUIVO CONFIG.JSON:\n";
echo "-------------------------------\n";
echo "# Criar ou editar config.json no diretório do projeto:\n";
echo "cat > /caminho/para/config.json << 'EOF'\n";
echo "{\n";
echo "  \"webhook\": \"$webhook_url\",\n";
echo "  \"webhookEnabled\": true,\n";
echo "  \"port\": 3000\n";
echo "}\n";
echo "EOF\n\n";

echo "OPÇÃO D - RECRIAR PROCESSO PM2:\n";
echo "--------------------------------\n";
echo "pm2 delete whatsapp-3000\n";
echo "pm2 start /caminho/para/app.js --name whatsapp-3000 --env WEBHOOK_URL=\"$webhook_url\"\n\n";

echo "🧪 TESTES APÓS CONFIGURAÇÃO:\n";
echo "============================\n";
echo "curl http://localhost:3000/status\n";
echo "pm2 logs whatsapp-3000 --lines 10\n";
echo "# Procurar por mensagens como:\n";
echo "# ✅ 'Webhook configurado para: https://app...'\n";
echo "# ✅ 'Webhook URL set to: https://app...'\n";
echo "# ✅ 'Listening on port 3000'\n\n";

echo "📱 TESTE FINAL:\n";
echo "===============\n";
echo "# No seu computador, execute:\n";
echo "php verificar_mensagens_recentes.php\n";
echo "# Envie mensagem do WhatsApp para canal 3000\n";
echo "# Aguarde 30 segundos\n";
echo "# Execute novamente:\n";
echo "php verificar_mensagens_recentes.php\n\n";

echo "🔍 COMANDOS DE DEPURAÇÃO:\n";
echo "=========================\n";
echo "# Se não funcionar, verifique:\n";
echo "pm2 logs whatsapp-3000 --lines 50\n";
echo "journalctl -u whatsapp-3000 -n 20\n";
echo "cat /var/log/pm2.log\n";
echo "systemctl status pm2-root.service\n\n";

echo "📝 COMANDOS PARA COLETAR INFORMAÇÕES:\n";
echo "=====================================\n";
echo "echo '=== PM2 INFO ==='\n";
echo "pm2 show whatsapp-3000\n";
echo "echo '=== LOGS ==='\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "echo '=== ARQUIVOS ==='\n";
echo "find / -name '*whatsapp*' -type f 2>/dev/null | head -10\n";
echo "echo '=== PORTA ==='\n";
echo "netstat -tulpn | grep 3000\n\n";

echo "💡 DICAS IMPORTANTES:\n";
echo "=====================\n";
echo "1. O webhook deve ser: $webhook_url\n";
echo "2. Depois de qualquer mudança, sempre restart: pm2 restart whatsapp-3000\n";
echo "3. Verifique os logs para confirmar: pm2 logs whatsapp-3000\n";
echo "4. Teste o status: curl http://localhost:3000/status\n";
echo "5. Se der erro, copie e cole os logs para análise\n\n";

echo "🎯 OBJETIVO:\n";
echo "============\n";
echo "Fazer o VPS enviar mensagens recebidas do WhatsApp para nosso webhook!\n";
echo "Nossa aplicação já está 100% funcional, só falta a conexão!\n";

?> 