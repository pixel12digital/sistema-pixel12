<?php
require_once 'db.php';
require_once 'config.php';

function criarClienteAsaas($nome, $email, $telefone, $cpf_cnpj) {
    global $asaas_api_key, $asaas_api_url;
    $data = [
        'name' => $nome,
        'email' => $email,
        'phone' => $telefone,
        'cpfCnpj' => $cpf_cnpj
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $asaas_api_url . '/customers');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $asaas_api_key
    ]);
    $result = curl_exec($ch);
    if ($result === false) {
        curl_close($ch);
        return [false, 'Erro ao conectar à API do Asaas: ' . curl_error($ch)];
    }
    $resp = json_decode($result, true);
    curl_close($ch);
    if (isset($resp['id'])) {
        return [true, $resp['id']];
    } elseif (isset($resp['errors'])) {
        return [false, 'Erro Asaas: ' . json_encode($resp['errors'])];
    } else {
        return [false, 'Erro desconhecido ao criar cliente no Asaas.'];
    }
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
    if ($nome && $email && $telefone && $cpf_cnpj) {
        list($ok, $asaas_id_or_msg) = criarClienteAsaas($nome, $email, $telefone, $cpf_cnpj);
        if ($ok) {
            $asaas_id = $asaas_id_or_msg;
            $stmt = $mysqli->prepare("INSERT INTO clientes (asaas_id, nome, email, telefone, cpf_cnpj, data_criacao, data_atualizacao) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('sssss', $asaas_id, $nome, $email, $telefone, $cpf_cnpj);
            if ($stmt->execute()) {
                $msg = '<span style="color:green;">Cliente cadastrado com sucesso!</span>';
            } else {
                $msg = '<span style="color:red;">Erro ao salvar no banco: ' . $stmt->error . '</span>';
            }
            $stmt->close();
        } else {
            $msg = '<span style="color:red;">' . $asaas_id_or_msg . '</span>';
        }
    } else {
        $msg = '<span style="color:red;">Preencha todos os campos.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Cliente</title>
    <style>
        body { background: #181c23; color: #f5f5f5; font-family: Arial, sans-serif; }
        .form-container { max-width: 400px; margin: 3rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem; }
        label { display: block; margin-bottom: 0.5rem; color: #a259e6; }
        input[type=text], input[type=email] { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #a259e6; background: #232836; color: #fff; margin-bottom: 1.2rem; }
        button { background: #a259e6; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #7c2ae8; }
        .msg { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Cadastrar Cliente</h2>
        <form method="post">
            <div class="msg"><?= $msg ?></div>
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" required>
            <label for="cpf_cnpj">CPF ou CNPJ:</label>
            <input type="text" name="cpf_cnpj" id="cpf_cnpj" required>
            <button type="submit">Cadastrar</button>
        </form>
    </div>
</body>
</html> 