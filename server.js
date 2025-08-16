const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const compression = require('compression');
const mysql = require('mysql2/promise');
require('dotenv').config();

const app = express();
app.use(express.static("public"));
const PORT = process.env.PORT || 3000;

// Configuração do banco de dados
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASS || '',
  database: process.env.DB_NAME || 'whatsapp_multichannel'
};

// Pool de conexões MySQL
let dbPool;

// Função para conectar ao banco
async function connectDB() {
  try {
    dbPool = mysql.createPool(dbConfig);
    console.log('✅ Conectado ao banco de dados MySQL');
  } catch (error) {
    console.error('❌ Erro ao conectar ao banco:', error);
  }
}

// Middlewares de segurança
app.use(helmet());
app.use(cors());
app.use(compression());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Logging
if (process.env.NODE_ENV === 'development') {
  app.use(morgan('dev'));
}

// Health check
app.get('/health', (req, res) => {
  res.status(200).json({
    status: 'OK',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    environment: process.env.NODE_ENV || 'development'
  });
});

// Rota de teste
app.get('/api/test', (req, res) => {
  res.json({
    message: 'WhatsApp Multi-Canais API funcionando!',
    timestamp: new Date().toISOString()
  });
});

// Rota para listar usuários
app.get('/api/users', async (req, res) => {
  try {
    const [rows] = await dbPool.execute('SELECT * FROM users ORDER BY created_at DESC');
    res.json({ success: true, data: rows });
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
    res.status(500).json({ success: false, error: 'Erro interno do servidor' });
  }
});

// Rota para criar usuário
app.post('/api/users', async (req, res) => {
  try {
    const { name, email } = req.body;
    
    if (!name || !email) {
      return res.status(400).json({ 
        success: false, 
        error: 'Nome e email são obrigatórios' 
      });
    }

    const [result] = await dbPool.execute(
      'INSERT INTO users (name, email) VALUES (?, ?)',
      [name, email]
    );

    res.status(201).json({ 
      success: true, 
      message: 'Usuário criado com sucesso',
      id: result.insertId 
    });
  } catch (error) {
    console.error('Erro ao criar usuário:', error);
    res.status(500).json({ success: false, error: 'Erro interno do servidor' });
  }
});

// Rota para listar sessões
app.get('/api/sessions', async (req, res) => {
  try {
    const [rows] = await dbPool.execute('SELECT * FROM whatsapp_sessions ORDER BY created_at DESC');
    res.json({ success: true, data: rows });
  } catch (error) {
    console.error('Erro ao buscar sessões:', error);
    res.status(500).json({ success: false, error: 'Erro interno do servidor' });
  }
});

// Rota para criar sessão
app.post('/api/sessions', async (req, res) => {
  try {
    const { session_name, phone_number } = req.body;
    
    if (!session_name || !phone_number) {
      return res.status(400).json({ 
        success: false, 
        error: 'Nome da sessão e número de telefone são obrigatórios' 
      });
    }

    const [result] = await dbPool.execute(
      'INSERT INTO whatsapp_sessions (session_name, phone_number) VALUES (?, ?)',
      [session_name, phone_number]
    );

    res.status(201).json({ 
      success: true, 
      message: 'Sessão criada com sucesso',
      id: result.insertId 
    });
  } catch (error) {
    console.error('Erro ao criar sessão:', error);
    res.status(500).json({ success: false, error: 'Erro interno do servidor' });
  }
});

// Iniciar servidor
async function startServer() {
  await connectDB();
  
  app.listen(PORT, () => {
    console.log(`🚀 Servidor rodando na porta ${PORT}`);
    console.log(`🌍 Ambiente: ${process.env.NODE_ENV || 'development'}`);
    console.log(`📱 WhatsApp Multi-Canais iniciado com sucesso!`);
    console.log(`🔗 Local: http://localhost:${PORT}`);
    console.log(`🔗 API: http://localhost:${PORT}/api/test`);
    console.log(`🔗 Health: http://localhost:${PORT}/health`);
    console.log(`👥 Usuários: http://localhost:${PORT}/api/users`);
    console.log(`📱 Sessões: http://localhost:${PORT}/api/sessions`);
  });
}

startServer();
