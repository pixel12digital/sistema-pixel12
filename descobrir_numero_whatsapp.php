<?php
echo "🔍 DESCOBRINDO NÚMERO DO WHATSAPP\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';

// 1. Tentar endpoint /qr para ver se mostra o número
echo "🔍 1. Verificando endpoint /qr...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/qr");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
}

// 2. Tentar enviar uma mensagem para ver se retorna o número do remetente
echo "\n🔍 2. Tentando enviar mensagem para obter informações...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => '554797146908@c.us', // Número do Financeiro para teste
    'message' => 'Teste - ' . date('H:i:s')
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
}

// 3. Tentar endpoint /disconnect para ver se mostra informações
echo "\n🔍 3. Verificando informações de desconexão...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/disconnect");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
}

// 4. Tentar endpoint raiz
echo "\n🔍 4. Verificando endpoint raiz...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "   Resposta: $response\n";
}

echo "\n🎯 SUGESTÕES:\n";
echo "1. Verifique no WhatsApp Web qual número está conectado\n";
echo "2. Ou use o número que você já conhece (se for o mesmo do Financeiro)\n";
echo "3. Execute: php configurar_numero_manual.php\n\n";

echo "🎯 VERIFICAÇÃO CONCLUÍDA!\n";
?> 