<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA REDUZIR CONEXÕES
 * 
 * Este arquivo substitui o config_emergencia.php com otimizações reais
 * para economizar conexões e manter o sistema funcionando
 */

// Configurações de polling OTIMIZADAS
if (!defined('POLLING_CONFIGURACOES')) define('POLLING_CONFIGURACOES', 300000);    // 5 minutos
if (!defined('POLLING_WHATSAPP')) define('POLLING_WHATSAPP', 300000);         // 5 minutos
if (!defined('POLLING_MONITORAMENTO')) define('POLLING_MONITORAMENTO', 600000);    // 10 minutos
if (!defined('POLLING_CHAT')) define('POLLING_CHAT', 300000);             // 5 minutos
if (!defined('POLLING_COMUNICACAO')) define('POLLING_COMUNICACAO', 600000);      // 10 minutos

// Configurações de cache OTIMIZADAS
if (!defined('CACHE_ENABLED')) define('CACHE_ENABLED', true);
if (!defined('CACHE_TTL')) define('CACHE_TTL', 1800);                  // 30 minutos
if (!defined('CACHE_MAX_SIZE')) define('CACHE_MAX_SIZE', '50MB');

// Configurações de conexão OTIMIZADAS
if (!defined('DB_PERSISTENT')) define('DB_PERSISTENT', true);
if (!defined('DB_TIMEOUT')) define('DB_TIMEOUT', 5);
if (!defined('DB_MAX_RETRIES')) define('DB_MAX_RETRIES', 1);

// Sistema de cache em arquivo otimizado
function optimized_cache_get($key) {
    $file = 'cache/' . md5($key) . '.cache';
    if (file_exists($file) && (time() - filemtime($file)) < CACHE_TTL) {
        return unserialize(file_get_contents($file));
    }
    return null;
}

function optimized_cache_set($key, $value, $ttl = CACHE_TTL) {
    $cache_dir = 'cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    $file = $cache_dir . md5($key) . '.cache';
    file_put_contents($file, serialize($value));
    return true;
}

// Contador de conexões SIMPLES (sem bloqueio)
function check_connection_count() {
    $contador_file = 'cache/conexoes_contador.txt';
    $limite_conexoes = 450; // Limite alto para evitar bloqueio
    
    $cache_dir = 'cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
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
        return false;
    }
    
    $contador++;
    file_put_contents($contador_file, $contador);
    return true;
}

// Verificar limite apenas para operações MUITO críticas
$current_script = basename($_SERVER['SCRIPT_NAME']);
$critical_operations = ['webhook', 'api_webhook'];

$is_critical = false;
foreach ($critical_operations as $op) {
    if (strpos($current_script, $op) !== false || 
        (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], $op) !== false)) {
        $is_critical = true;
        break;
    }
}

// Só verificar limite para operações MUITO críticas
if ($is_critical && !check_connection_count()) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Limite de conexões excedido',
        'message' => 'Sistema temporariamente indisponível devido ao alto volume de acessos',
        'retry_after' => 300 // 5 minutos
    ]);
    exit;
}

// Só mostrar mensagem se for via web
if (php_sapi_name() !== 'cli') {
    echo "✅ Configuração otimizada carregada - Conexões reduzidas!\n";
}
?>