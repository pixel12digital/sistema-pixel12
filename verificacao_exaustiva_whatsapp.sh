#!/bin/bash

echo "🔍 VERIFICAÇÃO EXAUSTIVA WHATSAPP API"
echo "======================================"

# Variáveis
SERVER_IP="212.85.11.238"
API_DIR="/var/whatsapp-api"

echo ""
echo "📋 1. VERIFICANDO ESTRUTURA DE ARQUIVOS"
echo "----------------------------------------"
echo "📁 Verificando se estamos no diretório correto:"
pwd

echo ""
echo "📁 Verificando se ecosystem.config.js existe:"
if [ -f "ecosystem.config.js" ]; then
    echo "✅ ecosystem.config.js encontrado"
    ls -la ecosystem.config.js
else
    echo "❌ ecosystem.config.js NÃO encontrado"
    echo "🔍 Procurando em outros locais..."
    find / -name "ecosystem.config.js" 2>/dev/null | head -5
fi

echo ""
echo "📁 Verificando se whatsapp-api-server.js existe:"
if [ -f "whatsapp-api-server.js" ]; then
    echo "✅ whatsapp-api-server.js encontrado"
    ls -la whatsapp-api-server.js
else
    echo "❌ whatsapp-api-server.js NÃO encontrado"
fi

echo ""
echo "📋 2. VERIFICANDO CONFIGURAÇÃO DO PM2"
echo "--------------------------------------"
echo "📊 Status atual do PM2:"
pm2 list

echo ""
echo "🔍 Verificando variáveis de ambiente da instância 3001:"
pm2 env whatsapp-3001 | grep PORT || echo "❌ Variável PORT não encontrada"

echo ""
echo "📋 3. VERIFICANDO BINDING DO EXPRESS"
echo "------------------------------------"
echo "🔍 Verificando se as portas estão sendo escutadas:"
echo "   Porta 3000:"
ss -tlnp | grep :3000 || echo "   ❌ Porta 3000 não está sendo escutada"
echo "   Porta 3001:"
ss -tlnp | grep :3001 || echo "   ❌ Porta 3001 não está sendo escutada"

echo ""
echo "🔍 Verificando se estão escutando em 0.0.0.0:"
echo "   Porta 3000 (0.0.0.0):"
ss -tlnp | grep :3000 | grep "0.0.0.0" || echo "   ❌ Porta 3000 não está em 0.0.0.0"
echo "   Porta 3001 (0.0.0.0):"
ss -tlnp | grep :3001 | grep "0.0.0.0" || echo "   ❌ Porta 3001 não está em 0.0.0.0"

echo ""
echo "📋 4. TESTANDO CONECTIVIDADE LOCAL"
echo "----------------------------------"
echo "🔧 Testando porta 3000 localmente:"
curl -v http://127.0.0.1:3000/status 2>&1 | head -10 || echo "❌ Falha na porta 3000"

echo ""
echo "🔧 Testando porta 3001 localmente:"
curl -v http://127.0.0.1:3001/status 2>&1 | head -10 || echo "❌ Falha na porta 3001"

echo ""
echo "🔧 Testando QR da sessão comercial localmente:"
curl -v http://127.0.0.1:3001/qr?session=comercial 2>&1 | head -15 || echo "❌ Falha no QR comercial"

echo ""
echo "📋 5. VERIFICANDO FIREWALL"
echo "---------------------------"
echo "🔥 Status do UFW:"
ufw status | grep 3001 || echo "Porta 3001 não encontrada nas regras UFW"

echo ""
echo "🔥 Verificando iptables:"
iptables -L | grep 3001 || echo "Porta 3001 não encontrada no iptables"

echo ""
echo "📋 6. TESTANDO CONECTIVIDADE EXTERNA"
echo "------------------------------------"
echo "🌍 Testando porta 3000 externamente:"
curl -v http://${SERVER_IP}:3000/status 2>&1 | head -10 || echo "❌ Falha externa na porta 3000"

echo ""
echo "🌍 Testando porta 3001 externamente:"
curl -v http://${SERVER_IP}:3001/status 2>&1 | head -10 || echo "❌ Falha externa na porta 3001"

echo ""
echo "🌍 Testando QR da sessão comercial externamente:"
curl -v http://${SERVER_IP}:3001/qr?session=comercial 2>&1 | head -15 || echo "❌ Falha externa no QR comercial"

echo ""
echo "📋 7. MONITORANDO LOGS EM TEMPO REAL"
echo "------------------------------------"
echo "📊 Logs da instância 3001 (últimas 20 linhas):"
pm2 logs whatsapp-3001 --lines 20 --nostream

echo ""
echo "🔍 Procurando por logs específicos:"
pm2 logs whatsapp-3001 --lines 50 --nostream | grep -E "(DEBUG|INIT|QR raw|Binding confirmado)" || echo "Nenhum log de debug encontrado"

echo ""
echo "📋 8. VERIFICANDO PROCESSOS"
echo "----------------------------"
echo "🔍 Processos Node.js rodando:"
ps aux | grep node | grep -v grep

echo ""
echo "🔍 Processos escutando nas portas:"
lsof -i :3000 -i :3001 2>/dev/null || echo "Nenhum processo encontrado nas portas"

echo ""
echo "✅ VERIFICAÇÃO EXAUSTIVA CONCLUÍDA!"
echo ""
echo "📝 RESUMO DOS PROBLEMAS ENCONTRADOS:"
echo "   - Se porta não está sendo escutada: Problema no PM2/Node.js"
echo "   - Se não está em 0.0.0.0: Problema no binding do Express"
echo "   - Se falha local: Problema na aplicação"
echo "   - Se falha externa mas funciona local: Problema de firewall/CORS"
echo "   - Se logs não mostram inicialização: Problema no ecosystem.config.js" 