<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não fornecido']);
    exit;
}

try {
    $sql = "SELECT m.*, 
                   c.nome_exibicao as canal_nome,
                   c.porta as canal_porta,
                   c.identificador as canal_identificador,
                   CASE 
                       WHEN m.direcao = 'enviado' THEN 'Você'
                       WHEN m.direcao = 'recebido' THEN c.nome_exibicao
                       ELSE 'Sistema'
                   END as contato_interagiu
            FROM mensagens_comunicacao m
            LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
            WHERE m.cliente_id = ?
            ORDER BY m.data_hora ASC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($msg = $result->fetch_assoc()) {
        $mensagens[] = [
            'id' => $msg['id'],
            'mensagem' => $msg['mensagem'],
            'direcao' => $msg['direcao'],
            'status' => $msg['status'],
            'data_hora' => $msg['data_hora'],
            'anexo' => $msg['anexo'],
            'canal_nome' => $msg['canal_nome'] ?: 'WhatsApp',
            'canal_porta' => $msg['canal_porta'] ?: 3000,
            'canal_identificador' => $msg['canal_identificador'] ?: '',
            'contato_interagiu' => $msg['contato_interagiu'] ?: 'Sistema'
        ];
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens,
        'total' => count($mensagens)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar mensagens: ' . $e->getMessage()
    ]);
}
?> 