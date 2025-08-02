#!/bin/bash

echo "🧪 Testando QR Code corrigido..."

# Aguardar um pouco para o servidor inicializar
echo "⏳ Aguardando inicialização do servidor..."
sleep 5

# Testar endpoint de status geral
echo "📊 Testando status geral da porta 3001..."
curl -s http://localhost:3001/status | jq .

echo ""
echo "📱 Testando status da sessão comercial..."
curl -s http://localhost:3001/session/comercial/status | jq .

echo ""
echo "🔍 Testando QR code da sessão comercial..."
QR_RESPONSE=$(curl -s http://localhost:3001/qr?session=comercial)
echo "$QR_RESPONSE" | jq .

# Verificar se o QR não começa com "undefined,"
echo ""
echo "🔍 Verificando se o QR não começa com 'undefined,'..."
if echo "$QR_RESPONSE" | jq -r '.qr' | grep -q "^undefined,"; then
    echo "❌ PROBLEMA: QR ainda começa com 'undefined,'"
    echo "🔍 QR Code completo:"
    echo "$QR_RESPONSE" | jq -r '.qr'
else
    echo "✅ SUCESSO: QR não começa com 'undefined,'"
    echo "🔍 QR Code válido detectado!"
fi

echo ""
echo "📋 Logs recentes da instância 3001:"
pm2 logs whatsapp-3001 --lines 30 --nostream | grep -E "(QR payload raw|sessionName|DEBUG|Inicializando sessão)"

echo ""
echo "✅ Teste concluído!" 