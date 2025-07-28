<?php
/**
 * WRAPPER DE CONEXÃO OTIMIZADO PARA EMERGÊNCIA
 * 
 * Substitui temporariamente a conexão padrão
 */

require_once 'emergency_config.php';

class EmergencyDatabase {
    private static $instance = null;
    private $connection = null;
    private $cache = [];
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        // Verificar rate limit
        if (!checkRateLimit()) {
            throw new Exception('Rate limit excedido - aguarde 1 hora');
        }
        
        try {
            // Usar conexão persistente
            $persistent = DB_PERSISTENT ? 'p:' : '';
            $host = $persistent . DB_HOST;
            
            $this->connection = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception('Erro de conexão: ' . $this->connection->connect_error);
            }
            
            // Configurar timeout reduzido
            $this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, DB_TIMEOUT);
            $this->connection->set_charset('utf8mb4');
            
        } catch (Exception $e) {
            emergencyLog('Erro de conexão: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function query($sql, $cache_key = null) {
        // Verificar cache primeiro
        if ($cache_key && CACHE_ENABLED) {
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        // Executar query com timeout
        $result = $this->connection->query($sql);
        
        if ($result === false) {
            emergencyLog('Erro na query: ' . $this->connection->error);
            throw new Exception('Erro na query: ' . $this->connection->error);
        }
        
        // Armazenar no cache
        if ($cache_key && CACHE_ENABLED) {
            $this->setCache($cache_key, $result);
        }
        
        return $result;
    }
    
    private function getCache($key) {
        if (!isset($this->cache[$key])) {
            return false;
        }
        
        $cached = $this->cache[$key];
        if (time() > $cached['expires']) {
            unset($this->cache[$key]);
            return false;
        }
        
        return $cached['data'];
    }
    
    private function setCache($key, $data) {
        $this->cache[$key] = [
            'data' => $data,
            'expires' => time() + CACHE_TTL
        ];
        
        // Limpar cache se muito grande
        if (count($this->cache) > 50) {
            $this->cleanCache();
        }
    }
    
    private function cleanCache() {
        $current_time = time();
        foreach ($this->cache as $key => $cached) {
            if ($current_time > $cached['expires']) {
                unset($this->cache[$key]);
            }
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}

// Função helper
function getEmergencyDB() {
    return EmergencyDatabase::getInstance();
}

echo "✅ Wrapper de conexão de emergência criado!
";
?>