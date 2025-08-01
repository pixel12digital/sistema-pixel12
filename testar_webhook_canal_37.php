<?php
echo "🧪 TESTANDO WEBHOOK ESPECÍFICO - CANAL 37 (COMERCIAL)\n";
echo "====================================================\n\n";

// Dados de teste
$dados_teste = [
    'from' => '4797309525@c.us',
    'to' => '554797146908@c.us',
    'message' => 'Teste webhook específico canal 37 - ' . date('H:i:s'),
    'timestamp' => time(),
    'id' => 'test_' . time()
];

echo "📨 Dados de teste:\n";
echo "   From: {$dados_teste['from']}\n";
echo "   To: {$dados_teste['to']}\n";
echo "   Message: {$dados_teste['message']}\n";
echo "   Timestamp: {$dados_teste['timestamp']}\n";
echo "   ID: {$dados_teste['id']}\n\n";

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
    'User-Agent: Teste-Canal-37'
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
        }
    } else {
        echo "\n❌ Webhook retornou erro HTTP $http_code\n";
    }
}

echo "\n📋 Verificando logs...\n";
$log_file = 'logs/webhook_canal_37_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    echo "   📄 Log encontrado: $log_file\n";
    $log_content = file_get_contents($log_file);
    $last_lines = array_slice(explode("\n", $log_content), -10);
    echo "   📝 Últimas 10 linhas do log:\n";
    foreach ($last_lines as $line) {
        if (trim($line)) {
            echo "      $line\n";
        }
    }
} else {
    echo "   ⚠️ Arquivo de log não encontrado: $log_file\n";
}

echo "\n🎯 TESTE CONCLUÍDO!\n";
?> 