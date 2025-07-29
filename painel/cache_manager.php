<?php
/**
 * Gerenciador de Cache Otimizado
 * Reduz drasticamente as consultas ao banco de dados
 */

// Configurações de cache otimizadas
if (!defined('CACHE_DIR')) define('CACHE_DIR', __DIR__ . '/../cache/');
if (!defined('CACHE_TTL')) define('CACHE_TTL', 1800); // 30 minutos
if (!defined('CACHE_MAX_SIZE')) define('CACHE_MAX_SIZE', '50MB');

// Criar diretório de cache se não existir
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

/**
 * Cache de conversas com TTL estendido
 */
function cache_conversas($mysqli) {
    $cache_file = CACHE_DIR . 'conversas_recentes.cache';
    $cache_key = 'conversas_recentes';
    
    // Verificar cache primeiro
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_TTL) {
        $cached_data = unserialize(file_get_contents($cache_file));
        if ($cached_data && isset($cached_data['data']) && isset($cached_data['timestamp'])) {
            // Cache válido, retornar dados
            return $cached_data['data'];
        }
    }
    
    // Cache expirado ou inválido, buscar do banco
    $sql = "SELECT DISTINCT 
                c.id as cliente_id,
                c.nome,
                c.celular,
                c.cpf_cnpj,
                c.razao_social,
                c.email,
                c.endereco,
                c.cidade,
                c.estado,
                c.cep,
                c.data_criacao,
                c.data_atualizacao,
                c.contact_name,
                c.emails_adicionais,
                c.telefone,
                c.observacoes,
                c.asaas_id,
                c.rua,
                c.numero,
                c.complemento,
                c.bairro,
                c.pais,
                c.notificacao_desativada,
                c.referencia_externa,
                c.criado_em_asaas,
                c.telefone_editado_manual,
                c.celular_editado_manual,
                c.email_editado_manual,
                c.nome_editado_manual,
                c.endereco_editado_manual,
                c.data_ultima_edicao_manual,
                'WhatsApp' as canal_nome,
                m.ultima_mensagem,
                m.ultima_data,
                m.mensagens_nao_lidas
            FROM clientes c
            INNER JOIN (
                SELECT 
                    cliente_id,
                    MAX(data_hora) as ultima_data,
                    MAX(CASE WHEN direcao = 'recebido' THEN mensagem END) as ultima_mensagem,
                    COUNT(CASE WHEN direcao = 'recebido' AND status != 'lido' THEN 1 END) as mensagens_nao_lidas
                FROM mensagens_comunicacao 
                WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY cliente_id
            ) m ON c.id = m.cliente_id
            ORDER BY m.ultima_data DESC, c.id DESC
            LIMIT 100";
    
    $result = $mysqli->query($sql);
    $conversas = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $conversas[] = $row;
        }
    }
    
    // Salvar no cache
    $cache_data = [
        'data' => $conversas,
        'timestamp' => time()
    ];
    file_put_contents($cache_file, serialize($cache_data));
    
    return $conversas;
}

/**
 * Cache de mensagens com TTL estendido
 */
function cache_mensagens($mysqli, $cliente_id) {
    $cache_file = CACHE_DIR . "mensagens_{$cliente_id}.cache";
    
    // Verificar cache primeiro
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_TTL) {
        $cached_data = unserialize(file_get_contents($cache_file));
        if ($cached_data && isset($cached_data['data']) && isset($cached_data['timestamp'])) {
            return $cached_data['data'];
        }
    }
    
    // Cache expirado, buscar do banco
    $sql = "SELECT * FROM mensagens_comunicacao 
            WHERE cliente_id = ? 
            ORDER BY data_hora DESC 
            LIMIT 100";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = $row;
    }
    $stmt->close();
    
    // Salvar no cache
    $cache_data = [
        'data' => $mensagens,
        'timestamp' => time()
    ];
    file_put_contents($cache_file, serialize($cache_data));
    
    return $mensagens;
}

/**
 * Função cache_remember - Executa callback se cache não existir ou expirou
 */
function cache_remember($key, $callback, $ttl = CACHE_TTL) {
    $cache_file = CACHE_DIR . md5($key) . '.cache';
    
    // Verificar cache primeiro
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $ttl) {
        $cached_data = unserialize(file_get_contents($cache_file));
        if ($cached_data && isset($cached_data['data']) && isset($cached_data['timestamp'])) {
            return $cached_data['data'];
        }
    }
    
    // Cache expirado ou inválido, executar callback
    $data = $callback();
    
    // Salvar no cache
    $cache_data = [
        'data' => $data,
        'timestamp' => time()
    ];
    file_put_contents($cache_file, serialize($cache_data));
    
    return $data;
}

/**
 * Função cache_forget - Remove cache específico
 */
function cache_forget($key) {
    $cache_file = CACHE_DIR . md5($key) . '.cache';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}

/**
 * Função cache_cliente - Cache para dados de cliente específico
 */
function cache_cliente($cliente_id, $mysqli) {
    return cache_remember("cliente_{$cliente_id}", function() use ($cliente_id, $mysqli) {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
        return $cliente;
    }, 300); // Cache de 5 minutos para dados do cliente
}

/**
 * Invalidar cache específico
 */
function invalidate_cache($key) {
    $cache_file = CACHE_DIR . $key . '.cache';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}

/**
 * Limpar cache antigo
 */
function cleanup_cache() {
    $files = glob(CACHE_DIR . '*.cache');
    $now = time();
    
    foreach ($files as $file) {
        if (($now - filemtime($file)) > CACHE_TTL) {
            unlink($file);
        }
    }
}

// Limpar cache antigo a cada 10% das requisições
if (rand(1, 10) === 1) {
    cleanup_cache();
}
?>
