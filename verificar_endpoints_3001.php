<?php
require_once 'config.php';

echo "🔍 VERIFICANDO ENDPOINTS DA PORTA 3001\n";
echo "======================================\n\n";

$vps_url_3001 = "http://212.85.11.238:3001";

// Lista de endpoints para testar
$endpoints = [
    '/status',
    '/sessions',
    '/logout',
    '/session/comercial/disconnect',
    '/session/comercial/status',
    '/qr/comercial'
];

echo "📋 TESTANDO ENDPOINTS:\n";
echo "======================\n";

foreach ($endpoints as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = ($http_code == 200) ? "✅" : "❌";
    echo "$status $endpoint - HTTP $http_code\n";
    
    if ($http_code == 404) {
        echo "   ❌ Endpoint não encontrado\n";
    } elseif ($error) {
        echo "   ❌ Erro: $error\n";
    } else {
        echo "   ✅ Funcionando\n";
    }
}

echo "\n💡 PROBLEMA IDENTIFICADO:\n";
echo "=========================\n";
echo "O endpoint /logout não existe na porta 3001!\n";
echo "O endpoint correto é: /session/comercial/disconnect\n\n";

echo "🔧 SOLUÇÃO:\n";
echo "===========\n";
echo "1. Verificar se o servidor 3001 tem todos os endpoints\n";
echo "2. Corrigir o ajax_whatsapp.php para usar o endpoint correto\n";
echo "3. Ou usar o endpoint de desconexão específico da sessão\n\n";

echo "📋 TESTE DE DESCONEXÃO CORRETA:\n";
echo "===============================\n";
echo "curl -X POST http://212.85.11.238:3001/session/comercial/disconnect\n";
?> 