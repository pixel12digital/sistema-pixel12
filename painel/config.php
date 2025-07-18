<?php
date_default_timezone_set('America/Sao_Paulo');

/**
 * Configurações globais do sistema
 * Detecta automaticamente ambiente (local/produção) e ajusta configurações
 */

// Detectar ambiente automaticamente
$is_local = false;

// Verificar se está rodando via CLI
if (php_sapi_name() === 'cli') {
    // Se estiver via CLI, verificar se está no XAMPP
    $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $is_local = (
        strpos($document_root, 'xampp') !== false ||
        strpos(getcwd(), 'xampp') !== false ||
        strpos(__DIR__, 'xampp') !== false
    );
} else {
    // Se estiver via web, usar detecção normal
    $is_local = (
        $_SERVER['SERVER_NAME'] === 'localhost' || 
        strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
        strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
        !empty($_SERVER['XAMPP_ROOT']) ||
        !empty($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false
    );
}

/* ===== Credenciais do administrador padrão ===== */
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

/* ===== Configuração do banco de dados ===== */
// CORREÇÃO: Banco remoto para ambos os ambientes
define('DB_HOST', 'srv1607.hstgr.io');
define('DB_NAME', 'u342734079_revendaweb');
define('DB_USER', 'u342734079_revendaweb');
define('DB_PASS', 'Los@ngo#081081');

/* ===== Configurações por ambiente ===== */
if ($is_local) {
    // Configurações para desenvolvimento local (XAMPP)
    // API de teste Asaas
    define('ASAAS_API_KEY', getenv('ASAAS_API_KEY') ?: '$aact_test_CHAVE_DE_TESTE_AQUI');
    define('DEBUG_MODE', true);
    define('ENABLE_CACHE', false); // Desabilitar cache em desenvolvimento
} else {
    // Configurações para produção (Hostinger)
    // API de produção Asaas
    define('ASAAS_API_KEY', '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6Ojc2MDY0NjY2LTA0YmYtNDdjYi04NTJiLThlYjk3ZGEwNTc3Yjo6JGFhY2hfMDU3ZGNiNGMtNjYzNC00ODQxLWE3ZmEtNTUxMGFiZmZkNzNh');
    define('DEBUG_MODE', false);
    define('ENABLE_CACHE', true);
}

/* ===== Configurações compartilhadas ===== */
define('ASAAS_API_URL', 'https://www.asaas.com/api/v3');

/* ===== Configurações do sistema ===== */
define('CACHE_TTL_DEFAULT', 300);
define('CACHE_MAX_SIZE', '100MB');

/* ===== Configurações do WhatsApp ===== */
// CORREÇÃO: Detectar porta local automaticamente para XAMPP
if ($is_local) {
    // Ambiente local (XAMPP) - usar porta 8080
    define('WHATSAPP_ROBOT_URL', 'http://212.85.11.238:3000'); // Mantém VPS para testes
    define('LOCAL_BASE_URL', 'http://localhost:8080'); // URL base para XAMPP
} else {
    // Ambiente de produção (Hostinger)
    define('WHATSAPP_ROBOT_URL', 'http://212.85.11.238:3000');
    define('LOCAL_BASE_URL', null); // Não usado em produção
}

define('WHATSAPP_TIMEOUT', 10);

/* ===== Log para debug ===== */
if (DEBUG_MODE) {
    $env_info = $is_local ? 'DESENVOLVIMENTO' : 'PRODUÇÃO';
    $port_info = $is_local ? ' (Porta 8080)' : '';
    $db_info = ' (Banco Remoto)';
    error_log("[CONFIG] Ambiente detectado: {$env_info}{$port_info}{$db_info} | Host: " . $_SERVER['SERVER_NAME']);
}
?> 