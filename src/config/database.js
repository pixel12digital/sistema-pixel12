require('dotenv').config();

// Configuração do banco de dados MySQL (VPS)
const dbConfig = {
  host: process.env.VPS_DB_HOST || 'localhost',
  port: process.env.VPS_DB_PORT || 3306,
  user: process.env.VPS_DB_USER || 'root',
  password: process.env.VPS_DB_PASS || '',
  database: process.env.VPS_DB_NAME || 'whatsapp_multichannel',
  charset: 'utf8mb4',
  timezone: '+00:00'
};

module.exports = dbConfig;
