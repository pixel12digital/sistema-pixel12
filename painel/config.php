<?php
date_default_timezone_set('America/Sao_Paulo');

/**
 * Configurações globais do sistema
 * Detecta automaticamente ambiente (local/produção) e ajusta configurações
 */

// Detectar ambiente automaticamente
$is_local = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
    strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
    !empty($_SERVER['XAMPP_ROOT']) ||
    !empty($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false
);

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
define('WHATSAPP_ROBOT_URL', 'http://localhost:3000');
define('WHATSAPP_TIMEOUT', 10);

/* ===== Log para debug ===== */
if (DEBUG_MODE) {
    $env_info = $is_local ? 'DESENVOLVIMENTO' : 'PRODUÇÃO';
    error_log("[CONFIG] Ambiente detectado: {$env_info} | Host: " . $_SERVER['SERVER_NAME']);
}
?> 