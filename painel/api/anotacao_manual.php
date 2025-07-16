<?php
require_once '../config.php';
require_once '../db.php';
header('Content-Type: application/json');

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
if (!$cliente_id || !$mensagem) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
    exit;
}

$data_hora = date('Y-m-d H:i:s');
$tipo = 'anotacao';

// Verifica se existe a coluna 'titulo' na tabela
$tem_titulo = false;
$res = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'titulo'");
if ($res && $res->num_rows > 0) $tem_titulo = true;

if ($tem_titulo) {
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (cliente_id, mensagem, data_hora, tipo, direcao, titulo) VALUES (?, ?, ?, ?, 'enviado', ?)");
    $stmt->bind_param('issss', $cliente_id, $mensagem, $data_hora, $tipo, $titulo);
} else {
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (cliente_id, mensagem, data_hora, tipo, direcao) VALUES (?, ?, ?, ?, 'enviado')");
    $stmt->bind_param('isss', $cliente_id, $mensagem, $data_hora, $tipo);
}
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
} 