<?php
require_once 'config.php';
require_once 'db.php';
// Buscar clientes para o select
$clientes = [];
$res = $mysqli->query("SELECT id, nome, asaas_id FROM clientes ORDER BY nome");
while ($row = $res->fetch_assoc()) {
    $clientes[] = $row;
}

function criarCobrancaAsaas($asaas_id, $valor, $vencimento, $descricao, $tipo) {
    global $asaas_api_key, $asaas_api_url;
    $data = [
        'customer' => $asaas_id,
        'value' => floatval($valor),
        'dueDate' => $vencimento,
        'description' => $descricao,
        'billingType' => $tipo
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $asaas_api_url . '/payments');
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
        return [true, $resp];
    } elseif (isset($resp['errors'])) {
        return [false, 'Erro Asaas: ' . json_encode($resp['errors'])];
    } else {
        return [false, 'Erro desconhecido ao criar cobrança no Asaas.'];
    }
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $valor = trim($_POST['valor'] ?? '');
    $vencimento = trim($_POST['vencimento'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $asaas_id = '';
    foreach ($clientes as $c) {
        if ($c['id'] == $cliente_id) $asaas_id = $c['asaas_id'];
    }
    if ($asaas_id && $valor && $vencimento && $descricao && $tipo) {
        list($ok, $asaas_resp) = criarCobrancaAsaas($asaas_id, $valor, $vencimento, $descricao, $tipo);
        if ($ok) {
            $stmt = $mysqli->prepare("INSERT INTO cobrancas (asaas_payment_id, cliente_id, valor, status, vencimento, data_criacao, descricao, tipo, url_fatura, parcela, assinatura_id) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
            $stmt->bind_param('sidsssssss',
                $asaas_resp['id'],
                $cliente_id,
                $asaas_resp['value'],
                $asaas_resp['status'],
                $asaas_resp['dueDate'],
                $asaas_resp['description'],
                $asaas_resp['billingType'],
                $asaas_resp['invoiceUrl'],
                $asaas_resp['installmentNumber'],
                $asaas_resp['subscription']
            );
            if ($stmt->execute()) {
                $msg = '<span style="color:green;">Cobrança cadastrada com sucesso!</span>';
            } else {
                $msg = '<span style="color:red;">Erro ao salvar no banco: ' . $stmt->error . '</span>';
            }
            $stmt->close();
        } else {
            $msg = '<span style="color:red;">' . $asaas_resp . '</span>';
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
    <title>Cadastrar Cobrança</title>
    <style>
        body { background: #181c23; color: #f5f5f5; font-family: Arial, sans-serif; }
        .form-container { max-width: 480px; margin: 3rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem; }
        label { display: block; margin-bottom: 0.5rem; color: #a259e6; }
        input, select { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #a259e6; background: #232836; color: #fff; margin-bottom: 1.2rem; }
        button { background: #a259e6; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #7c2ae8; }
        .msg { margin-bottom: 1rem; }
        .step { display: none; }
        .step.active { display: block; }
        .step-buttons { display: flex; justify-content: space-between; margin-top: 1.5rem; }
        .hidden { display: none; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Cadastrar Cobrança</h2>
    <div id="msg" class="msg"></div>
    <form id="formCobranca" autocomplete="off">
        <!-- Etapa 1: Busca/Cadastro de Cliente -->
        <div class="step step-1 active">
            <label for="cpfCnpj">CPF ou CNPJ do cliente:</label>
            <input type="text" id="cpfCnpj" name="cpfCnpj" maxlength="18" placeholder="Digite e clique em buscar">
            <button type="button" id="btnBuscarCliente">Buscar no Asaas</button>
            <div id="clienteForm" class="hidden">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
                <label for="celular">Celular:</label>
                <input type="text" id="celular" name="celular" placeholder="(00) 00000-0000" required>
                <label for="cep">CEP:</label>
                <input type="text" id="cep" name="cep">
                <label for="rua">Rua:</label>
                <input type="text" id="rua" name="rua">
                <label for="numero">Número:</label>
                <input type="text" id="numero" name="numero">
                <label for="complemento">Complemento:</label>
                <input type="text" id="complemento" name="complemento">
                <label for="bairro">Bairro:</label>
                <input type="text" id="bairro" name="bairro">
                <label for="cidade">Cidade:</label>
                <input type="text" id="cidade" name="cidade">
                <div style="background:#232836;color:#f5f5f5;padding:10px 12px;border-radius:6px;margin-bottom:1rem;font-size:0.98em;">
                    <b>Atenção:</b> O sistema <u>nunca enviará notificações de cobrança via Asaas</u>. Toda comunicação com o cliente deve ser feita manualmente ou por outros meios.
                </div>
            </div>
            <div class="step-buttons">
                <button type="button" id="btnProx1" disabled>Próximo</button>
            </div>
        </div>
        <!-- Etapa 2: Tipo de Cobrança e Dados -->
        <div class="step step-2">
            <label for="tipoCobranca">Tipo de cobrança:</label>
            <select id="tipoCobranca" name="tipoCobranca" required>
                <option value="avulsa">Avulsa</option>
                <option value="parcelamento">Parcelamento</option>
                <option value="assinatura">Assinatura</option>
            </select>
            <div id="dadosCobranca"></div>
            <div class="step-buttons">
                <button type="button" id="btnVoltar1">Voltar</button>
                <button type="button" id="btnProx2">Próximo</button>
            </div>
        </div>
        <!-- Etapa 3: Resumo/Confirmação -->
        <div class="step step-3">
            <div id="resumoCobranca"></div>
            <div class="step-buttons">
                <button type="button" id="btnVoltar2">Voltar</button>
                <button type="submit" id="btnFinalizar">Cadastrar Cobrança</button>
            </div>
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="cobranca_add.js?v=1"></script>
</body>
</html> 