<?php
/**
 * 🔍 VERIFICAR SESSÃO WHATSAPP
 * Verifica status detalhado da sessão WhatsApp no VPS
 */

echo "🔍 VERIFICANDO SESSÃO WHATSAPP\n";
echo "==============================\n\n";

$vps_url = "http://212.85.11.238:3000";

// 1. Verificar status geral
echo "1️⃣ STATUS GERAL DO VPS:\n";
echo "=======================\n";

$status_url = "$vps_url/status";
$ch = curl_init($status_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $status_data = json_decode($response, true);
    echo "✅ VPS Online\n";
    echo "Status: " . ($status_data['status'] ?? 'N/A') . "\n";
    echo "Ready: " . ($status_data['ready'] ? 'SIM' : 'NÃO') . "\n";
    
    if (isset($status_data['clients_status']['default'])) {
        $client = $status_data['clients_status']['default'];
        echo "WhatsApp Conectado: " . ($client['ready'] ? 'SIM' : 'NÃO') . "\n";
        echo "QR Code Necessário: " . ($client['hasQR'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "❌ VPS Offline - HTTP: $http_code\n";
    exit;
}

echo "\n2️⃣ VERIFICANDO SESSÕES ATIVAS:\n";
echo "==============================\n";

$sessions_url = "$vps_url/sessions";
$ch_sessions = curl_init($sessions_url);
curl_setopt($ch_sessions, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_sessions, CURLOPT_TIMEOUT, 10);
$response_sessions = curl_exec($ch_sessions);
$http_code_sessions = curl_getinfo($ch_sessions, CURLINFO_HTTP_CODE);
curl_close($ch_sessions);

if ($http_code_sessions == 200) {
    $sessions_data = json_decode($response_sessions, true);
    echo "📋 Sessões encontradas:\n";
    foreach ($sessions_data as $session) {
        echo "   - " . $session . "\n";
    }
} else {
    echo "❌ Erro ao verificar sessões - HTTP: $http_code_sessions\n";
}

echo "\n3️⃣ VERIFICANDO STATUS DA SESSÃO 'default':\n";
echo "==========================================\n";

$session_status_url = "$vps_url/sessions/default";
$ch_session = curl_init($session_status_url);
curl_setopt($ch_session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_session, CURLOPT_TIMEOUT, 10);
$response_session = curl_exec($ch_session);
$http_code_session = curl_getinfo($ch_session, CURLINFO_HTTP_CODE);
curl_close($ch_session);

if ($http_code_session == 200) {
    $session_data = json_decode($response_session, true);
    echo "✅ Sessão 'default' encontrada\n";
    echo "Status: " . ($session_data['status'] ?? 'N/A') . "\n";
    echo "Ready: " . ($session_data['ready'] ? 'SIM' : 'NÃO') . "\n";
    echo "QR Code: " . ($session_data['hasQR'] ? 'SIM' : 'NÃO') . "\n";
    
    if (isset($session_data['qr'])) {
        echo "QR Code Data: " . substr($session_data['qr'], 0, 50) . "...\n";
    }
} else {
    echo "❌ Erro ao verificar sessão 'default' - HTTP: $http_code_session\n";
}

echo "\n4️⃣ TESTE DE ENVIO COM VERIFICAÇÃO DETALHADA:\n";
echo "============================================\n";

$send_url = "$vps_url/send/text";
$data_envio = [
    "number" => "554796164699",
    "message" => "🧪 Teste sessão WhatsApp - " . date('H:i:s')
];

echo "📤 Enviando mensagem de teste...\n";

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

echo "📡 HTTP Code: $http_code_send\n";
echo "📡 Response: $response_send\n";

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
} else if ($http_code_send == 200) {
    $response_data = json_decode($response_send, true);
    if (isset($response_data['success']) && $response_data['success']) {
        echo "✅ ENVIO ACEITO PELO VPS\n";
        echo "ID: " . ($response_data['id'] ?? 'N/A') . "\n";
        echo "Status: " . ($response_data['status'] ?? 'N/A') . "\n";
        
        // Verificar se há informações de entrega
        if (isset($response_data['delivery'])) {
            echo "Entrega: " . json_encode($response_data['delivery']) . "\n";
        }
    } else {
        echo "❌ Erro na resposta: " . ($response_data['message'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "❌ Erro HTTP: $http_code_send\n";
}

echo "\n5️⃣ VERIFICANDO LOGS DO VPS:\n";
echo "===========================\n";

echo "📋 Para verificar logs em tempo real, execute no VPS:\n";
echo "   pm2 logs whatsapp-3000 --lines 10\n";
echo "   tail -f /var/log/whatsapp-api.log\n";

echo "\n🎯 DIAGNÓSTICO:\n";
echo "===============\n";

if ($http_code_send == 200) {
    echo "✅ VPS está aceitando mensagens\n";
    echo "❓ PROBLEMA: Mensagens não estão sendo entregues no WhatsApp\n";
    echo "🔧 SOLUÇÃO: Verificar se a sessão WhatsApp está realmente conectada\n";
    echo "💡 RECOMENDAÇÃO: Reconectar WhatsApp no VPS\n";
} else {
    echo "❌ VPS não está aceitando mensagens\n";
    echo "🔧 SOLUÇÃO: Reiniciar serviços no VPS\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Acessar VPS: ssh root@212.85.11.238\n";
echo "2. Verificar logs: pm2 logs whatsapp-3000\n";
echo "3. Reconectar WhatsApp se necessário\n";
echo "4. Testar envio novamente\n";
?> 