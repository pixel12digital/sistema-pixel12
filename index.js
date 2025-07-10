const { Client } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');
const fetch = require('node-fetch');
const fs = require('fs');
const app = express();

// Endpoint de teste para verificar se o Express está rodando
app.get('/teste', (req, res) => {
  res.send('OK');
});

// Middleware de CORS para permitir requisições do painel
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  next();
});

// Limpa sessão antiga ao iniciar para forçar novo QR
const SESSION_PATH = './.wwebjs_auth';
if (fs.existsSync(SESSION_PATH)) {
  fs.rmSync(SESSION_PATH, { recursive: true, force: true });
  console.log('Sessão antiga removida. Será gerado novo QR.');
}

app.use(express.json());

let lastQr = null;
let isReady = false;

const client = new Client();

client.on('qr', qr => {
  lastQr = qr;
  qrcode.generate(qr, { small: true });
  console.log('QR gerado! Acesse /qr para pegar o QR no painel.');
});

client.on('ready', () => {
  isReady = true;
  console.log('WhatsApp está pronto!');
});

client.on('authenticated', () => {
  console.log('Autenticado!');
});

client.on('auth_failure', () => {
  console.log('Falha na autenticação. Escaneie o QR novamente.');
  isReady = false;
});

client.on('disconnected', () => {
  console.log('Desconectado!');
  isReady = false;
});

client.on('message', msg => {
  fetch('http://localhost:8080/loja-virtual-revenda/painel/receber_mensagem.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      from: msg.from,
      body: msg.body,
      timestamp: msg.timestamp
    })
  }).then(res => res.text()).then(txt => {
    console.log('Mensagem enviada ao painel:', txt);
  }).catch(err => {
    console.error('Erro ao enviar mensagem ao painel:', err);
  });
});

client.initialize();

// Endpoint para pegar o QR Code (texto)
app.get('/qr', (req, res) => {
  if (lastQr) {
    res.json({ qr: lastQr });
  } else {
    res.json({ qr: null });
  }
});

// Endpoint para status
app.get('/status', (req, res) => {
  res.json({ ready: isReady });
});

// Endpoint para enviar mensagem
app.post('/send', async (req, res) => {
  const { to, message } = req.body;
  if (!isReady) {
    return res.json({ success: false, error: 'WhatsApp não está pronto' });
  }
  try {
    await client.sendMessage(to + '@c.us', message);
    res.json({ success: true });
  } catch (err) {
    res.json({ success: false, error: err.message });
  }
});

// Endpoint para desconectar
app.post('/logout', (req, res) => {
  client.logout().then(() => {
    res.json({ success: true });
  }).catch(err => {
    res.json({ success: false, error: err.message });
  });
});

app.listen(3000, () => console.log('API do robô rodando em http://localhost:3000')); 