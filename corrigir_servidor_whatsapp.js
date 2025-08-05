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

// Middleware para parsing JSON - CORREÇÃO CRÍTICA
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Armazenar instâncias WhatsApp
const whatsappClients = {};
const clientStatus = {};

// Configuração do webhook
let webhookUrl = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// Configurar upload de arquivos
const upload = multer({ 
    dest: '/tmp/uploads/',
    limits: { fileSize: 50 * 1024 * 1024 }
});

// Criar diretório de sessões
const sessionsPath = './sessions';
fs.ensureDirSync(sessionsPath);

console.log('🚀 Iniciando WhatsApp Multi-Sessão API...');

// INICIALIZAR SESSÃO WHATSAPP
async function initializeWhatsApp(sessionName = 'default') {
    try {
        console.log(`✅ [INIT] initializeWhatsApp chamado para: ${sessionName}`);
        
        const client = new Client({
            authStrategy: new LocalAuth({
                clientId: sessionName,
                dataPath: `${sessionsPath}/${sessionName}`
            }),
            puppeteer: {
                headless: true,
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--no-first-run',
                    '--disable-gpu',
                    '--disable-web-security',
                    '--disable-features=VizDisplayCompositor',
                    '--disable-background-timer-throttling',
                    '--disable-backgrounding-occluded-windows',
                    '--disable-renderer-backgrounding',
                    '--disable-features=TranslateUI',
                    '--disable-ipc-flooding-protection',
                    '--disable-extensions',
                    '--disable-plugins',
                    '--disable-images',
                    '--disable-javascript-harmony-shipping',
                    '--disable-background-networking',
                    '--disable-default-apps',
                    '--disable-sync',
                    '--disable-translate',
                    '--hide-scrollbars',
                    '--mute-audio',
                    '--no-default-browser-check',
                    '--no-pings',
                    '--disable-client-side-phishing-detection',
                    '--disable-component-update',
                    '--disable-domain-reliability',
                    '--disable-features=AudioServiceOutOfProcess',
                    '--disable-hang-monitor',
                    '--disable-prompt-on-repost'
                ],
                timeout: 60000,
            }
        });

        // Inicializar status da sessão
        clientStatus[sessionName] = {
            status: 'initializing',
            message: 'Inicializando sessão WhatsApp...',
            timestamp: new Date().toISOString()
        };

        // QR Code gerado
        client.on('qr', (qr) => {
            console.log(`🔍 [DEBUG][${sessionName}:${PORT}] QR raw → [QR_CODE_VALIDO]`);
            
            clientStatus[sessionName] = {
                status: 'qr_ready',
                qr: qr,
                message: 'QR Code disponível para escaneamento',
                timestamp: new Date().toISOString()
            };
            
            console.log(`📱 [${sessionName}] QR Code gerado:`);
            qrcode.generate(qr, { small: true });
        });

        // Cliente pronto
        client.on('ready', () => {
            console.log(`✅ [${sessionName}] Cliente WhatsApp pronto!`);
            
            whatsappClients[sessionName] = client;
            console.log(`✅ [READY] whatsappClients["${sessionName}"] registrado com sucesso`);
            
            clientStatus[sessionName] = {
                status: 'connected',
                message: 'WhatsApp conectado e funcionando',
                timestamp: new Date().toISOString()
            };
        });

        // Cliente autenticado
        client.on('authenticated', () => {
            console.log(`🔐 [${sessionName}] Cliente autenticado`);
            clientStatus[sessionName] = {
                status: 'authenticated',
                message: 'Cliente autenticado, aguardando inicialização...',
                timestamp: new Date().toISOString()
            };
        });

        // Loading screen
        client.on('loading_screen', (percent, message) => {
            console.log(`⏳ [${sessionName}] Loading: ${percent}% - ${message}`);
        });

        // Mensagem recebida
        client.on('message', async (message) => {
            console.log(`📥 [${sessionName}] Mensagem recebida de ${message.from}: ${message.body}`);
            
            try {
                const webhookData = {
                    event: 'onmessage',
                    data: {
                        from: message.from.replace('@c.us', ''),
                        text: message.body,
                        type: message.type || 'text',
                        timestamp: message.timestamp,
                        session: sessionName
                    }
                };
                
                console.log(`📤 Enviando webhook para: ${webhookUrl}`);
                
                const response = await fetch(webhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(webhookData)
                });
                
                if (response.ok) {
                    console.log(`✅ Webhook enviado com sucesso - Status: ${response.status}`);
                } else {
                    console.log(`❌ Erro ao enviar webhook - Status: ${response.status}`);
                }
            } catch (webhookError) {
                console.error(`❌ Erro ao enviar webhook:`, webhookError);
            }
        });

        console.log(`🔍 [DEBUG] Inicializando WhatsApp para sessão="${sessionName}" na porta ${PORT}`);
        await client.initialize();
        
        console.log(`✅ [INIT] Client inicializado, aguardando evento 'ready' para registrar em whatsappClients`);
        return client;
    } catch (error) {
        console.error(`❌ Erro ao inicializar sessão ${sessionName}:`, error);
        clientStatus[sessionName] = {
            status: 'error',
            message: `Erro: ${error.message}`
        };
        throw error;
    }
}

