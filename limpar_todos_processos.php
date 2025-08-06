<?php
/**
 * 🧹 LIMPAR TODOS OS PROCESSOS NODE.JS
 * 
 * Este script remove todos os processos Node.js que estão atrapalhando
 */

echo "🧹 LIMPAR TODOS OS PROCESSOS NODE.JS\n";
echo "====================================\n\n";

echo "🎯 OBJETIVO:\n";
echo "============\n";
echo "Remover todos os processos Node.js que estão atrapalhando a aplicação\n";
echo "Manter apenas os processos relacionados ao Azure\n\n";

echo "🔧 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "=================================\n";
echo "Execute estes comandos EXATOS na VPS:\n\n";

echo "1️⃣ Parar todos os serviços PM2:\n";
echo "==============================\n";
echo "pm2 stop all\n";
echo "pm2 delete all\n\n";

echo "2️⃣ Matar todos os processos Node.js:\n";
echo "====================================\n";
echo "pkill -f node\n";
echo "pkill -f whatsapp\n";
echo "sleep 3\n\n";

echo "3️⃣ Matar processos específicos da porta 3000:\n";
echo "============================================\n";
echo "sudo fuser -k 3000/tcp 2>/dev/null || true\n";
echo "sudo fuser -k 3001/tcp 2>/dev/null || true\n";
echo "sleep 3\n\n";

echo "4️⃣ Verificar se ainda há processos Node.js:\n";
echo "===========================================\n";
echo "ps aux | grep node | grep -v grep\n\n";

echo "5️⃣ Matar processos restantes:\n";
echo "=============================\n";
echo "ps aux | grep node | grep -v grep | awk '{print \$2}' | xargs -r kill -9\n\n";

echo "6️⃣ Verificar se as portas estão livres:\n";
echo "======================================\n";
echo "netstat -tlnp | grep :3000 || echo 'Porta 3000 livre'\n";
echo "netstat -tlnp | grep :3001 || echo 'Porta 3001 livre'\n\n";

echo "7️⃣ Limpar cache e sessões:\n";
echo "==========================\n";
echo "rm -rf /var/whatsapp-api/sessions/*\n";
echo "rm -rf /var/whatsapp-api/.wwebjs_cache/*\n";
echo "rm -rf /var/whatsapp-api/.wwebjs_auth/*\n\n";

echo "8️⃣ Reiniciar apenas os serviços necessários:\n";
echo "============================================\n";
echo "PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n";
echo "sleep 5\n";
echo "PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001\n\n";

echo "9️⃣ Aguardar inicialização:\n";
echo "=========================\n";
echo "sleep 15\n\n";

echo "🔟 Verificar status:\n";
echo "===================\n";
echo "pm2 status\n";
echo "pm2 logs whatsapp-3000 --lines 10\n";
echo "pm2 logs whatsapp-3001 --lines 10\n\n";

echo "1️⃣1️⃣ Testar endpoints:\n";
echo "=====================\n";
echo "curl -s http://127.0.0.1:3000/status | jq .\n";
echo "curl -s http://127.0.0.1:3001/status | jq .\n";
echo "curl -s \"http://127.0.0.1:3000/qr?session=default\" | jq .\n";
echo "curl -s \"http://127.0.0.1:3001/qr?session=comercial\" | jq .\n\n";

echo "1️⃣2️⃣ Salvar configuração:\n";
echo "=======================\n";
echo "pm2 save\n\n";

echo "🎯 SCRIPT AUTOMATIZADO PARA VPS:\n";
echo "===============================\n";
echo "Crie este script na VPS e execute:\n\n";

$script_vps = '#!/bin/bash
echo "🧹 LIMPAR TODOS OS PROCESSOS NODE.JS..."
cd /var/whatsapp-api

echo "1. Parando todos os serviços PM2..."
pm2 stop all
pm2 delete all

echo "2. Matando todos os processos Node.js..."
pkill -f node
pkill -f whatsapp
sleep 3

echo "3. Matando processos específicos da porta 3000..."
sudo fuser -k 3000/tcp 2>/dev/null || true
sudo fuser -k 3001/tcp 2>/dev/null || true
sleep 3

echo "4. Verificando se ainda há processos Node.js..."
ps aux | grep node | grep -v grep

echo "5. Matando processos restantes..."
ps aux | grep node | grep -v grep | awk "{print \$2}" | xargs -r kill -9

echo "6. Verificando se as portas estão livres..."
netstat -tlnp | grep :3000 || echo "Porta 3000 livre"
netstat -tlnp | grep :3001 || echo "Porta 3001 livre"

echo "7. Limpando cache e sessões..."
rm -rf /var/whatsapp-api/sessions/*
rm -rf /var/whatsapp-api/.wwebjs_cache/*
rm -rf /var/whatsapp-api/.wwebjs_auth/*

echo "8. Reiniciando apenas os serviços necessários..."
PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000
sleep 5
PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001

echo "9. Aguardando inicialização..."
sleep 15

echo "10. Verificando status..."
pm2 status
pm2 logs whatsapp-3000 --lines 10
pm2 logs whatsapp-3001 --lines 10

echo "11. Testando endpoints..."
echo "Status VPS 3000:"
curl -s http://127.0.0.1:3000/status | jq . || echo "Erro ao testar status 3000"

echo "Status VPS 3001:"
curl -s http://127.0.0.1:3001/status | jq . || echo "Erro ao testar status 3001"

echo "QR Code VPS 3000:"
curl -s "http://127.0.0.1:3000/qr?session=default" | jq . || echo "Erro ao testar QR 3000"

echo "QR Code VPS 3001:"
curl -s "http://127.0.0.1:3001/qr?session=comercial" | jq . || echo "Erro ao testar QR 3001"

echo "12. Salvando configuração..."
pm2 save

echo "✅ LIMPEZA CONCLUÍDA!"
';

echo $script_vps;

echo "\n🎯 INSTRUÇÕES FINAIS:\n";
echo "=====================\n";
echo "1. Execute os comandos acima na VPS\n";
echo "2. Aguarde 2-3 minutos para a inicialização completa\n";
echo "3. Verifique se os QR Codes aparecem\n";
echo "4. Teste a conexão no painel\n\n";

echo "🔧 SE AINDA HOUVER PROBLEMAS:\n";
echo "============================\n";
echo "1. Verificar logs detalhados:\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n";
echo "   pm2 logs whatsapp-3001 --lines 100\n\n";

echo "2. Verificar se há erros no código:\n";
echo "   cat /var/whatsapp-api/whatsapp-api-server.js | grep -n 'error\\|Error\\|console.error'\n\n";

echo "3. Verificar se as dependências estão instaladas:\n";
echo "   cd /var/whatsapp-api\n";
echo "   npm list\n\n";

echo "4. Verificar se há problemas de permissão:\n";
echo "   ls -la /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "5. Verificar se há problemas de rede:\n";
echo "   netstat -tlnp | grep :3000\n";
echo "   netstat -tlnp | grep :3001\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos na VPS para limpar todos os processos!\n";
?> 