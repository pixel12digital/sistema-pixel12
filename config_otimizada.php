<?php
/**
 * Configurações Otimizadas para Redução de Conexões e Performance
 * Este arquivo contém configurações específicas para otimização
 */

// Verificar se já foi incluído
if (defined('CONFIG_OTIMIZADA_LOADED')) {
    return;
}

define('CONFIG_OTIMIZADA_LOADED', true);

/* ===== CONFIGURAÇÕES DE POLLING OTIMIZADAS ===== */
// Reduzidas drasticamente para economizar conexões
if (!defined('POLLING_INTERVAL')) define('POLLING_INTERVAL', 30);           // 30 segundos (era 5)
if (!defined('WHATSAPP_POLLING_INTERVAL')) define('WHATSAPP_POLLING_INTERVAL', 60); // 60 segundos (era 10)
if (!defined('COBRANCA_POLLING_INTERVAL')) define('COBRANCA_POLLING_INTERVAL', 300); // 5 minutos (era 60)

/* ===== CONFIGURAÇÕES DE CACHE OTIMIZADAS ===== */
if (!defined('CACHE_TTL_WHATSAPP')) define('CACHE_TTL_WHATSAPP', 300);     // 5 minutos
if (!defined('CACHE_TTL_CLIENTE')) define('CACHE_TTL_CLIENTE', 1800);      // 30 minutos
if (!defined('CACHE_TTL_COBRANCA')) define('CACHE_TTL_COBRANCA', 600);     // 10 minutos
if (!defined('CACHE_TTL_ASAAS')) define('CACHE_TTL_ASAAS', 900);          // 15 minutos

/* ===== CONFIGURAÇÕES DE CONEXÃO OTIMIZADAS ===== */
if (!defined('DB_PERSISTENT')) define('DB_PERSISTENT', true);
if (!defined('DB_TIMEOUT')) define('DB_TIMEOUT', 5);
if (!defined('DB_MAX_RETRIES')) define('DB_MAX_RETRIES', 1);

/* ===== CONFIGURAÇÕES DE POOL DE CONEXÕES ===== */
if (!defined('DB_MAX_CONNECTIONS')) define('DB_MAX_CONNECTIONS', 8);             // Máximo 8 conexões simultâneas
if (!defined('DB_CONNECTION_INTERVAL')) define('DB_CONNECTION_INTERVAL', 2);     // 2 segundos entre conexões

/* ===== CONFIGURAÇÕES DE RATE LIMITING ===== */
if (!defined('RATE_LIMIT_ENABLED')) define('RATE_LIMIT_ENABLED', true);
if (!defined('RATE_LIMIT_MAX_REQUESTS')) define('RATE_LIMIT_MAX_REQUESTS', 100); // 100 requisições por hora
if (!defined('RATE_LIMIT_WINDOW')) define('RATE_LIMIT_WINDOW', 3600);            // 1 hora

/* ===== CONFIGURAÇÕES DE MONITORAMENTO OTIMIZADAS ===== */
if (!defined('MONITORING_INTERVAL')) define('MONITORING_INTERVAL', 300);        // 5 minutos
if (!defined('MONITORING_TIMEOUT')) define('MONITORING_TIMEOUT', 10);           // 10 segundos
if (!defined('MONITORING_MAX_RETRIES')) define('MONITORING_MAX_RETRIES', 2);    // Máximo 2 tentativas

/* ===== CONFIGURAÇÕES DE LOG OTIMIZADAS ===== */
if (!defined('LOG_LEVEL')) define('LOG_LEVEL', 'ERROR');                       // Apenas erros em produção
if (!defined('LOG_MAX_SIZE')) define('LOG_MAX_SIZE', '10MB');                  // Máximo 10MB por arquivo
if (!defined('LOG_RETENTION_DAYS')) define('LOG_RETENTION_DAYS', 7);           // Manter logs por 7 dias

/* ===== CONFIGURAÇÕES DE SESSÃO OTIMIZADAS ===== */
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600);              // 1 hora
if (!defined('SESSION_GC_PROBABILITY')) define('SESSION_GC_PROBABILITY', 1);   // 1% de chance de limpeza
if (!defined('SESSION_GC_MAXLIFETIME')) define('SESSION_GC_MAXLIFETIME', 7200); // 2 horas

/* ===== CONFIGURAÇÕES DE API OTIMIZADAS ===== */
if (!defined('API_TIMEOUT')) define('API_TIMEOUT', 10);                        // 10 segundos
if (!defined('API_MAX_RETRIES')) define('API_MAX_RETRIES', 2);                 // Máximo 2 tentativas
if (!defined('API_RETRY_DELAY')) define('API_RETRY_DELAY', 1);                 // 1 segundo entre tentativas

/* ===== CONFIGURAÇÕES DE WEBSOCKET OTIMIZADAS ===== */
if (!defined('WEBSOCKET_PING_INTERVAL')) define('WEBSOCKET_PING_INTERVAL', 30); // 30 segundos
if (!defined('WEBSOCKET_PING_TIMEOUT')) define('WEBSOCKET_PING_TIMEOUT', 10);   // 10 segundos

/* ===== CONFIGURAÇÕES DE CRON OTIMIZADAS ===== */
if (!defined('CRON_LOCK_TIMEOUT')) define('CRON_LOCK_TIMEOUT', 300);           // 5 minutos
if (!defined('CRON_MAX_EXECUTION_TIME')) define('CRON_MAX_EXECUTION_TIME', 60); // 60 segundos

/* ===== CONFIGURAÇÕES DE MEMORY OTIMIZADAS ===== */
if (!defined('MEMORY_LIMIT')) define('MEMORY_LIMIT', '256M');                  // Limite de memória
if (!defined('MAX_EXECUTION_TIME')) define('MAX_EXECUTION_TIME', 30);          // 30 segundos

/* ===== CONFIGURAÇÕES DE DEBUG OTIMIZADAS ===== */
if (!defined('DEBUG_DB_QUERIES')) define('DEBUG_DB_QUERIES', false);           // Desabilitar debug de queries
if (!defined('DEBUG_API_CALLS')) define('DEBUG_API_CALLS', false);             // Desabilitar debug de API
if (!defined('DEBUG_WHATSAPP')) define('DEBUG_WHATSAPP', false);               // Desabilitar debug do WhatsApp

?> 