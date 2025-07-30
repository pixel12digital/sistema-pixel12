<?php
/**
 * API para Listar Conversas Fechadas
 * 
 * Endpoint: GET /api/conversas_fechadas.php
 * Retorna: JSON com lista de conversas fechadas
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

try {
    // Buscar conversas fechadas (últimas mensagens de cada cliente com status fechada)
    $sql = "SELECT 
                c.id as cliente_id,
                c.nome,
                c.contact_name,
                m.data_hora as ultima_mensagem,
                m.mensagem as ultima_mensagem_texto,
                m.direcao as ultima_direcao,
                COUNT(m2.id) as total_mensagens
            FROM clientes c
            INNER JOIN mensagens_comunicacao m ON c.id = m.cliente_id
            LEFT JOIN mensagens_comunicacao m2 ON c.id = m2.cliente_id
            WHERE m.status_conversa = 'fechada'
            AND m.data_hora = (
                SELECT MAX(data_hora) 
                FROM mensagens_comunicacao 
                WHERE cliente_id = c.id 
                AND status_conversa = 'fechada'
            )
            GROUP BY c.id, c.nome, c.contact_name, m.data_hora, m.mensagem, m.direcao
            ORDER BY m.data_hora DESC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro na consulta: " . $mysqli->error);
    }
    
    $conversas = [];
    while ($row = $result->fetch_assoc()) {
        $conversas[] = [
            'cliente_id' => $row['cliente_id'],
            'nome' => $row['contact_name'] ?: $row['nome'],
            'ultima_mensagem' => $row['ultima_mensagem'],
            'ultima_mensagem_texto' => $row['ultima_mensagem_texto'],
            'ultima_direcao' => $row['ultima_direcao'],
            'total_mensagens' => $row['total_mensagens']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'conversas' => $conversas,
        'total' => count($conversas)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?>