<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['from']) || !isset($data['body'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$from = $mysqli->real_escape_string($data['from']);
$body = $mysqli->real_escape_string($data['body']);
$timestamp = isset($data['timestamp']) ? intval($data['timestamp']) : time();

// Tenta encontrar canal pelo identificador (número)
$numero = preg_replace('/\D/', '', $from);
$canal = $mysqli->query("SELECT id FROM canais_comunicacao WHERE identificador LIKE '%$numero%' LIMIT 1")->fetch_assoc();
$canal_id = $canal ? intval($canal['id']) : 1;

// Opcional: tentar encontrar cliente pelo número
$cliente = $mysqli->query("SELECT id FROM clientes WHERE celular LIKE '%$numero%' OR telefone LIKE '%$numero%' LIMIT 1")->fetch_assoc();
$cliente_id = $cliente ? intval($cliente['id']) : null;

$data_hora = date('Y-m-d H:i:s', $timestamp);

$sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$body', 'texto', '$data_hora', 'recebido', 'recebido')";

if ($mysqli->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
} 