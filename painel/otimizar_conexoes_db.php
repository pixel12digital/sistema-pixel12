<?php
/**
 * OTIMIZADOR DE CONEXÕES DO BANCO DE DADOS
 * 
 * Script para resolver o problema de limite de conexões por hora
 */

echo "🔧 OTIMIZANDO CONEXÕES DO BANCO DE DADOS\n";
echo "========================================\n\n";

// 1. Verificar configuração atual
echo "1️⃣ CONFIGURAÇÃO ATUAL\n";
echo "=====================\n\n";

echo "📊 Configurações do banco:\n";
echo "   Host: " . DB_HOST . "\n";
echo "   Usuário: " . DB_USER . "\n";
echo "   Database: " . DB_NAME . "\n";
echo "   Limite de conexões por hora: 500\n\n";

// 2. Implementar pool de conexões
echo "2️⃣ IMPLEMENTANDO POOL DE CONEXÕES\n";
echo "==================================\n\n";

// Criar arquivo de configuração otimizada
$config_content = '<?php
/**
 * CONFIGURAÇÃO OTIMIZADA DE CONEXÕES
 * 
 * Pool de conexões para evitar limite de conexões por hora
 */

// Configurações de pool
define("DB_POOL_SIZE", 5);
define("DB_POOL_TIMEOUT", 300);
define("DB_POOL_RETRY_DELAY", 1);

// Pool de conexões
static $connection_pool = [];
static $pool_mutex = null;

function get_db_connection() {
    global $connection_pool, $pool_mutex;
    
    // Inicializar mutex se necessário
    if ($pool_mutex === null) {
        $pool_mutex = fopen(sys_get_temp_dir() . "/db_pool.lock", "w+");
    }
    
    // Tentar obter conexão do pool
    if (!empty($connection_pool)) {
        $connection = array_pop($connection_pool);
        
        // Verificar se a conexão ainda está ativa
        if ($connection && $connection->ping()) {
            return $connection;
        }
    }
    
    // Criar nova conexão
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($connection->connect_errno) {
        error_log("Erro ao conectar ao MySQL: " . $connection->connect_error);
        return null;
    }
    
    // Configurar conexão
    $connection->set_charset("utf8mb4");
    $connection->query("SET SESSION wait_timeout=" . DB_POOL_TIMEOUT);
    $connection->query("SET SESSION interactive_timeout=" . DB_POOL_TIMEOUT);
    
    return $connection;
}

function release_db_connection($connection) {
    global $connection_pool;
    
    if ($connection && count($connection_pool) < DB_POOL_SIZE) {
        $connection_pool[] = $connection;
    } else {
        $connection->close();
    }
}

// Função para limpar pool
function clear_connection_pool() {
    global $connection_pool;
    
    foreach ($connection_pool as $connection) {
        $connection->close();
    }
    $connection_pool = [];
}

// Registrar função de limpeza para execução no final do script
register_shutdown_function("clear_connection_pool");
?>';

file_put_contents('painel/db_pool.php', $config_content);
echo "✅ Arquivo de pool de conexões criado: painel/db_pool.php\n";

// 3. Atualizar arquivo db.php
echo "\n3️⃣ ATUALIZANDO ARQUIVO DB.PHP\n";
echo "==============================\n\n";

$db_content = '<?php
// Incluir configurações globais
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/db_pool.php";

// Obter conexão do pool
$mysqli = get_db_connection();

if (!$mysqli) {
    die("Erro ao obter conexão do banco de dados");
}

// Função para liberar conexão automaticamente
function cleanup_db_connection() {
    global $mysqli;
    if ($mysqli) {
        release_db_connection($mysqli);
    }
}

// Registrar limpeza automática
register_shutdown_function("cleanup_db_connection");
?>';

file_put_contents('painel/db.php', $db_content);
echo "✅ Arquivo db.php atualizado com pool de conexões\n";

