<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once '../db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Método inválido']);
  exit;
}
$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$vencimento = isset($_POST['vencimento']) ? $_POST['vencimento'] : '';
if (!$cliente_id || !$vencimento) {
  echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
  exit;
}
// Atualiza todas as mensagens do cliente para o vencimento informado e status 'enviado' do dia atual para 'pendente'
$hoje = date('Y-m-d');
$sql = "UPDATE mensagens_comunicacao SET status = 'pendente' WHERE cliente_id = $cliente_id AND DATE(data_hora) = '$hoje' AND status = 'enviado'";
if ($mysqli->query($sql)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => $mysqli->error]);
} 