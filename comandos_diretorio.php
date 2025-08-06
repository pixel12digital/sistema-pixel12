<?php
/**
 * 📁 COMANDOS PARA NAVEGAR PARA O DIRETÓRIO CORRETO
 */

echo "📁 COMANDOS PARA NAVEGAR PARA O DIRETÓRIO CORRETO\n";
echo "================================================\n\n";

echo "🎯 DIRETÓRIO ATUAL:\n";
echo "==================\n";
echo "Você está em: root@srv817568:~#\n";
echo "Precisa ir para: /var/whatsapp-api\n\n";

echo "🔧 COMANDOS PARA EXECUTAR:\n";
echo "=========================\n";
echo "1️⃣ Navegar para o diretório:\n";
echo "===========================\n";
echo "cd /var/whatsapp-api\n\n";

echo "2️⃣ Verificar se está no diretório correto:\n";
echo "=========================================\n";
echo "pwd\n";
echo "ls -la\n\n";

echo "3️⃣ Verificar se o arquivo existe:\n";
echo "================================\n";
echo "ls -la whatsapp-api-server.js\n\n";

echo "4️⃣ Agora executar os comandos de correção:\n";
echo "==========================================\n";
echo "pm2 stop whatsapp-3000\n";
echo "pm2 delete whatsapp-3000\n";
echo "sudo fuser -k 3000/tcp 2>/dev/null || true\n";
echo "sleep 3\n";
echo "netstat -tlnp | grep :3000 || echo 'Porta 3000 livre'\n";
echo "PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n";
echo "sleep 10\n";
echo "pm2 status\n";
echo "curl -s http://127.0.0.1:3000/status | jq .\n";
echo "curl -s \"http://127.0.0.1:3000/qr?session=default\" | jq .\n";
echo "pm2 save\n\n";

echo "🎯 SEQUÊNCIA COMPLETA:\n";
echo "=====================\n";
echo "cd /var/whatsapp-api && pwd && ls -la whatsapp-api-server.js\n\n";

echo "✅ DEPOIS EXECUTE:\n";
echo "=================\n";
echo "pm2 stop whatsapp-3000\n";
echo "pm2 delete whatsapp-3000\n";
echo "sudo fuser -k 3000/tcp 2>/dev/null || true\n";
echo "sleep 3\n";
echo "PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n";
echo "sleep 10\n";
echo "pm2 status\n";
echo "curl -s http://127.0.0.1:3000/status | jq .\n";
echo "curl -s \"http://127.0.0.1:3000/qr?session=default\" | jq .\n";
echo "pm2 save\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute primeiro: cd /var/whatsapp-api\n";
echo "Depois execute os comandos de correção!\n";
?> 