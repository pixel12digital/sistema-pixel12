<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Verificar se o cliente_id foi fornecido
    $cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
    
    if ($cliente_id <= 0) {
        throw new Exception('ID do cliente é obrigatório');
    }
    
    // Buscar mensagem agendada para o cliente
    $sql = "SELECT 
                ma.*,
                c.nome as cliente_nome,
                c.celular,
                c.contact_name
            FROM mensagens_agendadas ma
            JOIN clientes c ON ma.cliente_id = c.id
            WHERE ma.cliente_id = ?
            ORDER BY ma.data_agendada ASC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception('Erro na consulta: ' . $mysqli->error);
    }
    
    $mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = [
            'id' => $row['id'],
            'cliente_id' => $row['cliente_id'],
            'cliente_nome' => $row['cliente_nome'],
            'celular' => $row['celular'],
            'contact_name' => $row['contact_name'],
            'mensagem' => $row['mensagem'],
            'tipo' => $row['tipo'],
            'prioridade' => $row['prioridade'],
            'data_agendada' => $row['data_agendada'],
            'status' => $row['status'],
            'observacao' => $row['observacao'],
            'data_criacao' => $row['data_criacao'],
            'data_atualizacao' => $row['data_atualizacao']
        ];
    }
    
    // Buscar também as faturas do cliente para contexto
    $sql_faturas = "SELECT 
                        id,
                        valor,
                        vencimento,
                        status,
                        DATEDIFF(CURDATE(), vencimento) as dias_vencido
                    FROM cobrancas 
                    WHERE cliente_id = ? 
                    AND status IN ('PENDING', 'OVERDUE')
                    AND vencimento < CURDATE()
                    ORDER BY vencimento ASC";
    
    $stmt_faturas = $mysqli->prepare($sql_faturas);
    $stmt_faturas->bind_param('i', $cliente_id);
    $stmt_faturas->execute();
    $result_faturas = $stmt_faturas->get_result();
    
    $faturas = [];
    while ($fatura = $result_faturas->fetch_assoc()) {
        $faturas[] = [
            'id' => $fatura['id'],
            'valor' => $fatura['valor'],
            'vencimento' => $fatura['vencimento'],
            'dias_vencido' => $fatura['dias_vencido'],
            'status' => $fatura['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens,
        'faturas' => $faturas,
        'total_mensagens' => count($mensagens),
        'total_faturas' => count($faturas)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 