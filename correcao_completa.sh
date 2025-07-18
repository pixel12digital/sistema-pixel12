#!/bin/bash

echo "🔧 Corrigindo TODOS os problemas do whatsapp-api-server.js..."

# Backup do arquivo original
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)

# 1. Corrigir ponto e vírgula duplo
sed -i 's/;;/;/g' /var/whatsapp-api/whatsapp-api-server.js

# 2. Corrigir a função de formatação para incluir código do país
sed -i 's/return numeroLimpo + '\''@c.us'\'';/return '\''55'\'' + numeroLimpo + '\''@c.us'\'';/' /var/whatsapp-api/whatsapp-api-server.js

# 3. Testar sintaxe
echo "Testando sintaxe..."
node -c /var/whatsapp-api/whatsapp-api-server.js

if [ $? -eq 0 ]; then
    echo "✅ Sintaxe OK! Reiniciando PM2..."
    
    pm2 stop whatsapp-api
    pm2 delete whatsapp-api
    pm2 start /var/whatsapp-api/whatsapp-api-server.js --name whatsapp-api
    
    echo "Status:"
    pm2 status
    
    echo "Aguardando 10 segundos para conectar..."
    sleep 10
    
    echo "Testando API:"
    curl -X POST http://localhost:3000/send -H "Content-Type: application/json" -d '{"to": "47996164699", "message": "Teste após correção completa"}'
    
else
    echo "❌ Ainda há erros de sintaxe!"
fi 