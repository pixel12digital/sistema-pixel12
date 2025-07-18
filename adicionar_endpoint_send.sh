#!/bin/bash

echo "🔧 Adicionando endpoint /send ao WhatsApp API..."

# Fazer backup
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)

# Adicionar endpoint simples no final do arquivo
cat >> /var/whatsapp-api/whatsapp-api-server.js << 'EOF'

// Endpoint para envio de mensagens WhatsApp
app.post('/send', async (req, res) => {
    try {
        const { to, message } = req.body;
        
        // Validar parâmetros
        if (!to || !message) {
            return res.status(400).json({
                success: false,
                error: 'Parâmetros obrigatórios: to, message'
            });
        }
        
        console.log(`[SEND] Tentando enviar mensagem para ${to}: ${message}`);
        
        // Verificar se o cliente está conectado
        const client = whatsappClients['default'];
        if (!client || !clientStatus['default'] || clientStatus['default'].status !== 'connected') {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp não está conectado'
            });
        }
        
        // Formatar número
        let formattedNumber = to;
        if (!formattedNumber.includes('@')) {
            formattedNumber = formattedNumber + '@c.us';
        }
        
        // Enviar mensagem
        const result = await client.sendMessage(formattedNumber, message);
        
        console.log(`[SEND] Mensagem enviada com sucesso. ID: ${result.id._serialized}`);
        
        res.json({
            success: true,
            messageId: result.id._serialized,
            message: 'Mensagem enviada com sucesso'
        });
        
    } catch (error) {
        console.error('[SEND] Erro ao enviar mensagem:', error);
        res.status(500).json({
            success: false,
            error: error.message || 'Erro interno do servidor'
        });
    }
});
EOF

echo "✅ Endpoint adicionado!"

# Reiniciar servidor
echo "🔄 Reiniciando servidor..."
pkill -f "whatsapp-api-server.js"
sleep 2
nohup node /var/whatsapp-api/whatsapp-api-server.js > /var/whatsapp-api/logs/server.log 2>&1 &

echo "✅ Servidor reiniciado!"
echo "🧪 Teste o endpoint: curl -X POST http://localhost:3000/send -H 'Content-Type: application/json' -d '{\"to\":\"5511999999999\",\"message\":\"teste\"}'" 