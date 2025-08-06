<?php
/**
 * 🔧 CORRIGIR CONECTIVIDADE EXTERNA
 * 
 * Este script corrige problemas de conectividade externa dos serviços WhatsApp
 */

echo "🔧 CORRIGIR CONECTIVIDADE EXTERNA\n";
echo "=================================\n\n";

echo "🎯 PROBLEMA IDENTIFICADO:\n";
echo "=========================\n";
echo "❌ Os serviços estão rodando na VPS mas não são acessíveis externamente\n";
echo "🔍 Possíveis causas:\n";
echo "   - Firewall bloqueando as portas 3000 e 3001\n";
echo "   - Serviços não estão escutando em 0.0.0.0\n";
echo "   - Problemas de rede externa\n\n";

echo "🔧 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "=================================\n";
echo "Execute estes comandos EXATOS na VPS:\n\n";

echo "1️⃣ Verificar se os serviços estão rodando:\n";
echo "==========================================\n";
echo "pm2 status\n";
echo "netstat -tlnp | grep :3000\n";
echo "netstat -tlnp | grep :3001\n\n";

echo "2️⃣ Verificar se há firewall ativo:\n";
echo "==================================\n";
echo "iptables -L | grep -E '(3000|3001)'\n";
echo "ufw status\n";
echo "systemctl status firewalld\n\n";

echo "3️⃣ Abrir portas no firewall (se necessário):\n";
echo "============================================\n";
echo "iptables -A INPUT -p tcp --dport 3000 -j ACCEPT\n";
echo "iptables -A INPUT -p tcp --dport 3001 -j ACCEPT\n";
echo "iptables-save > /etc/iptables/rules.v4\n\n";

echo "4️⃣ Verificar se os serviços estão escutando em 0.0.0.0:\n";
echo "=======================================================\n";
echo "netstat -tlnp | grep -E '(3000|3001)'\n";
echo "ss -tlnp | grep -E '(3000|3001)'\n\n";

echo "5️⃣ Verificar configuração do whatsapp-api-server.js:\n";
echo "==================================================\n";
echo "grep -n 'listen\|port' /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "6️⃣ Reiniciar serviços com configuração correta:\n";
echo "==============================================\n";
echo "pm2 stop all\n";
echo "pm2 delete all\n";
echo "sleep 3\n";
echo "PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n";
echo "sleep 5\n";
echo "PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001\n";
echo "sleep 10\n";
echo "pm2 status\n\n";

echo "7️⃣ Verificar se as portas estão acessíveis:\n";
echo "===========================================\n";
echo "curl -s http://127.0.0.1:3000/status | jq .\n";
echo "curl -s http://127.0.0.1:3001/status | jq .\n";
echo "curl -s http://0.0.0.0:3000/status | jq .\n";
echo "curl -s http://0.0.0.0:3001/status | jq .\n\n";

echo "8️⃣ Testar conectividade externa:\n";
echo "================================\n";
echo "curl -s http://45.79.199.138:3000/status | jq .\n";
echo "curl -s http://45.79.199.138:3001/status | jq .\n\n";

echo "9️⃣ Verificar logs de erro:\n";
echo "==========================\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "🔧 SCRIPT AUTOMATIZADO PARA VPS:\n";
echo "===============================\n";
echo "Crie este script na VPS e execute:\n\n";

$script_vps = '#!/bin/bash
echo "🔧 CORRIGINDO CONECTIVIDADE EXTERNA..."

cd /var/whatsapp-api

echo "1. Verificando status atual..."
pm2 status
netstat -tlnp | grep -E "(3000|3001)"

echo "2. Verificando firewall..."
iptables -L | grep -E "(3000|3001)" || echo "Nenhuma regra encontrada para 3000/3001"

echo "3. Abrindo portas no firewall..."
iptables -A INPUT -p tcp --dport 3000 -j ACCEPT 2>/dev/null || true
iptables -A INPUT -p tcp --dport 3001 -j ACCEPT 2>/dev/null || true
iptables-save > /etc/iptables/rules.v4 2>/dev/null || true

echo "4. Parando serviços..."
pm2 stop all
pm2 delete all
sleep 3

echo "5. Verificando se as portas estão livres..."
netstat -tlnp | grep -E "(3000|3001)" || echo "Portas livres"

echo "6. Reiniciando serviços..."
PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000
sleep 5
PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001
sleep 10

echo "7. Verificando status..."
pm2 status

echo "8. Testando conectividade local..."
echo "Testando porta 3000:"
curl -s http://127.0.0.1:3000/status | jq . || echo "Erro ao testar 3000"

echo "Testando porta 3001:"
curl -s http://127.0.0.1:3001/status | jq . || echo "Erro ao testar 3001"

echo "9. Testando conectividade externa..."
echo "Testando 45.79.199.138:3000:"
curl -s http://45.79.199.138:3000/status | jq . || echo "Erro ao testar externo 3000"

echo "Testando 45.79.199.138:3001:"
curl -s http://45.79.199.138:3001/status | jq . || echo "Erro ao testar externo 3001"

echo "10. Verificando logs..."
echo "Logs whatsapp-3000:"
pm2 logs whatsapp-3000 --lines 10

echo "Logs whatsapp-3001:"
pm2 logs whatsapp-3001 --lines 10

echo "✅ CORREÇÃO CONCLUÍDA!"
';

echo $script_vps;

echo "\n🎯 VERIFICAÇÃO ADICIONAL:\n";
echo "=========================\n\n";

echo "1️⃣ Se ainda não funcionar, verificar:\n";
echo "   - Configuração do provedor de VPS (firewall)\n";
echo "   - Regras de segurança do servidor\n";
echo "   - Configuração de rede\n\n";

echo "2️⃣ Comandos de emergência:\n";
echo "   - Reiniciar servidor: reboot\n";
echo "   - Verificar serviços: systemctl status pm2-root\n";
echo "   - Verificar rede: ip addr show\n\n";

echo "3️⃣ Teste manual:\n";
echo "   - Acesse: http://45.79.199.138:3000/status\n";
echo "   - Acesse: http://45.79.199.138:3001/status\n";
echo "   - Se não funcionar, há problema de firewall externo\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos na VPS para corrigir a conectividade!\n";
?> 