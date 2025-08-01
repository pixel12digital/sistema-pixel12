<?php
require_once 'config.php';

echo "🔍 VERIFICANDO SERVIDOR PORTA 3000\n";
echo "==================================\n\n";

$vps_url_3000 = "http://212.85.11.238:3000";

// Testar endpoint básico
echo "📋 TESTE 1 - ENDPOINT BÁSICO:\n";
echo "=============================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Curl Error: " . ($curl_error ?: 'Nenhum') . "\n";
echo "Resposta: $response\n\n";

// Testar endpoint de sessões
echo "📋 TESTE 2 - ENDPOINT SESSÕES:\n";
echo "==============================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Curl Error: " . ($curl_error ?: 'Nenhum') . "\n";
echo "Resposta: $response\n\n";

// Testar endpoint de sessão específica
echo "📋 TESTE 3 - ENDPOINT SESSÃO DEFAULT:\n";
echo "=====================================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/session/default/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Curl Error: " . ($curl_error ?: 'Nenhum') . "\n";
echo "Resposta: $response\n\n";

// Testar POST para iniciar sessão
echo "📋 TESTE 4 - INICIAR SESSÃO (POST):\n";
echo "===================================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/session/start/default");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Curl Error: " . ($curl_error ?: 'Nenhum') . "\n";
echo "Resposta: $response\n\n";

echo "💡 DIAGNÓSTICO:\n";
echo "===============\n";
if ($http_code == 200) {
    echo "✅ Servidor está funcionando!\n";
    echo "✅ Endpoints estão respondendo\n";
} elseif ($http_code == 0 || $curl_error) {
    echo "❌ Servidor não está acessível\n";
    echo "❌ Verifique se o processo está rodando na VPS\n";
} else {
    echo "⚠️ Servidor responde mas com erro HTTP $http_code\n";
    echo "⚠️ Verifique os logs do servidor na VPS\n";
}

echo "\n🔧 SOLUÇÃO:\n";
echo "===========\n";
echo "1. SSH na VPS: ssh root@212.85.11.238\n";
echo "2. Verificar processo: pm2 status\n";
echo "3. Reiniciar se necessário: pm2 restart whatsapp-api\n";
echo "4. Verificar logs: pm2 logs whatsapp-api\n";
?> 