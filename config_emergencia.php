<?php
/**
 * CONFIGURAÇÃO DE EMERGÊNCIA
 * 
 * Configurações temporárias para resolver problema de conexões excessivas
 * Este arquivo deve ser incluído antes de qualquer conexão com o banco
 */

// Forçar cache em todos os casos
if (!defined('ENABLE_CACHE')) define('ENABLE_CACHE', true);
if (!defined('CACHE_TTL_DEFAULT')) define('CACHE_TTL_DEFAULT', 3600); // 1 hora

// Reduzir drasticamente o polling
if (!defined('POLLING_CONFIGURACOES')) define('POLLING_CONFIGURACOES', 600000);   // 10 minutos
if (!defined('POLLING_WHATSAPP')) define('POLLING_WHATSAPP', 600000);             // 10 minutos
if (!defined('POLLING_MONITORAMENTO')) define('POLLING_MONITORAMENTO', 900000);   // 15 minutos
if (!defined('POLLING_CHAT')) define('POLLING_CHAT', 600000);                     // 10 minutos
if (!defined('POLLING_COMUNICACAO')) define('POLLING_COMUNICACAO', 900000);       // 15 minutos

// Timeout de conexão reduzido
if (!defined('DB_CONNECT_TIMEOUT')) define('DB_CONNECT_TIMEOUT', 3);
if (!defined('DB_READ_TIMEOUT')) define('DB_READ_TIMEOUT', 5);

// Sistema de cache em arquivo
function cache_get($key) {
    $file = 'cache/' . md5($key) . '.cache';
    if (file_exists($file) && (time() - filemtime($file)) < 3600) {
        return unserialize(file_get_contents($file));
    }
    return null;
}

function cache_set($key, $value, $ttl = 3600) {
    $cache_dir = 'cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    $file = $cache_dir . md5($key) . '.cache';
    file_put_contents($file, serialize($value));
    return true;
}

// Contador de conexões
function check_connection_limit() {
    $contador_file = 'cache/conexoes_contador.txt';
    $limite_conexoes = 300; // Limite mais baixo
    
    $contador = 0;
    if (file_exists($contador_file)) {
        $contador = (int)file_get_contents($contador_file);
    }
    
    // Reset diário
    $hoje = date('Y-m-d');
    $ultimo_reset = file_exists('cache/ultimo_reset.txt') ? file_get_contents('cache/ultimo_reset.txt') : '';
    
    if ($ultimo_reset !== $hoje) {
        $contador = 0;
        file_put_contents('cache/ultimo_reset.txt', $hoje);
    }
    
    if ($contador >= $limite_conexoes) {
        return false; // Limite excedido
    }
    
    $contador++;
    file_put_contents($contador_file, $contador);
    return true;
}

// Verificar limite antes de conectar
if (!check_connection_limit()) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Limite de conexões excedido',
        'message' => 'Sistema temporariamente indisponível devido ao alto volume de acessos',
        'retry_after' => 300 // 5 minutos
    ]);
    exit;
}
?> 