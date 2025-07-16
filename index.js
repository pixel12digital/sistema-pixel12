const { Client, LocalAuth } = require('whatsapp-web.js');
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

// Remover código que apaga a sessão ao iniciar
// const SESSION_PATH = './.wwebjs_auth';
// if (fs.existsSync(SESSION_PATH)) {
//   fs.rmSync(SESSION_PATH, { recursive: true, force: true });
//   console.log('Sessão antiga removida. Será gerado novo QR.');
// }

app.use(express.json());

let lastQr = null;
let isReady = false;
let lastSession = null;
let humanSimulation = true; // Controle para simulação humana

// Sistema de fila para mensagens
let messageQueue = [];
let isProcessingQueue = false;

// Função para processar fila de mensagens
async function processMessageQueue() {
  if (isProcessingQueue || messageQueue.length === 0) {
    return;
  }
  
  isProcessingQueue = true;
  console.log(`📋 Processando fila: ${messageQueue.length} mensagens pendentes`);
  
  while (messageQueue.length > 0) {
    const { chatId, message, resolve, reject } = messageQueue.shift();
    
    try {
      console.log(`📤 Processando mensagem ${messageQueue.length + 1} da fila`);
      
      let msg;
      if (humanSimulation) {
        msg = await sendMessageWithHumanSimulation(client, chatId, message);
      } else {
        msg = await client.sendMessage(chatId, message);
      }
      
      console.log(`✅ Mensagem processada com sucesso:`, msg.id._serialized);
      resolve(msg);
      
      // Pausa entre mensagens da fila (evita spam)
      if (messageQueue.length > 0) {
        const pauseTime = Math.floor(Math.random() * 5000) + 3000; // 3-8 segundos
        console.log(`⏳ Aguardando ${pauseTime}ms antes da próxima mensagem...`);
        await delay(pauseTime);
      }
      
    } catch (error) {
      console.error(`❌ Erro ao processar mensagem da fila:`, error);
      reject(error);
    }
  }
  
  isProcessingQueue = false;
  console.log(`📋 Fila processada completamente`);
}

// Função para adicionar mensagem à fila
function addToMessageQueue(chatId, message) {
  return new Promise((resolve, reject) => {
    messageQueue.push({ chatId, message, resolve, reject });
    console.log(`📋 Mensagem adicionada à fila. Total: ${messageQueue.length}`);
    processMessageQueue(); // Inicia processamento se não estiver rodando
  });
}

// Inicializar o client com persistência de sessão
const client = new Client({
  authStrategy: new LocalAuth({ dataPath: './.wwebjs_auth' })
});

client.on('qr', qr => {
  lastQr = qr;
  qrcode.generate(qr, { small: true });
  console.log('QR gerado! Acesse /qr para pegar o QR no painel.');
});

client.on('ready', () => {
  isReady = true;
  lastSession = new Date().toISOString();
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
  lastSession = null;
});

client.on('message', msg => {
  console.log('📨 MENSAGEM RECEBIDA:');
  console.log('   De:', msg.from);
  console.log('   Conteúdo:', msg.body);
  console.log('   Timestamp:', msg.timestamp);
  console.log('   Tipo:', msg.type);
  
  // Enviar para o painel PHP
  const dados = {
    from: msg.from,
    body: msg.body,
    timestamp: msg.timestamp
  };
  
  console.log('📤 Enviando dados para o painel:', JSON.stringify(dados));
  
  fetch('http://localhost:8080/loja-virtual-revenda/painel/receber_mensagem.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  }).then(res => {
    console.log('📥 Status da resposta do painel:', res.status);
    return res.text();
  }).then(txt => {
    console.log('📥 Resposta do painel:', txt);
  }).catch(err => {
    console.error('❌ Erro ao enviar mensagem ao painel:', err);
  });
});

client.initialize();

