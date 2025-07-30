<?php
// Garantir que nenhum output seja enviado antes do JSON
ob_start();

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=5');

try {
    // Incluir arquivos necessários
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../db.php';
    
    // Verificar se o cache_manager existe antes de incluir
    $cache_manager_path = __DIR__ . '/../cache_manager.php';
    if (file_exists($cache_manager_path)) {
        require_once $cache_manager_path;
    }
    
    // Verificar conexão com banco
    if (!$mysqli || !$mysqli->ping()) {
        throw new Exception('Conexão com banco de dados indisponível');
    }
    
    // Buscar conversas
    $conversas = [];
    
    // Se cache_manager estiver disponível, usar cache
    if (function_exists('cache_conversas')) {
        try {
            $conversas = cache_conversas($mysqli);
        } catch (Exception $e) {
            error_log("Erro no cache_conversas: " . $e->getMessage());
            // Fallback para query direta
            $conversas = buscar_conversas_diretamente($mysqli);
        }
    } else {
        // Fallback para query direta
        $conversas = buscar_conversas_diretamente($mysqli);
    }
    
    // Limpar qualquer output anterior
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'conversas' => $conversas,
        'total' => count($conversas),
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("Erro ao buscar conversas recentes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'conversas' => [],
        'total' => 0,
        'timestamp' => time()
    ]);
} catch (Error $e) {
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("Erro fatal ao buscar conversas recentes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'conversas' => [],
        'total' => 0,
        'timestamp' => time()
    ]);
}

// Função fallback para buscar conversas diretamente
function buscar_conversas_diretamente($mysqli) {
    $sql = "SELECT 
                c.id as cliente_id,
                c.nome,
                c.celular,
                'WhatsApp' as canal_nome,
                COALESCE(ultima.mensagem, 'Sem mensagens') as ultima_mensagem,
                COALESCE(ultima.data_hora, c.data_criacao) as ultima_data,
                COALESCE(nao_lidas.total, 0) as mensagens_nao_lidas
            FROM clientes c
            LEFT JOIN (
                SELECT 
                    cliente_id,
                    mensagem,
                    data_hora,
                    ROW_NUMBER() OVER (PARTITION BY cliente_id ORDER BY data_hora DESC) as rn
                FROM mensagens_comunicacao 
                WHERE cliente_id IS NOT NULL
            ) ultima ON c.id = ultima.cliente_id AND ultima.rn = 1
            LEFT JOIN (
                SELECT 
                    cliente_id,
                    COUNT(*) as total
                FROM mensagens_comunicacao 
                WHERE direcao = 'recebido' 
                AND status != 'lido'
                AND cliente_id IS NOT NULL
                GROUP BY cliente_id
            ) nao_lidas ON c.id = nao_lidas.cliente_id
            WHERE ultima.cliente_id IS NOT NULL
            AND c.id NOT IN (
                SELECT DISTINCT m.cliente_id 
                FROM mensagens_comunicacao m
                INNER JOIN (
                    SELECT cliente_id, MAX(data_hora) as ultima_data
                    FROM mensagens_comunicacao 
                    WHERE cliente_id IS NOT NULL
                    GROUP BY cliente_id
                ) ultima_msg ON m.cliente_id = ultima_msg.cliente_id 
                AND m.data_hora = ultima_msg.ultima_data
                WHERE m.status_conversa = 'fechada' 
                AND m.cliente_id IS NOT NULL
            )
            ORDER BY ultima.data_hora DESC
            LIMIT 50";
    
    $result = $mysqli->query($sql);
    $conversas = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $conversas[] = $row;
        }
    }
    
    return $conversas;
}

// Garantir que nada mais seja enviado
ob_end_flush();
?> 