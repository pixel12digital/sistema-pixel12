<?php
echo "🔍 VERIFICANDO ENDPOINTS DO SERVIDOR 3001\n";
echo "=========================================\n\n";

$vps_ip = '212.85.11.238';

// Lista de endpoints para testar
$endpoints = [
    '/',
    '/status',
    '/info',
    '/send',
    '/webhook/config',
    '/qr',
    '/disconnect',
    '/clients',
    '/sessions'
];

echo "🧪 TESTANDO ENDPOINTS NO SERVIDOR 3001:\n";
echo "=====================================\n";

foreach ($endpoints as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001{$endpoint}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = $http_code === 200 ? '✅' : ($http_code === 404 ? '❌' : '⚠️');
    echo "   {$status} {$endpoint}: HTTP {$http_code}\n";
    
    if ($http_code === 200 && $endpoint === '/status') {
        $data = json_decode($response, true);
        if ($data) {
            echo "      Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
            if (isset($data['clients_status']['default']['number'])) {
                echo "      Número: " . $data['clients_status']['default']['number'] . "\n";
            } else {
                echo "      Número: Não disponível\n";
            }
        }
    }
}

echo "\n🧪 COMPARANDO COM SERVIDOR 3000:\n";
echo "===============================\n";

foreach ($endpoints as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3000{$endpoint}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = $http_code === 200 ? '✅' : ($http_code === 404 ? '❌' : '⚠️');
    echo "   {$status} {$endpoint}: HTTP {$http_code}\n";
    
    if ($http_code === 200 && $endpoint === '/status') {
        $data = json_decode($response, true);
        if ($data) {
            echo "      Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
            if (isset($data['clients_status']['default']['number'])) {
                echo "      Número: " . $data['clients_status']['default']['number'] . "\n";
            } else {
                echo "      Número: Não disponível\n";
            }
        }
    }
}

echo "\n🔍 ANÁLISE:\n";
echo "===========\n";

// Verificar se o servidor 3001 tem o endpoint /send
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['to' => 'test@c.us', 'message' => 'test']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Endpoint /send está funcionando no servidor 3001\n";
} else {
    echo "❌ Endpoint /send não está funcionando no servidor 3001 (HTTP $http_code)\n";
    echo "   Isso indica que o servidor 3001 não tem o endpoint /send implementado\n";
}

echo "\n🎯 RECOMENDAÇÕES:\n";
echo "================\n";

if ($http_code !== 200) {
    echo "1. 🔧 O servidor 3001 precisa ter o endpoint /send implementado\n";
    echo "2. 🔧 Compare o arquivo whatsapp-api-server.js da porta 3000 com a 3001\n";
    echo "3. 🔧 Copie a implementação do endpoint /send se necessário\n";
    echo "4. 🔧 Reinicie o servidor 3001 após as alterações\n";
}

echo "\n🎯 VERIFICAÇÃO CONCLUÍDA!\n";
?> 