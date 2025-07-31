<?php
require_once "config.php";

echo "🔍 DESCOBRINDO ENDPOINT DE ENVIO DE MENSAGENS\n";
echo "============================================\n\n";

$vps_ip = "212.85.11.238";
$porta = "3000";
$base_url = "http://{$vps_ip}:{$porta}";

echo "📡 Testando possíveis endpoints de envio:\n";
echo "   Base URL: $base_url\n\n";

// Possíveis endpoints de envio
$endpoints = [
    'send' => '/send',
    'send_text' => '/send/text',
    'send_message' => '/send/message',
    'message' => '/message',
    'chat' => '/chat',
    'send_chat' => '/send/chat',
    'api_send' => '/api/send',
    'api_message' => '/api/message'
];

foreach ($endpoints as $name => $endpoint) {
    echo "🔍 Testando $name ($endpoint): ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ EXISTE (HTTP 200)\n";
    } elseif ($http_code === 404) {
        echo "❌ NÃO EXISTE (HTTP 404)\n";
    } elseif ($http_code === 405) {
        echo "⚠️ EXISTE MAS MÉTODO NÃO PERMITIDO (HTTP 405)\n";
    } else {
        echo "❓ CÓDIGO DESCONHECIDO (HTTP $http_code)\n";
    }
}

echo "\n";

// Testar endpoint /send com POST
echo "📤 TESTANDO ENDPOINT /send COM POST:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/send');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'number' => '554796164699',
    'message' => 'Teste de endpoint - ' . date('H:i:s')
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 Resposta (HTTP $http_code):\n";
echo "   📄 " . substr($response, 0, 300) . "...\n";

echo "\n";

// Verificar se há documentação na raiz
echo "📚 VERIFICANDO DOCUMENTAÇÃO NA RAIZ:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Página raiz existe (HTTP 200)\n";
    echo "   📄 Conteúdo:\n";
    echo "   " . substr($response, 0, 500) . "...\n";
} else {
    echo "   ❌ Página raiz não existe (HTTP $http_code)\n";
}

echo "\n✅ DESCOBERTA CONCLUÍDA!\n";
?> 