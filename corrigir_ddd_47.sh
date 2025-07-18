#!/bin/bash

echo "🔧 Corrigindo DDD 47 para usar 8 dígitos..."

# Backup
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup.ddd47

# Remover 47 da lista de DDDs com 9 dígitos
sed -i "s/'47', //g" /var/whatsapp-api/whatsapp-api-server.js

# Adicionar 47 na lista de DDDs com 8 dígitos
sed -i "s/'23', '25', '26', '29', '36', '39', '40'/'23', '25', '26', '29', '36', '39', '40', '47'/g" /var/whatsapp-api/whatsapp-api-server.js

# Testar sintaxe
echo "Testando sintaxe..."
node -c /var/whatsapp-api/whatsapp-api-server.js

if [ $? -eq 0 ]; then
    echo "✅ Sintaxe OK! Reiniciando PM2..."
    pm2 restart whatsapp-api
    
    echo "Status:"
    pm2 status
    
    echo "Testando formatação DDD 47..."
    sleep 5
    curl -X POST http://localhost:3000/send -H "Content-Type: application/json" -d '{"to": "47996164699", "message": "Teste DDD 47 corrigido"}'
else
    echo "❌ Erro de sintaxe!"
fi 