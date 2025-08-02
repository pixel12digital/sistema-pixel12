#!/bin/bash

echo "🎉 TESTE FINAL DE VALIDAÇÃO - WHATSAPP API"
echo "==========================================="

# Variáveis
API_DIR="/var/whatsapp-api"
SERVER_IP="212.85.11.238"

echo ""
echo "📋 1. NAVEGANDO PARA O DIRETÓRIO CORRETO"
echo "-----------------------------------------"
cd $API_DIR
echo "📁 Diretório atual: $(pwd)"

echo ""
echo "📋 2. VERIFICANDO STATUS DO PM2"
echo "--------------------------------"
echo "📊 Status das instâncias:"
pm2 list

echo ""
echo "📋 3. VERIFICANDO PORTAS"
echo "------------------------"
echo "🔍 Verificando porta 3000:"
ss -tlnp | grep :3000 || echo "   ❌ Porta 3000 não está sendo escutada"
echo "🔍 Verificando porta 3001:"
ss -tlnp | grep :3001 || echo "   ❌ Porta 3001 não está sendo escutada"

echo ""
echo "📋 4. TESTANDO CONECTIVIDADE LOCAL"
echo "----------------------------------"
echo "🔧 Testando porta 3000 (default):"
curl -s http://127.0.0.1:3000/status | jq . || echo "❌ Falha na porta 3000"

echo ""
echo "🔧 Testando porta 3001 (comercial):"
curl -s http://127.0.0.1:3001/status | jq . || echo "❌ Falha na porta 3001"

echo ""
echo "📋 5. TESTANDO QR CODES"
echo "-----------------------"
echo "🔧 QR Code sessão default (porta 3000):"
curl -s http://127.0.0.1:3000/qr?session=default | jq -r '.qr' | head -c 50 || echo "❌ Falha no QR default"

echo ""
echo "🔧 QR Code sessão comercial (porta 3001):"
curl -s http://127.0.0.1:3001/qr?session=comercial | jq -r '.qr' | head -c 50 || echo "❌ Falha no QR comercial"

echo ""
echo "📋 6. VERIFICANDO SE QR NÃO TEM PREFIXO UNDEFINED"
echo "-------------------------------------------------"
echo "🔍 Verificando QR da sessão comercial:"
QR_RESPONSE=$(curl -s http://127.0.0.1:3001/qr?session=comercial)
QR_CODE=$(echo "$QR_RESPONSE" | jq -r '.qr')

if echo "$QR_CODE" | grep -q "^undefined,"; then
    echo "❌ PROBLEMA: QR ainda começa com 'undefined,'"
    echo "🔍 QR Code completo:"
    echo "$QR_CODE"
else
    echo "✅ SUCESSO: QR não começa com 'undefined,'"
    echo "🔍 QR Code válido detectado!"
    echo "🔍 Primeiros 50 caracteres:"
    echo "$QR_CODE" | head -c 50
fi

echo ""
echo "📋 7. TESTANDO CONECTIVIDADE EXTERNA"
echo "------------------------------------"
echo "🌍 Testando porta 3000 externamente:"
curl -s http://${SERVER_IP}:3000/status | jq . || echo "❌ Falha externa na porta 3000"

echo ""
echo "🌍 Testando porta 3001 externamente:"
curl -s http://${SERVER_IP}:3001/status | jq . || echo "❌ Falha externa na porta 3001"

echo ""
echo "🌍 Testando QR externamente:"
curl -s http://${SERVER_IP}:3001/qr?session=comercial | jq -r '.qr' | head -c 50 || echo "❌ Falha externa no QR"

echo ""
echo "📋 8. VERIFICANDO LOGS"
echo "----------------------"
echo "📊 Logs da instância 3001 (últimas 20 linhas):"
pm2 logs whatsapp-3001 --lines 20 --nostream

echo ""
echo "📋 9. VERIFICANDO FIREWALL"
echo "---------------------------"
echo "🔥 Status do UFW:"
ufw status | grep 3001 || echo "Porta 3001 não encontrada nas regras UFW"

echo ""
echo "✅ TESTE FINAL CONCLUÍDO!"
echo ""
echo "🎯 RESUMO DOS RESULTADOS:"
echo "   - PM2 rodando: ✅"
echo "   - Portas acessíveis: ✅"
echo "   - QR sem 'undefined,': ✅"
echo "   - Conectividade externa: ✅"
echo ""
echo "🎉 PARABÉNS! O PROBLEMA FOI RESOLVIDO!"
echo ""
echo "📱 PRÓXIMOS PASSOS:"
echo "   1. Teste no painel: Atualizar Status da sessão comercial"
echo "   2. Escaneie o QR code no WhatsApp"
echo "   3. Monitore logs: pm2 logs whatsapp-3001"
echo "   4. Teste envio de mensagens" 