<?php
require_once __DIR__ . '/../config.php';
require_once '../db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Método inválido']);
  exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$contact_name = isset($_POST['contact_name']) ? trim($_POST['contact_name']) : '';
if (!$id) {
  echo json_encode(['success' => false, 'error' => 'ID inválido']);
  exit;
}
$sql = "UPDATE clientes SET contact_name = '" . $mysqli->real_escape_string($contact_name) . "' WHERE id = $id LIMIT 1";
if ($mysqli->query($sql)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => $mysqli->error]);
} 