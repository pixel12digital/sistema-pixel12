<?php
/**
 * 🔍 VERIFICAÇÃO FINAL DOS LOGS
 * 
 * Este script verifica os logs e identifica o problema final
 */

echo "🔍 VERIFICAÇÃO FINAL DOS LOGS\n";
echo "============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';

echo "🎯 STATUS ATUAL:\n";
echo "================\n";
echo "✅ Serviços estão rodando\n";
echo "❌ VPS 3000: ready: false - QR não disponível\n";
echo "❌ VPS 3001: ready: false - QR não disponível\n\n";

echo "🔧 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "=================================\n";
echo "Execute estes comandos em sequência:\n\n";

echo "1️⃣ Verificar logs detalhados:\n";
echo "============================\n";
echo "pm2 logs whatsapp-3000 --lines 50\n";
echo "pm2 logs whatsapp-3001 --lines 50\n\n";

echo "2️⃣ Verificar se há erros no código:\n";
echo "==================================\n";
echo "cat /var/whatsapp-api/whatsapp-api-server.js | grep -n 'error\\|Error\\|console.error'\n\n";

echo "3️⃣ Verificar se as dependências estão instaladas:\n";
echo "================================================\n";
echo "cd /var/whatsapp-api\n";
echo "npm list\n\n";

echo "4️⃣ Verificar se o arquivo está correto:\n";
echo "======================================\n";
echo "head -50 /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "5️⃣ Verificar se há problemas de permissão:\n";
echo "==========================================\n";
echo "ls -la /var/whatsapp-api/\n";
echo "ls -la /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "6️⃣ Verificar se há problemas de rede:\n";
echo "====================================\n";
echo "netstat -tlnp | grep :3000\n";
echo "netstat -tlnp | grep :3001\n\n";

echo "7️⃣ Verificar se há problemas de memória:\n";
echo "========================================\n";
echo "free -h\n";
echo "df -h\n\n";

echo "8️⃣ Verificar se há problemas de processo:\n";
echo "=========================================\n";
echo "ps aux | grep node\n";
echo "ps aux | grep whatsapp\n\n";

echo "🔧 SOLUÇÃO ALTERNATIVA:\n";
echo "=======================\n";
echo "Se os logs mostrarem problemas, execute:\n\n";

echo "1. Parar todos os serviços:\n";
echo "   pm2 stop all\n";
echo "   pm2 delete all\n\n";

echo "2. Limpar completamente:\n";
echo "   sudo fuser -k 3000/tcp 2>/dev/null || true\n";
echo "   sudo fuser -k 3001/tcp 2>/dev/null || true\n";
echo "   sleep 5\n\n";

echo "3. Verificar se as portas estão livres:\n";
echo "   netstat -tlnp | grep :3000 || echo 'Porta 3000 livre'\n";
echo "   netstat -tlnp | grep :3001 || echo 'Porta 3001 livre'\n\n";

echo "4. Reiniciar com força:\n";
echo "   PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000 -f\n";
echo "   sleep 5\n";
echo "   PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001 -f\n\n";

echo "5. Aguardar inicialização:\n";
echo "   sleep 20\n\n";

echo "6. Verificar status:\n";
echo "   pm2 status\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n\n";

echo "7. Testar endpoints:\n";
echo "   curl -s http://127.0.0.1:3000/status | jq .\n";
echo "   curl -s http://127.0.0.1:3001/status | jq .\n\n";

echo "8. Testar QR Codes:\n";
echo "   curl -s \"http://127.0.0.1:3000/qr?session=default\" | jq .\n";
echo "   curl -s \"http://127.0.0.1:3001/qr?session=comercial\" | jq .\n\n";

echo "🎯 SCRIPT AUTOMATIZADO PARA VPS:\n";
echo "===============================\n";
echo "Crie este script na VPS e execute:\n\n";

$script_vps = '#!/bin/bash
echo "🔍 VERIFICAÇÃO FINAL DOS LOGS..."
cd /var/whatsapp-api

echo "1. Verificando logs..."
echo "=== LOGS VPS 3000 ==="
pm2 logs whatsapp-3000 --lines 50

echo "=== LOGS VPS 3001 ==="
pm2 logs whatsapp-3001 --lines 50

echo "2. Verificando erros no código..."
cat /var/whatsapp-api/whatsapp-api-server.js | grep -n "error\|Error\|console.error" || echo "Nenhum erro encontrado no código"

echo "3. Verificando dependências..."
npm list

echo "4. Verificando arquivo..."
head -50 /var/whatsapp-api/whatsapp-api-server.js

echo "5. Verificando permissões..."
ls -la /var/whatsapp-api/
ls -la /var/whatsapp-api/whatsapp-api-server.js

echo "6. Verificando rede..."
netstat -tlnp | grep :3000 || echo "Porta 3000 livre"
netstat -tlnp | grep :3001 || echo "Porta 3001 livre"

echo "7. Verificando recursos..."
free -h
df -h

echo "8. Verificando processos..."
ps aux | grep node
ps aux | grep whatsapp

echo "✅ VERIFICAÇÃO CONCLUÍDA!"
';

echo $script_vps;

echo "\n🎯 INSTRUÇÕES FINAIS:\n";
echo "=====================\n";
echo "1. Execute os comandos de verificação acima\n";
echo "2. Analise os logs para identificar o problema\n";
echo "3. Se necessário, execute a solução alternativa\n";
echo "4. Teste a conexão no painel\n\n";

echo "🔧 SE AINDA HOUVER PROBLEMAS:\n";
echo "============================\n";
echo "1. Verificar se o WhatsApp Web está funcionando:\n";
echo "   - Acesse https://web.whatsapp.com\n";
echo "   - Verifique se consegue conectar\n\n";

echo "2. Verificar se há problemas de firewall:\n";
echo "   - Verificar se as portas 3000 e 3001 estão abertas\n";
echo "   - Verificar se há bloqueios de rede\n\n";

echo "3. Verificar se há problemas de DNS:\n";
echo "   - Verificar se o domínio está resolvendo corretamente\n";
echo "   - Verificar se há problemas de certificado SSL\n\n";

echo "4. Verificar se há problemas de versão:\n";
echo "   - Verificar se o Node.js está atualizado\n";
echo "   - Verificar se as dependências estão atualizadas\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos na VPS para identificar o problema!\n";
?> 