<?php
/**
 * CONFIGURAÇÃO TEMPORÁRIA PARA REDUZIR CONEXÕES
 * 
 * Este arquivo deve ser incluído ANTES de qualquer conexão com banco
 */

// Configurações de emergência para reduzir conexões
define('EMERGENCY_MODE', true);
define('DB_PERSISTENT', true);
define('DB_TIMEOUT', 3);
define('DB_MAX_RETRIES', 1);
define('CACHE_ENABLED', true);
define('CACHE_TTL', 600);
define('LOG_LEVEL', 'ERROR');
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_REQUESTS', 50);
define('RATE_LIMIT_WINDOW', 3600);

// Função para verificar rate limit
function checkRateLimit() {
    if (!RATE_LIMIT_ENABLED) return true;
    
    $cache_file = 'cache/rate_limit_' . date('Y-m-d-H') . '.txt';
    $current_requests = 0;
    
    if (file_exists($cache_file)) {
        $current_requests = (int)file_get_contents($cache_file);
    }
    
    if ($current_requests >= RATE_LIMIT_MAX_REQUESTS) {
        return false;
    }
    
    file_put_contents($cache_file, $current_requests + 1);
    return true;
}

// Função para log otimizado
function emergencyLog($message) {
    if (LOG_LEVEL === 'ERROR') {
        error_log('[EMERGENCY] ' . $message);
    }
}

echo "✅ Configuração de emergência carregada!
";
?>