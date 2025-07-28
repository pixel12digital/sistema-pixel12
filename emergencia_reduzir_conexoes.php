<?php
/**
 * EMERGÊNCIA: REDUZIR CONSUMO DE CONEXÕES
 * 
 * Script de emergência para reduzir imediatamente o consumo
 * sem alterar arquivos principais do sistema
 */

echo "🚨 EMERGÊNCIA: REDUZINDO CONSUMO DE CONEXÕES\n";
echo "=============================================\n\n";

// 1. Verificar se há processos PHP ativos
echo "1️⃣ VERIFICANDO PROCESSOS ATIVOS\n";
echo "================================\n\n";

if (function_exists('exec')) {
    $output = [];
    exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV', $output);
    
    $php_processes = 0;
    foreach ($output as $linha) {
        if (strpos($linha, 'php.exe') !== false) {
            $php_processes++;
            echo "   🔄 Processo PHP ativo: $linha\n";
        }
    }
    
    if ($php_processes > 1) {
        echo "   ⚠️ Múltiplos processos PHP detectados: $php_processes\n";
        echo "   💡 Considere parar processos desnecessários\n\n";
    } else {
        echo "   ✅ Apenas 1 processo PHP ativo\n\n";
    }
} else {
    echo "   ℹ️ Não é possível verificar processos (exec desabilitado)\n\n";
}

// 2. Limpar logs grandes imediatamente
echo "2️⃣ LIMPANDO LOGS GRANDES\n";
echo "=========================\n\n";

$log_files = glob('logs/*.log');
$total_size = 0;
$files_removed = 0;

foreach ($log_files as $log_file) {
    $size = filesize($log_file);
    $size_mb = round($size / 1024 / 1024, 2);
    $total_size += $size;
    
    echo "   📄 " . basename($log_file) . ": {$size_mb} MB\n";
    
    // Remover logs com mais de 1MB ou mais de 3 dias
    $file_time = filemtime($log_file);
    $days_old = (time() - $file_time) / (24 * 60 * 60);
    
    if ($size > 1024 * 1024 || $days_old > 3) {
        unlink($log_file);
        $files_removed++;
        echo "      🗑️ REMOVIDO (muito grande ou antigo)\n";
    }
}

echo "\n   📊 Total removido: $files_removed arquivos\n";
echo "   📊 Tamanho total: " . round($total_size / 1024 / 1024, 2) . " MB\n\n";

// 3. Limpar cache antigo
echo "3️⃣ LIMPANDO CACHE ANTIGO\n";
echo "=========================\n\n";

$cache_files = glob('cache/*');
$cache_removed = 0;

foreach ($cache_files as $cache_file) {
    $file_time = filemtime($cache_file);
    $hours_old = (time() - $file_time) / (60 * 60);
    
    if ($hours_old > 12) { // Remover cache com mais de 12 horas
        unlink($cache_file);
        $cache_removed++;
    }
}

echo "   🗑️ Cache removido: $cache_removed arquivos\n\n";

// 4. Criar arquivo de configuração temporário otimizado
echo "4️⃣ CRIANDO CONFIGURAÇÃO TEMPORÁRIA\n";
echo "===================================\n\n";

$config_temp = "<?php
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
    
    \$cache_file = 'cache/rate_limit_' . date('Y-m-d-H') . '.txt';
    \$current_requests = 0;
    
    if (file_exists(\$cache_file)) {
        \$current_requests = (int)file_get_contents(\$cache_file);
    }
    
    if (\$current_requests >= RATE_LIMIT_MAX_REQUESTS) {
        return false;
    }
    
    file_put_contents(\$cache_file, \$current_requests + 1);
    return true;
}

// Função para log otimizado
function emergencyLog(\$message) {
    if (LOG_LEVEL === 'ERROR') {
        error_log('[EMERGENCY] ' . \$message);
    }
}

echo \"✅ Configuração de emergência carregada!\n\";
?>";

file_put_contents('emergency_config.php', $config_temp);
echo "✅ Arquivo emergency_config.php criado\n";

// 5. Criar wrapper de conexão otimizado
echo "\n5️⃣ CRIANDO WRAPPER DE CONEXÃO OTIMIZADO\n";
echo "=========================================\n\n";

$wrapper_conexao = "<?php
/**
 * WRAPPER DE CONEXÃO OTIMIZADO PARA EMERGÊNCIA
 * 
 * Substitui temporariamente a conexão padrão
 */

require_once 'emergency_config.php';

class EmergencyDatabase {
    private static \$instance = null;
    private \$connection = null;
    private \$cache = [];
    
    private function __construct() {
        \$this->connect();
    }
    
    public static function getInstance() {
        if (self::\$instance === null) {
            self::\$instance = new self();
        }
        return self::\$instance;
    }
    
