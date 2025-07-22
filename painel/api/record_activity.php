<?php
/**
 * Registra atividade do usuário para otimização de cache
 */
header('Content-Type: application/json');

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false]);
    exit;
}

$activityFile = sys_get_temp_dir() . "/chat_activity_{$cliente_id}.json";
$activity = [
    'last_activity' => time(),
    'cliente_id' => $cliente_id
];

file_put_contents($activityFile, json_encode($activity));

echo json_encode(['success' => true]);
?> 