#!/bin/bash

echo "🔧 Atualizando formatação simplificada na VPS..."

# Backup do arquivo atual
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)

# Substituir a função formatarNumeroWhatsapp pela versão simplificada
sed -i '/\/\/ Função para validar e ajustar número para formato WhatsApp/,/}/c\
// Função simplificada para formatar número (apenas código do país + DDD + número)\
function formatarNumeroWhatsapp(numero) {\
  // Remover todos os caracteres não numéricos\
  numero = String(numero).replace(/\\D/g, '\''\'');\
  \
  // Se já tem código do país (55), remover para processar\
  if (numero.startsWith('\''55'\'')) {\
    numero = numero.slice(2);\
  }\
  \
  // Verificar se tem pelo menos DDD (2 dígitos) + número (8 dígitos)\
  if (numero.length < 10) {\
    return null; // Número muito curto\
  }\
  \
  // Extrair DDD e número\
  const ddd = numero.slice(0, 2);\
  const telefone = numero.slice(2);\
  \
  // Retornar no formato: 55 + DDD + número + @c.us\
  // Deixar o número como está (você gerencia as regras no cadastro)\
  return '\''55'\'' + ddd + telefone + '\''@c.us'\'';\
}' /var/whatsapp-api/whatsapp-api-server.js

# Corrigir as chamadas para não adicionar @c.us novamente
sed -i 's/numeroAjustado + '\''@c.us'\''/numeroAjustado/g' /var/whatsapp-api/whatsapp-api-server.js

echo "✅ Formatação simplificada aplicada!"

# Testar sintaxe
echo "🧪 Testando sintaxe..."
node -c /var/whatsapp-api/whatsapp-api-server.js

if [ $? -eq 0 ]; then
    echo "✅ Sintaxe OK! Reiniciando PM2..."
    pm2 restart whatsapp-api
    
    echo "Status:"
    pm2 status
    
    echo "🧪 Teste em 3 segundos..."
    sleep 3
    curl -X POST http://localhost:3000/send -H "Content-Type: application/json" -d '{"to": "4799616469", "message": "Teste formatação simplificada"}'
else
    echo "❌ Erro de sintaxe!"
    echo "Restaurando backup..."
    cp /var/whatsapp-api/whatsapp-api-server.js.backup.* /var/whatsapp-api/whatsapp-api-server.js
    pm2 restart whatsapp-api
fi

echo "✅ Atualização concluída!" 