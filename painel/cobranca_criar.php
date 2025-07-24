<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

function postAsaas($endpoint, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY
    ]);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($result === false) return [false, 'Erro cURL: ' . $err];
    $resp = json_decode($result, true);
    if (isset($resp['errors'])) return [false, json_encode($resp['errors'])];
    return [true, $resp];
}

// Receber dados do formulário
parse_str(file_get_contents('php://input'), $post);
$tipo = $post['tipoCobranca'] ?? 'avulsa';
$clienteId = $post['clienteId'] ?? null;

// 1. Se não tem clienteId, cadastrar cliente no Asaas
if (!$clienteId) {
    $clienteData = [
        'name' => $post['nome'] ?? '',
        'cpfCnpj' => preg_replace('/\D/', '', $post['cpfCnpj'] ?? ''),
        'email' => $post['email'] ?? '',
        'mobilePhone' => $post['celular'] ?? '',
        'postalCode' => $post['cep'] ?? '',
        'address' => $post['rua'] ?? '',
        'addressNumber' => $post['numero'] ?? '',
        'complement' => $post['complemento'] ?? '',
        'province' => $post['bairro'] ?? '',
        'city' => $post['cidade'] ?? '',
        'notificationDisabled' => true
    ];
    list($ok, $resp) = postAsaas('/customers', $clienteData);
    if (!$ok) {
        echo json_encode(['success'=>false, 'error'=>'Erro ao cadastrar cliente: ' . $resp]);
        exit;
    }
    $clienteId = $resp['id'];
}

// 2. Montar payload da cobrança
if ($tipo === 'avulsa') {
    $data = [
        'customer' => $clienteId,
        'value' => floatval(str_replace(',', '.', $post['valor'] ?? '0')),
        'dueDate' => $post['vencimento'] ?? '',
        'description' => $post['descricao'] ?? '',
        'billingType' => $post['billingType'] ?? 'BOLETO',
    ];
    list($ok, $resp) = postAsaas('/payments', $data);
} elseif ($tipo === 'parcelamento') {
    $data = [
        'customer' => $clienteId,
        'installmentCount' => intval($post['installmentCount'] ?? 2),
        'totalValue' => floatval(str_replace(',', '.', $post['totalValue'] ?? '0')),
        'dueDate' => $post['dueDate'] ?? '',
        'description' => $post['descricao'] ?? '',
        'billingType' => $post['billingType'] ?? 'BOLETO',
    ];
    list($ok, $resp) = postAsaas('/payments', $data);
} elseif ($tipo === 'assinatura') {
    $data = [
        'customer' => $clienteId,
        'value' => floatval(str_replace(',', '.', $post['valor'] ?? '0')),
        'nextDueDate' => $post['nextDueDate'] ?? '',
        'description' => $post['descricao'] ?? '',
        'billingType' => $post['billingType'] ?? 'BOLETO',
        'cycle' => $post['cycle'] ?? 'MONTHLY',
    ];
    list($ok, $resp) = postAsaas('/subscriptions', $data);
} else {
    echo json_encode(['success'=>false, 'error'=>'Tipo de cobrança inválido.']);
    exit;
}

if ($ok) {
    echo json_encode(['success'=>true, 'data'=>$resp]);
} else {
    echo json_encode(['success'=>false, 'error'=>$resp]);
} 