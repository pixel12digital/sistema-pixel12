<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$asaas_payment_id = $input['asaas_payment_id'] ?? null;
$cobranca_id = $input['cobranca_id'] ?? null;

if (!$asaas_payment_id || !$cobranca_id) {
    echo json_encode(['success' => false, 'error' => 'Dados insuficientes para operação.']);
    exit;
}

// 1. Marcar como recebida no Asaas
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.asaas.com/api/v3/payments/$asaas_payment_id/receiveInCash");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "access_token: " . ASAAS_API_KEY
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['success' => false, 'error' => 'Erro ao marcar como recebida no Asaas: ' . $response]);
    exit;
}

// 2. Atualizar banco local
$data_pagamento = date('Y-m-d');
$stmt = $mysqli->prepare("UPDATE cobrancas SET status = 'RECEIVED', data_pagamento = ? WHERE id = ?");
$stmt->bind_param('si', $data_pagamento, $cobranca_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao atualizar cobrança local: ' . $stmt->error]);
}
$stmt->close(); 