// 4. Otimizar arquivos que fazem muitas conexões
echo "\n4️⃣ OTIMIZANDO ARQUIVOS COM MUITAS CONEXÕES\n";
echo "============================================\n\n";

// Lista de arquivos que podem estar causando muitas conexões
$files_to_optimize = [
    'painel/chat.php',
    'painel/receber_mensagem.php',
    'painel/ajax_whatsapp.php',
    'painel/cache_manager.php'
];

foreach ($files_to_optimize as $file) {
    if (file_exists($file)) {
        echo "🔧 Otimizando: $file\n";
        
        // Ler conteúdo do arquivo
        $content = file_get_contents($file);
        
        // Substituir conexões diretas por pool
        $content = str_replace(
            'new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME)',
            'get_db_connection()',
            $content
        );
        
        // Adicionar liberação de conexão no final
        if (strpos($content, 'register_shutdown_function') === false) {
            $content = str_replace(
                '<?php',
                '<?php
// Liberar conexão automaticamente
register_shutdown_function(function() {
    global $mysqli;
    if (isset($mysqli) && $mysqli) {
        release_db_connection($mysqli);
    }
});',
                $content
            );
        }
        
        file_put_contents($file, $content);
        echo "   ✅ Otimizado\n";
    }
}

// 5. Criar script de monitoramento de conexões
echo "\n5️⃣ CRIANDO MONITOR DE CONEXÕES\n";
echo "===============================\n\n";

$monitor_content = '<?php
/**
 * MONITOR DE CONEXÕES DO BANCO
 * 
 * Script para monitorar e controlar conexões
 */

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/db_pool.php";

echo "📊 MONITOR DE CONEXÕES DO BANCO\n";
echo "===============================\n\n";

// Verificar status das conexões
$connection = get_db_connection();
if ($connection) {
    $result = $connection->query("SHOW STATUS LIKE \'Connections\'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "🔗 Total de conexões: " . $row["Value"] . "\n";
    }
    
    $result = $connection->query("SHOW STATUS LIKE \'Threads_connected\'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "🧵 Conexões ativas: " . $row["Value"] . "\n";
    }
    
    $result = $connection->query("SHOW STATUS LIKE \'Max_used_connections\'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "📈 Máximo de conexões usadas: " . $row["Value"] . "\n";
    }
    
    release_db_connection($connection);
} else {
    echo "❌ Erro ao conectar ao banco\n";
}

echo "\n💡 DICAS PARA REDUZIR CONEXÕES:\n";
echo "   - Use cache para dados que não mudam frequentemente\n";
echo "   - Evite consultas desnecessárias\n";
echo "   - Use transações para operações múltiplas\n";
echo "   - Libere conexões após o uso\n";
?>';

file_put_contents('painel/monitor_conexoes.php', $monitor_content);
echo "✅ Monitor de conexões criado: painel/monitor_conexoes.php\n";

// 6. Testar nova configuração
echo "\n6️⃣ TESTANDO NOVA CONFIGURAÇÃO\n";
echo "==============================\n\n";

// Simular algumas conexões para testar
for ($i = 1; $i <= 3; $i++) {
    $connection = get_db_connection();
    if ($connection) {
        echo "✅ Conexão $i obtida com sucesso\n";
        release_db_connection($connection);
    } else {
        echo "❌ Erro ao obter conexão $i\n";
    }
}

echo "\n🎉 OTIMIZAÇÃO CONCLUÍDA!\n";
echo "========================\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "   1. Reinicie o servidor web\n";
echo "   2. Teste o chat novamente\n";
echo "   3. Monitore as conexões com: php painel/monitor_conexoes.php\n";
echo "   4. Se ainda houver problemas, aguarde 1 hora para resetar o limite\n\n";

echo "🔧 ARQUIVOS MODIFICADOS:\n";
echo "   - painel/db_pool.php (novo)\n";
echo "   - painel/db.php (atualizado)\n";
echo "   - painel/monitor_conexoes.php (novo)\n";
echo "   - Arquivos de chat otimizados\n";
?> 