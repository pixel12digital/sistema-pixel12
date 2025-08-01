<?php
echo "🧪 TESTANDO WEBHOOK CANAL 37 NA VPS\n";
echo "===================================\n\n";

// Dados de teste no formato correto
$dados_teste = [
    'event' => 'onmessage',
    'data' => [
        'from' => '4797309525@c.us',
        'to' => '554797146908@c.us',
        'text' => 'Teste webhook VPS canal 37 - ' . date('H:i:s'),
        'id' => 'test_vps_' . time(),
        'timestamp' => time()
    ]
];

echo "📨 Dados de teste (formato WPPConnect):\n";
echo "   Event: {$dados_teste['event']}\n";
echo "   From: {$dados_teste['data']['from']}\n";
echo "   To: {$dados_teste['data']['to']}\n";
echo "   Text: {$dados_teste['data']['text']}\n";
echo "   ID: {$dados_teste['data']['id']}\n\n";

// Testar webhook específico
$webhook_url = 'https://app.pixel12digital.com.br/api/webhook_canal_37.php';

echo "🔗 Testando webhook: $webhook_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Teste-VPS-Canal-37'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 Resultado:\n";
echo "   HTTP Code: $http_code\n";

if ($error) {
    echo "   ❌ Erro cURL: $error\n";
} else {
    echo "   ✅ Resposta: $response\n";
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['status']) && $data['status'] === 'ok') {
            echo "\n🎉 SUCESSO! Webhook funcionando corretamente!\n";
            echo "   Canal: " . ($data['canal'] ?? 'N/A') . "\n";
            echo "   Mensagem ID: " . ($data['mensagem_id'] ?? 'N/A') . "\n";
            echo "   Timestamp: " . ($data['timestamp'] ?? 'N/A') . "\n";
        } else {
            echo "\n⚠️ Resposta inesperada do webhook\n";
            if (isset($data['error'])) {
                echo "   Erro: " . $data['error'] . "\n";
            }
        }
    } else {
        echo "\n❌ Webhook retornou erro HTTP $http_code\n";
    }
}

// Testar também o formato direto
echo "\n🔄 Testando formato direto...\n";

$dados_direto = [
    'from' => '4797309525@c.us',
    'to' => '554797146908@c.us',
    'text' => 'Teste formato direto canal 37 - ' . date('H:i:s'),
    'id' => 'test_direto_' . time(),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_direto));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Teste-Formato-Direto'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Resultado formato direto:\n";
echo "   HTTP Code: $http_code\n";
echo "   Resposta: $response\n";

echo "\n🎯 TESTE CONCLUÍDO!\n";
?> 