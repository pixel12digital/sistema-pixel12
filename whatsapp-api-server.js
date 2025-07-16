const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode-terminal');
const fs = require('fs-extra');
const multer = require('multer');
const path = require('path');

const app = express();
const PORT = 3000;

// Configurações
app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Armazenar instâncias WhatsApp
const whatsappClients = {};
const clientStatus = {};

// Configurar upload de arquivos
const upload = multer({ 
    dest: '/tmp/uploads/',
    limits: { fileSize: 50 * 1024 * 1024 } // 50MB
});

// Criar diretório de sessões
const sessionsPath = './sessions';
fs.ensureDirSync(sessionsPath);

console.log('🚀 Iniciando WhatsApp Multi-Sessão API...');

// INICIALIZAR SESSÃO WHATSAPP
async function initializeWhatsApp(sessionName = 'default') {
    try {
        console.log(`📱 Inicializando sessão: ${sessionName}`);
        
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
                    '--no-zygote',
                    '--single-process',
                    '--disable-gpu'
                ]
            }
        });

        // QR Code para conectar
        client.on('qr', (qr) => {
            console.log(`\n📲 QR Code para sessão ${sessionName}:`);
            qrcode.generate(qr, { small: true });
            clientStatus[sessionName] = {
                status: 'qr_ready',
                qr: qr,
                message: 'Escaneie o QR Code no WhatsApp'
            };
        });

        // Cliente pronto
        client.on('ready', () => {
            console.log(`✅ WhatsApp sessão ${sessionName} conectado!`);
            clientStatus[sessionName] = {
                status: 'connected',
                message: 'WhatsApp conectado e funcionando'
            };
        });

        // Desconectado
        client.on('disconnected', (reason) => {
            console.log(`❌ WhatsApp sessão ${sessionName} desconectado:`, reason);
            clientStatus[sessionName] = {
                status: 'disconnected',
                message: `Desconectado: ${reason}`
            };
            
            // Tentar reconectar em 30 segundos
            setTimeout(() => {
                console.log(`🔄 Tentando reconectar sessão ${sessionName}...`);
                client.initialize();
            }, 30000);
        });

        // Erro de autenticação
        client.on('auth_failure', (msg) => {
            console.log(`🚨 Falha de autenticação sessão ${sessionName}:`, msg);
            clientStatus[sessionName] = {
                status: 'auth_failure',
                message: `Erro de autenticação: ${msg}`
            };
        });

        // Mensagem recebida (webhook futuro)
        client.on('message', async (message) => {
            console.log(`📥 [${sessionName}] Mensagem recebida de ${message.from}: ${message.body}`);
            // Aqui futuramente enviaremos webhook para o sistema PHP
        });

        await client.initialize();
        whatsappClients[sessionName] = client;
        
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
    res.json({
        success: true,
        message: 'WhatsApp Multi-Sessão API funcionando',
        timestamp: new Date().toISOString(),
        sessions: Object.keys(whatsappClients).length,
        clients_status: clientStatus
    });
});

// Inicializar nova sessão
app.post('/session/start/:sessionName', async (req, res) => {
    try {
        const { sessionName } = req.params;
        
        if (whatsappClients[sessionName]) {
            return res.json({
                success: true,
                message: `Sessão ${sessionName} já existe`,
                status: clientStatus[sessionName]
            });
        }

        await initializeWhatsApp(sessionName);
        
        res.json({
            success: true,
            message: `Sessão ${sessionName} iniciada. Escaneie o QR Code.`,
            session: sessionName,
            status: clientStatus[sessionName]
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: `Erro ao iniciar sessão: ${error.message}`
        });
    }
});

// Status de uma sessão específica
app.get('/session/:sessionName/status', (req, res) => {
    const { sessionName } = req.params;
    
    res.json({
        success: true,
        session: sessionName,
        exists: !!whatsappClients[sessionName],
        status: clientStatus[sessionName] || { status: 'not_found', message: 'Sessão não encontrada' }
    });
});

