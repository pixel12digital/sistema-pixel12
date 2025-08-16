module.exports = {
  // Chave secreta para assinar tokens JWT
  JWT_SECRET: process.env.JWT_SECRET || 'sua-chave-secreta-padrao-mude-em-producao',
  
  // Tempo de expiração do token (24 horas)
  JWT_EXPIRES_IN: process.env.JWT_EXPIRES_IN || '24h',
  
  // Tempo de expiração do refresh token (7 dias)
  JWT_REFRESH_EXPIRES_IN: process.env.JWT_REFRESH_EXPIRES_IN || '7d',
  
  // Configurações de senha
  PASSWORD_MIN_LENGTH: 6,
  PASSWORD_SALT_ROUNDS: 10,
  
  // Configurações de rate limiting
  LOGIN_MAX_ATTEMPTS: 5,
  LOGIN_LOCKOUT_TIME: 15 * 60 * 1000, // 15 minutos
  
  // Configurações de sessão
  SESSION_TIMEOUT: 24 * 60 * 60 * 1000, // 24 horas
  
  // Roles disponíveis no sistema
  ROLES: {
    ADMIN: 'admin',
    MANAGER: 'manager',
    AGENT: 'agent',
    VIEWER: 'viewer'
  },
  
  // Permissões por role
  PERMISSIONS: {
    admin: ['*'], // Admin tem acesso total
    manager: ['read', 'write', 'delete'],
    agent: ['read', 'write'],
    viewer: ['read']
  }
};
