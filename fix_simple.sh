#!/bin/bash

echo "Corrigindo erro da linha 139..."

# Backup
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup

# Remover linha 139 (chave extra)
sed -i '139d' /var/whatsapp-api/whatsapp-api-server.js

echo "Linha 139 removida!"

# Testar sintaxe
echo "Testando sintaxe..."
node -c /var/whatsapp-api/whatsapp-api-server.js

if [ $? -eq 0 ]; then
    echo "✅ Sintaxe OK! Reiniciando PM2..."
    
    pm2 stop whatsapp-api
    pm2 delete whatsapp-api
    pm2 start /var/whatsapp-api/whatsapp-api-server.js --name whatsapp-api
    
    echo "Status:"
    pm2 status
    
    echo "Testando API em 3 segundos..."
    sleep 3
    curl http://localhost:3000/test
else
    echo "❌ Ainda há erros!"
fi 