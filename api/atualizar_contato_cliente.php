<?php
require_once '../config.php';
require_once '../painel/db.php';

// Garantir que sempre retorne JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Ler dados de entrada
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validar dados de entrada
    if (!$data) {
        throw new Exception('Dados JSON inválidos');
    }
    
    $cliente_id = isset($data['cliente_id']) ? intval($data['cliente_id']) : 0;
    $contact_name = isset($data['contact_name']) ? trim($data['contact_name']) : '';
    
    if (!$cliente_id || !$contact_name) {
        throw new Exception('Dados obrigatórios ausentes');
    }
    
    // Verificar se o cliente existe
    $stmt_check = $mysqli->prepare('SELECT id FROM clientes WHERE id = ?');
    $stmt_check->bind_param('i', $cliente_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Cliente não encontrado');
    }
    $stmt_check->close();
    
    // Atualizar o contact_name
    $stmt = $mysqli->prepare('UPDATE clientes SET contact_name = ? WHERE id = ?');
    $stmt->bind_param('si', $contact_name, $cliente_id);
    
    if ($stmt->execute()) {
        // Sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Contato atualizado com sucesso',
            'cliente_id' => $cliente_id,
            'contact_name' => $contact_name
        ]);
    } else {
        throw new Exception('Erro ao atualizar no banco de dados: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Erro - sempre retornar JSON válido
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Erro fatal - sempre retornar JSON válido
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
} finally {
    // Garantir que a conexão seja fechada
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?> 