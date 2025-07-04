<?php
header('Content-Type: application/json');
require_once '../painel/config.php';
require_once '../painel/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    // Ler dados JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Dados inválidos');
    }
    
    $canal_id = intval($data['canal_id'] ?? 0);
    $numero = $data['numero'] ?? '';
    $mensagem = trim($data['mensagem'] ?? '');
    $tipo = $data['tipo'] ?? 'texto';
    
    if (!$canal_id || !$numero || !$mensagem) {
        throw new Exception('Dados incompletos');
    }
    
    // Verificar se o canal existe
    $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE id = $canal_id")->fetch_assoc();
    if (!$canal) {
        throw new Exception('Canal não encontrado');
    }
    
    // Buscar cliente pelo número
    $numero_limpo = preg_replace('/\D/', '', $numero);
    $cliente = null;
    
    // Tentar encontrar por celular
    $cliente_result = $mysqli->query("SELECT * FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$numero_limpo%'");
    if ($cliente_result && $cliente_result->num_rows > 0) {
        $cliente = $cliente_result->fetch_assoc();
    } else {
        // Tentar encontrar por telefone
        $cliente_result = $mysqli->query("SELECT * FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$numero_limpo%'");
        if ($cliente_result && $cliente_result->num_rows > 0) {
            $cliente = $cliente_result->fetch_assoc();
        }
    }
    
    // Se não encontrou cliente, criar um novo
    if (!$cliente) {
        $nome_cliente = "Cliente WhatsApp ($numero)";
        $mysqli->query("INSERT INTO clientes (nome, celular, data_criacao) VALUES ('$nome_cliente', '$numero', NOW())");
        $cliente_id = $mysqli->insert_id;
        $cliente = ['id' => $cliente_id, 'nome' => $nome_cliente, 'celular' => $numero];
    } else {
        $cliente_id = $cliente['id'];
    }
    
    // Salvar mensagem no banco
    $mensagem_escaped = $mysqli->real_escape_string($mensagem);
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, $cliente_id, '$mensagem_escaped', '$tipo', '$data_hora', 'recebido', 'recebido')";
    
    if (!$mysqli->query($sql)) {
        throw new Exception('Erro ao salvar mensagem: ' . $mysqli->error);
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem recebida e salva',
        'cliente_id' => $cliente_id,
        'cliente_nome' => $cliente['nome'],
        'mensagem_id' => $mysqli->insert_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['identificador']) && isset($data['status'])) {
    $identificador = $mysqli->real_escape_string($data['identificador']);
    $status = $mysqli->real_escape_string($data['status']);
    $mysqli->query("UPDATE canais_comunicacao SET status = '$status', data_conexao = NOW() WHERE identificador = '$identificador' LIMIT 1");
    echo json_encode(['ok' => true]);
} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Dados incompletos']);
}
?> 