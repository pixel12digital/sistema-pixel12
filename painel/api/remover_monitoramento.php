<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cliente_id'])) {
    echo json_encode(['success' => false, 'error' => 'Cliente ID não informado']);
    exit;
}

$cliente_id = intval($input['cliente_id']);

try {
    // Verificar se cliente existe
    $cliente = $mysqli->query("SELECT id, nome FROM clientes WHERE id = $cliente_id LIMIT 1")->fetch_assoc();
    
    if (!$cliente) {
        echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
        exit;
    }

    // IMPORTANTE: Remover do monitoramento é SEMPRE permitido
    // Não há validação que impeça remover, apenas validação para adicionar
    
    // Verificar se já existe registro de monitoramento
    $existe = $mysqli->query("SELECT id FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1")->fetch_assoc();

    if ($existe) {
        // Atualizar registro existente para monitorado = 0 (removido)
        $sql = "UPDATE clientes_monitoramento SET 
                monitorado = 0,
                data_atualizacao = NOW()
                WHERE cliente_id = $cliente_id";
    } else {
        // Criar novo registro como não monitorado
        $sql = "INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                VALUES ($cliente_id, 0, NOW(), NOW())";
    }

    if (!$mysqli->query($sql)) {
        throw new Exception("Erro ao remover do monitoramento: " . $mysqli->error);
    }

    // Log da ação
    $log_data = date('Y-m-d H:i:s') . " - Cliente {$cliente['nome']} (ID: $cliente_id) removido do monitoramento automático\n";
    file_put_contents('../logs/monitoramento_clientes.log', $log_data, FILE_APPEND);

    echo json_encode([
        'success' => true,
        'message' => 'Cliente removido do monitoramento com sucesso',
        'cliente_id' => $cliente_id,
        'cliente_nome' => $cliente['nome']
    ]);

} catch (Exception $e) {
    error_log("Erro ao remover monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 