<?php
require_once '../painel/config.php';
require_once '../painel/db.php';
$cliente_id = intval($_GET['cliente_id'] ?? 0);
$canal_id = intval($_GET['canal_id'] ?? 0);
$rs = $mysqli->query("SELECT mensagem, data_hora, direcao FROM mensagens_comunicacao WHERE cliente_id = $cliente_id AND canal_id = $canal_id ORDER BY data_hora ASC");
$msgs = [];
while($row = $rs->fetch_assoc()) $msgs[] = $row;
echo json_encode($msgs); 