// Endpoint para pegar o QR Code (texto)
app.get('/qr', (req, res) => {
  if (!isReady && !lastQr) {
    res.status(503).json({ qr: null, error: 'Robô não está conectado e QR não disponível.' });
    return;
  }
  res.json({ qr: lastQr });
});

// Endpoint para status
app.get('/status', (req, res) => {
  let number = null;
  if (client.info && client.info.wid && client.info.wid.user) {
    number = client.info.wid.user;
  }
  if (!isReady) {
    res.status(503).json({ ready: false, lastSession, number, error: 'Robô não está conectado.' });
    return;
  }
  res.json({ ready: isReady, lastSession, number });
});

// Função para validar e ajustar número para formato WhatsApp (criteriosa por DDD)
function formatarNumeroWhatsapp(numero) {
  numero = String(numero).replace(/\D/g, '');
  if (numero.startsWith('55')) numero = numero.slice(2);
  
  if (numero.length < 10) return null; // Precisa de pelo menos DDD + 8 dígitos
  
  const ddd = numero.slice(0, 2);
  const telefone = numero.slice(2);
  
  // Exceção: DDD 61 (Brasília) e DDD 11 (São Paulo) devem SEMPRE manter o nono dígito
  if (ddd === '61' || ddd === '11') {
    // Se já tem 9 dígitos, mantém; se tem 8, adiciona o 9
    if (telefone.length === 8) {
      numero = ddd + '9' + telefone;
    }
    // Se já tem 9 dígitos, mantém como está
  } else if (parseInt(ddd) <= 30) {
    // Sempre garantir o nono dígito para DDDs <= 30
    if (telefone.length === 8) {
      numero = ddd + '9' + telefone;
    }
    // Se já tem 9 dígitos, mantém
  } else {
    // DDD > 30: remova o nono dígito se houver
    if (telefone.length === 9 && telefone[0] === '9') {
      numero = ddd + telefone.slice(1);
    }
    // Se já tem 8 dígitos, mantém
  }
  
  // Só envia se for 10 ou 11 dígitos após ajuste
  if (numero.length === 10 || numero.length === 11) {
    return '55' + numero;
  }
  
  // Se não for válido, retorna null
  return null;
}

// Funções para simulação humana aprimorada
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function randomDelay(min, max) {
  const delay = Math.floor(Math.random() * (max - min + 1)) + min;
  return new Promise(resolve => setTimeout(resolve, delay));
}

function simulateTyping(text) {
  // Simula velocidade de digitação humana (60-220ms por caractere)
  const typingSpeed = Math.floor(Math.random() * 160) + 60;
  return text.length * typingSpeed;
}

function simulateHumanBehavior() {
  // Pausas aleatórias como um humano faria
  const behaviors = [
    { type: 'pause', duration: () => randomDelay(1200, 4000) }, // Pausa curta
    { type: 'pause', duration: () => randomDelay(800, 2200) },  // Pausa muito curta
    { type: 'pause', duration: () => randomDelay(2500, 7000) }, // Pausa média
    { type: 'pause', duration: () => randomDelay(5000, 12000) }, // Pausa longa
  ];
  const randomBehavior = behaviors[Math.floor(Math.random() * behaviors.length)];
  return randomBehavior.duration();
}

function isPunctuation(char) {
  return ['.', ',', ';', ':', '!', '?', '\n'].includes(char);
}

async function simulateCorrection(currentMessage) {
  // 10% de chance de "errar" e corrigir uma palavra
  if (Math.random() < 0.10 && currentMessage.length > 5) {
    const words = currentMessage.split(' ');
    if (words.length > 1) {
      words[words.length - 1] = '...'; // simula apagando
      await randomDelay(300, 800);
      words[words.length - 1] = '';
      await randomDelay(200, 600);
      // "corrige" escrevendo de novo
      words[words.length - 1] = 'corrigido';
      await randomDelay(200, 600);
      return words.join(' ');
    }
  }
  return currentMessage;
}

