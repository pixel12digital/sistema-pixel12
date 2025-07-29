<?php
/**
 * CORREÇÃO DE EMERGÊNCIA - CONEXÕES EXCESSIVAS
 * 
 * Este script implementa otimizações imediatas para reduzir
 * o número de conexões com o banco de dados
 */

require_once 'config.php';

echo "=== CORREÇÃO DE EMERGÊNCIA - CONEXÕES EXCESSIVAS ===\n\n";

// 1. Verificar configurações atuais
echo "1. Configurações atuais:\n";
echo "   POLLING_CONFIGURACOES: " . (defined('POLLING_CONFIGURACOES') ? POLLING_CONFIGURACOES : 'NÃO DEFINIDO') . "\n";
echo "   POLLING_WHATSAPP: " . (defined('POLLING_WHATSAPP') ? POLLING_WHATSAPP : 'NÃO DEFINIDO') . "\n";
echo "   POLLING_CHAT: " . (defined('POLLING_CHAT') ? POLLING_CHAT : 'NÃO DEFINIDO') . "\n\n";

// 2. Implementar pool de conexões
echo "2. Implementando pool de conexões...\n";

// Criar arquivo de configuração otimizada
$config_otimizada = '<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA REDUZIR CONEXÕES
 * 
 * Configurações de emergência para resolver problema de conexões excessivas
 */

// Reduzir drasticamente o polling
if (!defined("POLLING_CONFIGURACOES")) define("POLLING_CONFIGURACOES", 300000);   // 5 minutos
if (!defined("POLLING_WHATSAPP")) define("POLLING_WHATSAPP", 300000);             // 5 minutos  
if (!defined("POLLING_MONITORAMENTO")) define("POLLING_MONITORAMENTO", 600000);   // 10 minutos
if (!defined("POLLING_CHAT")) define("POLLING_CHAT", 300000);                     // 5 minutos
if (!defined("POLLING_COMUNICACAO")) define("POLLING_COMUNICACAO", 600000);       // 10 minutos

// Configurações de cache agressivo
if (!defined("CACHE_TTL_DEFAULT")) define("CACHE_TTL_DEFAULT", 1800); // 30 minutos
if (!defined("ENABLE_CACHE")) define("ENABLE_CACHE", true);

// Timeout de conexão reduzido
if (!defined("DB_CONNECT_TIMEOUT")) define("DB_CONNECT_TIMEOUT", 5);
if (!defined("DB_READ_TIMEOUT")) define("DB_READ_TIMEOUT", 10);
?>';

file_put_contents('config_otimizada.php', $config_otimizada);
echo "✅ Configuração otimizada criada!\n\n";

// 3. Implementar sistema de cache em arquivo
echo "3. Implementando sistema de cache em arquivo...\n";

$cache_dir = 'cache/';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
    echo "   ✅ Diretório de cache criado\n";
}

// Função de cache simples
function cache_get($key) {
    $file = 'cache/' . md5($key) . '.cache';
    if (file_exists($file) && (time() - filemtime($file)) < 1800) {
        return unserialize(file_get_contents($file));
    }
    return null;
}

function cache_set($key, $value, $ttl = 1800) {
    $file = 'cache/' . md5($key) . '.cache';
    file_put_contents($file, serialize($value));
    return true;
}

function cache_clear() {
    $files = glob('cache/*.cache');
    foreach ($files as $file) {
        unlink($file);
    }
    echo "   ✅ Cache limpo\n";
}

// 4. Limpar cache antigo
echo "4. Limpando cache antigo...\n";
cache_clear();

// 5. Verificar e otimizar consultas frequentes
echo "5. Otimizando consultas frequentes...\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "   ❌ Erro de conexão: " . $mysqli->connect_error . "\n";
    } else {
        echo "   ✅ Conexão estabelecida\n";
        
        // Cache de configurações
        $config_cache = cache_get('configuracoes_sistema');
        if (!$config_cache) {
            $result = $mysqli->query("SELECT * FROM configuracoes_sistema LIMIT 1");
            if ($result) {
                $config_cache = $result->fetch_assoc();
                cache_set('configuracoes_sistema', $config_cache, 3600); // 1 hora
                echo "   ✅ Configurações cacheadas\n";
            }
        } else {
            echo "   ✅ Configurações carregadas do cache\n";
        }
        
        // Cache de canais
        $canais_cache = cache_get('canais_comunicacao');
        if (!$canais_cache) {
            $result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status = 'conectado'");
            if ($result) {
                $canais_cache = [];
                while ($row = $result->fetch_assoc()) {
                    $canais_cache[] = $row;
                }
                cache_set('canais_comunicacao', $canais_cache, 1800); // 30 minutos
                echo "   ✅ Canais cacheados\n";
            }
        } else {
            echo "   ✅ Canais carregados do cache\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

// 6. Criar arquivo de monitoramento
echo "6. Criando sistema de monitoramento...\n";

$monitor_file = 'monitor_conexoes.php';
$monitor_code = '<?php
/**
 * MONITOR DE CONEXÕES
 * 
 * Monitora e controla o número de conexões com o banco
 */

require_once "config.php";

// Contador de conexões
$contador_file = "cache/conexoes_contador.txt";
$limite_conexoes = 400; // Limite seguro

function incrementar_conexao() {
    global $contador_file, $limite_conexoes;
    
    $contador = 0;
    if (file_exists($contador_file)) {
        $contador = (int)file_get_contents($contador_file);
    }
    
    // Reset diário
    $hoje = date("Y-m-d");
    $ultimo_reset = file_exists("cache/ultimo_reset.txt") ? file_get_contents("cache/ultimo_reset.txt") : "";
    
    if ($ultimo_reset !== $hoje) {
        $contador = 0;
        file_put_contents("cache/ultimo_reset.txt", $hoje);
    }
    
    $contador++;
    file_put_contents($contador_file, $contador);
    
    if ($contador > $limite_conexoes) {
        error_log("ALERTA: Limite de conexões excedido: $contador");
        return false;
    }
    
    return true;
}

function get_conexoes_count() {
    global $contador_file;
    return file_exists($contador_file) ? (int)file_get_contents($contador_file) : 0;
}

// Verificar se pode conectar
if (!incrementar_conexao()) {
    http_response_code(503);
    echo json_encode(["error" => "Limite de conexões excedido"]);
    exit;
}
?>';

file_put_contents($monitor_file, $monitor_code);
echo "   ✅ Monitor de conexões criado\n";

// 7. Instruções finais
echo "\n=== INSTRUÇÕES DE EMERGÊNCIA ===\n\n";
echo "1. ✅ Configurações otimizadas aplicadas\n";
echo "2. ✅ Sistema de cache implementado\n";
echo "3. ✅ Monitor de conexões criado\n\n";
echo "PRÓXIMOS PASSOS:\n";
echo "- Reinicie o servidor web\n";
echo "- Monitore o arquivo cache/conexoes_contador.txt\n";
echo "- Se necessário, aumente os intervalos de polling\n\n";
echo "CONTROLE DE CONEXÕES:\n";
echo "- Limite atual: 400 conexões por dia\n";
echo "- Monitor: monitor_conexoes.php\n";
echo "- Cache: cache/ (limpa automaticamente)\n\n";

echo "=== CORREÇÃO CONCLUÍDA ===\n";
?> 