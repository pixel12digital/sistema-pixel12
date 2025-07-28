<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Método inválido']);
  exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$permitidos = ['pendente','enviado','erro'];
if (!$id || !in_array($status, $permitidos)) {
  error_log("Corrigir status: id recebido = $id, status = $status");
  echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
  exit;
}
// Verifica se o ID existe
$res = $mysqli->query("SELECT id FROM mensagens_comunicacao WHERE id = $id");
if (!$res || $res->num_rows == 0) {
  echo json_encode(['success' => false, 'error' => 'Mensagem não encontrada']);
  exit;
}
$sql = "UPDATE mensagens_comunicacao SET status = '" . $mysqli->real_escape_string($status) . "' WHERE id = $id LIMIT 1";
if ($mysqli->query($sql)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => $mysqli->error]);
} 