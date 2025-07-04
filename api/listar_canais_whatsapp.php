<?php
require_once '../painel/config.php';
require_once '../painel/db.php';
$rs = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status = 'conectado'");
$canais = [];
while($row = $rs->fetch_assoc()) $canais[] = $row;
echo json_encode($canais); 