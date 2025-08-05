const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode-terminal');
const fs = require('fs-extra');
const multer = require('multer');
const path = require('path');

const app = express();
const PORT = parseInt(process.env.PORT, 10) || 3000;
const sessionName = PORT === 3001 ? 'comercial' : 'default';

console.log(`🚩 [STARTUP] Porta ${PORT} → sessão="${sessionName}"`);

// Configurações CORS e middleware
app.use(cors({
    origin: [
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://212.85.11.238:8080',
        'http://212.85.11.238',
        'https://212.85.11.238',
        'http://localhost:3000',
        'http://localhost:3001'
    ],
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));

app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Configuração de upload
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        const uploadPath = path.join(__dirname, 'uploads');
        if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + '-' + file.originalname);
    }
});
const upload = multer({ storage: storage });

// Estado global
let clients = {};
let qrCodes = {};
let isReady = {};

// Função para criar cliente WhatsApp
function createClient(session) {
    console.log(`🔄 [${session}] Criando cliente...`);
    
    const client = new Client({
        authStrategy: new LocalAuth({
            clientId: session,
            dataPath: `./sessions/${session}`
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
                '--disable-gpu'
            ]
        }
    });

    // Event listeners
    client.on('qr', (qr) => {
        console.log(`📱 [${session}] QR Code gerado!`);
        qrCodes[session] = qr;
        qrcode.generate(qr, { small: true });
    });

    client.on('ready', () => {
        console.log(`✅ [${session}] Cliente conectado!`);
        isReady[session] = true;
        qrCodes[session] = null; // Limpar QR após conectar
    });

    client.on('authenticated', () => {
        console.log(`🔐 [${session}] Autenticado!`);
    });

    client.on('auth_failure', (msg) => {
        console.log(`❌ [${session}] Falha na autenticação:`, msg);
    });

    client.on('disconnected', (reason) => {
        console.log(`⚠️ [${session}] Desconectado:`, reason);
        isReady[session] = false;
        qrCodes[session] = null;
    });

    clients[session] = client;
    return client;
}

// Inicializar cliente para a sessão da porta
const client = createClient(sessionName);
client.initialize();

// ENDPOINTS

// Status geral
app.get('/status', (req, res) => {
    const clientsStatus = {};
    
    Object.keys(clients).forEach(session => {
        clientsStatus[session] = {
            ready: isReady[session] || false,
            hasQR: !!qrCodes[session],
            qr: qrCodes[session] || null // CORREÇÃO: Expor QR code
        };
    });

    res.json({
        status: 'running',
        ready: isReady[sessionName] || false,
        port: PORT.toString(),
        timestamp: new Date().toISOString(),
        lastSession: new Date().toISOString(),
        clients_status: clientsStatus
    });
});

// CORREÇÃO: Endpoint /qr funcional
app.get('/qr', (req, res) => {
    const session = req.query.session || sessionName;
    
    console.log(`📱 [QR REQUEST] Sessão: ${session}`);
    
    if (!clients[session]) {
        return res.status(404).json({
            success: false,
            message: `Sessão ${session} não encontrada`,
            suggestion: `Inicie uma sessão primeiro usando POST /session/start/${session}`
        });
    }

    if (qrCodes[session]) {
        res.json({
            success: true,
            qr: qrCodes[session],
            session: session,
            message: 'QR Code disponível'
        });
    } else if (isReady[session]) {
        res.json({
            success: false,
            message: 'Cliente já está conectado',
            session: session,
            ready: true
        });
    } else {
        res.json({
            success: false,
            message: 'QR Code não disponível no momento',
            session: session,
            suggestion: 'Aguarde alguns segundos e tente novamente'
        });
    }
});

// Iniciar sessão
app.post('/session/start/:sessionName', (req, res) => {
    const session = req.params.sessionName;
    
    console.log(`🚀 [START] Iniciando sessão: ${session}`);
    
    if (clients[session]) {
        return res.json({
            success: true,
            message: 'Sessão já existe',
            session: session
        });
    }

    try {
        const newClient = createClient(session);
        newClient.initialize();
        
        res.json({
            success: true,
            message: 'Sessão iniciada com sucesso',
            session: session
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message,
            session: session
        });
    }
});

// Listar sessões
app.get('/sessions', (req, res) => {
    const sessions = Object.keys(clients).map(session => ({
        name: session,
        ready: isReady[session] || false,
        hasQR: !!qrCodes[session]
    }));

    res.json({
        success: true,
        sessions: sessions,
        total: sessions.length
    });
});

// Enviar mensagem de texto
app.post('/send/text', async (req, res) => {
    try {
        const { sessionName: session = sessionName, number, message } = req.body;
        
        if (!clients[session] || !isReady[session]) {
            return res.status(400).json({
                success: false,
                error: 'Cliente não está conectado'
            });
        }

        const chatId = number.includes('@') ? number : `${number}@c.us`;
        await clients[session].sendMessage(chatId, message);

        res.json({
            success: true,
            message: 'Mensagem enviada com sucesso'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Webhook config
app.get('/webhook/config', (req, res) => {
    res.json({
        success: true,
        webhook: 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php',
        message: 'Webhook configurado'
    });
});

app.post('/webhook/config', (req, res) => {
    const { url } = req.body;
    console.log(`🔗 [WEBHOOK] Configurado: ${url}`);
    
    res.json({
        success: true,
        webhook: url,
        message: 'Webhook configurado com sucesso'
    });
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`🚀 [SERVER] WhatsApp API rodando na porta ${PORT}`);
    console.log(`📱 [SESSION] Sessão ativa: ${sessionName}`);
    console.log(`🔗 [ENDPOINTS] Disponíveis:`);
    console.log(`   GET  /status - Status geral`);
    console.log(`   GET  /qr - QR Code da sessão`);
    console.log(`   POST /session/start/:name - Iniciar sessão`);
    console.log(`   GET  /sessions - Listar sessões`);
    console.log(`   POST /send/text - Enviar mensagem`);
    console.log(`   GET|POST /webhook/config - Configurar webhook`);
}); 