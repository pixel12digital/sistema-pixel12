<?php
require_once '../config.php';
require_once '../painel/db.php';
header('Content-Type: application/json');
$res = $mysqli->query("SELECT canal_id FROM canais_padrao_funcoes WHERE funcao = 'financeiro' LIMIT 1");
if ($res && ($row = $res->fetch_assoc())) {
    echo json_encode(['canal_id' => intval($row['canal_id'])]);
} else {
    echo json_encode(['canal_id' => null]);
} 