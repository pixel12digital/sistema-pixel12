<?php
/**
 * CONFIGURAÇÃO DE EMERGÊNCIA SUAVE
 * 
 * Versão mais permissiva para permitir o chat funcionar
 * enquanto ainda controla conexões excessivas
 */

// Forçar cache em todos os casos
if (!defined('ENABLE_CACHE')) define('ENABLE_CACHE', true);
if (!defined('CACHE_TTL_DEFAULT')) define('CACHE_TTL_DEFAULT', 1800); // 30 minutos

// Polling moderado (não tão restritivo)
if (!defined('POLLING_CONFIGURACOES')) define('POLLING_CONFIGURACOES', 300000);   // 5 minutos
if (!defined('POLLING_WHATSAPP')) define('POLLING_WHATSAPP', 300000);             // 5 minutos
if (!defined('POLLING_MONITORAMENTO')) define('POLLING_MONITORAMENTO', 600000);   // 10 minutos
if (!defined('POLLING_CHAT')) define('POLLING_CHAT', 300000);                     // 5 minutos
if (!defined('POLLING_COMUNICACAO')) define('POLLING_COMUNICACAO', 600000);       // 10 minutos

// Timeout de conexão moderado
if (!defined('DB_CONNECT_TIMEOUT')) define('DB_CONNECT_TIMEOUT', 5);
if (!defined('DB_READ_TIMEOUT')) define('DB_READ_TIMEOUT', 10);

// Sistema de cache em arquivo
function cache_get($key) {
    $file = 'cache/' . md5($key) . '.cache';
    if (file_exists($file) && (time() - filemtime($file)) < 1800) {
        return unserialize(file_get_contents($file));
    }
    return null;
}

function cache_set($key, $value, $ttl = 1800) {
    $cache_dir = 'cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    $file = $cache_dir . md5($key) . '.cache';
    file_put_contents($file, serialize($value));
    return true;
}

// Contador de conexões mais permissivo
function check_connection_limit() {
    $contador_file = 'cache/conexoes_contador.txt';
    $limite_conexoes = 450; // Limite mais alto
    
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
    
    // Só bloquear se realmente exceder muito o limite
    if ($contador >= $limite_conexoes) {
        error_log("ALERTA: Limite de conexões excedido: $contador");
        return false; // Limite excedido
    }
    
    $contador++;
    file_put_contents($contador_file, $contador);
    return true;
}

// Verificar limite apenas para operações críticas
// Não bloquear para páginas principais como chat
$current_script = basename($_SERVER['SCRIPT_NAME']);
$critical_operations = ['api', 'ajax', 'polling', 'webhook'];

$is_critical = false;
foreach ($critical_operations as $op) {
    if (strpos($current_script, $op) !== false || strpos($_SERVER['REQUEST_URI'], $op) !== false) {
        $is_critical = true;
        break;
    }
}

// Só verificar limite para operações críticas
if ($is_critical && !check_connection_limit()) {
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