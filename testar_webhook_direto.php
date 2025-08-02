<?php
echo "🔍 TESTE DIRETO DO WEBHOOK\n";
echo "=========================\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

$test_data = json_encode([
    'from' => '5547999999999@c.us',
    'body' => 'Teste direto'
]);

echo "URL: $webhook_url\n";
echo "Dados: $test_data\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$verbose_output = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose_output);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

rewind($verbose_output);
$verbose_log = stream_get_contents($verbose_output);
fclose($verbose_output);

curl_close($ch);

echo "Status HTTP: $http_code\n";
echo "Erro cURL: " . ($curl_error ?: 'Nenhum') . "\n\n";

echo "Headers e detalhes técnicos:\n";
echo "============================\n";
echo substr($verbose_log, 0, 1000) . "\n\n";

echo "Resposta do servidor:\n";
echo "=====================\n";
echo $response . "\n\n";

// Tentar identificar se o JSON é válido
if ($response) {
    $json_data = json_decode($response, true);
    if ($json_data) {
        echo "✅ JSON válido retornado\n";
        echo "Success: " . ($json_data['success'] ? 'true' : 'false') . "\n";
        if (isset($json_data['ana_response'])) {
            echo "Ana respondeu: " . substr($json_data['ana_response'], 0, 100) . "...\n";
        }
    } else {
        echo "❌ JSON inválido ou erro no formato\n";
        echo "Erro JSON: " . json_last_error_msg() . "\n";
    }
}

echo "\n🔍 ANÁLISE:\n";
echo "===========\n";
if ($http_code === 200) {
    echo "✅ HTTP 200 - Funcionando perfeitamente!\n";
} elseif ($http_code === 500 && $response && strpos($response, '"success":true') !== false) {
    echo "⚠️ HTTP 500 MAS FUNCIONANDO - Falso erro\n";
    echo "💡 Webhook processa mas retorna status incorreto\n";
    echo "🔧 Sugere erro de PHP (notice/warning) antes do JSON\n";
} else {
    echo "❌ Erro real no webhook\n";
}
?> 