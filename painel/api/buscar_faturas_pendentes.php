<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

$cliente_id = intval($_GET['cliente_id'] ?? 0);

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não informado']);
    exit;
}

try {
    // Buscar faturas pendentes do cliente
    $sql = "SELECT 
                cob.id,
                cob.valor,
                cob.status,
                cob.vencimento,
                DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                cob.url_fatura,
                cob.descricao
            FROM cobrancas cob
            WHERE cob.cliente_id = $cliente_id
            AND cob.status IN ('PENDING', 'OVERDUE')
            ORDER BY cob.vencimento ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar faturas pendentes: " . $mysqli->error);
    }

    $faturas = [];
    while ($row = $result->fetch_assoc()) {
        $faturas[] = [
            'id' => $row['id'],
            'valor' => $row['valor'],
            'status' => $row['status'],
            'vencimento' => $row['vencimento'],
            'vencimento_formatado' => $row['vencimento_formatado'],
            'url_fatura' => $row['url_fatura'],
            'descricao' => $row['descricao']
        ];
    }

    echo json_encode([
        'success' => true,
        'faturas' => $faturas,
        'total' => count($faturas),
        'cliente_id' => $cliente_id
    ]);

} catch (Exception $e) {
    error_log("Erro ao buscar faturas pendentes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'faturas' => []
    ]);
}
?> 