// Função para enviar mensagem com simulação humana aprimorada
async function sendMessageWithHumanSimulation(client, chatId, message) {
  try {
    console.log('🤖 Iniciando simulação humana aprimorada...');
    // 1. Pausa inicial aleatória (como se estivesse pensando)
    await simulateHumanBehavior();
    console.log('⏳ Pausa inicial simulada');
    // 2. Simular tempo de digitação
    let currentMessage = '';
    const chars = message.split('');
    for (let i = 0; i < chars.length; i++) {
      currentMessage += chars[i];
      // Pausa entre caracteres (simula digitação)
      await randomDelay(60, 220);
      // Pausa extra entre frases
      if (isPunctuation(chars[i])) {
        await randomDelay(300, 1200);
      }
      // Pequena chance de corrigir
      if (Math.random() < 0.02 && i > 5) {
        currentMessage = await simulateCorrection(currentMessage);
      }
    }
    // 3. Pausa final antes de enviar (como humano revisando)
    await randomDelay(1500, 4000);
    console.log('📤 Enviando mensagem...');
    // 4. Enviar a mensagem
    const msg = await client.sendMessage(chatId, currentMessage);
    // 5. Pausa pós-envio (como humano faria)
    await randomDelay(1200, 3000);
    console.log('✅ Mensagem enviada com simulação humana aprimorada');
    return msg;
  } catch (error) {
    console.error('❌ Erro na simulação humana:', error);
    throw error;
  }
}

// Endpoint para enviar mensagem
app.post('/send', async (req, res) => {
  let { to, message } = req.body;
  if (!isReady) {
    return res.json({ success: false, error: 'WhatsApp não está pronto' });
  }
  
  // Validação e ajuste do número
  const numeroAjustado = formatarNumeroWhatsapp(to);
  if (!numeroAjustado) {
    return res.json({ success: false, error: 'Número inválido para envio no WhatsApp.' });
  }
  
  try {
    // Adicionar mensagem à fila
    const msg = await addToMessageQueue(numeroAjustado + '@c.us', message);
    
    // Log do envio
    console.log(`Mensagem enviada para ${numeroAjustado}:`, msg.id._serialized);
    
    // Monitorar status da mensagem
    setTimeout(async () => {
      try {
        const messageStatus = await msg.getStatus();
        console.log(`Status da mensagem ${msg.id._serialized}:`, messageStatus);
        
        // Se não foi entregue, tentar novamente
        if (messageStatus === 'SENT' || messageStatus === 'PENDING') {
          console.log(`Mensagem ${msg.id._serialized} não entregue, aguardando...`);
          
          // Aguardar mais um pouco e verificar novamente
          setTimeout(async () => {
            try {
              const finalStatus = await msg.getStatus();
              console.log(`Status final da mensagem ${msg.id._serialized}:`, finalStatus);
              
              if (finalStatus === 'SENT') {
                console.log(`⚠️ Mensagem ${msg.id._serialized} ainda não entregue - possível bloqueio`);
              }
            } catch (statusErr) {
              console.error('Erro ao verificar status final:', statusErr);
            }
          }, 30000); // 30 segundos
        }
      } catch (statusErr) {
        console.error('Erro ao verificar status da mensagem:', statusErr);
      }
    }, 10000); // 10 segundos
    
    res.json({ 
      success: true, 
      messageId: msg.id._serialized,
      status: 'enviado',
      queuePosition: messageQueue.length + 1
    });
    
  } catch (err) {
    console.error('Erro ao enviar mensagem:', err);
    res.json({ success: false, error: err.message });
  }
});

// Endpoint para verificar status de uma mensagem
app.get('/message-status/:messageId', async (req, res) => {
  const { messageId } = req.params;
  
  if (!isReady) {
    return res.json({ success: false, error: 'WhatsApp não está pronto' });
  }
  
  try {
    // Buscar a mensagem pelo ID
    const msg = await client.getMessageById(messageId);
    if (!msg) {
      return res.json({ success: false, error: 'Mensagem não encontrada' });
    }
    
    const status = await msg.getStatus();
    res.json({ 
      success: true, 
      messageId: messageId,
      status: status,
      timestamp: new Date().toISOString()
    });
    
  } catch (err) {
    console.error('Erro ao verificar status da mensagem:', err);
    res.json({ success: false, error: err.message });
  }
});

