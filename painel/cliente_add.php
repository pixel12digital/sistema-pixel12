<?php
require_once 'config.php';
require_once 'db.php';

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
$success = false;
$message = '';

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
                $success = true;
                $message = 'Cliente cadastrado com sucesso!';
                $msg = '<span style="color:green;">Cliente cadastrado com sucesso!</span>';
            } else {
                $message = 'Erro ao salvar no banco: ' . $stmt->error;
                $msg = '<span style="color:red;">Erro ao salvar no banco: ' . $stmt->error . '</span>';
            }
            $stmt->close();
        } else {
            $message = $asaas_id_or_msg;
            $msg = '<span style="color:red;">' . $asaas_id_or_msg . '</span>';
        }
    } else {
        $message = 'Preencha todos os campos.';
        $msg = '<span style="color:red;">Preencha todos os campos.</span>';
    }
}

// Se for uma requisição AJAX, retorna JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Cliente</title>
    <base href="/loja-virtual-revenda/">
</head>
<body>
<?php include 'menu_lateral.php'; ?>
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