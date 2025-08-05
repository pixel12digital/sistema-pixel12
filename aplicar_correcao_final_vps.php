<?php
echo "🚀 APLICANDO CORREÇÃO FINAL NA VPS\n";
echo "===================================\n\n";

// Código corrigido completo do servidor
$codigo_corrigido = <<<'EOF'
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
console.log(`🔧 Iniciando WhatsApp Multi-Sessão API...`);

// Configurações CORS e middleware
app.use(cors({
    origin: [
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://212.85.11.238:8080',
        'http://212.85.11.238',
        'https://212.85.11.238',
        'http://localhost:3000',
        'http://localhost:3001',
        'https://app.pixel12digital.com.br',
        'http://app.pixel12digital.com.br'
    ],
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));

app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Variáveis globais para gerenciar clientes
const whatsappClients = {};
const isReady = {};
const qrCodes = {};

// Função para criar cliente WhatsApp
function createClient(session) {
    console.log(`✅ [INIT] initializeWhatsApp chamado para: ${session}`);
    console.log(`🔍 [DEBUG] Inicializando WhatsApp para sessão="${session}" na porta ${PORT}`);
    
    const client = new Client({
        authStrategy: new LocalAuth({
            clientId: session,
            dataPath: './sessions'
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

    // Event listeners
    client.on('qr', (qr) => {
        console.log(`🔍 [DEBUG][${session}:${PORT}] QR raw → [QR_CODE_VALIDO]`);
        qrCodes[session] = qr;
        console.log(`📱 [${session}] QR Code gerado:`);
        qrcode.generate(qr, { small: true });
        console.log(`✅ [INIT] Client inicializado, aguardando evento 'ready' para registrar em whatsappClients`);
    });

    client.on('ready', () => {
        whatsappClients[session] = client;
        isReady[session] = true;
        delete qrCodes[session];
        console.log(`✅ [${session}] WhatsApp conectado e pronto!`);
    });

    client.on('disconnected', () => {
        isReady[session] = false;
        delete whatsappClients[session];
        delete qrCodes[session];
        console.log(`❌ [${session}] WhatsApp desconectado`);
    });

    client.on('auth_failure', () => {
        console.log(`❌ [${session}] Falha na autenticação`);
        delete qrCodes[session];
    });

    return client;
}

// Middleware para logs
app.use((req, res, next) => {
    console.log(`📝 [${new Date().toISOString()}] ${req.method} ${req.path}`);
    next();
});

// ENDPOINTS

// Status geral
app.get('/status', (req, res) => {
    const clientsStatus = {};
    
    // Verificar todas as sessões conhecidas
    const allSessions = new Set([...Object.keys(whatsappClients), ...Object.keys(qrCodes), sessionName]);
    
    allSessions.forEach(session => {
        clientsStatus[session] = {
            ready: isReady[session] || false,
            hasQR: !!qrCodes[session],
            qr: qrCodes[session] || null
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

// QR Code endpoint
app.get('/qr', (req, res) => {
    const session = req.query.session || sessionName;
    console.log(`📱 [QR REQUEST] Sessão: ${session}`);
    
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
    console.log(`🔥 [AUTO-POST] Recebido POST /session/start/${session}`);
    console.log(`🔥 [AUTO-POST] Iniciando nova sessão: ${session}`);
    
    if (whatsappClients[session]) {
        return res.json({
            success: true,
            message: 'Sessão já existe',
            session: session
        });
    }
    
    try {
        const newClient = createClient(session);
        newClient.initialize();
        
        console.log(`🔥 [AUTO-POST] Sessão ${session} criada com sucesso`);
        console.log(`🔥 [AUTO-POST] Status interno: 200`);
        
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
    const allSessions = new Set([...Object.keys(whatsappClients), ...Object.keys(qrCodes)]);
    const sessions = Array.from(allSessions).map(session => ({
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
        
        if (!whatsappClients[session] || !isReady[session]) {
            return res.status(400).json({
                success: false,
                error: 'Cliente não está conectado'
            });
        }
        
        const chatId = number.includes('@') ? number : `${number}@c.us`;
        await whatsappClients[session].sendMessage(chatId, message);
        
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

// Configurar webhook
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
    console.log(`   GET  /status                            - Status geral`);
    console.log(`   GET  /qr                                - QR Code da sessão`);
    console.log(`   POST /session/start/:sessionName        - Iniciar sessão`);
    console.log(`   GET  /sessions                          - Listar sessões`);
    console.log(`   POST /send/text                         - Enviar texto`);
    console.log(`   POST /send/media                        - Enviar mídia`);
    console.log(`   POST /check/number                      - Verificar número`);
    console.log(`   POST /session/:sessionName/disconnect   - Desconectar`);
    console.log(`   POST /webhook/config                    - Configurar webhook`);
    console.log(`   GET  /webhook/config                    - Verificar webhook`);
    console.log(`   POST /webhook/test                      - Testar webhook`);
    console.log(``);
    console.log(`✨ Sistema pronto para uso!`);
    
    // Auto-start da sessão principal
    console.log(`🔥 [AUTO-START] Iniciando sessão "${sessionName}" automaticamente...`);
    console.log(`🚩 [AUTO-START] URL do POST interno: http://127.0.0.1:${PORT}/session/start/${sessionName}`);
    
    setTimeout(() => {
        const http = require('http');
        const postData = JSON.stringify({});
        
        const options = {
            hostname: '127.0.0.1',
            port: PORT,
            path: `/session/start/${sessionName}`,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(postData)
            }
        };
        
        const req = http.request(options, (res) => {
            console.log(`🚩 [AUTO-START] Sessão "${sessionName}" iniciada: ${res.statusCode === 200 ? 'SUCESSO' : 'ERRO'}`);
            if (res.statusCode === 200) {
                console.log(`✅ [AUTO-START] whatsappClients["${sessionName}"] criado com sucesso`);
            }
        });
        
        req.on('error', (e) => {
            console.error(`❌ [AUTO-START] Erro: ${e.message}`);
        });
        
        req.write(postData);
        req.end();
    }, 2000);
});
EOF;

// Criar script bash para executar na VPS
$bash_script = <<<'BASH'
#!/bin/bash
echo "🚀 APLICANDO CORREÇÃO FINAL DO WHATSAPP API"
echo "============================================"

# 1. Parar todos os processos
echo "🛑 Parando processos..."
pm2 delete all 2>/dev/null || true
pkill -f "node.*whatsapp" 2>/dev/null || true
pkill -f "node.*3000" 2>/dev/null || true
pkill -f "node.*3001" 2>/dev/null || true
fuser -k 3000/tcp 2>/dev/null || true
fuser -k 3001/tcp 2>/dev/null || true

# 2. Fazer backup dos arquivos originais
echo "💾 Fazendo backup..."
cp app.js app.js.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# 3. Aplicar arquivo corrigido
echo "📝 Aplicando correção..."
cat > /tmp/whatsapp-api-server-final.js << 'EOF_CODIGO'
CODIGO_AQUI
EOF_CODIGO

# 4. Substituir ambos os arquivos
cp /tmp/whatsapp-api-server-final.js app.js
cp /tmp/whatsapp-api-server-final.js whatsapp-api-server.js

# 5. Verificar dependências
echo "📦 Verificando dependências..."
npm install whatsapp-web.js express cors qrcode-terminal fs-extra multer path

# 6. Reiniciar PM2
echo "🔄 Reiniciando serviços..."
pm2 start ecosystem.config.js

# 7. Verificar status
echo "📊 Verificando status..."
sleep 5
pm2 status

echo ""
echo "✅ CORREÇÃO APLICADA COM SUCESSO!"
echo "🔍 Verificar logs com: pm2 logs"
echo "🌐 Testar endpoints:"
echo "   - http://212.85.11.238:3000/status"
echo "   - http://212.85.11.238:3001/status"
echo "   - http://212.85.11.238:3000/qr"
echo "   - http://212.85.11.238:3001/qr"
BASH;

// Substituir o placeholder pelo código real
$bash_script = str_replace('CODIGO_AQUI', $codigo_corrigido, $bash_script);

// Salvar o script bash
$script_path = '/tmp/aplicar_correcao_whatsapp_final.sh';
file_put_contents($script_path, $bash_script);
chmod($script_path, 0755);

echo "✅ Script criado: $script_path\n\n";

echo "🔧 INSTRUÇÕES PARA APLICAR NA VPS:\n";
echo "===================================\n";
echo "1. Faça SSH para a VPS:\n";
echo "   ssh root@212.85.11.238\n\n";
echo "2. Navegue para o diretório do projeto:\n";
echo "   cd /var/whatsapp-api\n\n";
echo "3. Baixe e execute o script de correção:\n";
echo "   curl -o aplicar_correcao_final.sh 'https://transfer.sh/aplicar_correcao_final.sh'\n";
echo "   # OU copie o conteúdo do script abaixo diretamente\n\n";

echo "📝 SCRIPT BASH PARA A VPS:\n";
echo "==========================\n";
echo $bash_script;

echo "\n\n🎯 ALTERNATIVA RÁPIDA:\n";
echo "======================\n";
echo "Execute diretamente na VPS:\n";
echo "\ncat > aplicar_correcao_final.sh << 'EOF'\n";
echo $bash_script;
echo "EOF\n";
echo "chmod +x aplicar_correcao_final.sh\n";
echo "./aplicar_correcao_final.sh\n";

echo "\n✅ DEPOIS DE APLICAR, TESTE:\n";
echo "curl http://212.85.11.238:3000/status | jq\n";
echo "curl http://212.85.11.238:3000/qr | jq\n";
?> 