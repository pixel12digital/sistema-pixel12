#!/bin/bash
# Script de reconexão automática WhatsApp VPS
# Execute via: ssh root@212.85.11.238 "bash reconectar_whatsapp.sh"

echo "=== RECONEXÃO AUTOMÁTICA WHATSAPP ==="
echo "Data: $(date)"

echo "1. Verificando PM2..."
pm2 list

echo "2. Parando serviços WhatsApp..."
pm2 stop whatsapp-3000 2>/dev/null || echo "whatsapp-3000 não estava rodando"
pm2 stop whatsapp-3001 2>/dev/null || echo "whatsapp-3001 não estava rodando"

echo "3. Limpando cache/sessões antigas..."
cd /var/whatsapp-api
rm -rf .wwebjs_auth/ 2>/dev/null || echo "Sem sessões antigas"
rm -rf .wwebjs_cache/ 2>/dev/null || echo "Sem cache antigo"

echo "4. Reiniciando serviços..."
pm2 start ecosystem.config.js 2>/dev/null || pm2 restart all

echo "5. Aguardando inicialização..."
sleep 10

echo "6. Verificando status final..."
pm2 list

echo "7. Testando endpoints..."
curl -s http://localhost:3000/status | jq .ready 2>/dev/null || echo "Canal 3000: Verificar manualmente"
curl -s http://localhost:3001/status | jq .ready 2>/dev/null || echo "Canal 3001: Verificar manualmente"

echo "=== RECONEXÃO CONCLUÍDA ==="
echo "Acesse http://212.85.11.238:3000/qr para escanear QR Code"
