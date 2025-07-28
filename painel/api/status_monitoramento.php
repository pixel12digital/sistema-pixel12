<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Buscar informações de status do sistema de monitoramento
    
    // Última execução de monitoramento
    $sql_ultima = "SELECT MAX(data_hora) as ultima_execucao 
                   FROM mensagens_comunicacao 
                   WHERE tipo = 'cobranca_vencida'";
    $result_ultima = $mysqli->query($sql_ultima);
    $ultima_execucao = null;
    
    if ($result_ultima && $row = $result_ultima->fetch_assoc()) {
        $ultima_execucao = $row['ultima_execucao'];
    }
    
    // Calcular próxima verificação (2 horas após a última)
    $proxima_verificacao = null;
    if ($ultima_execucao) {
        $proxima_timestamp = strtotime($ultima_execucao) + (2 * 60 * 60); // 2 horas
        $proxima_verificacao = date('d/m/Y H:i', $proxima_timestamp);
        $ultima_execucao_formatada = date('d/m/Y H:i', strtotime($ultima_execucao));
    } else {
        $ultima_execucao_formatada = 'Nunca';
        $proxima_verificacao = 'Aguardando primeira execução';
    }
    
    // Estatísticas atuais
    $sql_stats = "SELECT 
                    COUNT(DISTINCT cm.cliente_id) as total_monitorados,
                    COUNT(DISTINCT CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN c.id END) as clientes_com_vencidas,
                    SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_total_vencido,
                    COUNT(CASE WHEN mc.tipo = 'cobranca_vencida' AND DATE(mc.data_hora) = CURDATE() THEN 1 END) as mensagens_hoje
                  FROM clientes c
                  LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id AND cm.monitorado = 1
                  LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                  LEFT JOIN mensagens_comunicacao mc ON c.id = mc.cliente_id";
    
    $result_stats = $mysqli->query($sql_stats);
    $estatisticas = $result_stats->fetch_assoc();
    
    // Status do sistema de monitoramento
    $status_sistema = 'ativo';
    if (!$ultima_execucao) {
        $status_sistema = 'aguardando_primeira_execucao';
    } elseif (strtotime($ultima_execucao) < (time() - (6 * 60 * 60))) {
        // Se última execução foi há mais de 6 horas
        $status_sistema = 'inativo';
    }
    
    // Verificar se há problemas
    $problemas = [];
    if (intval($estatisticas['total_monitorados']) === 0) {
        $problemas[] = 'Nenhum cliente está sendo monitorado';
    }
    
    // Status da API do Asaas
    $status_api_asaas = 'desconhecido';
    if (file_exists('../logs/status_chave_atual.json')) {
        $status_chave = json_decode(file_get_contents('../logs/status_chave_atual.json'), true);
        if ($status_chave && isset($status_chave['valida'])) {
            $status_api_asaas = $status_chave['valida'] ? 'valida' : 'invalida';
            if (!$status_chave['valida']) {
                $problemas[] = 'Chave da API do Asaas está inválida';
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'status_sistema' => $status_sistema,
        'ultima_execucao' => $ultima_execucao_formatada,
        'proxima_verificacao' => $proxima_verificacao,
        'status_api_asaas' => $status_api_asaas,
        'estatisticas' => [
            'total_monitorados' => intval($estatisticas['total_monitorados']),
            'clientes_com_vencidas' => intval($estatisticas['clientes_com_vencidas']),
            'valor_total_vencido' => floatval($estatisticas['valor_total_vencido']),
            'mensagens_hoje' => intval($estatisticas['mensagens_hoje'])
        ],
        'problemas' => $problemas,
        'tem_problemas' => count($problemas) > 0,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Erro ao verificar status do monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'status_sistema' => 'erro',
        'ultima_execucao' => 'Erro',
        'proxima_verificacao' => 'Erro'
    ]);
}
?> 