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
if (!defined('ADMIN_USER')) define('ADMIN_USER', 'admin');
if (!defined('ADMIN_PASS')) define('ADMIN_PASS', 'admin123');

/* ===== Configuração do banco de dados ===== */
if ($is_local) {
    // Configurações para desenvolvimento local (XAMPP)
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'loja_virtual');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
    
    // API de teste Asaas
    if (!defined('ASAAS_API_KEY')) define('ASAAS_API_KEY', getenv('ASAAS_API_KEY') ?: '$aact_test_CHAVE_DE_TESTE_AQUI');
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
    if (!defined('ENABLE_CACHE')) define('ENABLE_CACHE', false); // Desabilitar cache em desenvolvimento
} else {
    // Configurações para produção (Hostinger)
    if (!defined('DB_HOST')) define('DB_HOST', 'srv1607.hstgr.io');
    if (!defined('DB_NAME')) define('DB_NAME', 'u342734079_revendaweb');
    if (!defined('DB_USER')) define('DB_USER', 'u342734079_revendaweb');
    if (!defined('DB_PASS')) define('DB_PASS', 'Los@ngo#081081');
    
    // API de produção Asaas - CHAVE CORRETA
    if (!defined('ASAAS_API_KEY')) define('ASAAS_API_KEY', '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjFkZGExMjcyLWMzN2MtNGM3MS1iMTBmLTY4YWU4MjM4ZmE1Nzo6JGFhY2hfM2EzNTI4OTUtOGFjNC00MmFlLTliZTItNjRkZDg2YTAzOWRj');
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
    if (!defined('ENABLE_CACHE')) define('ENABLE_CACHE', true);
}

/* ===== Configurações compartilhadas ===== */
if (!defined('ASAAS_API_URL')) define('ASAAS_API_URL', 'https://www.asaas.com/api/v3');

/* ===== Configurações do sistema ===== */
if (!defined('CACHE_TTL_DEFAULT')) define('CACHE_TTL_DEFAULT', 600); // 10 minutos
if (!defined('CACHE_MAX_SIZE')) define('CACHE_MAX_SIZE', '200MB');

/* ===== Configurações do WhatsApp ===== */
if (!defined('WHATSAPP_ROBOT_URL')) define('WHATSAPP_ROBOT_URL', 'http://212.85.11.238:3000');
if (!defined('WHATSAPP_TIMEOUT')) define('WHATSAPP_TIMEOUT', 10);

/* ===== CONFIGURAÇÕES OTIMIZADAS PARA REDUZIR CONEXÕES ===== */
// Configurações de polling otimizadas - REDUZIDAS para evitar muitas conexões
if (!defined('POLLING_CONFIGURACOES')) define("POLLING_CONFIGURACOES", 120000);   // 2 minutos
if (!defined('POLLING_WHATSAPP')) define("POLLING_WHATSAPP", 60000);             // 1 minuto
if (!defined('POLLING_MONITORAMENTO')) define("POLLING_MONITORAMENTO", 120000);   // 2 minutos
if (!defined('POLLING_CHAT')) define("POLLING_CHAT", 120000);                    // 2 minutos
if (!defined('POLLING_COMUNICACAO')) define("POLLING_COMUNICACAO", 300000);       // 5 minutos

// Configurações de conexão otimizadas
if (!defined('DB_PERSISTENT')) define("DB_PERSISTENT", true);                    // Usar conexões persistentes
if (!defined('DB_TIMEOUT')) define("DB_TIMEOUT", 30);                            // Timeout de 30 segundos
if (!defined('DB_MAX_RETRIES')) define("DB_MAX_RETRIES", 2);                     // Máximo 2 tentativas de reconexão

// Configurações de pool de conexões
if (!defined('DB_MAX_CONNECTIONS')) define("DB_MAX_CONNECTIONS", 8);             // Máximo 8 conexões simultâneas
if (!defined('DB_CONNECTION_INTERVAL')) define("DB_CONNECTION_INTERVAL", 2);     // 2 segundos entre conexões

// Configurações de rate limiting
if (!defined('RATE_LIMIT_ENABLED')) define("RATE_LIMIT_ENABLED", true);
if (!defined('RATE_LIMIT_MAX_REQUESTS')) define("RATE_LIMIT_MAX_REQUESTS", 100); // 100 requisições por hora
if (!defined('RATE_LIMIT_WINDOW')) define("RATE_LIMIT_WINDOW", 3600);            // 1 hora

/* ===== Log para debug ===== */
if (DEBUG_MODE) {
    $env_info = $is_local ? 'DESENVOLVIMENTO' : 'PRODUÇÃO';
    $server_name = $_SERVER['SERVER_NAME'] ?? 'CLI';
    $current_dir = getcwd();
    error_log("[CONFIG] Ambiente detectado: {$env_info} | Host: {$server_name} | Dir: {$current_dir}");
}
?>
