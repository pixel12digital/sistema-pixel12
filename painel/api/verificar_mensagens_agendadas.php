<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Buscar mensagens agendadas para o cliente Eduardo (ID: 274)
    $cliente_id = 274;
    
    $sql = "SELECT 
                ma.id,
                ma.cliente_id,
                ma.mensagem,
                ma.tipo,
                ma.prioridade,
                ma.data_agendada,
                ma.status,
                ma.data_criacao,
                c.nome as cliente_nome
            FROM mensagens_agendadas ma
            JOIN clientes c ON ma.cliente_id = c.id
            WHERE ma.cliente_id = $cliente_id
            ORDER BY ma.data_agendada ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar mensagens agendadas: " . $mysqli->error);
    }
    
    $mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = [
            'id' => $row['id'],
            'cliente_id' => $row['cliente_id'],
            'cliente_nome' => $row['cliente_nome'],
            'tipo' => $row['tipo'],
            'prioridade' => $row['prioridade'],
            'data_agendada' => $row['data_agendada'],
            'status' => $row['status'],
            'data_criacao' => $row['data_criacao'],
            'mensagem_preview' => substr($row['mensagem'], 0, 100) . '...'
        ];
    }
    
    // Buscar também informações do cliente
    $sql_cliente = "SELECT 
                        c.id,
                        c.nome,
                        c.celular,
                        cm.monitorado,
                        COUNT(cob.id) as total_cobrancas,
                        COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                        SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido
                    FROM clientes c
                    LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
                    LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                    WHERE c.id = $cliente_id
                    GROUP BY c.id, c.nome, c.celular, cm.monitorado";
    
    $result_cliente = $mysqli->query($sql_cliente);
    $cliente_info = $result_cliente ? $result_cliente->fetch_assoc() : null;
    
    echo json_encode([
        'success' => true,
        'cliente' => $cliente_info,
        'mensagens_agendadas' => $mensagens,
        'total_mensagens' => count($mensagens)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 