// Endpoint para reenviar mensagem (retry)
app.post('/retry', async (req, res) => {
  let { to, message, originalMessageId } = req.body;
  
  if (!isReady) {
    return res.json({ success: false, error: 'WhatsApp não está pronto' });
  }
  
  // Validação e ajuste do número
  const numeroAjustado = formatarNumeroWhatsapp(to);
  if (!numeroAjustado) {
    return res.json({ success: false, error: 'Número inválido para envio no WhatsApp.' });
  }
  
  try {
    // Aguardar um pouco antes de reenviar (evitar spam)
    await new Promise(resolve => setTimeout(resolve, 5000));
    
    // Enviar nova mensagem
    const msg = await client.sendMessage(numeroAjustado + '@c.us', message);
    
    console.log(`Retry: Mensagem reenviada para ${numeroAjustado}:`, msg.id._serialized);
    
    res.json({ 
      success: true, 
      messageId: msg.id._serialized,
      status: 'reenviado',
      originalMessageId: originalMessageId
    });
    
  } catch (err) {
    console.error('Erro ao reenviar mensagem:', err);
    res.json({ success: false, error: err.message });
  }
});

// Endpoint para controlar simulação humana
app.post('/simulation', (req, res) => {
  const { enabled } = req.body;
  
  if (typeof enabled === 'boolean') {
    humanSimulation = enabled;
    console.log(`🤖 Simulação humana ${humanSimulation ? 'ATIVADA' : 'DESATIVADA'}`);
    res.json({ 
      success: true, 
      message: `Simulação humana ${humanSimulation ? 'ativada' : 'desativada'}`,
      humanSimulation: humanSimulation
    });
  } else {
    res.json({ 
      success: false, 
      error: 'Parâmetro "enabled" deve ser true ou false' 
    });
  }
});

app.get('/simulation', (req, res) => {
  res.json({ 
    success: true, 
    humanSimulation: humanSimulation 
  });
});

// Endpoint para verificar status da fila
app.get('/queue', (req, res) => {
  res.json({
    success: true,
    queueLength: messageQueue.length,
    isProcessing: isProcessingQueue,
    status: isProcessingQueue ? 'processando' : (messageQueue.length > 0 ? 'aguardando' : 'vazia')
  });
});

// Endpoint para limpar fila (emergência)
app.post('/queue/clear', (req, res) => {
  const queueLength = messageQueue.length;
  messageQueue = [];
  isProcessingQueue = false;
  
  console.log(`🗑️ Fila limpa. ${queueLength} mensagens removidas.`);
  
  res.json({
    success: true,
    message: `Fila limpa. ${queueLength} mensagens removidas.`,
    queueLength: 0
  });
});

// Endpoint para desconectar (POST e GET)
const SESSION_PATH = './.wwebjs_auth';

app.post('/logout', (req, res) => {
  client.logout().then(() => {
    isReady = false;
    lastSession = null;
    // Remove a sessão local
    if (fs.existsSync(SESSION_PATH)) {
      fs.rmSync(SESSION_PATH, { recursive: true, force: true });
    }
    // Limpa o QR antigo
    lastQr = null;
    // Reinicializa o cliente para forçar novo QR
    client.destroy().then(() => {
      client.initialize();
      res.json({ success: true });
    });
  }).catch(err => {
    res.json({ success: false, error: err.message });
  });
});
app.get('/logout', (req, res) => {
  client.logout().then(() => {
    isReady = false;
    lastSession = null;
    res.json({ success: true });
  }).catch(err => {
    res.json({ success: false, error: err.message });
  });
});

app.listen(3000, () => console.log('API do robô rodando em http://localhost:3000')); 