<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
set_time_limit(30);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$chave = isset($_POST['chave']) ? trim($_POST['chave']) : '';
$forcar = isset($_POST['forcar']) ? intval($_POST['forcar']) : 0;

if (empty($chave)) {
    echo json_encode(['success' => false, 'error' => 'Chave não fornecida']);
    exit;
}

// Opcional: validar formato da chave (exemplo para chaves Asaas)
if (!preg_match('/^\$a?c?t?_(test|prod)_/', $chave)) {
    echo json_encode([
        'success' => false,
        'error' => 'Formato de chave inválido. Deve começar com $act_test_ ou $act_prod_'
    ]);
    exit;
}

// Atualizar ou inserir a chave no banco de dados
global $mysqli;
if (!$mysqli || $mysqli->connect_errno) {
    echo json_encode(['success' => false, 'error' => 'Erro ao conectar ao banco de dados']);
    exit;
}

// Verifica se já existe a configuração
try {
    $query = "SELECT * FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        // Atualiza
        $update = $mysqli->prepare("UPDATE configuracoes SET valor = ? WHERE chave = 'asaas_api_key'");
        $update->bind_param('s', $chave);
        $ok = $update->execute();
        $update->close();
        if ($ok) {
            echo json_encode(['success' => true, 'mensagem' => 'Chave atualizada com sucesso!', 'status' => 'atualizada']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar a chave no banco.']);
        }
    } else {
        // Insere
        $insert = $mysqli->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('asaas_api_key', ?)");
        $insert->bind_param('s', $chave);
        $ok = $insert->execute();
        $insert->close();
        if ($ok) {
            echo json_encode(['success' => true, 'mensagem' => 'Chave cadastrada com sucesso!', 'status' => 'cadastrada']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao cadastrar a chave no banco.']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro: ' . $e->getMessage()]);
} 