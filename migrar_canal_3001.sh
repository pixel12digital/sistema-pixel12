#!/bin/bash
# Script para migrar canal 3001 para API correta
# Executar na VPS: bash migrar_canal_3001.sh

echo "🔄 MIGRANDO CANAL 3001 PARA API CORRETA"
echo "====================================="

# 1. Parar serviço atual
echo "1️⃣ Parando serviço atual..."
pm2 stop whatsapp-3001
pm2 delete whatsapp-3001

# 2. Verificar se arquivo existe
echo "2️⃣ Verificando arquivo da API..."
if [ ! -f "/var/whatsapp-api/whatsapp-api-server.js" ]; then
    echo "❌ Arquivo whatsapp-api-server.js não encontrado"
    exit 1
fi

# 3. Copiar e modificar arquivo
echo "3️⃣ Copiando e modificando arquivo..."
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server-3001.js

# 4. Modificar porta e sessão
echo "4️⃣ Modificando configurações..."
sed -i 's/const PORT = 3000/const PORT = 3001/g' /var/whatsapp-api/whatsapp-api-server-3001.js
sed -i 's/sessionName: "default"/sessionName: "comercial"/g' /var/whatsapp-api/whatsapp-api-server-3001.js

# 5. Iniciar novo serviço
echo "5️⃣ Iniciando novo serviço..."
pm2 start /var/whatsapp-api/whatsapp-api-server-3001.js --name whatsapp-3001
pm2 save

# 6. Aguardar inicialização
echo "6️⃣ Aguardando inicialização..."
sleep 5

# 7. Verificar status
echo "7️⃣ Verificando status..."
pm2 status
curl -s http://212.85.11.238:3001/status

# 8. Configurar webhook
echo "8️⃣ Configurando webhook..."
curl -X POST http://212.85.11.238:3001/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"}'

echo "✅ MIGRAÇÃO CONCLUÍDA!"
echo "🎉 Canal 3001 migrado para API correta!"
