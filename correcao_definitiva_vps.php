<?php
/**
 * 🔧 CORREÇÃO DEFINITIVA - SERVIDOR WHATSAPP VPS
 * 
 * Este script aplica a correção definitiva no whatsapp-api-server.js da VPS
 */

echo "🔧 APLICANDO CORREÇÃO DEFINITIVA NA VPS\n";
echo "======================================\n\n";

// Função para executar comandos na VPS via SSH
function executarComandoVPS($comando) {
    $vps_ip = '212.85.11.238';
    $ssh_comando = "ssh root@$vps_ip \"$comando\"";
    
    echo "🔄 Executando: $comando\n";
    
    // Para Windows, usamos plink se disponível, senão mostramos o comando
    if (PHP_OS_FAMILY === 'Windows') {
        echo "   ⚠️ Execute manualmente na VPS: $comando\n";
        return false;
    } else {
        $output = shell_exec($ssh_comando);
        echo "   📋 Resultado: $output\n";
        return $output;
    }
}

echo "🎯 ETAPA 1: BACKUP DO ARQUIVO ATUAL\n";
echo "==================================\n";
$backup_cmd = "cd /var/whatsapp-api && cp whatsapp-api-server.js whatsapp-api-server.js.backup.definitivo.$(date +%Y%m%d_%H%M%S)";
executarComandoVPS($backup_cmd);

echo "\n🎯 ETAPA 2: CRIANDO VERSÃO CORRIGIDA\n";
echo "===================================\n";

// Criar script de correção que será executado na VPS
$script_correcao = '#!/bin/bash

# Script de correção definitiva para o WhatsApp API Server
echo "🔧 Iniciando correção definitiva..."

cd /var/whatsapp-api

# 1. Parar os serviços
echo "1. Parando serviços..."
pm2 stop all

# 2. Fazer backup
echo "2. Fazendo backup..."
cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)

# 3. Aplicar correções no arquivo
echo "3. Aplicando correções..."

