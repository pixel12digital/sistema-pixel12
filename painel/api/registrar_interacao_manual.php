<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$cobranca_id = isset($_POST['cobranca_id']) ? intval($_POST['cobranca_id']) : null;
$data_hora = isset($_POST['data_hora']) ? $_POST['data_hora'] : '';

if (!$cliente_id || !$data_hora) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$mensagem = 'Interação manual registrada';
$direcao = 'enviado';
$status = 'manual';
$tipo = 'manual';

// Canal: pode ser nulo ou 1 (ajustar se necessário)
$canal_id = 1;

if ($cobranca_id) {
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iiisssss', $canal_id, $cliente_id, $cobranca_id, $mensagem, $tipo, $data_hora, $direcao, $status);
} else {
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iisssss', $canal_id, $cliente_id, $mensagem, $tipo, $data_hora, $direcao, $status);
}
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
} 