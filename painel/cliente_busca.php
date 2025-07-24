<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
$cpfCnpj = preg_replace('/\D/', '', $_GET['cpfCnpj'] ?? '');
if (!$cpfCnpj) {
    echo json_encode(['success'=>false, 'error'=>'CPF ou CNPJ não informado.']);
    exit;
}
// Buscar cliente no Asaas
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $asaas_api_url . '/customers?cpfCnpj=' . $cpfCnpj);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . $asaas_api_key
]);
$result = curl_exec($ch);
if ($result === false) {
    echo json_encode(['success'=>false, 'error'=>'Erro ao conectar à API do Asaas.']);
    exit;
}
$resp = json_decode($result, true);
curl_close($ch);
if (!empty($resp['data']) && isset($resp['data'][0]['id'])) {
    echo json_encode(['success'=>true, 'data'=>$resp['data'][0]]);
} else {
    echo json_encode(['success'=>false]);
} 