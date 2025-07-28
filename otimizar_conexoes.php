<?php
/**
 * OTIMIZAR CONEXÕES COM BANCO DE DADOS
 * 
 * Script para otimizar e reduzir o consumo de conexões
 */

require_once 'config.php';

echo "🔧 OTIMIZANDO CONEXÕES COM BANCO DE DADOS\n";
echo "==========================================\n\n";

// 1. Verificar configuração atual
echo "1️⃣ CONFIGURAÇÃO ATUAL\n";
echo "======================\n\n";

echo "📊 Configurações do banco:\n";
echo "   Host: " . DB_HOST . "\n";
echo "   Database: " . DB_NAME . "\n";
echo "   Usuário: " . DB_USER . "\n";
echo "   Debug Mode: " . (DEBUG_MODE ? 'ON' : 'OFF') . "\n";
echo "   Ambiente: " . ($is_local ? 'LOCAL' : 'PRODUÇÃO') . "\n\n";

// 2. Criar arquivo de configuração otimizada
echo "2️⃣ CRIANDO CONFIGURAÇÃO OTIMIZADA\n";
echo "==================================\n\n";

$config_otimizada = "<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA REDUZIR CONEXÕES
 * 
 * Esta configuração implementa:
 * - Conexões persistentes
 * - Pool de conexões
 * - Timeout otimizado
 * - Cache de consultas
 */

// Configurações de conexão otimizadas
define('DB_HOST', '" . DB_HOST . "');
define('DB_NAME', '" . DB_NAME . "');
define('DB_USER', '" . DB_USER . "');
define('DB_PASS', '" . DB_PASS . "');

// Configurações de otimização
define('DB_PERSISTENT', true);           // Usar conexões persistentes
define('DB_TIMEOUT', 5);                 // Timeout de 5 segundos
define('DB_MAX_RETRIES', 3);             // Máximo 3 tentativas
define('DB_RETRY_DELAY', 1);             // Delay de 1 segundo entre tentativas
define('CACHE_ENABLED', true);           // Habilitar cache
define('CACHE_TTL', 300);                // Cache por 5 minutos
define('LOG_LEVEL', 'ERROR');            // Log apenas erros

// Configurações de rate limiting
define('RATE_LIMIT_ENABLED', true);      // Habilitar rate limiting
define('RATE_LIMIT_MAX_REQUESTS', 100);  // Máximo 100 requisições por hora
define('RATE_LIMIT_WINDOW', 3600);       // Janela de 1 hora

// Configurações de webhook
define('WEBHOOK_RETRY_LIMIT', 2);        // Máximo 2 tentativas de retry
define('WEBHOOK_RETRY_DELAY', 2);        // Delay de 2 segundos
define('WEBHOOK_TIMEOUT', 10);           // Timeout de 10 segundos

// Configurações de IA
define('IA_CACHE_ENABLED', true);        // Cache de respostas da IA
define('IA_CACHE_TTL', 600);             // Cache por 10 minutos
define('IA_TIMEOUT', 15);                // Timeout da IA

echo \"✅ Configuração otimizada criada!\n\";
?>";

file_put_contents('config_otimizada.php', $config_otimizada);
echo "✅ Arquivo config_otimizada.php criado\n";

// 3. Criar classe de conexão otimizada
echo "\n3️⃣ CRIANDO CLASSE DE CONEXÃO OTIMIZADA\n";
echo "========================================\n\n";

$classe_conexao = "<?php
/**
 * CLASSE DE CONEXÃO OTIMIZADA
 * 
 * Implementa pool de conexões e cache para reduzir consumo
 */

class DatabaseConnection {
    private static \$instance = null;
    private \$connection = null;
    private \$cache = [];
    private \$request_count = 0;
    private \$last_request_time = 0;
    
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
        \$retries = 0;
        \$max_retries = defined('DB_MAX_RETRIES') ? DB_MAX_RETRIES : 3;
        
