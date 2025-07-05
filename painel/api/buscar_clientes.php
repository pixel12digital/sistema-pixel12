<?php
require_once '../config.php';
require_once '../db.php';
header('Content-Type: application/json');
$termo = trim($_GET['termo'] ?? '');
if (strlen($termo) < 3) {
    echo json_encode([]);
    exit;
}
$termo_sql = $mysqli->real_escape_string($termo);
$res = $mysqli->query("SELECT id, nome, email, celular FROM clientes WHERE nome LIKE '%$termo_sql%' OR email LIKE '%$termo_sql%' OR celular LIKE '%$termo_sql%' ORDER BY nome LIMIT 20");
$clientes = [];
while ($cli = $res && $res->num_rows ? $res->fetch_assoc() : null) {
    $clientes[] = $cli;
}
echo json_encode($clientes); 