<?php
require_once '../config.php';
require_once '../db.php';
header('Content-Type: application/json');
$canais = [];
$res = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY nome_exibicao, id");
if ($res) while ($c = $res->fetch_assoc()) $canais[] = $c;
echo json_encode($canais); 