#!/bin/bash

echo "🔧 Corrigindo TODOS os problemas do whatsapp-api-server.js..."

# Backup do arquivo original
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)

# 1. Corrigir ponto e vírgula duplo na linha 151
sed -i '151s/let formattedNumber = formatarNumeroBrasileiro(to);;/let formattedNumber = formatarNumeroBrasileiro(to);/' /var/whatsapp-api/whatsapp-api-server.js

# 2. Remover linha 139 problemática se existir
sed -i '139d' /var/whatsapp-api/whatsapp-api-server.js

# 3. Verificar se há outros pontos e vírgulas duplos
sed -i 's/;;/;/g' /var/whatsapp-api/whatsapp-api-server.js

# 4. Verificar se há aspas malformadas
sed -i "s/'\\''/'/g" /var/whatsapp-api/whatsapp-api-server.js

# 5. Testar sintaxe
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
    echo "Verificando linha 139:"
    sed -n '135,145p' /var/whatsapp-api/whatsapp-api-server.js
fi 