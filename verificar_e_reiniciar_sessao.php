<?php
require_once "config.php";
require_once "painel/db.php";

echo "🔄 VERIFICANDO E REINICIANDO SESSÃO WHATSAPP\n";
echo "===========================================\n\n";

// 1. Verificar status atual
echo "📡 VERIFICANDO STATUS ATUAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ VPS respondeu (HTTP 200)\n";
    echo "   📊 Status: " . ($data['ready'] ? 'CONECTADO' : 'DESCONECTADO') . "\n";
    echo "   📱 Client Status: " . ($data['clients_status']['default']['status'] ?? 'N/A') . "\n";
    echo "   📞 Client Number: " . ($data['clients_status']['default']['number'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ VPS não respondeu (HTTP $http_code)\n";
    exit;
}

echo "\n";

// 2. Tentar logout para limpar a sessão
echo "🚪 FAZENDO LOGOUT PARA LIMPAR SESSÃO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/logout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Logout realizado\n";
    echo "   📊 Resposta: " . ($data['message'] ?? 'N/A') . "\n";
} else {
    echo "   ⚠️ Logout falhou (HTTP $http_code)\n";
}

echo "\n";

// 3. Aguardar 3 segundos
echo "⏳ Aguardando 3 segundos...\n";
sleep(3);

// 4. Verificar status após logout
echo "📡 VERIFICANDO STATUS APÓS LOGOUT:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ VPS respondeu (HTTP 200)\n";
    echo "   📊 Status: " . ($data['ready'] ? 'CONECTADO' : 'DESCONECTADO') . "\n";
    echo "   📱 Client Status: " . ($data['clients_status']['default']['status'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ VPS não respondeu (HTTP $http_code)\n";
}

echo "\n";

// 5. Se ainda estiver conectado, tentar forçar desconexão
if (isset($data['ready']) && $data['ready']) {
    echo "🔄 FORÇANDO DESCONEXÃO COMPLETA:\n";
    
    // Tentar desconectar via session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/session/default/disconnect");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        echo "   ✅ Desconexão forçada realizada\n";
    } else {
        echo "   ⚠️ Desconexão forçada falhou (HTTP $http_code)\n";
    }
    
    echo "   ⏳ Aguardando 5 segundos...\n";
    sleep(5);
}

echo "\n";

// 6. Verificar status final
echo "📡 STATUS FINAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ VPS respondeu (HTTP 200)\n";
    echo "   📊 Status: " . ($data['ready'] ? 'CONECTADO' : 'DESCONECTADO') . "\n";
    echo "   📱 Client Status: " . ($data['clients_status']['default']['status'] ?? 'N/A') . "\n";
    echo "   📞 Client Number: " . ($data['clients_status']['default']['number'] ?? 'N/A') . "\n";
    
    if (!$data['ready']) {
        echo "\n🎉 SESSÃO LIMPA! Agora você pode reconectar via QR Code.\n";
        echo "   Acesse o painel e clique em 'Conectar' no canal financeiro.\n";
    } else {
        echo "\n⚠️ SESSÃO AINDA ATIVA. Pode ser necessário reiniciar o servidor na VPS.\n";
    }
} else {
    echo "   ❌ VPS não respondeu (HTTP $http_code)\n";
}

echo "\n✅ PROCESSO CONCLUÍDO!\n";
?> 