        while (\$retries < \$max_retries) {
            try {
                // Usar conexão persistente se configurado
                \$persistent = defined('DB_PERSISTENT') && DB_PERSISTENT ? 'p:' : '';
                \$host = \$persistent . DB_HOST;
                
                \$this->connection = new mysqli(\$host, DB_USER, DB_PASS, DB_NAME);
                
                if (\$this->connection->connect_error) {
                    throw new Exception('Erro de conexão: ' . \$this->connection->connect_error);
                }
                
                // Configurar timeout
                \$timeout = defined('DB_TIMEOUT') ? DB_TIMEOUT : 5;
                \$this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, \$timeout);
                
                // Configurar charset
                \$this->connection->set_charset('utf8mb4');
                
                break;
            } catch (Exception \$e) {
                \$retries++;
                if (\$retries >= \$max_retries) {
                    error_log('Erro de conexão após ' . \$max_retries . ' tentativas: ' . \$e->getMessage());
                    throw \$e;
                }
                
                \$delay = defined('DB_RETRY_DELAY') ? DB_RETRY_DELAY : 1;
                sleep(\$delay);
            }
        }
    }
    
    public function query(\$sql, \$cache_key = null, \$cache_ttl = 300) {
        // Rate limiting
        if (!\$this->checkRateLimit()) {
            throw new Exception('Rate limit excedido');
        }
        
        // Verificar cache
        if (\$cache_key && defined('CACHE_ENABLED') && CACHE_ENABLED) {
            \$cached_result = \$this->getCache(\$cache_key);
            if (\$cached_result !== false) {
                return \$cached_result;
            }
        }
        
        // Executar query
        \$result = \$this->connection->query(\$sql);
        
        if (\$result === false) {
            error_log('Erro na query: ' . \$this->connection->error . ' | SQL: ' . \$sql);
            throw new Exception('Erro na query: ' . \$this->connection->error);
        }
        
        // Armazenar no cache
        if (\$cache_key && defined('CACHE_ENABLED') && CACHE_ENABLED) {
            \$this->setCache(\$cache_key, \$result, \$cache_ttl);
        }
        
        return \$result;
    }
    
    private function checkRateLimit() {
        if (!defined('RATE_LIMIT_ENABLED') || !RATE_LIMIT_ENABLED) {
            return true;
        }
        
        \$current_time = time();
        \$window = defined('RATE_LIMIT_WINDOW') ? RATE_LIMIT_WINDOW : 3600;
        \$max_requests = defined('RATE_LIMIT_MAX_REQUESTS') ? RATE_LIMIT_MAX_REQUESTS : 100;
        
        // Reset contador se passou a janela
        if (\$current_time - \$this->last_request_time > \$window) {
            \$this->request_count = 0;
            \$this->last_request_time = \$current_time;
        }
        
        \$this->request_count++;
        return \$this->request_count <= \$max_requests;
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
    
    private function setCache(\$key, \$data, \$ttl) {
        \$this->cache[\$key] = [
            'data' => \$data,
            'expires' => time() + \$ttl
        ];
        
        // Limpar cache antigo se muito grande
        if (count(\$this->cache) > 100) {
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

// Função helper para usar a conexão otimizada
function getDB() {
    return DatabaseConnection::getInstance();
}

echo \"✅ Classe de conexão otimizada criada!\n\";
?>";

file_put_contents('painel/db_otimizado.php', $classe_conexao);
echo "✅ Arquivo painel/db_otimizado.php criado\n";

// 4. Criar script de limpeza de logs
echo "\n4️⃣ CRIANDO SCRIPT DE LIMPEZA\n";
echo "==============================\n\n";

$script_limpeza = "<?php
/**
 * SCRIPT DE LIMPEZA E OTIMIZAÇÃO
 * 
 * Remove logs antigos e otimiza o sistema
 */

require_once 'config_otimizada.php';
require_once 'painel/db_otimizado.php';

echo \"🧹 LIMPEZA E OTIMIZAÇÃO DO SISTEMA\n\";
echo \"==================================\n\n\";

// 1. Limpar logs antigos
echo \"1️⃣ LIMPANDO LOGS ANTIGOS\n\";
echo \"========================\n\n\";

\$log_files = glob('logs/*.log');
\$total_size = 0;
\$files_removed = 0;

foreach (\$log_files as \$log_file) {
    \$file_time = filemtime(\$log_file);
    \$days_old = (time() - \$file_time) / (24 * 60 * 60);
    
    if (\$days_old > 7) { // Remover logs com mais de 7 dias
        \$size = filesize(\$log_file);
        \$total_size += \$size;
        unlink(\$log_file);
        \$files_removed++;
        echo \"   🗑️ Removido: \" . basename(\$log_file) . \" (\" . round(\$size/1024, 2) . \" KB)\n\";
    }
}

echo \"   📊 Total removido: \$files_removed arquivos, \" . round(\$total_size/1024/1024, 2) . \" MB\n\";

// 2. Limpar cache antigo
echo \"\n2️⃣ LIMPANDO CACHE ANTIGO\n\";
echo \"=========================\n\n\";

\$cache_files = glob('cache/*');
\$cache_removed = 0;

foreach (\$cache_files as \$cache_file) {
    \$file_time = filemtime(\$cache_file);
    \$hours_old = (time() - \$file_time) / (60 * 60);
    
    if (\$hours_old > 24) { // Remover cache com mais de 24 horas
        unlink(\$cache_file);
        \$cache_removed++;
    }
}

echo \"   🗑️ Cache removido: \$cache_removed arquivos\n\";

// 3. Otimizar tabelas do banco
echo \"\n3️⃣ OTIMIZANDO TABELAS DO BANCO\n\";
echo \"===============================\n\n\";

try {
    \$db = getDB();
    
    \$tables = ['mensagens_comunicacao', 'clientes', 'canais_comunicacao'];
    
    foreach (\$tables as \$table) {
        \$result = \$db->query(\"OPTIMIZE TABLE \$table\");
        echo \"   ✅ Tabela \$table otimizada\n\";
    }
    
} catch (Exception \$e) {
    echo \"   ❌ Erro ao otimizar tabelas: \" . \$e->getMessage() . \"\n\";
}

// 4. Verificar e corrigir índices
echo \"\n4️⃣ VERIFICANDO ÍNDICES\n\";
echo \"=======================\n\n\";

try {
    \$db = getDB();
    
    // Verificar se o índice no numero_whatsapp existe
    \$result = \$db->query(\"SHOW INDEX FROM mensagens_comunicacao WHERE Key_name = 'idx_numero_whatsapp'\");
    
    if (\$result->num_rows == 0) {
        echo \"   🔧 Criando índice para numero_whatsapp...\n\";
        \$db->query(\"CREATE INDEX idx_numero_whatsapp ON mensagens_comunicacao(numero_whatsapp)\");
        echo \"   ✅ Índice criado com sucesso\n\";
    } else {
        echo \"   ✅ Índice numero_whatsapp já existe\n\";
    }
    
} catch (Exception \$e) {
    echo \"   ❌ Erro ao verificar índices: \" . \$e->getMessage() . \"\n\";
}

echo \"\n✅ Limpeza e otimização concluída!\n\";
?>";

file_put_contents('limpeza_otimizacao.php', $script_limpeza);
echo "✅ Arquivo limpeza_otimizacao.php criado\n";

// 5. Criar script de monitoramento
echo "\n5️⃣ CRIANDO SCRIPT DE MONITORAMENTO\n";
echo "===================================\n\n";

$script_monitoramento = "<?php
/**
 * SCRIPT DE MONITORAMENTO DE CONEXÕES
 * 
 * Monitora o consumo de conexões em tempo real
 */

require_once 'config_otimizada.php';
require_once 'painel/db_otimizado.php';

echo \"📊 MONITORAMENTO DE CONEXÕES\n\";
echo \"============================\n\n\";

// Função para formatar bytes
function formatBytes(\$bytes, \$precision = 2) {
    \$units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for (\$i = 0; \$bytes > 1024 && \$i < count(\$units) - 1; \$i++) {
        \$bytes /= 1024;
    }
    
    return round(\$bytes, \$precision) . ' ' . \$units[\$i];
}

// Monitorar por 5 minutos
\$start_time = time();
\$end_time = \$start_time + 300; // 5 minutos

echo \"🕐 Monitorando por 5 minutos...\n\n\";

while (time() < \$end_time) {
    try {
        \$db = getDB();
        
        // Verificar estatísticas
        \$result = \$db->query(\"SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()\");
        \$stats = \$result->fetch_assoc();
        
        \$current_time = date('H:i:s');
        echo \"[\$current_time] Mensagens hoje: {\$stats['total']}\n\";
        
        // Verificar tamanho dos logs
        \$log_files = glob('logs/*.log');
        \$total_log_size = 0;
        foreach (\$log_files as \$log_file) {
            \$total_log_size += filesize(\$log_file);
        }
        
        echo \"   📄 Tamanho dos logs: \" . formatBytes(\$total_log_size) . \"\n\";
        
        // Aguardar 30 segundos
        sleep(30);
        
    } catch (Exception \$e) {
        echo \"❌ Erro: \" . \$e->getMessage() . \"\n\";
        sleep(30);
    }
}

echo \"\n✅ Monitoramento concluído!\n\";
?>";

file_put_contents('monitorar_conexoes.php', $script_monitoramento);
echo "✅ Arquivo monitorar_conexoes.php criado\n";

// 6. Recomendações finais
echo "\n6️⃣ RECOMENDAÇÕES DE IMPLEMENTAÇÃO\n";
echo "===================================\n\n";

echo "🔧 Para implementar as otimizações:\n\n";

echo "1. **Substituir conexões atuais:**\n";
echo "   - Renomear db.php para db_old.php\n";
echo "   - Renomear db_otimizado.php para db.php\n";
echo "   - Atualizar includes nos arquivos\n\n";

echo "2. **Usar configuração otimizada:**\n";
echo "   - Renomear config.php para config_old.php\n";
echo "   - Renomear config_otimizada.php para config.php\n\n";

echo "3. **Executar limpeza regular:**\n";
echo "   - Adicionar limpeza_otimizacao.php ao cron\n";
echo "   - Executar diariamente às 2h da manhã\n\n";

echo "4. **Monitorar consumo:**\n";
echo "   - Executar monitorar_conexoes.php periodicamente\n";
echo "   - Verificar logs de erro do servidor\n\n";

echo "5. **Configurações do servidor:**\n";
echo "   - Aumentar max_connections_per_hour (se possível)\n";
echo "   - Configurar connection_timeout\n";
echo "   - Implementar rate limiting no servidor\n\n";

echo "✅ Otimizações criadas com sucesso!\n";
echo "📁 Arquivos criados:\n";
echo "   - config_otimizada.php\n";
echo "   - painel/db_otimizado.php\n";
echo "   - limpeza_otimizacao.php\n";
echo "   - monitorar_conexoes.php\n\n";

echo "🚀 Próximo passo: Implementar as otimizações gradualmente!\n";
?> 