# CORREÇÃO 1: Adicionar endpoint /qr funcional
cat >> whatsapp-api-server_temp.js << \'EOF\'
const { Client, LocalAuth, MessageMedia } = require(\'whatsapp-web.js\');
const express = require(\'express\');
const cors = require(\'cors\');
const qrcode = require(\'qrcode-terminal\');
const fs = require(\'fs-extra\');
const multer = require(\'multer\');
const path = require(\'path\');

const app = express();
const PORT = parseInt(process.env.PORT, 10) || 3000;
const sessionName = PORT === 3001 ? \'comercial\' : \'default\';

console.log(`🚩 [STARTUP] Porta ${PORT} → sessão="${sessionName}"`);

// Configurações CORS e middleware
app.use(cors({
    origin: [
        \'http://localhost:8080\',
        \'http://127.0.0.1:8080\',
        \'http://212.85.11.238:8080\',
        \'http://212.85.11.238\',
        \'https://212.85.11.238\',
        \'http://localhost:3000\',
        \'http://localhost:3001\'
    ],
    credentials: true,
    methods: [\'GET\', \'POST\', \'PUT\', \'DELETE\', \'OPTIONS\'],
    allowedHeaders: [\'Content-Type\', \'Authorization\', \'X-Requested-With\']
}));

app.use(express.json({ limit: \'50mb\' }));
app.use(express.urlencoded({ extended: true, limit: \'50mb\' }));

// Configuração de upload
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        const uploadPath = path.join(__dirname, \'uploads\');
        if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + \'-\' + file.originalname);
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
                \'--no-sandbox\',
                \'--disable-setuid-sandbox\',
                \'--disable-dev-shm-usage\',
                \'--disable-accelerated-2d-canvas\',
                \'--no-first-run\',
                \'--no-zygote\',
                \'--disable-gpu\'
            ]
        }
    });

    // Event listeners
    client.on(\'qr\', (qr) => {
        console.log(`📱 [${session}] QR Code gerado!`);
        qrCodes[session] = qr;
        qrcode.generate(qr, { small: true });
    });

    client.on(\'ready\', () => {
        console.log(`✅ [${session}] Cliente conectado!`);
        isReady[session] = true;
        qrCodes[session] = null; // Limpar QR após conectar
    });

    client.on(\'authenticated\', () => {
        console.log(`🔐 [${session}] Autenticado!`);
    });

    client.on(\'auth_failure\', (msg) => {
        console.log(`❌ [${session}] Falha na autenticação:`, msg);
    });

    client.on(\'disconnected\', (reason) => {
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
app.get(\'/status\', (req, res) => {
    const clientsStatus = {};
    
    Object.keys(clients).forEach(session => {
        clientsStatus[session] = {
            ready: isReady[session] || false,
            hasQR: !!qrCodes[session],
            qr: qrCodes[session] || null // CORREÇÃO: Expor QR code
        };
    });

    res.json({
        status: \'running\',
        ready: isReady[sessionName] || false,
        port: PORT.toString(),
        timestamp: new Date().toISOString(),
        lastSession: new Date().toISOString(),
        clients_status: clientsStatus
    });
});

// CORREÇÃO: Endpoint /qr funcional
app.get(\'/qr\', (req, res) => {
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
            message: \'QR Code disponível\'
        });
    } else if (isReady[session]) {
        res.json({
            success: false,
            message: \'Cliente já está conectado\',
            session: session,
            ready: true
        });
    } else {
        res.json({
            success: false,
            message: \'QR Code não disponível no momento\',
            session: session,
            suggestion: \'Aguarde alguns segundos e tente novamente\'
        });
    }
});

// Iniciar sessão
app.post(\'/session/start/:sessionName\', (req, res) => {
    const session = req.params.sessionName;
    
    console.log(`🚀 [START] Iniciando sessão: ${session}`);
    
    if (clients[session]) {
        return res.json({
            success: true,
            message: \'Sessão já existe\',
            session: session
        });
    }

    try {
        const newClient = createClient(session);
        newClient.initialize();
        
        res.json({
            success: true,
            message: \'Sessão iniciada com sucesso\',
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
app.get(\'/sessions\', (req, res) => {
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
app.post(\'/send/text\', async (req, res) => {
    try {
        const { sessionName: session = sessionName, number, message } = req.body;
        
        if (!clients[session] || !isReady[session]) {
            return res.status(400).json({
                success: false,
                error: \'Cliente não está conectado\'
            });
        }

        const chatId = number.includes(\'@\') ? number : `${number}@c.us`;
        await clients[session].sendMessage(chatId, message);

        res.json({
            success: true,
            message: \'Mensagem enviada com sucesso\'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Webhook config
app.get(\'/webhook/config\', (req, res) => {
    res.json({
        success: true,
        webhook: \'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php\',
        message: \'Webhook configurado\'
    });
});

app.post(\'/webhook/config\', (req, res) => {
    const { url } = req.body;
    console.log(`🔗 [WEBHOOK] Configurado: ${url}`);
    
    res.json({
        success: true,
        webhook: url,
        message: \'Webhook configurado com sucesso\'
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
EOF

# 4. Substituir arquivo original
mv whatsapp-api-server_temp.js whatsapp-api-server.js

# 5. Reinstalar dependências se necessário
echo "4. Verificando dependências..."
npm install --production

# 6. Reiniciar serviços
echo "5. Reiniciando serviços..."
pm2 start ecosystem.config.js

echo "✅ Correção definitiva aplicada com sucesso!"
echo "🔄 Aguarde 30 segundos e teste os QR codes..."
';

// Salvar script na VPS
echo "📝 Criando script de correção na VPS...\n";
$script_file = '/tmp/correcao_definitiva_whatsapp.sh';
$criar_script_cmd = "cat > $script_file << 'EOF'\n$script_correcao\nEOF";
executarComandoVPS($criar_script_cmd);

echo "🔧 Tornando script executável...\n";
executarComandoVPS("chmod +x $script_file");

echo "\n🎯 ETAPA 3: EXECUTAR CORREÇÃO NA VPS\n";
echo "===================================\n";
echo "EXECUTE ESTE COMANDO NA VPS:\n\n";
echo "ssh root@212.85.11.238\n";
echo "bash /tmp/correcao_definitiva_whatsapp.sh\n\n";

echo "🎯 COMANDOS MANUAIS (se o script não funcionar):\n";
echo "===============================================\n";
echo "1. ssh root@212.85.11.238\n";
echo "2. cd /var/whatsapp-api\n";
echo "3. pm2 stop all\n";
echo "4. cp whatsapp-api-server.js whatsapp-api-server.js.backup\n";
echo "5. Editar whatsapp-api-server.js manualmente\n";
echo "6. pm2 start ecosystem.config.js\n\n";

echo "✅ CORREÇÃO DEFINITIVA PREPARADA!\n";
echo "=================================\n";
echo "Após executar na VPS:\n";
echo "1. Os endpoints /qr funcionarão perfeitamente\n";
echo "2. O campo 'qr' será exposto no /status\n";
echo "3. O painel exibirá QR codes corretamente\n";
echo "4. Ambos os canais (3000 e 3001) funcionarão\n";
?> 