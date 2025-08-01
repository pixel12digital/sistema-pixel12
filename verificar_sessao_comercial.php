<?php
require_once 'config.php';

echo "🔍 VERIFICANDO SESSÃO COMERCIAL\n";
echo "===============================\n\n";

$vps_url_3001 = "http://212.85.11.238:3001";

// Verificar status geral
echo "📋 STATUS GERAL:\n";
echo "================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Resposta: $response\n\n";

// Verificar sessões
echo "📋 SESSÕES ATIVAS:\n";
echo "==================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_sessions = curl_exec($ch);
$http_code_sessions = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_sessions\n";
echo "Resposta: $response_sessions\n\n";

// Verificar QR Code da sessão comercial
echo "📋 QR CODE SESSÃO COMERCIAL:\n";
echo "============================\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/qr/comercial");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_qr = curl_exec($ch);
$http_code_qr = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_qr\n";
echo "Resposta: $response_qr\n\n";

echo "💡 INSTRUÇÕES:\n";
echo "==============\n";
echo "1. Se a sessão estiver 'qr_ready', escaneie o QR Code\n";
echo "2. Se estiver 'connected', a sessão está pronta\n";
echo "3. Acesse: http://212.85.11.238:3001/qr?session=comercial\n";
echo "4. Escaneie com o WhatsApp 4797309525\n";
?> 