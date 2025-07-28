<?php
/**
 * Configurações globais do sistema
 * Detecta automaticamente ambiente (local/produção) e ajusta configurações
 */

date_default_timezone_set('America/Sao_Paulo');

// Detectar ambiente automaticamente com múltiplas verificações
$is_local = false;

// Verificação 1: Servidor localhost
if (isset($_SERVER['SERVER_NAME'])) {
    $server_name = $_SERVER['SERVER_NAME'];
    $is_local = (
        $server_name === 'localhost' || 
        strpos($server_name, '127.0.0.1') !== false ||
        strpos($server_name, '.local') !== false
    );
}

// Verificação 2: XAMPP detectado
if (!$is_local && isset($_SERVER['DOCUMENT_ROOT'])) {
    $is_local = (strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false ||
                 strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') !== false ||
                 strpos($_SERVER['DOCUMENT_ROOT'], 'mamp') !== false);
}

// Verificação 3: Variável XAMPP_ROOT
if (!$is_local && !empty($_SERVER['XAMPP_ROOT'])) {
    $is_local = true;
}

// Verificação 4: Via CLI - verificar diretório atual
if (!$is_local && php_sapi_name() === 'cli') {
    $current_dir = getcwd();
    $is_local = (strpos($current_dir, 'xampp') !== false ||
                 strpos($current_dir, 'wamp') !== false ||
                 strpos($current_dir, 'mamp') !== false ||
                 strpos($current_dir, 'localhost') !== false);
}

// Verificação 5: Arquivo marcador (criar .local_env se quiser forçar PRODUÇÃO)
if ($is_local && file_exists('.local_env')) {
    $is_local = false; // Forçar produção
}

// Verificação 6: Forçar banco remoto para este projeto específico
// Como estamos desenvolvendo mas queremos usar o banco remoto
if (strpos(__DIR__, 'loja-virtual-revenda') !== false) {
    $is_local = false; // Forçar produção para usar banco remoto
}

/* ===== Credenciais do administrador padrão ===== */
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

/* ===== Configuração do banco de dados ===== */
if ($is_local) {
    // Configurações para desenvolvimento local (XAMPP)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'loja_virtual');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    
    // API de teste Asaas
    define('ASAAS_API_KEY', getenv('ASAAS_API_KEY') ?: '$aact_test_CHAVE_DE_TESTE_AQUI');
    define('DEBUG_MODE', true);
    define('ENABLE_CACHE', false); // Desabilitar cache em desenvolvimento
} else {
    // Configurações para produção (Hostinger)
    define('DB_HOST', 'srv1607.hstgr.io');
    define('DB_NAME', 'u342734079_revendaweb');
    define('DB_USER', 'u342734079_revendaweb');
    define('DB_PASS', 'Los@ngo#081081');
    
    // API de produção Asaas - CHAVE CORRETA
    define('ASAAS_API_KEY', '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjFkZGExMjcyLWMzN2MtNGM3MS1iMTBmLTY4YWU4MjM4ZmE1Nzo6JGFhY2hfM2EzNTI4OTUtOGFjNC00MmFlLTliZTItNjRkZDg2YTAzOWRj');
    define('DEBUG_MODE', false);
    define('ENABLE_CACHE', true);
}

/* ===== Configurações compartilhadas ===== */
define('ASAAS_API_URL', 'https://www.asaas.com/api/v3');

/* ===== Configurações do sistema ===== */
define('CACHE_TTL_DEFAULT', 300);
define('CACHE_MAX_SIZE', '100MB');

/* ===== Configurações do WhatsApp ===== */
define('WHATSAPP_ROBOT_URL', 'http://212.85.11.238:3000');
define('WHATSAPP_TIMEOUT', 10);

/* ===== CONFIGURAÇÕES OTIMIZADAS PARA REDUZIR CONEXÕES ===== */
// Configurações de polling otimizadas
define("POLLING_CONFIGURACOES", 60000);    // 60 segundos
define("POLLING_WHATSAPP", 30000);         // 30 segundos
define("POLLING_MONITORAMENTO", 60000);    // 60 segundos
define("POLLING_CHAT", 60000);             // 60 segundos
define("POLLING_COMUNICACAO", 120000);     // 2 minutos

// Configurações de cache
define("CACHE_ENABLED", true);
define("CACHE_TTL", 300);                  // 5 minutos

// Configurações de conexão otimizadas
define("DB_PERSISTENT", true);
define("DB_TIMEOUT", 10);
define("DB_MAX_RETRIES", 3);

// Configurações de rate limiting
define("RATE_LIMIT_ENABLED", true);
define("RATE_LIMIT_MAX_REQUESTS", 100);    // 100 requisições por hora
define("RATE_LIMIT_WINDOW", 3600);         // 1 hora

/* ===== Log para debug ===== */
if (DEBUG_MODE) {
    $env_info = $is_local ? 'DESENVOLVIMENTO' : 'PRODUÇÃO';
    $server_name = $_SERVER['SERVER_NAME'] ?? 'CLI';
    $current_dir = getcwd();
    error_log("[CONFIG] Ambiente detectado: {$env_info} | Host: {$server_name} | Dir: {$current_dir}");
}
?>
