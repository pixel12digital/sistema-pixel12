<?php
require_once "config.php";

echo "🔍 VERIFICANDO ESTRUTURA COMPLETA DA API\n";
echo "=======================================\n\n";

$vps_ip = "212.85.11.238";
$porta = "3000";
$base_url = "http://{$vps_ip}:{$porta}";

echo "📡 Analisando API na porta $porta:\n";
echo "   Base URL: $base_url\n\n";

// 1. Verificar status detalhado
echo "📊 STATUS DETALHADO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Status obtido:\n";
    echo "   📊 Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "   📊 Message: " . ($data['message'] ?? 'N/A') . "\n";
    echo "   📊 Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
    echo "   📊 Sessions: " . ($data['sessions'] ?? 'N/A') . "\n";
    
    if (isset($data['clients_status']['default'])) {
        $client = $data['clients_status']['default'];
        echo "   📱 Client Status: " . ($client['status'] ?? 'N/A') . "\n";
        echo "   📱 Client Message: " . ($client['message'] ?? 'N/A') . "\n";
        echo "   📱 Client Number: " . ($client['number'] ?? 'N/A') . "\n";
    }
} else {
    echo "   ❌ Erro ao obter status (HTTP $http_code)\n";
}

echo "\n";

// 2. Verificar sessões
echo "📋 SESSÕES DISPONÍVEIS:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Sessões obtidas:\n";
    echo "   📊 Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "   📊 Total: " . ($data['total'] ?? 'N/A') . "\n";
    
    if (isset($data['sessions']) && is_array($data['sessions'])) {
        foreach ($data['sessions'] as $session) {
            echo "   📱 Sessão: " . ($session['name'] ?? 'N/A') . "\n";
            if (isset($session['status'])) {
                echo "      Status: " . ($session['status']['status'] ?? 'N/A') . "\n";
                echo "      Message: " . ($session['status']['message'] ?? 'N/A') . "\n";
            }
        }
    }
} else {
    echo "   ❌ Erro ao obter sessões (HTTP $http_code)\n";
}

echo "\n";

// 3. Verificar QR code
echo "📱 QR CODE:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/qr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ QR Code obtido:\n";
    echo "   📊 Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "   📊 Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
    echo "   📊 Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "   📊 Message: " . ($data['message'] ?? 'N/A') . "\n";
    echo "   📊 QR: " . ($data['qr'] ? 'Disponível' : 'Não disponível') . "\n";
} else {
    echo "   ❌ Erro ao obter QR (HTTP $http_code)\n";
}

echo "\n";

// 4. Tentar descobrir endpoints por tentativa
echo "🔍 DESCOBRINDO ENDPOINTS ADICIONAIS:\n";
$possible_endpoints = [
    'api', 'v1', 'v2', 'rest', 'webhook', 'events', 'messages', 'contacts', 'groups'
];

foreach ($possible_endpoints as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base_url . '/' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ /$endpoint existe (HTTP 200)\n";
    } elseif ($http_code === 405) {
        echo "   ⚠️ /$endpoint existe mas método não permitido (HTTP 405)\n";
    }
}

echo "\n";

// 5. Conclusão
echo "📋 CONCLUSÃO:\n";
echo "   Esta API parece ser apenas para monitoramento de status e QR code.\n";
echo "   Não há endpoints de envio de mensagens disponíveis.\n";
echo "   Para envio de mensagens, pode ser necessário:\n";
echo "   1. Configurar um servidor WhatsApp diferente\n";
echo "   2. Usar uma API diferente\n";
echo "   3. Verificar se há outro servidor na VPS\n";

echo "\n✅ ANÁLISE CONCLUÍDA!\n";
?> 