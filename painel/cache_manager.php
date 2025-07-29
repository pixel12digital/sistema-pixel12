<?php
/**
 * Gerenciador de Cache Otimizado
 * Reduz drasticamente as consultas ao banco de dados
 */

// Configurações de cache otimizadas
define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_TTL', 1800); // 30 minutos
define('CACHE_MAX_SIZE', '50MB');

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
                c.nome as cliente_nome,
                c.celular as cliente_celular,
                c.cpf_cnpj as cliente_cpf_cnpj,
                c.razao_social as cliente_razao_social,
                c.email as cliente_email,
                c.endereco as cliente_endereco,
                c.cidade as cliente_cidade,
                c.estado as cliente_estado,
                c.cep as cliente_cep,
                c.data_cadastro as cliente_data_cadastro,
                c.data_atualizacao as cliente_data_atualizacao,
                c.sistema_id as cliente_sistema_id,
                c.contact_name as cliente_contact_name,
                c.emails_adicionais as cliente_emails_adicionais,
                c.telefone as cliente_telefone,
                c.status as cliente_status,
                c.monitorado as cliente_monitorado,
                c.ultima_atividade as cliente_ultima_atividade,
                c.observacoes as cliente_observacoes,
                c.tags as cliente_tags,
                c.prioridade as cliente_prioridade,
                c.categoria as cliente_categoria,
                c.origem as cliente_origem,
                c.ultima_interacao as cliente_ultima_interacao,
                c.frequencia_interacao as cliente_frequencia_interacao,
                c.valor_medio as cliente_valor_medio,
                c.ultima_compra as cliente_ultima_compra,
                c.total_compras as cliente_total_compras,
                c.satisfacao as cliente_satisfacao,
                c.risco as cliente_risco,
                c.etiquetas as cliente_etiquetas,
                c.notas as cliente_notas,
                c.anexos as cliente_anexos,
                c.configuracoes as cliente_configuracoes,
                c.metadados as cliente_metadados,
                c.versao as cliente_versao,
                c.ultima_mensagem_id,
                c.ultima_mensagem_texto,
                c.ultima_mensagem_data,
                c.ultima_mensagem_direcao,
                c.total_mensagens,
                c.mensagens_nao_lidas,
                c.status_chat,
                c.ultima_atividade_chat,
                c.prioridade_chat,
                c.tags_chat,
                c.observacoes_chat,
                c.configuracoes_chat,
                c.metadados_chat,
                c.versao_chat,
                c.ultima_mensagem_id_chat,
                c.ultima_mensagem_texto_chat,
                c.ultima_mensagem_data_chat,
                c.ultima_mensagem_direcao_chat,
                c.total_mensagens_chat,
                c.mensagens_nao_lidas_chat,
                c.status_chat_chat,
                c.ultima_atividade_chat_chat,
                c.prioridade_chat_chat,
                c.tags_chat_chat,
                c.observacoes_chat_chat,
                c.configuracoes_chat_chat,
                c.metadados_chat_chat,
                c.versao_chat_chat
            FROM clientes c
            WHERE c.id IN (
                SELECT DISTINCT cliente_id 
                FROM mensagens_comunicacao 
                WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY data_hora DESC
            )
            ORDER BY c.ultima_atividade DESC, c.id DESC
            LIMIT 50";
    
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
