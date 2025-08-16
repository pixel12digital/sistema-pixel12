const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const compression = require('compression');
const mysql = require('mysql2/promise');
require('dotenv').config();

// Importar modelos e rotas
const User = require('./src/models/User');
const authRoutes = require('./src/api/routes/auth');

const app = express();
app.use(express.static("public"));
const PORT = process.env.PORT || 3000;

// Configuração do banco de dados (VPS)
const dbConfig = {
  host: process.env.VPS_DB_HOST || 'localhost',
  port: process.env.VPS_DB_PORT || 3306,
  user: process.env.VPS_DB_USER || 'root',
  password: process.env.VPS_DB_PASS || '',
  database: process.env.VPS_DB_NAME || 'whatsapp_multichannel'
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

// Usar rotas de autenticação
app.use('/api/auth', authRoutes);

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
  
  // Configurar pool de conexões no modelo User
  User.setPool(dbPool);
  
  // Inicializar tabela de usuários
  try {
    const userModel = new User();
    await userModel.createTable();
    console.log('✅ Sistema de autenticação inicializado');
  } catch (error) {
    console.error('❌ Erro ao inicializar sistema de autenticação:', error);
  }
  
  app.listen(PORT, () => {
    console.log(`🚀 Servidor rodando na porta ${PORT}`);
    console.log(`🌍 Ambiente: ${process.env.NODE_ENV || 'development'}`);
    console.log(`📱 WhatsApp Multi-Canais iniciado com sucesso!`);
    console.log(`🔗 Local: http://localhost:${PORT}`);
    console.log(`🔗 API: http://localhost:${PORT}/api/test`);
    console.log(`🔗 Health: http://localhost:${PORT}/health`);
    console.log(`🔐 Auth: http://localhost:${PORT}/api/auth/login`);
    console.log(`📱 Sessões: http://localhost:${PORT}/api/sessions`);
  });
}

startServer();
