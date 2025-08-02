#!/bin/bash

echo "🧪 Teste Completo de Conectividade WhatsApp API"
echo "================================================"

# Variáveis
SERVER_IP="212.85.11.238"
DEFAULT_PORT="3000"
COMERCIAL_PORT="3001"

echo ""
echo "🔍 1. Verificando se as portas estão sendo escutadas:"
echo "   Porta 3000 (Default):"
ss -tlnp | grep :3000 || echo "   ❌ Porta 3000 não está sendo escutada"
echo "   Porta 3001 (Comercial):"
ss -tlnp | grep :3001 || echo "   ❌ Porta 3001 não está sendo escutada"

echo ""
echo "🌐 2. Testando conectividade local:"
echo "   Testando porta 3000 localmente:"
curl -s http://localhost:3000/status | jq . || echo "   ❌ Falha na porta 3000"
echo "   Testando porta 3001 localmente:"
curl -s http://localhost:3001/status | jq . || echo "   ❌ Falha na porta 3001"

echo ""
echo "🌍 3. Testando conectividade externa:"
echo "   Testando porta 3000 externamente:"
curl -s http://${SERVER_IP}:3000/status | jq . || echo "   ❌ Falha externa na porta 3000"
echo "   Testando porta 3001 externamente:"
curl -s http://${SERVER_IP}:3001/status | jq . || echo "   ❌ Falha externa na porta 3001"

echo ""
echo "🔗 4. Testando Proxy Reverso (se configurado):"
echo "   Testando proxy para sessão default:"
curl -s http://${SERVER_IP}/whatsapp/default/status | jq . || echo "   ❌ Falha no proxy default"
echo "   Testando proxy para sessão comercial:"
curl -s http://${SERVER_IP}/whatsapp/comercial/status | jq . || echo "   ❌ Falha no proxy comercial"

echo ""
echo "📱 5. Testando QR Codes:"
echo "   QR Code sessão default:"
curl -s http://${SERVER_IP}:3000/qr?session=default | jq -r '.qr' | head -c 50 || echo "   ❌ Falha no QR default"
echo "   QR Code sessão comercial:"
curl -s http://${SERVER_IP}:3001/qr?session=comercial | jq -r '.qr' | head -c 50 || echo "   ❌ Falha no QR comercial"

echo ""
echo "📋 6. Status do PM2:"
pm2 list

echo ""
echo "📊 7. Logs recentes da instância comercial:"
pm2 logs whatsapp-3001 --lines 10 --nostream | grep -E "(QR payload raw|sessionName|DEBUG|Inicializando sessão)" || echo "   Nenhum log relevante encontrado"

echo ""
echo "🔥 8. Status do Firewall:"
ufw status | grep 3001 || echo "   Porta 3001 não encontrada nas regras UFW"

echo ""
echo "✅ Teste completo concluído!"
echo ""
echo "📝 Resumo dos problemas encontrados:"
echo "   - Se porta não está sendo escutada: Problema no PM2/Node.js"
echo "   - Se falha local mas funciona PM2: Problema na aplicação"
echo "   - Se falha externa mas funciona local: Problema de firewall/CORS"
echo "   - Se proxy falha: Problema na configuração do Nginx" 