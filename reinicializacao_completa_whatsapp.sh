#!/bin/bash

echo "🔄 REINICIALIZAÇÃO COMPLETA WHATSAPP API"
echo "========================================"

# Variáveis
API_DIR="/var/whatsapp-api"
SERVER_IP="212.85.11.238"

echo ""
echo "📋 1. PARANDO TODAS AS INSTÂNCIAS"
echo "----------------------------------"
echo "🛑 Parando todas as instâncias do PM2..."
pm2 delete all

echo ""
echo "📋 2. VERIFICANDO ESTRUTURA DE ARQUIVOS"
echo "----------------------------------------"
echo "📁 Diretório atual:"
pwd

echo ""
echo "📁 Verificando se ecosystem.config.js existe:"
if [ -f "ecosystem.config.js" ]; then
    echo "✅ ecosystem.config.js encontrado"
    echo "📄 Conteúdo do ecosystem.config.js:"
    cat ecosystem.config.js | head -20
else
    echo "❌ ecosystem.config.js NÃO encontrado"
    echo "🔍 Procurando em outros locais..."
    find / -name "ecosystem.config.js" 2>/dev/null | head -5
    exit 1
fi

echo ""
echo "📁 Verificando se whatsapp-api-server.js existe:"
if [ -f "whatsapp-api-server.js" ]; then
    echo "✅ whatsapp-api-server.js encontrado"
else
    echo "❌ whatsapp-api-server.js NÃO encontrado"
    exit 1
fi

echo ""
echo "📋 3. CONFIGURANDO FIREWALL"
echo "----------------------------"
echo "🔥 Permitindo porta 3001 no UFW..."
ufw allow 3001/tcp
ufw reload

echo ""
echo "📋 4. INICIANDO COM CAMINHO ABSOLUTO"
echo "------------------------------------"
echo "🚀 Iniciando PM2 com caminho absoluto..."
pm2 start $(pwd)/ecosystem.config.js

echo ""
echo "📋 5. SALVANDO CONFIGURAÇÃO"
echo "---------------------------"
echo "💾 Salvando configuração do PM2..."
pm2 save

echo ""
echo "📋 6. VERIFICANDO VARIÁVEIS DE AMBIENTE"
echo "---------------------------------------"
echo "🔍 Verificando variáveis da instância 3001:"
pm2 env whatsapp-3001 | grep PORT || echo "❌ Variável PORT não encontrada"

echo ""
echo "📋 7. AGUARDANDO INICIALIZAÇÃO"
echo "-------------------------------"
echo "⏳ Aguardando 10 segundos para inicialização completa..."
sleep 10

echo ""
echo "📋 8. VERIFICANDO STATUS"
echo "------------------------"
echo "📊 Status do PM2:"
pm2 list

echo ""
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
echo "📋 9. TESTANDO CONECTIVIDADE"
echo "-----------------------------"
echo "🔧 Testando porta 3000 localmente:"
curl -s http://127.0.0.1:3000/status | jq . || echo "❌ Falha na porta 3000"

echo ""
echo "🔧 Testando porta 3001 localmente:"
curl -s http://127.0.0.1:3001/status | jq . || echo "❌ Falha na porta 3001"

echo ""
echo "🔧 Testando QR da sessão comercial localmente:"
curl -s http://127.0.0.1:3001/qr?session=comercial | jq . || echo "❌ Falha no QR comercial"

echo ""
echo "🌍 Testando porta 3001 externamente:"
curl -s http://${SERVER_IP}:3001/status | jq . || echo "❌ Falha externa na porta 3001"

echo ""
echo "🌍 Testando QR da sessão comercial externamente:"
curl -s http://${SERVER_IP}:3001/qr?session=comercial | jq . || echo "❌ Falha externa no QR comercial"

echo ""
echo "📋 10. MONITORANDO LOGS"
echo "-----------------------"
echo "📊 Logs da instância 3001 (últimas 30 linhas):"
pm2 logs whatsapp-3001 --lines 30 --nostream

echo ""
echo "🔍 Procurando por logs específicos de inicialização:"
pm2 logs whatsapp-3001 --lines 50 --nostream | grep -E "(API rodando|Binding confirmado|Inicializando sessão|comercial)" || echo "Nenhum log de inicialização encontrado"

echo ""
echo "✅ REINICIALIZAÇÃO COMPLETA CONCLUÍDA!"
echo ""
echo "🧪 PRÓXIMOS TESTES:"
echo "   1. Abra no navegador: http://${SERVER_IP}:3001/qr?session=comercial"
echo "   2. Teste no painel: Atualizar Status da sessão comercial"
echo "   3. Monitore logs em tempo real: pm2 logs whatsapp-3001" 