// Enviar mensagem de texto
app.post('/send/text', async (req, res) => {
    try {
        const { sessionName = 'default', number, message } = req.body;
        
        if (!whatsappClients[sessionName]) {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não encontrada`
            });
        }

        if (clientStatus[sessionName]?.status !== 'connected') {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não está conectada`
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
        res.status(500).json({
            success: false,
            message: `Erro ao enviar mensagem: ${error.message}`
        });
    }
});

// Enviar mídia
app.post('/send/media', upload.single('file'), async (req, res) => {
    try {
        const { sessionName = 'default', number, caption = '' } = req.body;
        const file = req.file;
        
        if (!file) {
            return res.status(400).json({
                success: false,
                message: 'Arquivo não fornecido'
            });
        }

        if (!whatsappClients[sessionName]) {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não encontrada`
            });
        }

        if (clientStatus[sessionName]?.status !== 'connected') {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não está conectada`
            });
        }

        const client = whatsappClients[sessionName];
        const formattedNumber = number.includes('@c.us') ? number : `${number}@c.us`;
        
        const media = MessageMedia.fromFilePath(file.path);
        await client.sendMessage(formattedNumber, media, { caption });
        
        // Limpar arquivo temporário
        fs.unlinkSync(file.path);
        
        res.json({
            success: true,
            message: 'Mídia enviada com sucesso',
            session: sessionName,
            to: number,
            filename: file.originalname,
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: `Erro ao enviar mídia: ${error.message}`
        });
    }
});

// Verificar se número existe no WhatsApp
app.post('/check/number', async (req, res) => {
    try {
        const { sessionName = 'default', number } = req.body;
        
        if (!whatsappClients[sessionName]) {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não encontrada`
            });
        }

        const client = whatsappClients[sessionName];
        const formattedNumber = number.includes('@c.us') ? number : `${number}@c.us`;
        
        const isRegistered = await client.isRegisteredUser(formattedNumber);
        
        res.json({
            success: true,
            number: number,
            exists: isRegistered,
            message: isRegistered ? 'Número existe no WhatsApp' : 'Número não encontrado no WhatsApp'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: `Erro ao verificar número: ${error.message}`
        });
    }
});

// Listar todas as sessões
app.get('/sessions', (req, res) => {
    const sessions = Object.keys(whatsappClients).map(sessionName => ({
        name: sessionName,
        status: clientStatus[sessionName] || { status: 'unknown' }
    }));
    
    res.json({
        success: true,
        total: sessions.length,
        sessions: sessions
    });
});

// Desconectar sessão
app.post('/session/:sessionName/disconnect', async (req, res) => {
    try {
        const { sessionName } = req.params;
        
        if (!whatsappClients[sessionName]) {
            return res.status(400).json({
                success: false,
                message: `Sessão ${sessionName} não encontrada`
            });
        }

        const client = whatsappClients[sessionName];
        await client.logout();
        await client.destroy();
        
        delete whatsappClients[sessionName];
        delete clientStatus[sessionName];
        
        res.json({
            success: true,
            message: `Sessão ${sessionName} desconectada com sucesso`
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: `Erro ao desconectar sessão: ${error.message}`
        });
    }
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`\n🌐 API WhatsApp rodando em http://localhost:${PORT}`);
    console.log(`📋 Endpoints disponíveis:`);
    console.log(`   GET  /status                          - Status geral`);
    console.log(`   POST /session/start/:sessionName      - Iniciar sessão`);
    console.log(`   GET  /session/:sessionName/status     - Status da sessão`);
    console.log(`   POST /send/text                       - Enviar texto`);
    console.log(`   POST /send/media                      - Enviar mídia`);
    console.log(`   POST /check/number                    - Verificar número`);
    console.log(`   GET  /sessions                        - Listar sessões`);
    console.log(`   POST /session/:sessionName/disconnect - Desconectar`);
    console.log(`\n✨ Sistema pronto para uso!`);
    
    // Inicializar sessão padrão
    setTimeout(() => {
        console.log('\n🔄 Inicializando sessão padrão...');
        initializeWhatsApp('default').catch(console.error);
    }, 2000);
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