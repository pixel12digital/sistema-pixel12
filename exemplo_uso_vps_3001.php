<?php
/**
 * EXEMPLO DE USO - VPS 3001 PRINCIPAL
 * Como usar a VPS 3001 que está funcionando perfeitamente
 */

require_once 'config_vps_3001_principal.php';

echo "📝 EXEMPLO DE USO - VPS 3001 PRINCIPAL\n";
echo "=====================================\n\n";

// Exemplo 1: Obter URL da VPS
echo "1️⃣ OBTENDO URL DA VPS\n";
echo "URL Principal: " . getVpsPrincipal() . "\n";
echo "URL para porta 3000: " . getVpsUrl('3000') . "\n";
echo "URL para porta 3001: " . getVpsUrl('3001') . "\n\n";

// Exemplo 2: Verificar endpoints
echo "2️⃣ VERIFICANDO ENDPOINTS\n";
$endpoints = ['/status', '/qr', '/session/start/default'];
foreach ($endpoints as $endpoint) {
    $funciona = endpointFuncionaVps3001($endpoint);
    echo "Endpoint $endpoint: " . ($funciona ? '✅' : '❌') . "\n";
}

echo "\n";

// Exemplo 3: Fazer requisição
echo "3️⃣ FAZENDO REQUISIÇÃO\n";
$ch = curl_init(getVpsPrincipal() . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $status = json_decode($response, true);
    echo "✅ Requisição bem-sucedida\n";
    echo "📊 Status: " . ($status['status'] ?? 'unknown') . "\n";
    echo "📱 Ready: " . ($status['ready'] ? 'true' : 'false') . "\n";
} else {
    echo "❌ Erro na requisição (HTTP $http_code)\n";
}

echo "\n✅ Exemplo concluído!\n";
?>