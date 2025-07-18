#!/bin/bash

# Script para corrigir erros de sintaxe no whatsapp-api-server.js
# Baseado nos erros identificados nos logs

echo "Corrigindo erros de sintaxe no whatsapp-api-server.js..."

# Backup do arquivo original
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup

# Correção 1: Linha 85 - Adicionar aspa de fechamento
sed -i '85s/let numeroLimpo = numero.replace(\/\[\\s\\-\\(\\)\]\/g, '\'');/let numeroLimpo = numero.replace(\/\[\\s\\-\\(\\)\]\/g, '\''\'');/' /var/whatsapp-api/whatsapp-api-server.js

# Correção 2: Linha 103 - Corrigir aspas no array de DDDs
sed -i '103s/\[11'\'', 12'\'', 13'\'', 14'\'', 15'\'', 16'\'', 17'\'', 18'\'', 19'\'', 21'\'', 22'\'', 24'\'', 27'\'', 28'\'', 31'\'', 32'\'', 33'\'', 34'\'', 35'\'', 37'\'', 38'\'', 41'\'', 42'\'', 43'\'', 44'\'', 45'\'', 46'\'', 47'\'', 48'\'', 49'\'', 51'\'', 53'\'', 54'\'', 55'\'', 61'\'', 62'\'', 63'\'', 64'\'', 65'\'', 66'\'', 67'\'', 68'\'', 69'\'', 71'\'', 73'\'', 74'\'', 75'\'', 77'\'', 79'\'', 81'\'', 82'\'', 83'\'', 84'\'', 85'\'', 86'\'', 87'\'', 88'\'', 89'\'', 91'\'', 92'\'', 93'\'', 94'\'', 95'\'', 96'\'', 97'\'', 98'\'', 99'\''\];/\[\x2711\x27, \x2712\x27, \x2713\x27, \x2714\x27, \x2715\x27, \x2716\x27, \x2717\x27, \x2718\x27, \x2719\x27, \x2721\x27, \x2722\x27, \x2724\x27, \x2727\x27, \x2728\x27, \x2731\x27, \x2732\x27, \x2733\x27, \x2734\x27, \x2735\x27, \x2737\x27, \x2738\x27, \x2741\x27, \x2742\x27, \x2743\x27, \x2744\x27, \x2745\x27, \x2746\x27, \x2747\x27, \x2748\x27, \x2749\x27, \x2751\x27, \x2753\x27, \x2754\x27, \x2755\x27, \x2761\x27, \x2762\x27, \x2763\x27, \x2764\x27, \x2765\x27, \x2766\x27, \x2767\x27, \x2768\x27, \x2769\x27, \x2771\x27, \x2773\x27, \x2774\x27, \x2775\x27, \x2777\x27, \x2779\x27, \x2781\x27, \x2782\x27, \x2783\x27, \x2784\x27, \x2785\x27, \x2786\x27, \x2787\x27, \x2788\x27, \x2789\x27, \x2791\x27, \x2792\x27, \x2793\x27, \x2794\x27, \x2795\x27, \x2796\x27, \x2797\x27, \x2798\x27, \x2799\x27\];/' /var/whatsapp-api/whatsapp-api-server.js

# Correção 3: Linha 139 - Remover chave extra
sed -i '139d' /var/whatsapp-api/whatsapp-api-server.js

echo "Correções aplicadas!"

# Testar sintaxe
echo "Testando sintaxe..."
if node -c /var/whatsapp-api/whatsapp-api-server.js; then
    echo "✅ Sintaxe OK!"
    
    # Reiniciar PM2
    echo "Reiniciando PM2..."
    pm2 stop whatsapp-api
    pm2 delete whatsapp-api
    pm2 start /var/whatsapp-api/whatsapp-api-server.js --name whatsapp-api
    
    echo "Status do PM2:"
    pm2 status
    
    echo "Testando API..."
    sleep 3
    curl http://localhost:3000/test
else
    echo "❌ Ainda há erros de sintaxe!"
    echo "Verifique o arquivo manualmente com: nano /var/whatsapp-api/whatsapp-api-server.js"
fi 