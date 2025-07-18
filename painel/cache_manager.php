<?php
/**
 * Gerenciador de Cache Central - Otimização Geral do Sistema
 * Reduz drasticamente o consumo de requisições ao banco de dados
 */

class CacheManager {
    private static $instance = null;
    private $cacheDir;
    private $defaultTTL = 300; // 5 minutos
    private $memoryCache = [];
    
    private function __construct() {
        $this->cacheDir = sys_get_temp_dir() . '/loja_virtual_cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Cache em memória para requisições na mesma execução
     */
    public function getMemory($key) {
        if (isset($this->memoryCache[$key])) {
            $data = $this->memoryCache[$key];
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unset($this->memoryCache[$key]);
        }
        return null;
    }
    
    public function setMemory($key, $value, $ttl = 60) {
        $this->memoryCache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    /**
     * Cache em arquivo para persistir entre requisições
     */
    public function get($key, $checkMemory = true) {
        // Verificar cache em memória primeiro
        if ($checkMemory) {
            $memory = $this->getMemory($key);
            if ($memory !== null) {
                return $memory;
            }
        }
        
        $file = $this->cacheDir . md5($key) . '.cache';
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        if (!$data || $data['expires'] < time()) {
            @unlink($file);
            return null;
        }
        
        // Salvar também em memória
        $this->setMemory($key, $data['value'], min($data['expires'] - time(), 300));
        
        return $data['value'];
    }
    
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?: $this->defaultTTL;
        
        // Salvar em memória
        $this->setMemory($key, $value, $ttl);
        
        // Salvar em arquivo
        $file = $this->cacheDir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($file, json_encode($data));
        return true;
    }
    
    /**
     * Cache com callback - executa função apenas se não houver cache
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }
    
    /**
     * Invalidar cache por padrão
     */
    public function forget($pattern) {
        // Limpar memória
        foreach ($this->memoryCache as $key => $value) {
            if (strpos($key, $pattern) !== false) {
                unset($this->memoryCache[$key]);
            }
        }
        
        // Limpar arquivos
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            if ($data && isset($data['key']) && strpos($data['key'], $pattern) !== false) {
                @unlink($file);
            }
        }
    }
    
    /**
     * Limpeza automática de cache expirado
     */
    public function cleanup() {
        $files = glob($this->cacheDir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$data || $data['expires'] < time()) {
                @unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Estatísticas do cache
     */
    public function stats() {
        $files = glob($this->cacheDir . '*.cache');
        $total = count($files);
        $expired = 0;
        $size = 0;
        
        foreach ($files as $file) {
            $size += filesize($file);
            $data = json_decode(file_get_contents($file), true);
            if (!$data || $data['expires'] < time()) {
                $expired++;
            }
        }
        
        return [
            'total_files' => $total,
            'expired_files' => $expired,
            'memory_cache_size' => count($this->memoryCache),
            'disk_size_bytes' => $size,
            'disk_size_mb' => round($size / 1024 / 1024, 2)
        ];
    }
}

/**
 * Funções auxiliares globais para facilitar o uso
 */
function cache_get($key) {
    return CacheManager::getInstance()->get($key);
}

function cache_set($key, $value, $ttl = null) {
    return CacheManager::getInstance()->set($key, $value, $ttl);
}

function cache_remember($key, $callback, $ttl = null) {
    return CacheManager::getInstance()->remember($key, $callback, $ttl);
}

function cache_forget($pattern) {
    return CacheManager::getInstance()->forget($pattern);
}

/**
 * Cache específico para consultas SQL
 */
function cache_query($mysqli, $sql, $params = [], $ttl = 300) {
    $key = 'sql_' . md5($sql . serialize($params));
    
    return cache_remember($key, function() use ($mysqli, $sql, $params) {
        if (empty($params)) {
            $result = $mysqli->query($sql);
        } else {
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $types = str_repeat('s', count($params)); // Assume string por padrão
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
            } else {
                throw new Exception("Erro na preparação da query: " . $mysqli->error);
            }
        }
        
        if (!$result) {
            return null;
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }, $ttl);
}

/**
 * Cache específico para dados de cliente
 */
function cache_cliente($cliente_id, $mysqli) {
    return cache_remember("cliente_{$cliente_id}", function() use ($cliente_id, $mysqli) {
        $sql = "SELECT * FROM clientes WHERE id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
        return $cliente;
    }, 600); // 10 minutos para dados de cliente
}

/**
 * Cache específico para conversas
 */
function cache_conversas($mysqli) {
    return cache_remember('conversas_recentes', function() use ($mysqli) {
        // Query otimizada para buscar conversas com última mensagem
        $sql = "SELECT 
                    c.id as cliente_id,
                    c.nome,
                    c.celular,
                    cc.nome_exibicao as canal_nome,
                    m.mensagem as ultima_mensagem,
                    m.data_hora as ultima_data
                FROM clientes c
                INNER JOIN (
                    SELECT 
                        cliente_id,
                        MAX(data_hora) as max_data_hora
                    FROM mensagens_comunicacao 
                    WHERE cliente_id IS NOT NULL AND cliente_id > 0
                    GROUP BY cliente_id
                ) ultima ON c.id = ultima.cliente_id
                INNER JOIN mensagens_comunicacao m ON m.cliente_id = ultima.cliente_id AND m.data_hora = ultima.max_data_hora
                LEFT JOIN canais_comunicacao cc ON m.canal_id = cc.id
                ORDER BY m.data_hora DESC
                LIMIT 50";
        
        $result = $mysqli->query($sql);
        $conversas = [];
        
        while ($conv = $result->fetch_assoc()) {
            $conversas[] = $conv;
        }
        
        return $conversas;
    }, 120); // 2 minutos para lista de conversas
}

/**
 * Cache específico para status de canais
 */
function cache_status_canais($mysqli) {
    return cache_remember('status_canais', function() use ($mysqli) {
        $sql = "SELECT id, nome_exibicao, porta, tipo, status, identificador FROM canais_comunicacao WHERE status != 'excluido'";
        $result = $mysqli->query($sql);
        $canais = [];
        
        while ($canal = $result->fetch_assoc()) {
            $canais[] = $canal;
        }
        
        return $canais;
    }, 60); // 1 minuto para status de canais
}
?> 