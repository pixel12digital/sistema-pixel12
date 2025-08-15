const { Sequelize } = require('sequelize');
require('dotenv').config();

// Configuração do banco de dados
const sequelize = new Sequelize(
  process.env.DB_NAME || 'whatsapp_multichannel',
  process.env.DB_USER || 'root',
  process.env.DB_PASS || '',
  {
    host: process.env.DB_HOST || 'localhost',
    port: process.env.DB_PORT || 3306,
    dialect: 'mysql',
    logging: process.env.NODE_ENV === 'development' ? console.log : false,
    pool: {
      max: 10,
      min: 0,
      acquire: 30000,
      idle: 10000
    },
    define: {
      timestamps: true,
      underscored: true,
      freezeTableName: true
    },
    dialectOptions: {
      charset: 'utf8mb4',
      collate: 'utf8mb4_unicode_ci',
      supportBigNumbers: true,
      bigNumberStrings: true
    },
    timezone: '+00:00'
  }
);

// Testar conexão
sequelize.authenticate()
  .then(() => {
    console.log('✅ Conexão com banco de dados estabelecida com sucesso');
  })
  .catch(err => {
    console.error('❌ Erro ao conectar com banco de dados:', err);
  });

module.exports = sequelize;