// ENDPOINTS DA API

// Status geral
app.get('/status', (req, res) => {
    const response = {
        status: 'running',
        ready: false,
        port: PORT.toString(),
        timestamp: new Date().toISOString(),
        lastSession: clientStatus[sessionName]?.timestamp || null,
        clients_status: {}
    };
    
    // Verificar se alguma sessão está conectada
    const connectedSessions = Object.values(clientStatus).filter(status => status.status === 'connected');
    if (connectedSessions.length > 0) {
        response.ready = true;
    }
    
    // Adicionar status das sessões
    Object.keys(clientStatus).forEach(session => {
        response.clients_status[session] = {
            ready: clientStatus[session].status === 'connected',
            hasQR: clientStatus[session].status === 'qr_ready'
        };
    });
    
    res.json(response);
});

// Listar sessões ativas
app.get('/sessions', (req, res) => {
    const sessions = Object.keys(whatsappClients).map(sessionName => ({
        name: sessionName,
        status: clientStatus[sessionName] || { status: 'unknown' },
        hasClient: !!whatsappClients[sessionName]
    }));
    
    res.json({
        success: true,
        sessions: sessions,
        total: sessions.length
    });
});

// Inicializar nova sessão
app.post('/session/start/:sessionName', async (req, res) => {
    try {
        const { sessionName } = req.params;
        
        console.log(`🔥 [AUTO-POST] Recebido POST /session/start/${sessionName}`);
        
        if (whatsappClients[sessionName]) {
            return res.json({
                success: true,
                message: `Sessão ${sessionName} já existe`,
                status: clientStatus[sessionName]
            });
        }
        
        console.log(`🔥 [AUTO-POST] Iniciando nova sessão: ${sessionName}`);
        const client = await initializeWhatsApp(sessionName);
        
        console.log(`🔥 [AUTO-POST] Sessão ${sessionName} criada com sucesso`);
        
        res.json({
            success: true,
            message: `Sessão ${sessionName} iniciada com sucesso`,
            status: clientStatus[sessionName]
        });
    } catch (error) {
        console.error(`🔥 [AUTO-POST] Erro ao iniciar sessão:`, error);
        res.status(500).json({
            success: false,
            message: `Erro ao iniciar sessão: ${error.message}`
        });
    }
});

// Status de uma sessão específica
app.get('/session/:sessionName/status', (req, res) => {
    const { sessionName } = req.params;
    
    if (!whatsappClients[sessionName]) {
        return res.status(404).json({
            success: false,
            message: `Sessão ${sessionName} não encontrada`
        });
    }
    
    res.json({
        success: true,
        session: sessionName,
        status: clientStatus[sessionName] || { status: 'unknown' }
    });
});

// Endpoint específico para QR Code
app.get('/qr', (req, res) => {
    const sessionName = req.query.session || 'default';
    
    if (!whatsappClients[sessionName]) {
        return res.status(404).json({
            success: false,
            message: `Sessão ${sessionName} não encontrada`,
            suggestion: 'Inicie uma sessão primeiro usando POST /session/start/default'
        });
    }
    
    const status = clientStatus[sessionName];
    
    if (!status) {
        return res.status(503).json({
            success: false,
            message: 'Status da sessão não disponível'
        });
    }
    
    if (status.status === 'connected') {
        return res.json({
            success: true,
            qr: null,
            ready: true,
            message: 'WhatsApp já está conectado',
            status: 'connected'
        });
    }
    
    if (status.status === 'qr_ready' && status.qr) {
        return res.json({
            success: true,
            qr: status.qr,
            ready: false,
            message: 'QR Code disponível para escaneamento',
            status: 'qr_ready'
        });
    }
    
    return res.status(503).json({
        success: false,
        qr: null,
        ready: false,
        message: 'QR Code não disponível no momento',
        status: status.status,
        suggestion: 'Aguarde alguns segundos e tente novamente'
    });
});

