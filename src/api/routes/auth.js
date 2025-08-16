const express = require('express');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const User = require('../../models/User');
const { authenticateToken, requireAdmin, requireAdminOrManager, logAccess } = require('../middleware/auth');

const router = express.Router();

// Middleware de logging para todas as rotas de auth
router.use(logAccess);

// ========================================
// ROTAS PÚBLICAS (não precisam de autenticação)
// ========================================

// POST /api/auth/login - Login do usuário
router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;

    // Validação dos campos
    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: 'Username e senha são obrigatórios',
        error: 'MISSING_CREDENTIALS'
      });
    }

    // Buscar usuário por username
    const user = await User.findByUsername(username);
    
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Credenciais inválidas',
        error: 'INVALID_CREDENTIALS'
      });
    }

    // Verificar se o usuário está ativo
    if (!user.is_active) {
      return res.status(401).json({
        success: false,
        message: 'Usuário inativo',
        error: 'USER_INACTIVE'
      });
    }

    // Verificar senha
    const isValidPassword = await User.verifyPassword(password, user.password);
    
    if (!isValidPassword) {
      return res.status(401).json({
        success: false,
        message: 'Credenciais inválidas',
        error: 'INVALID_CREDENTIALS'
      });
    }

    // Gerar token JWT
    const token = jwt.sign(
      { 
        userId: user.id, 
        username: user.username,
        role: user.role 
      },
      process.env.JWT_SECRET || 'sua-chave-secreta-padrao',
      { expiresIn: '24h' }
    );

    // Atualizar último login
    await User.updateLastLogin(user.id);

    // Retornar resposta de sucesso
    res.json({
      success: true,
      message: 'Login realizado com sucesso',
      data: {
        token,
        user: {
          id: user.id,
          username: user.username,
          email: user.email,
          full_name: user.full_name,
          role: user.role
        }
      }
    });

    console.log(`✅ Login realizado: ${user.username} (${user.role})`);

  } catch (error) {
    console.error('❌ Erro no login:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// POST /api/auth/register - Registro de novo usuário (apenas admin)
router.post('/register', authenticateToken, requireAdmin, async (req, res) => {
  try {
    const { username, email, password, full_name, role = 'agent' } = req.body;

    // Validação dos campos
    if (!username || !email || !password || !full_name) {
      return res.status(400).json({
        success: false,
        message: 'Todos os campos são obrigatórios',
        error: 'MISSING_FIELDS'
      });
    }

    // Validação da senha
    if (password.length < 6) {
      return res.status(400).json({
        success: false,
        message: 'A senha deve ter pelo menos 6 caracteres',
        error: 'WEAK_PASSWORD'
      });
    }

    // Validação do email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.status(400).json({
        success: false,
        message: 'Email inválido',
        error: 'INVALID_EMAIL'
      });
    }

    // Validação da role
    const validRoles = ['admin', 'manager', 'agent', 'viewer'];
    if (!validRoles.includes(role)) {
      return res.status(400).json({
        success: false,
        message: 'Role inválida',
        error: 'INVALID_ROLE'
      });
    }

    // Criar usuário
    const newUser = await User.create({
      username,
      email,
      password,
      full_name,
      role
    });

    res.status(201).json({
      success: true,
      message: 'Usuário criado com sucesso',
      data: {
        id: newUser.id,
        username: newUser.username,
        email: newUser.email,
        full_name: newUser.full_name,
        role: newUser.role
      }
    });

    console.log(`✅ Usuário criado: ${newUser.username} (${newUser.role}) por ${req.user.username}`);

  } catch (error) {
    if (error.message === 'Username ou email já existem') {
      return res.status(409).json({
        success: false,
        message: error.message,
        error: 'USER_ALREADY_EXISTS'
      });
    }

    console.error('❌ Erro ao criar usuário:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// ========================================
// ROTAS PROTEGIDAS (precisam de autenticação)
// ========================================

// GET /api/auth/me - Obter dados do usuário logado
router.get('/me', authenticateToken, async (req, res) => {
  try {
    const user = await User.findById(req.user.id);
    
    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'Usuário não encontrado',
        error: 'USER_NOT_FOUND'
      });
    }

    res.json({
      success: true,
      data: {
        id: user.id,
        username: user.username,
        email: user.email,
        full_name: user.full_name,
        role: user.role,
        is_active: user.is_active,
        last_login: user.last_login,
        created_at: user.created_at
      }
    });

  } catch (error) {
    console.error('❌ Erro ao buscar usuário:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// POST /api/auth/logout - Logout (opcional, para invalidar token no frontend)
router.post('/logout', authenticateToken, async (req, res) => {
  try {
    // Em um sistema real, você poderia adicionar o token a uma blacklist
    // Por enquanto, apenas retornamos sucesso (o frontend deve remover o token)
    
    res.json({
      success: true,
      message: 'Logout realizado com sucesso'
    });

    console.log(`✅ Logout realizado: ${req.user.username}`);

  } catch (error) {
    console.error('❌ Erro no logout:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// PUT /api/auth/change-password - Alterar senha do usuário logado
router.put('/change-password', authenticateToken, async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;

    if (!currentPassword || !newPassword) {
      return res.status(400).json({
        success: false,
        message: 'Senha atual e nova senha são obrigatórias',
        error: 'MISSING_PASSWORDS'
      });
    }

    if (newPassword.length < 6) {
      return res.status(400).json({
        success: false,
        message: 'A nova senha deve ter pelo menos 6 caracteres',
        error: 'WEAK_PASSWORD'
      });
    }

    // Buscar usuário com senha
    const user = await User.findByUsername(req.user.username);
    
    // Verificar senha atual
    const isValidPassword = await User.verifyPassword(currentPassword, user.password);
    
    if (!isValidPassword) {
      return res.status(401).json({
        success: false,
        message: 'Senha atual incorreta',
        error: 'INVALID_CURRENT_PASSWORD'
      });
    }

    // Atualizar senha
    await User.updatePassword(user.id, newPassword);

    res.json({
      success: true,
      message: 'Senha alterada com sucesso'
    });

    console.log(`✅ Senha alterada para: ${req.user.username}`);

  } catch (error) {
    console.error('❌ Erro ao alterar senha:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// ========================================
// ROTAS DE ADMINISTRAÇÃO (apenas admin)
// ========================================

// GET /api/auth/users - Listar todos os usuários (apenas admin)
router.get('/users', authenticateToken, requireAdmin, async (req, res) => {
  try {
    const users = await User.findAll();
    
    res.json({
      success: true,
      data: users
    });

  } catch (error) {
    console.error('❌ Erro ao listar usuários:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// GET /api/auth/users/:id - Obter usuário específico (apenas admin)
router.get('/users/:id', authenticateToken, requireAdmin, async (req, res) => {
  try {
    const userId = parseInt(req.params.id);
    const user = await User.findById(userId);
    
    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'Usuário não encontrado',
        error: 'USER_NOT_FOUND'
      });
    }

    res.json({
      success: true,
      data: user
    });

  } catch (error) {
    console.error('❌ Erro ao buscar usuário:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// PUT /api/auth/users/:id - Atualizar usuário (apenas admin)
router.put('/users/:id', authenticateToken, requireAdmin, async (req, res) => {
  try {
    const userId = parseInt(req.params.id);
    const { username, email, full_name, role, is_active } = req.body;

    // Verificar se o usuário existe
    const existingUser = await User.findById(userId);
    if (!existingUser) {
      return res.status(404).json({
        success: false,
        message: 'Usuário não encontrado',
        error: 'USER_NOT_FOUND'
      });
    }

    // Atualizar usuário
    const success = await User.update(userId, {
      username,
      email,
      full_name,
      role,
      is_active
    });

    if (success) {
      res.json({
        success: true,
        message: 'Usuário atualizado com sucesso'
      });

      console.log(`✅ Usuário atualizado: ID ${userId} por ${req.user.username}`);
    } else {
      res.status(400).json({
        success: false,
        message: 'Erro ao atualizar usuário',
        error: 'UPDATE_FAILED'
      });
    }

  } catch (error) {
    console.error('❌ Erro ao atualizar usuário:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// DELETE /api/auth/users/:id - Deletar usuário (apenas admin)
router.delete('/users/:id', authenticateToken, requireAdmin, async (req, res) => {
  try {
    const userId = parseInt(req.params.id);

    // Não permitir deletar o próprio usuário
    if (userId === req.user.id) {
      return res.status(400).json({
        success: false,
        message: 'Não é possível deletar seu próprio usuário',
        error: 'SELF_DELETE_NOT_ALLOWED'
      });
    }

    // Verificar se o usuário existe
    const existingUser = await User.findById(userId);
    if (!existingUser) {
      return res.status(404).json({
        success: false,
        message: 'Usuário não encontrado',
        error: 'USER_NOT_FOUND'
      });
    }

    // Deletar usuário (soft delete)
    const success = await User.delete(userId);

    if (success) {
      res.json({
        success: true,
        message: 'Usuário deletado com sucesso'
      });

      console.log(`✅ Usuário deletado: ID ${userId} por ${req.user.username}`);
    } else {
      res.status(400).json({
        success: false,
        message: 'Erro ao deletar usuário',
        error: 'DELETE_FAILED'
      });
    }

  } catch (error) {
    console.error('❌ Erro ao deletar usuário:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

// GET /api/auth/stats - Estatísticas dos usuários (apenas admin)
router.get('/stats', authenticateToken, requireAdmin, async (req, res) => {
  try {
    const users = await User.findAll();
    const roleCounts = await User.countByRole();
    
    const stats = {
      total: users.length,
      active: users.filter(u => u.is_active).length,
      inactive: users.filter(u => !u.is_active).length,
      byRole: roleCounts,
      recentLogins: users
        .filter(u => u.last_login)
        .sort((a, b) => new Date(b.last_login) - new Date(a.last_login))
        .slice(0, 5)
        .map(u => ({
          username: u.username,
          last_login: u.last_login
        }))
    };

    res.json({
      success: true,
      data: stats
    });

  } catch (error) {
    console.error('❌ Erro ao buscar estatísticas:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno no servidor',
      error: 'INTERNAL_ERROR'
    });
  }
});

module.exports = router;
