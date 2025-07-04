<?php
require_once __DIR__ . '/config.php';
// Arquivo de teste para validação de token e ID do cliente Asaas
// Use este arquivo para testar se o token e o ID do cliente estão corretos e a API está respondendo.
// Basta acessar http://localhost:8080/loja-virtual-revenda/test_curl.php ou o caminho correspondente no servidor.
// Altere $token e $customerId conforme necessário para novos testes.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Teste de cURL PHP para Asaas
$token = ASAAS_API_KEY;
$customerId = 'cus_000087142281'; // ID real do cliente

$url = "https://www.asaas.com/api/v3/customers";

echo "<b>URL:</b> $url<br>";
echo "<b>Token início:</b> " . substr($token, 0, 8) . "...<br>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "access_token: $token"
]);
// Descomente a linha abaixo se houver erro de SSL (apenas para teste)
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<b>HTTP Code:</b> $httpCode<br>";
if ($result === false || $result === "") {
    echo "<b>Erro cURL:</b> $error<br>";
} else {
    echo "<b>Resposta:</b><br><pre>";
    var_dump($result);
    echo "</pre>";
} 