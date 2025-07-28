<?php
/**
 * API PARA CONVERSAS TEMPORÁRIAS
 * 
 * Carrega conversas do arquivo local quando o banco está indisponível
 */

header('Content-Type: application/json');

// Verificar se há arquivo de mensagens temporárias
$mensagens_file = __DIR__ . '/../../logs/mensagens_temporarias.json';

if (file_exists($mensagens_file)) {
    $mensagens = json_decode(file_get_contents($mensagens_file), true);
} else {
    $mensagens = [];
}

// Agrupar mensagens por cliente
$conversas = [];
$clientes = [];

foreach ($mensagens as $msg) {
    $cliente_id = $msg['cliente_id'] ?? 'desconhecido';
    $cliente_nome = $msg['cliente_nome'] ?? 'Cliente ' . $cliente_id;
    
    if (!isset($conversas[$cliente_id])) {
        $conversas[$cliente_id] = [
            'cliente_id' => $cliente_id,
            'nome' => $cliente_nome,
            'ultima_mensagem' => '',
            'ultima_data' => '',
            'nao_lidas' => 0,
            'canal_nome' => $msg['canal_nome'] ?? 'Canal',
            'mensagens' => []
        ];
    }
    
    $conversas[$cliente_id]['mensagens'][] = $msg;
    
    // Atualizar última mensagem
    if (empty($conversas[$cliente_id]['ultima_data']) || 
        strtotime($msg['data_hora']) > strtotime($conversas[$cliente_id]['ultima_data'])) {
        $conversas[$cliente_id]['ultima_mensagem'] = $msg['mensagem'];
        $conversas[$cliente_id]['ultima_data'] = $msg['data_hora'];
    }
    
    // Contar mensagens não lidas
    if ($msg['direcao'] === 'recebido' && ($msg['status'] ?? 'recebido') === 'recebido') {
        $conversas[$cliente_id]['nao_lidas']++;
    }
}

// Ordenar por última mensagem
usort($conversas, function($a, $b) {
    return strtotime($b['ultima_data']) - strtotime($a['ultima_data']);
});

// Formatar datas
foreach ($conversas as &$conv) {
    if (!empty($conv['ultima_data'])) {
        $conv['ultima_data'] = date('H:i', strtotime($conv['ultima_data']));
    }
    $conv['ultima_mensagem'] = substr($conv['ultima_mensagem'], 0, 50);
}

echo json_encode([
    'success' => true,
    'conversas' => array_values($conversas),
    'total' => count($conversas),
    'modo' => 'temporario'
]);
?> 