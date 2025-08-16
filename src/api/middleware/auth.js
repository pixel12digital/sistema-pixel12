const jwt = require('jsonwebtoken');
const User = require('../../models/User');

// Middleware para verificar token JWT
const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      return res.status(401).json({
        success: false,
        message: 'Token de acesso não fornecido',
        error: 'MISSING_TOKEN'
      });
    }

    // Verificar e decodificar o token
    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'sua-chave-secreta-padrao');
    
    // Buscar usuário no banco
    const user = await User.findById(decoded.userId);
    
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Usuário não encontrado',
        error: 'USER_NOT_FOUND'
      });
    }

    if (!user.is_active) {
      return res.status(401).json({
        success: false,
        message: 'Usuário inativo',
        error: 'USER_INACTIVE'
      });
    }

    // Adicionar informações do usuário ao request
    req.user = {
      id: user.id,
      username: user.username,
      email: user.email,
      full_name: user.full_name,
      role: user.role
    };

    next();
  } catch (error) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        success: false,
        message: 'Token inválido',
        error: 'INVALID_TOKEN'
      });
    }
    
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Token expirado',
        error: 'TOKEN_EXPIRED'
      });
    }

    console.error('❌ Erro na autenticação:', error);
    return res.status(500).json({
      success: false,
      message: 'Erro interno na autenticação',
      error: 'AUTH_ERROR'
    });
  }
};

// Middleware para verificar permissões de role
const requireRole = (allowedRoles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        success: false,
        message: 'Usuário não autenticado',
        error: 'NOT_AUTHENTICATED'
      });
    }

    // Se allowedRoles for um array, verificar se o usuário tem uma das roles permitidas
    if (Array.isArray(allowedRoles)) {
      if (!allowedRoles.includes(req.user.role)) {
        return res.status(403).json({
          success: false,
          message: 'Acesso negado: permissão insuficiente',
          error: 'INSUFFICIENT_PERMISSIONS',
          required: allowedRoles,
          current: req.user.role
        });
      }
    } else {
      // Se for uma string única, verificar se o usuário tem essa role
      if (req.user.role !== allowedRoles) {
        return res.status(403).json({
          success: false,
          message: 'Acesso negado: permissão insuficiente',
          error: 'INSUFFICIENT_PERMISSIONS',
          required: allowedRoles,
          current: req.user.role
        });
      }
    }

    next();
  };
};

// Middleware para verificar se é admin
const requireAdmin = requireRole('admin');

// Middleware para verificar se é admin ou manager
const requireAdminOrManager = requireRole(['admin', 'manager']);

// Middleware para verificar se é admin, manager ou agent
const requireAdminManagerOrAgent = requireRole(['admin', 'manager', 'agent']);

// Middleware para verificar se o usuário está acessando seus próprios dados
const requireOwnership = (paramName = 'id') => {
  return (req, res, next) => {
    const resourceId = parseInt(req.params[paramName]);
    const userId = req.user.id;

    // Admin pode acessar qualquer recurso
    if (req.user.role === 'admin') {
      return next();
    }

    // Usuário só pode acessar seus próprios dados
    if (resourceId !== userId) {
      return res.status(403).json({
        success: false,
        message: 'Acesso negado: você só pode acessar seus próprios dados',
        error: 'OWNERSHIP_REQUIRED'
      });
    }

    next();
  };
};

// Middleware para logging de acesso
const logAccess = (req, res, next) => {
  const timestamp = new Date().toISOString();
  const method = req.method;
  const url = req.originalUrl;
  const userAgent = req.get('User-Agent');
  const ip = req.ip || req.connection.remoteAddress;
  
  if (req.user) {
    console.log(`🔐 [${timestamp}] ${method} ${url} - Usuário: ${req.user.username} (${req.user.role}) - IP: ${ip}`);
  } else {
    console.log(`🌐 [${timestamp}] ${method} ${url} - Não autenticado - IP: ${ip}`);
  }
  
  next();
};

module.exports = {
  authenticateToken,
  requireRole,
  requireAdmin,
  requireAdminOrManager,
  requireAdminManagerOrAgent,
  requireOwnership,
  logAccess
};
