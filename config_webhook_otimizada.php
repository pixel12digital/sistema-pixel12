<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA WEBHOOK
 * 
 * Configurações para melhorar performance e confiabilidade
 */

// Configurações de conexão otimizadas
define('DB_PERSISTENT', true);
define('DB_TIMEOUT', 10);
define('DB_MAX_RETRIES', 3);

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_TTL', 300); // 5 minutos

// Configurações de rate limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_REQUESTS', 100); // 100 requisições por hora
define('RATE_LIMIT_WINDOW', 3600); // 1 hora

// Configurações de log
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_MAX_SIZE', 10485760); // 10MB
define('LOG_ROTATION', true);

// Configurações de webhook
define('WEBHOOK_TIMEOUT', 30);
define('WEBHOOK_MAX_RETRIES', 3);
define('WEBHOOK_RETRY_DELAY', 5); // segundos

// Configurações de monitoramento
define('MONITOR_ENABLED', true);
define('MONITOR_INTERVAL', 5); // segundos
define('MONITOR_ALERT_THRESHOLD', 10); // mensagens perdidas

// Configurações de WhatsApp
define('WHATSAPP_TIMEOUT', 15);
define('WHATSAPP_MAX_RETRIES', 2);
define('WHATSAPP_RETRY_DELAY', 3); // segundos

echo "✅ Configuração otimizada carregada\n";
?>