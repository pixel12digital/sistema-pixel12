<?php
/**
 * 🧪 TESTE DE ENVIO VIA WHATSAPP API
 * Testa se o VPS está enviando mensagens corretamente
 */

echo "🧪 TESTE DE ENVIO VIA WHATSAPP API\n";
echo "==================================\n\n";

// Configuração
$vps_url = "http://212.85.11.238:3000";
$numero_teste = "554796164699";
$mensagem_teste = "🧪 Teste de envio via API - " . date('H:i:s');

echo "📱 Testando envio para: $numero_teste\n";
echo "💬 Mensagem: $mensagem_teste\n";
echo "🌐 VPS URL: $vps_url\n\n";

// Teste 1: Verificar status do VPS
echo "1️⃣ VERIFICANDO STATUS DO VPS...\n";
$status_url = "$vps_url/status";
$ch_status = curl_init($status_url);
curl_setopt($ch_status, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_status, CURLOPT_TIMEOUT, 10);
$response_status = curl_exec($ch_status);
$http_code_status = curl_getinfo($ch_status, CURLINFO_HTTP_CODE);
curl_close($ch_status);

if ($http_code_status == 200) {
    $status_data = json_decode($response_status, true);
    echo "✅ VPS Status: " . ($status_data['status'] ?? 'N/A') . "\n";
    echo "✅ Ready: " . ($status_data['ready'] ? 'SIM' : 'NÃO') . "\n";
    echo "✅ Port: " . ($status_data['port'] ?? 'N/A') . "\n";
    
    if (isset($status_data['clients_status']['default'])) {
        $client = $status_data['clients_status']['default'];
        echo "✅ WhatsApp Conectado: " . ($client['ready'] ? 'SIM' : 'NÃO') . "\n";
        echo "✅ QR Code Necessário: " . ($client['hasQR'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "❌ Erro ao verificar status: HTTP $http_code_status\n";
    exit;
}

echo "\n2️⃣ TESTANDO ENVIO DE MENSAGEM...\n";

// Teste 2: Enviar mensagem
$send_url = "$vps_url/send/text";
$data_envio = [
    "number" => $numero_teste,
    "message" => $mensagem_teste
];

$ch_send = curl_init($send_url);
curl_setopt($ch_send, CURLOPT_POST, true);
curl_setopt($ch_send, CURLOPT_POSTFIELDS, json_encode($data_envio));
curl_setopt($ch_send, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch_send, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_send, CURLOPT_TIMEOUT, 15);

$response_send = curl_exec($ch_send);
$curl_error = curl_error($ch_send);
$http_code_send = curl_getinfo($ch_send, CURLINFO_HTTP_CODE);
curl_close($ch_send);

echo "📤 Resposta HTTP: $http_code_send\n";
echo "📤 Resposta: $response_send\n";

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
} else {
    if ($http_code_send == 200) {
        $response_data = json_decode($response_send, true);
        if (isset($response_data['success']) && $response_data['success']) {
            echo "✅ MENSAGEM ENVIADA COM SUCESSO!\n";
            echo "✅ ID: " . ($response_data['id'] ?? 'N/A') . "\n";
            echo "✅ Status: " . ($response_data['status'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Erro na resposta: " . ($response_data['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "❌ Erro HTTP: $http_code_send\n";
    }
}

echo "\n3️⃣ VERIFICANDO LOGS DO VPS...\n";
echo "Execute no VPS: pm2 logs whatsapp-3000 --lines 5\n";

echo "\n🎯 RESULTADO DO TESTE:\n";
echo "======================\n";
if ($http_code_send == 200) {
    echo "✅ ENVIO FUNCIONANDO - O problema pode estar no webhook\n";
    echo "🔍 Verifique os logs do webhook para ver se está processando\n";
} else {
    echo "❌ ENVIO COM PROBLEMA - Verifique o VPS\n";
    echo "🔧 Execute no VPS: pm2 restart whatsapp-3000\n";
}
?> 