<?php
/**
 * API PARA MENSAGENS TEMPORÁRIAS
 * 
 * Carrega mensagens de um cliente específico do arquivo local
 */

header('Content-Type: application/json');

$cliente_id = $_GET['cliente_id'] ?? null;

if (!$cliente_id) {
    echo json_encode([
        'success' => false,
        'error' => 'ID do cliente não fornecido'
    ]);
    exit;
}

// Verificar se há arquivo de mensagens temporárias
$mensagens_file = __DIR__ . '/../../logs/mensagens_temporarias.json';

if (file_exists($mensagens_file)) {
    $todas_mensagens = json_decode(file_get_contents($mensagens_file), true);
} else {
    $todas_mensagens = [];
}

// Filtrar mensagens do cliente
$mensagens_cliente = [];
foreach ($todas_mensagens as $msg) {
    if (($msg['cliente_id'] ?? '') == $cliente_id) {
        $mensagens_cliente[] = [
            'id' => $msg['id'] ?? uniqid(),
            'mensagem' => $msg['mensagem'] ?? '',
            'direcao' => $msg['direcao'] ?? 'recebido',
            'data_hora' => date('H:i', strtotime($msg['data_hora'] ?? 'now')),
            'status' => $msg['status'] ?? 'recebido',
            'tipo' => $msg['tipo'] ?? 'texto',
            'anexo' => $msg['anexo'] ?? null
        ];
    }
}

// Ordenar por data/hora
usort($mensagens_cliente, function($a, $b) {
    return strtotime($a['data_hora']) - strtotime($b['data_hora']);
});

// Marcar mensagens como lidas
foreach ($mensagens_cliente as &$msg) {
    if ($msg['direcao'] === 'recebido') {
        $msg['status'] = 'lido';
    }
}

echo json_encode([
    'success' => true,
    'mensagens' => $mensagens_cliente,
    'total' => count($mensagens_cliente),
    'cliente_id' => $cliente_id,
    'modo' => 'temporario'
]);
?> 