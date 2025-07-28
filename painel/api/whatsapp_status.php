<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
$identificador = isset($_GET['identificador']) ? $mysqli->real_escape_string($_GET['identificador']) : '';
if (!$identificador) {
    echo json_encode(['status' => 'erro', 'msg' => 'Identificador não informado']);
    exit;
}
$res = $mysqli->query("SELECT status FROM canais_comunicacao WHERE identificador = '$identificador' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['status' => 'nao_encontrado']);
}
$mysqli->close(); 