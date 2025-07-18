#!/bin/bash

echo "🔧 Corrigindo formatação de números..."

# Backup
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup

# Encontrar e substituir a função de formatação
sed -i '/formatarNumeroBrasileiro/,/}/d' /var/whatsapp-api/whatsapp-api-server.js

# Adicionar a função correta antes do endpoint /send
sed -i '/\/\/ Endpoint para envio de mensagens WhatsApp/i\
// Função para formatar números brasileiros\
function formatarNumeroBrasileiro(numero) {\
    // Remover espaços, traços e parênteses\
    let numeroLimpo = numero.replace(/[\\s\\-\\(\\)]/g, '\''\'');\
    \
    // Se já tem @c.us, retornar como está\
    if (numeroLimpo.includes('\''@'\'')) {\
        return numeroLimpo;\
    }\
    \
    // Verificar se é um número brasileiro (começa com 55)\
    if (numeroLimpo.startsWith('\''55'\'')) {\
        numeroLimpo = numeroLimpo.substring(2); // Remove o 55\
    }\
    \
    // Verificar se tem DDD (2 dígitos)\
    if (numeroLimpo.length >= 10) {\
        const ddd = numeroLimpo.substring(0, 2);\
        const numeroSemDDD = numeroLimpo.substring(2);\
        \
        // DDDs que usam 9 dígitos para celular (maioria)\
        const dddCom9Digitos = [\''11'\'', \''12'\'', \''13'\'', \''14'\'', \''15'\'', \''16'\'', \''17'\'', \''18'\'', \''19'\'', \''21'\'', \''22'\'', \''24'\'', \''27'\'', \''28'\'', \''31'\'', \''32'\'', \''33'\'', \''34'\'', \''35'\'', \''37'\'', \''38'\'', \''41'\'', \''42'\'', \''43'\'', \''44'\'', \''45'\'', \''46'\'', \''47'\'', \''48'\'', \''49'\'', \''51'\'', \''53'\'', \''54'\'', \''55'\'', \''61'\'', \''62'\'', \''63'\'', \''64'\'', \''65'\'', \''66'\'', \''67'\'', \''68'\'', \''69'\'', \''71'\'', \''73'\'', \''74'\'', \''75'\'', \''77'\'', \''79'\'', \''81'\'', \''82'\'', \''83'\'', \''84'\'', \''85'\'', \''86'\'', \''87'\'', \''88'\'', \''89'\'', \''91'\'', \''92'\'', \''93'\'', \''94'\'', \''95'\'', \''96'\'', \''97'\'', \''98'\'', \''99'\''];\
        \
        // Se o DDD usa 9 dígitos e o número tem 9 dígitos, remover o 9\
        if (dddCom9Digitos.includes(ddd) && numeroSemDDD.length === 9 && numeroSemDDD.startsWith('\''9'\'')) {\
            numeroLimpo = ddd + numeroSemDDD.substring(1); // Remove o 9\
        }\
    }\
    \
    return numeroLimpo + '\''@c.us'\'';\
}' /var/whatsapp-api/whatsapp-api-server.js

echo "✅ Formatação corrigida!"

# Testar sintaxe
echo "Testando sintaxe..."
node -c /var/whatsapp-api/whatsapp-api-server.js

if [ $? -eq 0 ]; then
    echo "✅ Sintaxe OK! Reiniciando PM2..."
    pm2 restart whatsapp-api
    
    echo "Status:"
    pm2 status
    
    echo "Teste em 3 segundos..."
    sleep 3
    curl -X POST http://localhost:3000/send -H "Content-Type: application/json" -d '{"to": "47996164699", "message": "Teste formatação corrigida"}'
else
    echo "❌ Erro de sintaxe!"
fi 