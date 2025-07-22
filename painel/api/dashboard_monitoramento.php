<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

try {
    // Buscar estatísticas gerais
    $estatisticas = [];
    
    // Total de clientes monitorados
    $sql = "SELECT COUNT(*) as total FROM clientes_monitoramento WHERE monitorado = 1";
    $result = $mysqli->query($sql);
    $estatisticas['total_monitorados'] = $result->fetch_assoc()['total'];
    
    // Total de cobranças vencidas
    $sql = "SELECT COUNT(*) as total FROM cobrancas 
            WHERE status IN ('PENDING', 'OVERDUE') 
            AND vencimento < CURDATE()";
    $result = $mysqli->query($sql);
    $estatisticas['total_vencidas'] = $result->fetch_assoc()['total'];
    
    // Total de mensagens enviadas hoje
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
            WHERE tipo = 'cobranca_vencida' 
            AND DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    $estatisticas['total_mensagens'] = $result->fetch_assoc()['total'];
    
    // Próxima verificação (baseada na última execução)
    $sql = "SELECT MAX(data_hora) as ultima FROM mensagens_comunicacao 
            WHERE tipo = 'cobranca_vencida'";
    $result = $mysqli->query($sql);
    $ultima = $result->fetch_assoc()['ultima'];
    
    if ($ultima) {
        $proxima = date('Y-m-d H:i:s', strtotime($ultima) + (2 * 60 * 60)); // 2 horas
        $estatisticas['proxima_verificacao'] = date('d/m/Y H:i', strtotime($proxima));
        $estatisticas['ultima_execucao'] = date('d/m/Y H:i', strtotime($ultima));
    } else {
        $estatisticas['proxima_verificacao'] = 'N/A';
        $estatisticas['ultima_execucao'] = 'Nunca';
    }
    
    // Buscar TODOS os clientes em monitoramento (não apenas os com cobranças vencidas)
    $sql = "SELECT DISTINCT 
                c.id,
                c.nome,
                c.celular,
                c.contact_name,
                cm.monitorado,
                COUNT(cob.id) as quantidade_cobrancas,
                SUM(cob.valor) as valor_total,
                SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                MAX(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN DATEDIFF(CURDATE(), cob.vencimento) ELSE 0 END) as dias_vencido,
                (
                    SELECT MAX(data_hora) 
                    FROM mensagens_comunicacao mc 
                    WHERE mc.cliente_id = c.id 
                    AND mc.tipo = 'cobranca_vencida'
                ) as ultima_mensagem
            FROM clientes c
            JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
            WHERE cm.monitorado = 1
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY c.id, c.nome, c.celular, c.contact_name, cm.monitorado
            ORDER BY cobrancas_vencidas DESC, valor_vencido DESC, c.nome ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar clientes monitorados: " . $mysqli->error);
    }
    
    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        // Formatar dados
        $row['valor_total'] = floatval($row['valor_total'] ?? 0);
        $row['valor_vencido'] = floatval($row['valor_vencido'] ?? 0);
        $row['dias_vencido'] = intval($row['dias_vencido'] ?? 0);
        $row['quantidade_cobrancas'] = intval($row['quantidade_cobrancas'] ?? 0);
        $row['cobrancas_vencidas'] = intval($row['cobrancas_vencidas'] ?? 0);
        $row['monitorado'] = boolval($row['monitorado']);
        
        // Formatar última mensagem
        if ($row['ultima_mensagem']) {
            $row['ultima_mensagem'] = date('d/m/Y H:i', strtotime($row['ultima_mensagem']));
        }
        
        $clientes[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'estatisticas' => $estatisticas,
        'clientes' => $clientes,
        'total_clientes' => count($clientes),
        'data_consulta' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Erro no dashboard de monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'estatisticas' => [],
        'clientes' => []
    ]);
}
?> 