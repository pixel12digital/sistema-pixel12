<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

try {
    // Obter período da requisição
    $periodo = $_GET['periodo'] ?? 'mes';
    $data_inicio = null;
    $data_fim = null;

    // Definir datas baseadas no período
    switch ($periodo) {
        case 'hoje':
            $data_inicio = date('Y-m-d');
            $data_fim = date('Y-m-d');
            break;
        case 'semana':
            $data_inicio = date('Y-m-d', strtotime('-7 days'));
            $data_fim = date('Y-m-d');
            break;
        case 'mes':
            $data_inicio = date('Y-m-d', strtotime('-30 days'));
            $data_fim = date('Y-m-d');
            break;
        case 'trimestre':
            $data_inicio = date('Y-m-d', strtotime('-90 days'));
            $data_fim = date('Y-m-d');
            break;
        case 'ano':
            $data_inicio = date('Y-m-d', strtotime('-365 days'));
            $data_fim = date('Y-m-d');
            break;
        case 'personalizado':
            $data_inicio = $_GET['inicio'] ?? date('Y-m-d', strtotime('-30 days'));
            $data_fim = $_GET['fim'] ?? date('Y-m-d');
            break;
        default:
            $data_inicio = date('Y-m-d', strtotime('-30 days'));
            $data_fim = date('Y-m-d');
    }

    // Buscar estatísticas gerais
    $estatisticas = buscarEstatisticas($mysqli, $data_inicio, $data_fim);
    
    // Buscar dados para gráficos
    $graficos = buscarDadosGraficos($mysqli, $data_inicio, $data_fim);
    
    // Buscar top clientes
    $top_clientes = buscarTopClientes($mysqli, $data_inicio, $data_fim);
    
    // Buscar dados de monitoramento
    $monitoramento = buscarDadosMonitoramento($mysqli, $data_inicio, $data_fim);

    echo json_encode([
        'success' => true,
        'estatisticas' => $estatisticas,
        'graficos' => $graficos,
        'top_clientes' => $top_clientes,
        'monitoramento' => $monitoramento,
        'periodo' => [
            'inicio' => $data_inicio,
            'fim' => $data_fim
        ]
    ]);

} catch (Exception $e) {
    error_log("Erro nos relatórios financeiros: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Busca estatísticas gerais
 */
function buscarEstatisticas($mysqli, $data_inicio, $data_fim) {
    $sql = "SELECT 
                COUNT(*) as total_faturas,
                COUNT(CASE WHEN status IN ('PENDING', 'OVERDUE') THEN 1 END) as total_vencidas,
                COUNT(CASE WHEN status IN ('RECEIVED', 'CONFIRMED') THEN 1 END) as total_recebidas,
                SUM(valor) as valor_total,
                SUM(CASE WHEN status IN ('PENDING', 'OVERDUE') THEN valor ELSE 0 END) as valor_vencido,
                SUM(CASE WHEN status IN ('RECEIVED', 'CONFIRMED') THEN valor ELSE 0 END) as valor_recebido
            FROM cobrancas 
            WHERE DATE(vencimento) BETWEEN '$data_inicio' AND '$data_fim'";
    
    $result = $mysqli->query($sql);
    $data = $result->fetch_assoc();
    
    // Calcular taxa de efetividade
    $valor_total = floatval($data['valor_total']);
    $valor_recebido = floatval($data['valor_recebido']);
    $taxa_efetividade = $valor_total > 0 ? ($valor_recebido / $valor_total) * 100 : 0;
    
    // Buscar total de clientes monitorados
    $sql_monitorados = "SELECT COUNT(*) as total_monitorados FROM clientes_monitoramento WHERE monitorado = 1";
    $result_monitorados = $mysqli->query($sql_monitorados);
    $monitorados = $result_monitorados->fetch_assoc();
    
    return [
        'total_faturas' => intval($data['total_faturas']),
        'total_vencidas' => intval($data['total_vencidas']),
        'total_recebidas' => intval($data['total_recebidas']),
        'total_monitorados' => intval($monitorados['total_monitorados']),
        'valor_total' => floatval($data['valor_total']),
        'valor_vencido' => floatval($data['valor_vencido']),
        'valor_recebido' => floatval($data['valor_recebido']),
        'taxa_efetividade' => $taxa_efetividade
    ];
}

/**
 * Busca dados para gráficos
 */
function buscarDadosGraficos($mysqli, $data_inicio, $data_fim) {
    // Gráfico de status
    $sql_status = "SELECT 
                    status,
                    COUNT(*) as quantidade,
                    SUM(valor) as valor_total
                   FROM cobrancas 
                   WHERE DATE(vencimento) BETWEEN '$data_inicio' AND '$data_fim'
                   GROUP BY status";
    
    $result_status = $mysqli->query($sql_status);
    $status_data = [];
    $total_faturas = 0;
    
    while ($row = $result_status->fetch_assoc()) {
        $total_faturas += $row['quantidade'];
        $status_data[] = $row;
    }
    
    // Calcular percentuais e cores
    $status_cores = [
        'PENDING' => '#3b82f6',
        'OVERDUE' => '#ef4444',
        'RECEIVED' => '#10b981',
        'CONFIRMED' => '#059669'
    ];
    
    $status_grafico = [];
    foreach ($status_data as $status) {
        $percentual = $total_faturas > 0 ? ($status['quantidade'] / $total_faturas) * 100 : 0;
        $status_grafico[] = [
            'status' => $status['status'],
            'quantidade' => intval($status['quantidade']),
            'valor_total' => floatval($status['valor_total']),
            'percentual' => round($percentual, 1),
            'cor' => $status_cores[$status['status']] ?? '#6b7280'
        ];
    }
    
    // Gráfico de evolução mensal (últimos 6 meses)
    $sql_evolucao = "SELECT 
                        DATE_FORMAT(vencimento, '%Y-%m') as mes,
                        COUNT(*) as quantidade,
                        SUM(valor) as valor
                     FROM cobrancas 
                     WHERE vencimento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(vencimento, '%Y-%m')
                     ORDER BY mes";
    
    $result_evolucao = $mysqli->query($sql_evolucao);
    $evolucao_data = [];
    
    while ($row = $result_evolucao->fetch_assoc()) {
        $evolucao_data[] = [
            'mes' => date('M/Y', strtotime($row['mes'] . '-01')),
            'quantidade' => intval($row['quantidade']),
            'valor' => floatval($row['valor'])
        ];
    }
    
    return [
        'status' => $status_grafico,
        'evolucao' => $evolucao_data
    ];
}

/**
 * Busca top clientes
 */
function buscarTopClientes($mysqli, $data_inicio, $data_fim) {
    $sql = "SELECT 
                c.id,
                c.nome,
                COUNT(cob.id) as total_faturas,
                SUM(cob.valor) as valor_total,
                SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') THEN cob.valor ELSE 0 END) as valor_vencido,
                MAX(cob.status) as status,
                cm.monitorado
            FROM clientes c
            LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
            LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            WHERE DATE(cob.vencimento) BETWEEN '$data_inicio' AND '$data_fim'
            GROUP BY c.id, c.nome, cm.monitorado
            HAVING total_faturas > 0
            ORDER BY valor_total DESC
            LIMIT 10";
    
    $result = $mysqli->query($sql);
    $clientes = [];
    
    while ($row = $result->fetch_assoc()) {
        $clientes[] = [
            'id' => intval($row['id']),
            'nome' => $row['nome'],
            'total_faturas' => intval($row['total_faturas']),
            'valor_total' => floatval($row['valor_total']),
            'valor_vencido' => floatval($row['valor_vencido']),
            'status' => $row['status'],
            'monitorado' => boolval($row['monitorado'])
        ];
    }
    
    return $clientes;
}

/**
 * Busca dados de monitoramento
 */
function buscarDadosMonitoramento($mysqli, $data_inicio, $data_fim) {
    // Clientes monitorados
    $sql_monitorados = "SELECT COUNT(*) as clientes_monitorados FROM clientes_monitoramento WHERE monitorado = 1";
    $result_monitorados = $mysqli->query($sql_monitorados);
    $monitorados = $result_monitorados->fetch_assoc();
    
    // Mensagens enviadas no período
    $sql_mensagens = "SELECT COUNT(*) as mensagens_enviadas 
                      FROM mensagens_comunicacao 
                      WHERE tipo = 'cobranca_vencida' 
                      AND DATE(data_hora) BETWEEN '$data_inicio' AND '$data_fim'";
    $result_mensagens = $mysqli->query($sql_mensagens);
    $mensagens = $result_mensagens->fetch_assoc();
    
    // Taxa de resposta (simulada - seria calculada baseada em respostas dos clientes)
    $taxa_resposta = 0;
    if ($mensagens['mensagens_enviadas'] > 0) {
        // Simular taxa de resposta baseada em mensagens com resposta
        $sql_respostas = "SELECT COUNT(*) as respostas 
                          FROM mensagens_comunicacao 
                          WHERE tipo = 'resposta_cliente' 
                          AND DATE(data_hora) BETWEEN '$data_inicio' AND '$data_fim'";
        $result_respostas = $mysqli->query($sql_respostas);
        $respostas = $result_respostas->fetch_assoc();
        $taxa_resposta = ($respostas['respostas'] / $mensagens['mensagens_enviadas']) * 100;
    }
    
    return [
        'clientes_monitorados' => intval($monitorados['clientes_monitorados']),
        'mensagens_enviadas' => intval($mensagens['mensagens_enviadas']),
        'taxa_resposta' => round($taxa_resposta, 1)
    ];
}
?> 