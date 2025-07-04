<?php
require_once '../painel/config.php';
require_once '../painel/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$canal_id = intval($data['canal_id'] ?? 0);
$cliente_id = intval($data['cliente_id'] ?? 0);
$mensagem = trim($data['mensagem'] ?? '');
if (!$canal_id || !$cliente_id || !$mensagem) {
    echo json_encode(['success'=>false,'error'=>'Dados incompletos']); exit;
}
$mensagem_escaped = $mysqli->real_escape_string($mensagem);
$data_hora = date('Y-m-d H:i:s');
$sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES ($canal_id, $cliente_id, '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'pendente')";
if ($mysqli->query($sql)) {
    // Aqui você pode disparar o envio real via Node.js (ex: via API REST)
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>$mysqli->error]);
} 