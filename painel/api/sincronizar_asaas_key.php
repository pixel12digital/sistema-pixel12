<?php
require_once '../db.php';
header('Content-Type: application/json');

// Aceitar tanto application/json quanto form-data
$chave_atual = '';
$forcar = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['chave'])) {
        $chave_atual = trim($_POST['chave']);
        $forcar = isset($_POST['forcar']) && $_POST['forcar'] == 1;
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['chave'])) {
            $chave_atual = trim($input['chave']);
            $forcar = isset($input['forcar']) && $input['forcar'] == 1;
        }
    }
}
if (!$chave_atual) {
    echo json_encode(['success' => false, 'error' => 'Chave não informada']);
    exit;
}
// Buscar chave do banco
$res = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
$chave_banco = '';
if ($res && ($row = $res->fetch_assoc())) {
    $chave_banco = trim($row['valor']);
}
if ($chave_banco === $chave_atual && !$forcar) {
    echo json_encode(['success' => true, 'status' => 'sincronizada', 'mensagem' => 'Chave já está sincronizada no banco.']);
    exit;
}
// Atualizar banco (sempre que forçar ou se diferente)
$chave_escaped = $mysqli->real_escape_string($chave_atual);
$sql = "INSERT INTO configuracoes (chave, valor, descricao, data_atualizacao) VALUES ('asaas_api_key', '$chave_escaped', 'Chave da API do Asaas', NOW())\n    ON DUPLICATE KEY UPDATE valor = '$chave_escaped', data_atualizacao = NOW()";
if ($mysqli->query($sql)) {
    echo json_encode(['success' => true, 'status' => $forcar ? 'forcada' : 'atualizada', 'mensagem' => 'Chave do banco foi atualizada com sucesso!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao atualizar a chave no banco: ' . $mysqli->error]);
} 