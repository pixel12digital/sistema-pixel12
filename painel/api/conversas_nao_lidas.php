<?php
// Garantir que nenhum output seja enviado antes do JSON
ob_start();

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=30');

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
    
    // Buscar conversas com mensagens não lidas
    $conversas_nao_lidas = [];
    $total_global = 0;
    
    // Se cache_manager estiver disponível, usar cache
    if (function_exists('cache_remember')) {
        try {
            $conversas_nao_lidas = cache_remember("conversas_nao_lidas", function() use ($mysqli) {
                return buscar_conversas_nao_lidas_diretamente($mysqli);
            }, 30);
            
            $total_global = cache_remember("total_mensagens_nao_lidas", function() use ($mysqli) {
                return contar_total_nao_lidas($mysqli);
            }, 30);
        } catch (Exception $e) {
            error_log("Erro no cache: " . $e->getMessage());
            // Fallback para query direta
            $conversas_nao_lidas = buscar_conversas_nao_lidas_diretamente($mysqli);
            $total_global = contar_total_nao_lidas($mysqli);
        }
    } else {
        // Fallback para query direta
        $conversas_nao_lidas = buscar_conversas_nao_lidas_diretamente($mysqli);
        $total_global = contar_total_nao_lidas($mysqli);
    }
    
    // Limpar qualquer output anterior
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'conversas' => $conversas_nao_lidas,
        'total_global' => $total_global,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("Erro ao buscar conversas não lidas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'conversas' => [],
        'total_global' => 0,
        'timestamp' => time()
    ]);
} catch (Error $e) {
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("Erro fatal ao buscar conversas não lidas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'conversas' => [],
        'total_global' => 0,
        'timestamp' => time()
    ]);
}

// Função para buscar conversas não lidas diretamente
function buscar_conversas_nao_lidas_diretamente($mysqli) {
    $sql = "SELECT DISTINCT
                c.id as cliente_id,
                c.nome,
                c.celular,
                c.telefone,
                ch.nome_exibicao as canal_nome,
                COUNT(mc.id) as total_nao_lidas,
                MAX(mc.data_hora) as ultima_nao_lida
            FROM mensagens_comunicacao mc
            INNER JOIN clientes c ON mc.cliente_id = c.id
            LEFT JOIN canais_comunicacao ch ON mc.canal_id = ch.id
            WHERE mc.direcao = 'recebido' 
            AND mc.status != 'lido'
            AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY c.id, c.nome, c.celular, c.telefone, ch.nome_exibicao
            ORDER BY ultima_nao_lida DESC
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

// Função para contar total de mensagens não lidas
function contar_total_nao_lidas($mysqli) {
    // 🚀 CORREÇÃO: Usar a mesma lógica da busca de conversas
    // Só contar mensagens de clientes válidos para manter coerência
    $sql = "SELECT COUNT(DISTINCT c.id) as total 
            FROM mensagens_comunicacao mc
            INNER JOIN clientes c ON mc.cliente_id = c.id
            WHERE mc.direcao = 'recebido' 
            AND mc.status != 'lido'
            AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    
    return intval($row['total']);
}

// Garantir que nada mais seja enviado
ob_end_flush();
?> 