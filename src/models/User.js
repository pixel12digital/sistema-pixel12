const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');

// Variável global para o pool de conexões
let dbPool = null;

class User {
  constructor() {
    this.tableName = 'users';
  }

  // Método para configurar o pool de conexões
  static setPool(pool) {
    dbPool = pool;
  }

  // Método para obter pool de conexões
  getPool() {
    if (!dbPool) {
      throw new Error('Pool de conexões não configurado. Use User.setPool() primeiro.');
    }
    return dbPool;
  }

  // Criar tabela de usuários se não existir
  async createTable() {
    const pool = this.getPool();
    try {
      const createTableSQL = `
        CREATE TABLE IF NOT EXISTS ${this.tableName} (
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
      `;
      
      await pool.execute(createTableSQL);
      
      // Criar usuário admin padrão se não existir
      await this.createDefaultAdmin();
      
      console.log('✅ Tabela users criada/verificada com sucesso');
    } catch (error) {
      console.error('❌ Erro ao criar tabela users:', error);
      throw error;
    }
  }

  // Criar usuário admin padrão
  async createDefaultAdmin() {
    const pool = this.getPool();
    try {
      const [rows] = await pool.execute(
        'SELECT COUNT(*) as count FROM users WHERE role = "admin"'
      );
      
      if (rows[0].count === 0) {
        const hashedPassword = await bcrypt.hash('admin123', 10);
        await pool.execute(
          'INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)',
          ['admin', 'admin@sistema-pixel12.com', hashedPassword, 'Administrador do Sistema', 'admin']
        );
        console.log('✅ Usuário admin padrão criado: admin / admin123');
      }
    } catch (error) {
      console.error('❌ Erro ao criar usuário admin:', error);
    }
  }

  // Buscar usuário por ID
  async findById(id) {
    const pool = this.getPool();
    try {
      const [rows] = await pool.execute(
        'SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users WHERE id = ?',
        [id]
      );
      return rows[0] || null;
    } catch (error) {
      console.error('❌ Erro ao buscar usuário por ID:', error);
      throw error;
    }
  }

  // Buscar usuário por username
  async findByUsername(username) {
    const pool = this.getPool();
    try {
      const [rows] = await pool.execute(
        'SELECT * FROM users WHERE username = ? AND is_active = TRUE',
        [username]
      );
      return rows[0] || null;
    } catch (error) {
      console.error('❌ Erro ao buscar usuário por username:', error);
      throw error;
    }
  }

  // Buscar usuário por email
  async findByEmail(email) {
    const pool = this.getPool();
    try {
      const [rows] = await pool.execute(
        'SELECT * FROM users WHERE email = ? AND is_active = TRUE',
        [email]
      );
      return rows[0] || null;
    } catch (error) {
      console.error('❌ Erro ao buscar usuário por email:', error);
      throw error;
    }
  }

  // Criar novo usuário
  async create(userData) {
    const pool = this.getPool();
    try {
      const { username, email, password, full_name, role = 'agent' } = userData;
      
      // Verificar se username ou email já existem
      const [existingUsers] = await pool.execute(
        'SELECT id FROM users WHERE username = ? OR email = ?',
        [username, email]
      );
      
      if (existingUsers.length > 0) {
        throw new Error('Username ou email já existem');
      }
      
      // Hash da senha
      const hashedPassword = await bcrypt.hash(password, 10);
      
      const [result] = await pool.execute(
        'INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)',
        [username, email, hashedPassword, full_name, role]
      );
      
      return { id: result.insertId, username, email, full_name, role };
    } catch (error) {
      console.error('❌ Erro ao criar usuário:', error);
      throw error;
    }
  }

  // Atualizar usuário
  async update(id, userData) {
    const pool = this.getPool();
    try {
      const { username, email, full_name, role, is_active } = userData;
      
      const updateFields = [];
      const updateValues = [];
      
      if (username !== undefined) {
        updateFields.push('username = ?');
        updateValues.push(username);
      }
      if (email !== undefined) {
        updateFields.push('email = ?');
        updateValues.push(email);
      }
      if (full_name !== undefined) {
        updateFields.push('full_name = ?');
        updateValues.push(full_name);
      }
      if (role !== undefined) {
        updateFields.push('role = ?');
        updateValues.push(role);
      }
      if (is_active !== undefined) {
        updateFields.push('is_active = ?');
        updateValues.push(is_active);
      }
      
      if (updateFields.length === 0) {
        throw new Error('Nenhum campo para atualizar');
      }
      
      updateValues.push(id);
      
      const [result] = await pool.execute(
        `UPDATE users SET ${updateFields.join(', ')} WHERE id = ?`,
        updateValues
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('❌ Erro ao atualizar usuário:', error);
      throw error;
    }
  }

  // Atualizar senha
  async updatePassword(id, newPassword) {
    const pool = this.getPool();
    try {
      const hashedPassword = await bcrypt.hash(newPassword, 10);
      
      const [result] = await pool.execute(
        'UPDATE users SET password = ? WHERE id = ?',
        [hashedPassword, id]
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('❌ Erro ao atualizar senha:', error);
      throw error;
    }
  }

  // Atualizar último login
  async updateLastLogin(id) {
    const pool = this.getPool();
    try {
      await pool.execute(
        'UPDATE users SET last_login = NOW() WHERE id = ?',
        [id]
      );
    } catch (error) {
      console.error('❌ Erro ao atualizar último login:', error);
    }
  }

  // Listar todos os usuários
  async findAll() {
    const pool = this.getPool();
    try {
      const [rows] = await pool.execute(
        'SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC'
      );
      return rows;
    } catch (error) {
      console.error('❌ Erro ao listar usuários:', error);
      throw error;
    }
  }

  // Deletar usuário (soft delete)
  async delete(id) {
    const pool = this.getPool();
    try {
      const [result] = await pool.execute(
        'UPDATE users SET is_active = FALSE WHERE id = ?',
        [id]
      );
      
      return result.affectedRows > 0;
    } catch (error) {
      console.error('❌ Erro ao deletar usuário:', error);
      throw error;
    }
  }

  // Verificar senha
  async verifyPassword(password, hashedPassword) {
    return await bcrypt.compare(password, hashedPassword);
  }

  // Contar usuários por role
  async countByRole() {
    const pool = this.getPool();
    try {
      const [rows] = await pool.execute(
        'SELECT role, COUNT(*) as count FROM users WHERE is_active = TRUE GROUP BY role'
      );
      return rows;
    } catch (error) {
      console.error('❌ Erro ao contar usuários por role:', error);
      throw error;
    }
  }
}

module.exports = User;
