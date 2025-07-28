<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Buscar todos os clientes que estão sendo monitorados
    $sql = "SELECT 
                cm.cliente_id,
                cm.monitorado,
                c.nome as cliente_nome,
                c.celular,
                COUNT(cob.id) as total_cobrancas,
                SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                MAX(mc.data_hora) as ultima_mensagem
            FROM clientes c
            LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
            LEFT JOIN mensagens_comunicacao mc ON c.id = mc.cliente_id AND mc.tipo = 'cobranca_vencida'
            WHERE cm.monitorado = 1
            GROUP BY cm.cliente_id, cm.monitorado, c.nome, c.celular";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar dados de monitoramento: " . $mysqli->error);
    }
    
    $dados_monitoramento = [];
    while ($row = $result->fetch_assoc()) {
        $cliente_id = $row['cliente_id'];
        // Buscar próximas ações agendadas (mensagens pendentes para o cliente)
        $proximas_acoes = [];
        $sql_agendadas = "SELECT tipo, data_agendada, status FROM mensagens_agendadas WHERE cliente_id = $cliente_id AND status IN ('agendada','pendente') ORDER BY data_agendada ASC LIMIT 3";
        $res_agendadas = $mysqli->query($sql_agendadas);
        if ($res_agendadas) {
            while ($ag = $res_agendadas->fetch_assoc()) {
                $proximas_acoes[] = [
                    'tipo' => $ag['tipo'],
                    'data_agendada' => $ag['data_agendada'],
                    'status' => $ag['status']
                ];
            }
        }
        // Buscar histórico recente de envios (últimos 3)
        $historico_envios = [];
        $sql_envios = "SELECT tipo, canal_id, data_hora, status, direcao FROM mensagens_comunicacao WHERE cliente_id = $cliente_id ORDER BY data_hora DESC LIMIT 3";
        $res_envios = $mysqli->query($sql_envios);
        if ($res_envios) {
            while ($env = $res_envios->fetch_assoc()) {
                $historico_envios[] = [
                    'tipo' => $env['tipo'],
                    'canal_id' => $env['canal_id'],
                    'data_hora' => $env['data_hora'],
                    'status' => $env['status'],
                    'direcao' => $env['direcao']
                ];
            }
        }
        $dados_monitoramento[$cliente_id] = [
            'monitorado' => boolval($row['monitorado']),
            'cliente_nome' => $row['cliente_nome'],
            'celular' => $row['celular'],
            'total_cobrancas' => intval($row['total_cobrancas']),
            'valor_vencido' => floatval($row['valor_vencido']),
            'cobrancas_vencidas' => intval($row['cobrancas_vencidas']),
            'ultima_mensagem' => $row['ultima_mensagem'] ? date('d/m/Y H:i', strtotime($row['ultima_mensagem'])) : null,
            'proximas_acoes' => $proximas_acoes,
            'historico_envios' => $historico_envios
        ];
    }
    
    // Buscar estatísticas gerais de monitoramento
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
    
    echo json_encode([
        'success' => true,
        'dados_monitoramento' => $dados_monitoramento,
        'estatisticas' => [
            'total_monitorados' => intval($estatisticas['total_monitorados']),
            'clientes_com_vencidas' => intval($estatisticas['clientes_com_vencidas']),
            'valor_total_vencido' => floatval($estatisticas['valor_total_vencido']),
            'mensagens_hoje' => intval($estatisticas['mensagens_hoje'])
        ]
    ]);

} catch (Exception $e) {
    error_log("Erro ao buscar dados de monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'dados_monitoramento' => [],
        'estatisticas' => []
    ]);
}
?> 