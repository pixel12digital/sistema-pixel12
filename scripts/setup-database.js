require('dotenv').config();
const mysql = require('mysql2/promise');

async function setupDatabase() {
  console.log('🔧 Configurando banco de dados na VPS...\n');
  
  // Primeiro, conectar como root (você precisa da senha do root da VPS)
  const rootConfig = {
    host: process.env.VPS_DB_HOST,
    port: process.env.VPS_DB_PORT,
    user: 'root', // Usuário root da VPS
    password: process.env.VPS_ROOT_PASS || 'SUA_SENHA_ROOT_AQUI', // Adicione no .env
    charset: 'utf8mb4',
    timezone: '+00:00'
  };
  
  console.log('📋 Configuração para conectar como root:');
  console.log(`Host: ${rootConfig.host}`);
  console.log(`Port: ${rootConfig.port}`);
  console.log(`User: ${rootConfig.user}`);
  console.log(`Password: ${rootConfig.password ? 'DEFINIDO' : 'NÃO DEFINIDO'}\n`);
  
  try {
    console.log('🔄 Conectando como root...');
    const rootConnection = await mysql.createConnection(rootConfig);
    console.log('✅ Conectado como root!');
    
    // 1. Criar banco de dados se não existir
    console.log('🔄 Criando banco de dados...');
    await rootConnection.execute(`CREATE DATABASE IF NOT EXISTS ${process.env.VPS_DB_NAME}`);
    console.log(`✅ Banco ${process.env.VPS_DB_NAME} criado/verificado!`);
    
    // 2. Criar usuário whatsapp_user se não existir
    console.log('🔄 Criando usuário whatsapp_user...');
    try {
      await rootConnection.execute(`
        CREATE USER IF NOT EXISTS '${process.env.VPS_DB_USER}'@'%' 
        IDENTIFIED BY '${process.env.VPS_DB_PASS}'
      `);
      console.log('✅ Usuário whatsapp_user criado!');
    } catch (error) {
      if (error.code === 'ER_USER_ALREADY_EXISTS') {
        console.log('⚠️ Usuário whatsapp_user já existe, atualizando senha...');
        await rootConnection.execute(`
          ALTER USER '${process.env.VPS_DB_USER}'@'%' 
          IDENTIFIED BY '${process.env.VPS_DB_PASS}'
        `);
        console.log('✅ Senha do usuário atualizada!');
      } else {
        throw error;
      }
    }
    
    // 3. Dar permissões ao usuário
    console.log('🔄 Configurando permissões...');
    await rootConnection.execute(`
      GRANT ALL PRIVILEGES ON ${process.env.VPS_DB_NAME}.* 
      TO '${process.env.VPS_DB_USER}'@'%'
    `);
    await rootConnection.execute('FLUSH PRIVILEGES');
    console.log('✅ Permissões configuradas!');
    
    // 4. Testar conexão com o novo usuário
    console.log('🔄 Testando conexão com o novo usuário...');
    const testConfig = {
      host: process.env.VPS_DB_HOST,
      port: process.env.VPS_DB_PORT,
      user: process.env.VPS_DB_USER,
      password: process.env.VPS_DB_PASS,
      database: process.env.VPS_DB_NAME,
      charset: 'utf8mb4',
      timezone: '+00:00'
    };
    
    const testConnection = await mysql.createConnection(testConfig);
    console.log('✅ Conexão com whatsapp_user funcionando!');
    
    // 5. Criar tabelas básicas
    console.log('🔄 Criando tabelas básicas...');
    
    // Tabela users
    await testConnection.execute(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'manager', 'agent', 'viewer') DEFAULT 'agent',
        is_active BOOLEAN DEFAULT TRUE,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Tabela users criada!');
    
    // Tabela whatsapp_sessions
    await testConnection.execute(`
      CREATE TABLE IF NOT EXISTS whatsapp_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_name VARCHAR(100) UNIQUE NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        status ENUM('connected', 'disconnected', 'connecting', 'error') DEFAULT 'disconnected',
        qr_code TEXT,
        session_data JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Tabela whatsapp_sessions criada!');
    
    // Fechar conexões
    await testConnection.end();
    await rootConnection.end();
    
    console.log('\n🎉 Configuração do banco concluída com sucesso!');
    console.log('✅ Banco de dados criado');
    console.log('✅ Usuário whatsapp_user criado');
    console.log('✅ Permissões configuradas');
    console.log('✅ Tabelas básicas criadas');
    console.log('\n🚀 Agora você pode executar o servidor!');
    
  } catch (error) {
    console.error('❌ Erro durante a configuração:', error.message);
    console.error('Código do erro:', error.code);
    console.error('Número do erro MySQL:', error.errno);
    
    if (error.code === 'ER_ACCESS_DENIED_ERROR') {
      console.log('\n💡 SOLUÇÃO:');
      console.log('1. Adicione a senha do root da VPS no arquivo .env:');
      console.log('   VPS_ROOT_PASS=sua_senha_root_aqui');
      console.log('2. Ou execute manualmente na VPS:');
      console.log('   mysql -u root -p');
      console.log('   CREATE USER "whatsapp_user"@"%" IDENTIFIED BY "sua_senha_123";');
      console.log('   GRANT ALL PRIVILEGES ON whatsapp_multichannel.* TO "whatsapp_user"@"%";');
      console.log('   FLUSH PRIVILEGES;');
    }
  }
}

setupDatabase();
