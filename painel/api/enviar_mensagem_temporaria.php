<?php
/**
 * API PARA ENVIAR MENSAGENS TEMPORÁRIAS
 * 
 * Salva mensagens localmente quando o banco está indisponível
 */

header('Content-Type: application/json');

$cliente_id = $_POST['cliente_id'] ?? null;
$mensagem = $_POST['mensagem'] ?? '';
$canal_id = $_POST['canal_id'] ?? 36;

if (!$cliente_id || !$mensagem) {
    echo json_encode([
        'success' => false,
        'error' => 'Dados incompletos'
    ]);
    exit;
}

// Verificar se há arquivo de mensagens temporárias
$mensagens_file = __DIR__ . '/../../logs/mensagens_temporarias.json';

if (file_exists($mensagens_file)) {
    $mensagens = json_decode(file_get_contents($mensagens_file), true);
} else {
    $mensagens = [];
}

// Criar nova mensagem
$nova_mensagem = [
    'id' => uniqid(),
    'cliente_id' => $cliente_id,
    'cliente_nome' => 'Cliente ' . $cliente_id,
    'mensagem' => $mensagem,
    'direcao' => 'enviado',
    'data_hora' => date('Y-m-d H:i:s'),
    'status' => 'enviado',
    'tipo' => 'texto',
    'canal_id' => $canal_id,
    'canal_nome' => 'Canal'
];

// Adicionar à lista
$mensagens[] = $nova_mensagem;

// Salvar no arquivo
if (file_put_contents($mensagens_file, json_encode($mensagens, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'mensagem_id' => $nova_mensagem['id'],
        'nome' => $nova_mensagem['cliente_nome'],
        'modo' => 'temporario'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao salvar mensagem'
    ]);
}
?> 