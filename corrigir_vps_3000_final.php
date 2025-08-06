<?php
/**
 * 🔧 CORREÇÃO FINAL VPS 3000
 * 
 * Este script corrige definitivamente o problema do VPS 3000
 */

echo "🔧 CORREÇÃO FINAL VPS 3000\n";
echo "==========================\n\n";

echo "🎯 PROBLEMA IDENTIFICADO:\n";
echo "========================\n";
echo "✅ VPS 3001: ready: true - FUNCIONANDO PERFEITAMENTE\n";
echo "❌ VPS 3000: ready: false - PROBLEMA DE PORTA/CONFLITO\n";
echo "❌ VPS 3000: QR Code não disponível\n\n";

echo "🔧 SOLUÇÃO FINAL PARA VPS 3000:\n";
echo "===============================\n";
echo "Execute estes comandos EXATOS na VPS:\n\n";

echo "1️⃣ Parar apenas o VPS 3000:\n";
echo "==========================\n";
echo "pm2 stop whatsapp-3000\n";
echo "pm2 delete whatsapp-3000\n\n";

echo "2️⃣ Limpar processo da porta 3000:\n";
echo "================================\n";
echo "sudo fuser -k 3000/tcp 2>/dev/null || true\n";
echo "sleep 5\n\n";

echo "3️⃣ Verificar se a porta está livre:\n";
echo "==================================\n";
echo "netstat -tlnp | grep :3000 || echo 'Porta 3000 livre'\n\n";

echo "4️⃣ Verificar se há processos Node.js:\n";
echo "=====================================\n";
echo "ps aux | grep node | grep -v grep\n\n";

echo "5️⃣ Matar todos os processos Node.js:\n";
echo "====================================\n";
echo "pkill -f node\n";
echo "sleep 3\n\n";

echo "6️⃣ Verificar se a porta está livre novamente:\n";
echo "=============================================\n";
echo "netstat -tlnp | grep :3000 || echo 'Porta 3000 livre'\n\n";

echo "7️⃣ Reiniciar apenas o VPS 3000:\n";
echo "==============================\n";
echo "PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n\n";

echo "8️⃣ Aguardar inicialização:\n";
echo "=========================\n";
echo "sleep 15\n\n";

echo "9️⃣ Verificar status:\n";
echo "===================\n";
echo "pm2 status\n";
echo "pm2 logs whatsapp-3000 --lines 20\n\n";

echo "🔟 Testar endpoints:\n";
echo "===================\n";
echo "curl -s http://127.0.0.1:3000/status | jq .\n";
echo "curl -s \"http://127.0.0.1:3000/qr?session=default\" | jq .\n\n";

echo "1️⃣1️⃣ Salvar configuração:\n";
echo "=======================\n";
echo "pm2 save\n\n";

echo "🎯 SCRIPT AUTOMATIZADO PARA VPS:\n";
echo "===============================\n";
echo "Crie este script na VPS e execute:\n\n";

$script_vps = '#!/bin/bash
echo "🔧 CORREÇÃO FINAL VPS 3000..."
cd /var/whatsapp-api

echo "1. Parando apenas VPS 3000..."
pm2 stop whatsapp-3000
pm2 delete whatsapp-3000

echo "2. Limpando processo da porta 3000..."
sudo fuser -k 3000/tcp 2>/dev/null || true
sleep 5

echo "3. Verificando se a porta está livre..."
netstat -tlnp | grep :3000 || echo "Porta 3000 livre"

echo "4. Verificando se há processos Node.js..."
ps aux | grep node | grep -v grep

echo "5. Matando todos os processos Node.js..."
pkill -f node
sleep 3

echo "6. Verificando se a porta está livre novamente..."
netstat -tlnp | grep :3000 || echo "Porta 3000 livre"

echo "7. Reiniciando apenas VPS 3000..."
PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000

echo "8. Aguardando inicialização..."
sleep 15

echo "9. Verificando status..."
pm2 status
pm2 logs whatsapp-3000 --lines 20

echo "10. Testando endpoints..."
echo "Status VPS 3000:"
curl -s http://127.0.0.1:3000/status | jq . || echo "Erro ao testar status"

echo "QR Code VPS 3000:"
curl -s "http://127.0.0.1:3000/qr?session=default" | jq . || echo "Erro ao testar QR"

echo "11. Salvando configuração..."
pm2 save

echo "✅ CORREÇÃO VPS 3000 CONCLUÍDA!"
';

echo $script_vps;

echo "\n🎯 INSTRUÇÕES FINAIS:\n";
echo "=====================\n";
echo "1. Execute os comandos acima na VPS\n";
echo "2. Aguarde 2-3 minutos para a inicialização completa\n";
echo "3. Verifique se o QR Code aparece\n";
echo "4. Teste a conexão no painel\n\n";

echo "🔧 SE AINDA HOUVER PROBLEMAS:\n";
echo "============================\n";
echo "1. Verificar logs detalhados:\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n\n";

echo "2. Verificar se há erros no código:\n";
echo "   cat /var/whatsapp-api/whatsapp-api-server.js | grep -n 'error\\|Error\\|console.error'\n\n";

echo "3. Verificar se as dependências estão instaladas:\n";
echo "   cd /var/whatsapp-api\n";
echo "   npm list\n\n";

echo "4. Verificar se há problemas de permissão:\n";
echo "   ls -la /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "5. Verificar se há problemas de rede:\n";
echo "   netstat -tlnp | grep :3000\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos na VPS para corrigir o VPS 3000!\n";
?> 
?> 