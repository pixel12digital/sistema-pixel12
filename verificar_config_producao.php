<?php
/**
 * 🔍 VERIFICAR CONFIGURAÇÃO EM PRODUÇÃO
 * Testa se WHATSAPP_ROBOT_URL está configurado corretamente
 */

echo "🔍 VERIFICANDO CONFIGURAÇÃO EM PRODUÇÃO\n";
echo "=======================================\n\n";

// Simular ambiente de produção
$_SERVER['SERVER_NAME'] = 'app.pixel12digital.com.br';
$_SERVER['DOCUMENT_ROOT'] = '/home/u342734079/domains/app.pixel12digital.com.br/public_html';

// Incluir configuração
require_once 'config.php';

echo "1️⃣ CONFIGURAÇÕES DETECTADAS:\n";
echo "============================\n";
echo "✅ Ambiente: " . (strpos(__DIR__, 'xampp') !== false ? 'LOCAL' : 'PRODUÇÃO') . "\n";
echo "✅ WHATSAPP_ROBOT_URL: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'NÃO DEFINIDO') . "\n";
echo "✅ DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'ATIVO' : 'INATIVO') : 'NÃO DEFINIDO') . "\n";

echo "\n2️⃣ TESTANDO CONEXÃO COM VPS:\n";
echo "=============================\n";

$vps_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';
$test_url = "$vps_url/status";

echo "🌐 Testando: $test_url\n";

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 HTTP Code: $http_code\n";
echo "📡 Response: " . substr($response, 0, 100) . "...\n";

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
} else if ($http_code == 200) {
    echo "✅ VPS respondendo corretamente\n";
} else {
    echo "❌ VPS com problema - HTTP: $http_code\n";
}

echo "\n3️⃣ TESTANDO ENVIO DE MENSAGEM:\n";
echo "===============================\n";

$send_url = "$vps_url/send/text";
$data_envio = [
    "number" => "554796164699",
    "message" => "🧪 Teste configuração produção - " . date('H:i:s')
];

echo "📤 Enviando para: $send_url\n";

$ch_send = curl_init($send_url);
curl_setopt($ch_send, CURLOPT_POST, true);
curl_setopt($ch_send, CURLOPT_POSTFIELDS, json_encode($data_envio));
curl_setopt($ch_send, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch_send, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_send, CURLOPT_TIMEOUT, 15);
curl_setopt($ch_send, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch_send, CURLOPT_SSL_VERIFYHOST, false);

$response_send = curl_exec($ch_send);
$curl_error_send = curl_error($ch_send);
$http_code_send = curl_getinfo($ch_send, CURLINFO_HTTP_CODE);
curl_close($ch_send);

echo "📡 Resposta HTTP: $http_code_send\n";
echo "📡 Resposta: $response_send\n";

if ($curl_error_send) {
    echo "❌ Erro cURL: $curl_error_send\n";
} else if ($http_code_send == 200) {
    $response_data = json_decode($response_send, true);
    if (isset($response_data['success']) && $response_data['success']) {
        echo "✅ ENVIO FUNCIONANDO!\n";
    } else {
        echo "❌ Erro na resposta: " . ($response_data['message'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "❌ Erro HTTP: $http_code_send\n";
}

echo "\n🎯 DIAGNÓSTICO FINAL:\n";
echo "=====================\n";

if (defined('WHATSAPP_ROBOT_URL') && $http_code_send == 200) {
    echo "✅ CONFIGURAÇÃO CORRETA - O problema pode estar nos logs\n";
    echo "🔍 Verifique os logs do webhook em produção\n";
} else {
    echo "❌ CONFIGURAÇÃO COM PROBLEMA\n";
    echo "🔧 WHATSAPP_ROBOT_URL precisa ser configurado corretamente\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Verificar logs do webhook em produção\n";
echo "2. Testar envio real de mensagem\n";
echo "3. Monitorar se Ana responde via WhatsApp\n";
?> 