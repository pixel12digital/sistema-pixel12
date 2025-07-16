<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de cliente inválido']);
    exit;
}

$cliente_id = intval($_GET['id']);

// Buscar dados completos do cliente
$sql = "SELECT * FROM clientes WHERE id = ? LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

if (!$cliente) {
    echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
    exit;
}

// Buscar cobranças do cliente
$cobrancas = [];
$res_cob = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = $cliente_id ORDER BY vencimento DESC");
while ($cob = $res_cob && $res_cob->num_rows ? $res_cob->fetch_assoc() : null) {
    $cobrancas[] = $cob;
}

// Buscar mensagens do cliente
$mensagens = [];
$res_msg = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m LEFT JOIN canais_comunicacao c ON m.canal_id = c.id WHERE m.cliente_id = $cliente_id ORDER BY m.data_hora DESC");
while ($msg = $res_msg && $res_msg->num_rows ? $res_msg->fetch_assoc() : null) {
    $mensagens[] = $msg;
}

// Calcular totais financeiros
$total_pago = $total_aberto = $total_vencido = 0.0;
foreach ($cobrancas as $cob) {
    $valor = floatval($cob['valor']);
    if ($cob['status'] === 'RECEIVED' || $cob['status'] === 'PAID') {
        $total_pago += $valor;
    } elseif ($cob['status'] === 'PENDING' && strtotime($cob['vencimento']) < time()) {
        $total_vencido += $valor;
    } elseif ($cob['status'] === 'PENDING') {
        $total_aberto += $valor;
    }
}

echo json_encode([
    'success' => true,
    'cliente' => $cliente,
    'cobrancas' => $cobrancas,
    'mensagens' => $mensagens,
    'totais' => [
        'pago' => $total_pago,
        'aberto' => $total_aberto,
        'vencido' => $total_vencido
    ]
]);
?> 