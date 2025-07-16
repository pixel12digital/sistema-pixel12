<?php
require_once __DIR__ . '/../painel/db.php';
header('Content-Type: application/json');
$res = $mysqli->query("SELECT id, nome, descricao, valor, periodicidade, ativo FROM planos_assinatura ORDER BY nome");
$planos = [];
while ($row = $res->fetch_assoc()) {
    $planos[] = $row;
}
echo json_encode($planos); 