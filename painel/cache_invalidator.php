<?php
/**
 * Sistema de Invalidação Inteligente de Cache
 * Limpa automaticamente os caches relevantes quando dados são alterados
 */

require_once 'cache_manager.php';

class CacheInvalidator {
    private static $instance = null;
    private $cache;
    
    private function __construct() {
        $this->cache = CacheManager::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Invalidar cache quando mensagem é criada/atualizada
     */
    public function onMessageChanged($cliente_id, $message_data = null) {
        // Invalidar caches específicos do cliente
        $this->cache->forget("mensagens_{$cliente_id}");
        $this->cache->forget("mensagens_html_{$cliente_id}");
        $this->cache->forget("historico_html_{$cliente_id}");
        $this->cache->forget("detalhes_cliente_{$cliente_id}");
        
        // Invalidar cache de conversas (afeta lista)
        $this->cache->forget("conversas_recentes");
        
        // Log para debug
        error_log("[CACHE] Invalidado cache para cliente {$cliente_id} após mudança em mensagem");
    }
    
    /**
     * Invalidar cache quando cliente é criado/atualizado
     */
    public function onClientChanged($cliente_id, $client_data = null) {
        // Invalidar todos os caches relacionados ao cliente
        $this->cache->forget("cliente_{$cliente_id}");
        $this->cache->forget("cliente_numero_{$cliente_id}"); // Cache específico para números
        $this->cache->forget("detalhes_cliente_{$cliente_id}");
        $this->cache->forget("mensagens_{$cliente_id}");
        $this->cache->forget("mensagens_html_{$cliente_id}");
        $this->cache->forget("historico_html_{$cliente_id}");
        
        // Invalidar caches de busca (pode afetar resultados)
        $this->cache->forget("buscar_clientes_");
        $this->cache->forget("clientes_nova_conversa_");
        $this->cache->forget("conversas_recentes");
        
        error_log("[CACHE] Invalidado cache para cliente {$cliente_id} após mudança nos dados");
    }
    
    /**
     * Invalidar cache quando canal é alterado
     */
    public function onChannelChanged($canal_id, $canal_data = null) {
        // Invalidar status de canais
        $this->cache->forget("status_canais");
        $this->cache->forget("status_canal_{$canal_id}");
        $this->cache->forget("status_canais_completo");
        
        error_log("[CACHE] Invalidado cache para canal {$canal_id}");
    }
    
    /**
     * Invalidar cache quando mensagens são marcadas como lidas
     */
    public function onMessageRead($cliente_id) {
        // Invalidar todos os caches relacionados ao cliente
        $this->cache->forget("cliente_{$cliente_id}");
        $this->cache->forget("cliente_numero_{$cliente_id}");
        $this->cache->forget("detalhes_cliente_{$cliente_id}");
        $this->cache->forget("mensagens_{$cliente_id}");
        $this->cache->forget("mensagens_html_{$cliente_id}");
        $this->cache->forget("historico_html_{$cliente_id}");
        
        // Invalidar caches de conversas e mensagens não lidas
        $this->cache->forget("conversas_nao_lidas");
        $this->cache->forget("total_mensagens_nao_lidas");
        $this->cache->forget("conversas_recentes");
        
        // Log da invalidação
        $this->logInvalidation('message_read', $cliente_id, [
            'action' => 'Mensagens marcadas como lidas',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Invalidação global para operações que afetam múltiplos elementos
     */
    public function invalidateAll($pattern = null) {
        if ($pattern) {
            $this->cache->forget($pattern);
        } else {
            // Limpeza geral
            $this->cache->cleanup();
        }
        
        error_log("[CACHE] Invalidação global executada" . ($pattern ? " para padrão: {$pattern}" : ""));
    }
    
    /**
     * Pré-aquecimento de cache para dados frequentemente acessados
     */
    public function warmupCache($mysqli) {
        try {
            // Pre-carregar conversas recentes
            cache_conversas($mysqli);
            
            // Pre-carregar status de canais
            cache_status_canais($mysqli);
            
            error_log("[CACHE] Pré-aquecimento de cache concluído");
        } catch (Exception $e) {
            error_log("[CACHE] Erro no pré-aquecimento: " . $e->getMessage());
        }
    }
    
    /**
     * Estatísticas de performance do cache
     */
    public function getStats() {
        return $this->cache->stats();
    }
}

/**
 * Funções auxiliares para fácil integração
 */
function invalidate_client_cache($cliente_id, $data = null) {
    CacheInvalidator::getInstance()->onClientChanged($cliente_id, $data);
}

function invalidate_message_cache($cliente_id, $data = null) {
    CacheInvalidator::getInstance()->onMessageChanged($cliente_id, $data);
}

function invalidate_channel_cache($canal_id, $data = null) {
    CacheInvalidator::getInstance()->onChannelChanged($canal_id, $data);
}

function warmup_cache($mysqli) {
    CacheInvalidator::getInstance()->warmupCache($mysqli);
}

/**
 * Hook automático para interceptar operações no banco
 * Chama automaticamente a invalidação quando dados são alterados
 */
class DatabaseHook {
    private $mysqli;
    private $invalidator;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->invalidator = CacheInvalidator::getInstance();
    }
    
    /**
     * Wrapper para INSERT que automaticamente invalida cache
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        $stmt = $this->mysqli->prepare($sql);
        
        if ($stmt) {
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();
            $insert_id = $this->mysqli->insert_id;
            $stmt->close();
            
            // Invalidar cache baseado na tabela
            $this->autoInvalidate($table, $data, $insert_id);
            
            return $insert_id;
        }
        
        return false;
    }
    
    /**
     * Wrapper para UPDATE que automaticamente invalida cache
     */
    public function update($table, $data, $where, $where_params = []) {
        $set_clause = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $set_clause[] = "{$field} = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set_clause) . " WHERE {$where}";
        $stmt = $this->mysqli->prepare($sql);
        
        if ($stmt) {
            $all_params = array_merge($values, $where_params);
            $types = str_repeat('s', count($all_params));
            $stmt->bind_param($types, ...$all_params);
            $result = $stmt->execute();
            $stmt->close();
            
            // Invalidar cache baseado na tabela
            $this->autoInvalidate($table, $data);
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Invalidação automática baseada na tabela modificada
     */
    private function autoInvalidate($table, $data, $id = null) {
        switch ($table) {
            case 'mensagens_comunicacao':
                if (isset($data['cliente_id'])) {
                    $this->invalidator->onMessageChanged($data['cliente_id'], $data);
                }
                break;
                
            case 'clientes':
                $cliente_id = $id ?: ($data['id'] ?? null);
                if ($cliente_id) {
                    $this->invalidator->onClientChanged($cliente_id, $data);
                }
                break;
                
            case 'canais_comunicacao':
                $canal_id = $id ?: ($data['id'] ?? null);
                if ($canal_id) {
                    $this->invalidator->onChannelChanged($canal_id, $data);
                }
                break;
        }
    }
}

/**
 * Função para criar uma instância do hook de banco com cache
 */
function create_cached_db($mysqli) {
    return new DatabaseHook($mysqli);
}
?> 