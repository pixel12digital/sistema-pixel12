#!/bin/bash

# 🔧 DEPLOY CONFIGURAÇÃO DE WEBHOOK NA VPS
# Este script aplica a nova estrutura de webhookConfig no servidor VPS

echo "🔧 DEPLOY CONFIGURAÇÃO DE WEBHOOK NA VPS"
echo "========================================"
echo ""

# Configurações
VPS_IP="212.85.11.238"
VPS_USER="root"
WEBHOOK_URL="https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php"
WEBHOOK_EVENTS='["onmessage", "onqr", "onready", "onclose"]'

echo "🎯 CONFIGURAÇÃO A SER APLICADA:"
echo "- VPS: $VPS_IP"
echo "- URL: $WEBHOOK_URL"
echo "- Events: $WEBHOOK_EVENTS"
echo ""

# 1. Conectar na VPS e fazer backup
echo "1️⃣ Fazendo backup do arquivo atual..."
ssh $VPS_USER@$VPS_IP "cd /var/whatsapp-api && cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)"

if [ $? -eq 0 ]; then
    echo "✅ Backup criado com sucesso"
else
    echo "❌ Erro ao criar backup"
    exit 1
fi

# 2. Aplicar as mudanças no arquivo
echo "2️⃣ Aplicando nova configuração de webhook..."

# Criar arquivo temporário com as mudanças
cat > /tmp/webhook_config.js << 'EOF'
// Variável global para webhook
let webhookConfig = {
    url: 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php',
    events: ['onmessage', 'onqr', 'onready', 'onclose']
};
EOF

# Aplicar mudanças via SSH
ssh $VPS_USER@$VPS_IP "cd /var/whatsapp-api && \
    sed -i '/^\/\/ Configuração do webhook/,/^\/\/ Configurar upload de arquivos/c\
// Variável global para webhook\
let webhookConfig = {\
    url: \"$WEBHOOK_URL\",\
    events: $WEBHOOK_EVENTS\
};' whatsapp-api-server.js"

# 3. Aplicar mudanças nos endpoints
echo "3️⃣ Atualizando endpoints de webhook..."

ssh $VPS_USER@$VPS_IP "cd /var/whatsapp-api && \
    sed -i '/^\/\/ Endpoint para configurar webhook/,/^\/\/ Endpoint para verificar webhook/c\
// Endpoint para configurar webhook\
app.post(\"/webhook/config\", (req, res) => {\
    const { url, events } = req.body;\
    \
    if (url) {\
        webhookConfig.url = url;\
        if (events) webhookConfig.events = events;\
        \
        console.log(\`🔗 [WEBHOOK] Configurado: \${webhookConfig.url}\`);\
        res.json({\
            success: true,\
            webhook: webhookConfig.url,\
            events: webhookConfig.events,\
            message: \"Webhook configurado com sucesso\"\
        });\
    } else {\
        res.status(400).json({ error: \"URL do webhook é obrigatória\" });\
    }\
});\
\
// Endpoint para verificar webhook\
app.get(\"/webhook/config\", (req, res) => {\
    res.json({\
        success: true,\
        webhook: webhookConfig.url,\
        events: webhookConfig.events,\
        message: \"Webhook configurado\"\
    });\
});' whatsapp-api-server.js"

# 4. Atualizar referências ao webhookUrl
echo "4️⃣ Atualizando referências ao webhookUrl..."

ssh $VPS_USER@$VPS_IP "cd /var/whatsapp-api && \
    sed -i 's/webhookUrl/webhookConfig.url/g' whatsapp-api-server.js"

# 5. Reiniciar os serviços
echo "5️⃣ Reiniciando serviços..."

ssh $VPS_USER@$VPS_IP "pm2 restart whatsapp-3000"
ssh $VPS_USER@$VPS_IP "pm2 restart whatsapp-3001"

# 6. Verificar se os serviços estão rodando
echo "6️⃣ Verificando status dos serviços..."

ssh $VPS_USER@$VPS_IP "pm2 status"

# 7. Testar a configuração
echo "7️⃣ Testando nova configuração..."

echo "Testando porta 3000..."
curl -s "http://$VPS_IP:3000/webhook/config" | jq '.'

echo "Testando porta 3001..."
curl -s "http://$VPS_IP:3001/webhook/config" | jq '.'

echo ""
echo "🎯 DEPLOY CONCLUÍDO!"
echo "==================="
echo "✅ Nova configuração de webhook aplicada"
echo "✅ Serviços reiniciados"
echo "✅ Endpoints atualizados"
echo ""
echo "🔍 PARA TESTAR:"
echo "==============="
echo "1. Acesse: http://$VPS_IP:3000/webhook/config"
echo "2. Acesse: http://$VPS_IP:3001/webhook/config"
echo "3. Envie uma mensagem para o WhatsApp"
echo "4. Verifique se o webhook está funcionando"
echo ""
echo "📝 LOGS DISPONÍVEIS:"
echo "==================="
echo "- Logs do VPS: pm2 logs whatsapp-3000 --lines 50"
echo "- Logs do VPS: pm2 logs whatsapp-3001 --lines 50"
echo "- Logs do webhook: https://app.pixel12digital.com.br/painel/logs/"
echo "" 