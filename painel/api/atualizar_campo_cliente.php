<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Verificar se o Content-Type é JSON
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Content-Type deve ser application/json']);
    exit;
}

// Ler dados JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dados JSON inválidos']);
    exit;
}

// Validar campos obrigatórios
$required_fields = ['cliente_id', 'campo', 'valor'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Campo obrigatório não fornecido: $field"]);
        exit;
    }
}

$cliente_id = intval($data['cliente_id']);
$campo = $data['campo'];
$valor = trim($data['valor']);

// Validar ID do cliente
if ($cliente_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID do cliente inválido']);
    exit;
}

// Lista de campos permitidos para edição
$campos_permitidos = [
    'nome', 'contact_name', 'cpf_cnpj', 'razao_social', 
    'email', 'telefone', 'celular', 'cep', 'rua', 
    'numero', 'complemento', 'bairro', 'observacoes'
];

if (!in_array($campo, $campos_permitidos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Campo não permitido para edição']);
    exit;
}

// Validar e formatar valores específicos
$valor_formatado = $valor;

// CPF/CNPJ - remover formatação e validar
if ($campo === 'cpf_cnpj') {
    $valor_limpo = preg_replace('/[^0-9]/', '', $valor);
    if (strlen($valor_limpo) !== 11 && strlen($valor_limpo) !== 14) {
        echo json_encode(['success' => false, 'error' => 'CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos']);
        exit;
    }
    $valor_formatado = $valor_limpo;
}

// Telefone/Celular - remover formatação e validar
if ($campo === 'telefone' || $campo === 'celular') {
    $valor_limpo = preg_replace('/[^0-9]/', '', $valor);
    if (strlen($valor_limpo) < 10 || strlen($valor_limpo) > 11) {
        echo json_encode(['success' => false, 'error' => 'Telefone deve ter 10 ou 11 dígitos']);
        exit;
    }
    $valor_formatado = $valor_limpo;
}

// Email - validar formato
if ($campo === 'email' && !empty($valor)) {
    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Email inválido']);
        exit;
    }
}

// CEP - remover formatação e validar
if ($campo === 'cep') {
    $valor_limpo = preg_replace('/[^0-9]/', '', $valor);
    if (strlen($valor_limpo) !== 8) {
        echo json_encode(['success' => false, 'error' => 'CEP deve ter 8 dígitos']);
        exit;
    }
    $valor_formatado = $valor_limpo;
}

try {
    // Verificar se o cliente existe
    $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
        exit;
    }
    $stmt->close();
    
    // Atualizar o campo
    $sql = "UPDATE clientes SET $campo = ?, data_atualizacao = NOW() WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $mysqli->error);
    }
    
    $stmt->bind_param('si', $valor_formatado, $cliente_id);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Erro ao executar a query: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        // Verificar se o valor já era o mesmo
        $stmt_check = $mysqli->prepare("SELECT $campo FROM clientes WHERE id = ? LIMIT 1");
        $stmt_check->bind_param('i', $cliente_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row = $result_check->fetch_assoc();
        $stmt_check->close();
        
        if ($row && $row[$campo] === $valor_formatado) {
            echo json_encode(['success' => true, 'message' => 'Valor já estava atualizado']);
            exit;
        } else {
            throw new Exception("Nenhuma linha foi atualizada");
        }
    }
    
    $stmt->close();
    
    // Log da atualização (opcional)
    try {
        $log_sql = "INSERT INTO log_alteracoes (tabela, registro_id, campo, valor_anterior, valor_novo, usuario, data_hora) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt_log = $mysqli->prepare($log_sql);
        if ($stmt_log) {
            $tabela = 'clientes';
            $usuario = 'sistema'; // Você pode implementar um sistema de usuários
            $valor_anterior = ''; // Para simplificar, não estamos capturando o valor anterior
            
            $stmt_log->bind_param('sissss', $tabela, $cliente_id, $campo, $valor_anterior, $valor_formatado, $usuario);
            $stmt_log->execute();
            $stmt_log->close();
        }
    } catch (Exception $log_error) {
        // Ignorar erros de log - não é crítico para a funcionalidade
        error_log("Erro ao registrar log de alteração: " . $log_error->getMessage());
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Campo atualizado com sucesso',
        'valor_atualizado' => $valor_formatado
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao atualizar campo do cliente: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?> 