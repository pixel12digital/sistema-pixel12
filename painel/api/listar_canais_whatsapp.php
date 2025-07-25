<?php
require_once __DIR__ . '/../../config.php';
require_once '../db.php';
header('Content-Type: application/json');

// Buscar apenas canais WhatsApp conectados
$sql = "SELECT id, identificador AS numero, nome_exibicao AS nome, status FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status = 'conectado'";
$result = $mysqli->query($sql);
$canais = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $canais[] = $row;
    }
    echo json_encode(['success' => true, 'canais' => $canais]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao buscar canais: ' . $mysqli->error]);
} 