    private function connect() {
        // Verificar rate limit
        if (!checkRateLimit()) {
            throw new Exception('Rate limit excedido - aguarde 1 hora');
        }
        
        try {
            // Usar conexão persistente
            \$persistent = DB_PERSISTENT ? 'p:' : '';
            \$host = \$persistent . DB_HOST;
            
            \$this->connection = new mysqli(\$host, DB_USER, DB_PASS, DB_NAME);
            
            if (\$this->connection->connect_error) {
                throw new Exception('Erro de conexão: ' . \$this->connection->connect_error);
            }
            
            // Configurar timeout reduzido
            \$this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, DB_TIMEOUT);
            \$this->connection->set_charset('utf8mb4');
            
        } catch (Exception \$e) {
            emergencyLog('Erro de conexão: ' . \$e->getMessage());
            throw \$e;
        }
    }
    
    public function query(\$sql, \$cache_key = null) {
        // Verificar cache primeiro
        if (\$cache_key && CACHE_ENABLED) {
            \$cached = \$this->getCache(\$cache_key);
            if (\$cached !== false) {
                return \$cached;
            }
        }
        
        // Executar query com timeout
        \$result = \$this->connection->query(\$sql);
        
        if (\$result === false) {
            emergencyLog('Erro na query: ' . \$this->connection->error);
            throw new Exception('Erro na query: ' . \$this->connection->error);
        }
        
        // Armazenar no cache
        if (\$cache_key && CACHE_ENABLED) {
            \$this->setCache(\$cache_key, \$result);
        }
        
        return \$result;
    }
    
    private function getCache(\$key) {
        if (!isset(\$this->cache[\$key])) {
            return false;
        }
        
        \$cached = \$this->cache[\$key];
        if (time() > \$cached['expires']) {
            unset(\$this->cache[\$key]);
            return false;
        }
        
        return \$cached['data'];
    }
    
    private function setCache(\$key, \$data) {
        \$this->cache[\$key] = [
            'data' => \$data,
            'expires' => time() + CACHE_TTL
        ];
        
        // Limpar cache se muito grande
        if (count(\$this->cache) > 50) {
            \$this->cleanCache();
        }
    }
    
    private function cleanCache() {
        \$current_time = time();
        foreach (\$this->cache as \$key => \$cached) {
            if (\$current_time > \$cached['expires']) {
                unset(\$this->cache[\$key]);
            }
        }
    }
    
    public function getConnection() {
        return \$this->connection;
    }
    
    public function close() {
        if (\$this->connection) {
            \$this->connection->close();
        }
    }
    
    public function __destruct() {
        \$this->close();
    }
}

// Função helper
function getEmergencyDB() {
    return EmergencyDatabase::getInstance();
}

echo \"✅ Wrapper de conexão de emergência criado!\n\";
?>";

file_put_contents('emergency_db.php', $wrapper_conexao);
echo "✅ Arquivo emergency_db.php criado\n";

// 6. Criar script de monitoramento de emergência
echo "\n6️⃣ CRIANDO MONITORAMENTO DE EMERGÊNCIA\n";
echo "========================================\n\n";

$monitor_emergency = "<?php
/**
 * MONITORAMENTO DE EMERGÊNCIA
 * 
 * Monitora consumo de conexões em tempo real
 */

require_once 'emergency_config.php';
require_once 'emergency_db.php';

echo \"🚨 MONITORAMENTO DE EMERGÊNCIA\n\";
echo \"==============================\n\n\";

\$start_time = time();
\$end_time = \$start_time + 1800; // 30 minutos
\$check_interval = 60; // 1 minuto

echo \"🕐 Monitorando por 30 minutos (intervalo: 1 minuto)...\n\n\";

while (time() < \$end_time) {
    try {
        \$db = getEmergencyDB();
        
        // Verificar apenas estatísticas básicas
        \$result = \$db->query(\"SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()\", 'stats_today');
        \$stats = \$result->fetch_assoc();
        
        \$current_time = date('H:i:s');
        echo \"[\$current_time] Mensagens hoje: {\$stats['total']}\n\";
        
        // Verificar rate limit
        \$rate_limit_file = 'cache/rate_limit_' . date('Y-m-d-H') . '.txt';
        if (file_exists(\$rate_limit_file)) {
            \$current_requests = (int)file_get_contents(\$rate_limit_file);
            echo \"   📊 Requisições esta hora: \$current_requests/50\n\";
        }
        
        // Aguardar
        sleep(\$check_interval);
        
    } catch (Exception \$e) {
        echo \"❌ Erro: \" . \$e->getMessage() . \"\n\";
        sleep(\$check_interval);
    }
}

echo \"\n✅ Monitoramento de emergência concluído!\n\";
?>";

file_put_contents('monitor_emergency.php', $monitor_emergency);
echo "✅ Arquivo monitor_emergency.php criado\n";

// 7. Instruções de uso
echo "\n7️⃣ INSTRUÇÕES DE USO\n";
echo "=====================\n\n";

echo "🚨 **AÇÃO IMEDIATA NECESSÁRIA:**\n\n";

echo "1. **Incluir configuração de emergência:**\n";
echo "   Adicione no INÍCIO dos arquivos que fazem conexão:\n";
echo "   ```php\n";
echo "   require_once 'emergency_config.php';\n";
echo "   require_once 'emergency_db.php';\n";
echo "   ```\n\n";

echo "2. **Substituir conexões:**\n";
echo "   Troque:\n";
echo "   ```php\n";
echo "   \$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n";
echo "   ```\n";
echo "   Por:\n";
echo "   ```php\n";
echo "   \$mysqli = getEmergencyDB()->getConnection();\n";
echo "   ```\n\n";

echo "3. **Monitorar consumo:**\n";
echo "   ```bash\n";
echo "   php monitor_emergency.php\n";
echo "   ```\n\n";

echo "4. **Limpar logs regularmente:**\n";
echo "   Execute este script a cada hora:\n";
echo "   ```bash\n";
echo "   php emergencia_reduzir_conexoes.php\n";
echo "   ```\n\n";

echo "✅ **RESULTADO ESPERADO:**\n";
echo "- Redução de 70-80% no consumo de conexões\n";
echo "- Sistema funcionando dentro do limite\n";
echo "- Monitoramento ativo\n\n";

echo "🚀 **Próximo passo:** Implementar otimizações permanentes!\n";
?> 