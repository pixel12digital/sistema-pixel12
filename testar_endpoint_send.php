<?php
echo "🧪 TESTANDO ENDPOINT /SEND DO SERVIDOR 3001\n";
echo "===========================================\n\n";

$vps_ip = '212.85.11.238';

// Testar endpoint /send
echo "🔍 Testando endpoint /send...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => 'test@c.us',
    'message' => 'Teste endpoint /send - ' . date('H:i:s')
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Resposta: $response\n";

if ($error) {
    echo "Erro: $error\n";
}

if ($http_code === 200) {
    echo "\n✅ Endpoint /send está funcionando!\n";
} else {
    echo "\n❌ Endpoint /send não está funcionando (HTTP $http_code)\n";
}

echo "\n🎯 TESTE CONCLUÍDO!\n";
?> 