// Enviar mensagem de texto - CORREÇÃO CRÍTICA
app.post('/send/text', async (req, res) => {
    try {
        console.log(`[DEBUG] Recebido POST /send/text com body:`, req.body);
        
        const { sessionName = 'default', number, message } = req.body;
        
        if (!number || !message) {
            return res.status(400).json({
                success: false,
                message: 'Número e mensagem são obrigatórios',
                received: { sessionName, number, message }
            });
        }
        
        if (!whatsappClients[sessionName]) {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não encontrada`,
                available_sessions: Object.keys(whatsappClients)
            });
        }

        if (clientStatus[sessionName]?.status !== 'connected') {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não está conectada`,
                current_status: clientStatus[sessionName]
            });
        }

        const client = whatsappClients[sessionName];
        const formattedNumber = number.includes('@c.us') ? number : `${number}@c.us`;
        
        await client.sendMessage(formattedNumber, message);
        
        res.json({
            success: true,
            message: 'Mensagem enviada com sucesso',
            session: sessionName,
            to: number,
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        console.error(`[ERROR] Erro ao enviar mensagem:`, error);
        res.status(500).json({
            success: false,
            message: `Erro ao enviar mensagem: ${error.message}`,
            session: req.body.sessionName || 'unknown'
        });
    }
});

// Configurar webhook - CORREÇÃO CRÍTICA
app.post('/webhook/config', (req, res) => {
    console.log(`[DEBUG] Recebido POST /webhook/config com body:`, req.body);
    
    const { url } = req.body;
    
    if (!url) {
        return res.status(400).json({
            success: false,
            message: 'URL do webhook é obrigatória'
        });
    }
    
    webhookUrl = url;
    
    res.json({
        success: true,
        message: 'Webhook configurado com sucesso',
        webhook_url: webhookUrl
    });
});

// Verificar configuração do webhook
app.get('/webhook/config', (req, res) => {
    res.json({
        success: true,
        webhook_url: webhookUrl
    });
});

// Testar webhook
app.post('/webhook/test', async (req, res) => {
    try {
        const testData = {
            event: 'test',
            data: {
                from: '5511999999999',
                text: 'Teste de webhook',
                timestamp: new Date().toISOString(),
                session: 'test'
            }
        };
        
        const response = await fetch(webhookUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(testData)
        });
        
        const responseText = await response.text();
        
        res.json({
            success: response.ok,
            message: response.ok ? 'Webhook testado com sucesso' : 'Erro ao testar webhook',
            webhook_url: webhookUrl,
            response_status: response.status,
            response_text: responseText
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: `Erro ao testar webhook: ${error.message}`,
            webhook_url: webhookUrl
        });
    }
});

// Iniciar servidor
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🌐 API escutando em 0.0.0.0:${PORT} (sessão=${sessionName})`);
    console.log(`🌐 Acessível externamente em http://212.85.11.238:${PORT}`);
    console.log(`📋 Endpoints disponíveis:`);
    console.log(`   GET  /status                          - Status geral`);
    console.log(`   GET  /sessions                        - Listar sessões`);
    console.log(`   POST /session/start/:sessionName      - Iniciar sessão`);
    console.log(`   GET  /session/:sessionName/status     - Status da sessão`);
    console.log(`   GET  /qr?session=name                 - QR Code da sessão`);
    console.log(`   POST /send/text                       - Enviar texto`);
    console.log(`   POST /send/media                      - Enviar mídia`);
    console.log(`   POST /check/number                    - Verificar número`);
    console.log(`   POST /session/:sessionName/disconnect - Desconectar`);
    console.log(`   POST /webhook/config                  - Configurar webhook`);
    console.log(`   GET  /webhook/config                  - Verificar webhook`);
    console.log(`   POST /webhook/test                    - Testar webhook`);
    console.log(`\n✨ Sistema pronto para uso!`);
    
    // Inicializar sessão automaticamente
    console.log(`🚩 [AUTO-START] Iniciando sessão "${sessionName}" automaticamente...`);
    
    const autoStartUrl = `http://127.0.0.1:${PORT}/session/start/${sessionName}`;
    console.log(`🚩 [AUTO-START] URL do POST interno: ${autoStartUrl}`);
    
    fetch(autoStartUrl, { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log(`🎯 [AUTO-POST] Status interno: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log(`🚩 [AUTO-START] Sessão "${sessionName}" iniciada:`, data.success ? 'SUCESSO' : 'FALHA');
        if (data.success) {
            console.log(`✅ [AUTO-START] whatsappClients["${sessionName}"] criado com sucesso`);
        } else {
            console.log(`❌ [AUTO-START] Erro ao iniciar sessão "${sessionName}":`, data.message);
        }
    })
    .catch(error => {
        console.error(`❌ [AUTO-START] Erro ao fazer POST para iniciar sessão "${sessionName}":`, error.message);
    });
});

// Graceful shutdown
process.on('SIGINT', async () => {
    console.log('\n🛑 Encerrando servidor...');
    
    for (const [sessionName, client] of Object.entries(whatsappClients)) {
        try {
            console.log(`📱 Desconectando sessão ${sessionName}...`);
            await client.destroy();
        } catch (error) {
            console.error(`Erro ao desconectar ${sessionName}:`, error);
        }
    }
    
    process.exit(0);
}); 