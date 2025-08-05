const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Storage for client instances
const clients = new Map();

// Initialize WhatsApp client for this port
function initializeClient(sessionId = 'default') {
    const client = new Client({
        authStrategy: new LocalAuth({
            clientId: `${sessionId}_${PORT}`,
            dataPath: `./sessions/${sessionId}_${PORT}`
        }),
        puppeteer: {
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ]
        }
    });

    let qrCodeData = '';
    let isReady = false;
    let lastSession = null;

    client.on('qr', (qr) => {
        console.log(`[PORT ${PORT}] QR Code gerado para sessão ${sessionId}`);
        qrCodeData = qr;
    });

    client.on('ready', () => {
        console.log(`[PORT ${PORT}] Cliente WhatsApp pronto! Sessão: ${sessionId}`);
        isReady = true;
        lastSession = new Date().toISOString();
    });

    client.on('authenticated', () => {
        console.log(`[PORT ${PORT}] Cliente autenticado! Sessão: ${sessionId}`);
        isReady = true;
        lastSession = new Date().toISOString();
    });

    client.on('auth_failure', (msg) => {
        console.error(`[PORT ${PORT}] Falha na autenticação:`, msg);
        isReady = false;
    });

    client.on('disconnected', (reason) => {
        console.log(`[PORT ${PORT}] Cliente desconectado:`, reason);
        isReady = false;
        qrCodeData = '';
    });

    client.on('message', async (message) => {
        console.log(`[PORT ${PORT}] Mensagem recebida de ${message.from}: ${message.body}`);
        
        // Aqui você pode processar as mensagens recebidas
        // e enviar para o webhook do seu sistema
    });

    // Inicializar cliente
    client.initialize().catch(err => {
        console.error(`[PORT ${PORT}] Erro ao inicializar cliente:`, err);
    });

    clients.set(sessionId, {
        client,
        qrCodeData: () => qrCodeData,
        isReady: () => isReady,
        lastSession: () => lastSession
    });

    return client;
}

// Routes
app.get('/status', (req, res) => {
    const defaultClient = clients.get('default');
    
    res.json({
        status: 'running',
        ready: defaultClient ? defaultClient.isReady() : false,
        port: PORT,
        timestamp: new Date().toISOString(),
        lastSession: defaultClient ? defaultClient.lastSession() : null,
        clients_status: {
            default: {
                ready: defaultClient ? defaultClient.isReady() : false,
                hasQR: defaultClient ? (defaultClient.qrCodeData() !== '') : false
            }
        }
    });
});

app.get('/session/:sessionId/qr', async (req, res) => {
    const sessionId = req.params.sessionId || 'default';
    const clientData = clients.get(sessionId);
    
    if (!clientData) {
        return res.status(404).json({ error: 'Sessão não encontrada' });
    }

    const qrData = clientData.qrCodeData();
    
    if (!qrData) {
        return res.json({ 
            error: 'QR Code não disponível',
            ready: clientData.isReady(),
            message: clientData.isReady() ? 'Cliente já está conectado' : 'QR Code ainda não foi gerado'
        });
    }

    try {
        const qrImage = await qrcode.toDataURL(qrData);
        res.json({
            success: true,
            qr: qrData,
            qrImage: qrImage,
            ready: false
        });
    } catch (error) {
        res.status(500).json({ error: 'Erro ao gerar QR Code', details: error.message });
    }
});

app.post('/session/:sessionId/disconnect', (req, res) => {
    const sessionId = req.params.sessionId || 'default';
    const clientData = clients.get(sessionId);
    
    if (!clientData) {
        return res.status(404).json({ error: 'Sessão não encontrada' });
    }

    clientData.client.logout().then(() => {
        res.json({ success: true, message: 'Cliente desconectado com sucesso' });
    }).catch(err => {
        res.status(500).json({ error: 'Erro ao desconectar', details: err.message });
    });
});

app.post('/send-message', async (req, res) => {
    const { to, message, sessionId = 'default' } = req.body;
    const clientData = clients.get(sessionId);
    
    if (!clientData || !clientData.isReady()) {
        return res.status(400).json({ error: 'Cliente não está pronto' });
    }

    try {
        const chatId = to.includes('@') ? to : `${to}@c.us`;
        const response = await clientData.client.sendMessage(chatId, message);
        
        res.json({
            success: true,
            messageId: response.id.id,
            timestamp: response.timestamp
        });
    } catch (error) {
        res.status(500).json({ error: 'Erro ao enviar mensagem', details: error.message });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 WhatsApp API rodando na porta ${PORT}`);
    console.log(`📱 Status: http://localhost:${PORT}/status`);
    console.log(`🔗 QR Code: http://localhost:${PORT}/session/default/qr`);
    
    // Initialize default client
    initializeClient('default');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log(`\n[PORT ${PORT}] Encerrando aplicação...`);
    
    clients.forEach((clientData, sessionId) => {
        console.log(`[PORT ${PORT}] Desconectando sessão ${sessionId}...`);
        clientData.client.destroy();
    });
    
    process.exit(0);
});

module.exports = app; 