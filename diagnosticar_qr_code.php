<?php
require_once 'config.php';

echo "🔍 DIAGNÓSTICO QR CODE\n";
echo "======================\n\n";

// Testar porta 3000 (Financeiro)
echo "📋 TESTANDO PORTA 3000 (FINANCEIRO):\n";
echo "===================================\n";
$vps_url_3000 = "http://212.85.11.238:3000";

// 1. Status geral
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: $http_code\n";
echo "Resposta: $response\n\n";

// 2. Sessões
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_sessions = curl_exec($ch);
$http_code_sessions = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Sessões HTTP: $http_code_sessions\n";
echo "Resposta: $response_sessions\n\n";

// 3. QR Code
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/qr");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_qr = curl_exec($ch);
$http_code_qr = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "QR Code HTTP: $http_code_qr\n";
echo "Resposta: $response_qr\n\n";

// Testar porta 3001 (Comercial)
echo "📋 TESTANDO PORTA 3001 (COMERCIAL):\n";
echo "===================================\n";
$vps_url_3001 = "http://212.85.11.238:3001";

// 1. Status geral
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: $http_code_3001\n";
echo "Resposta: $response_3001\n\n";

// 2. Sessões
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_sessions_3001 = curl_exec($ch);
$http_code_sessions_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Sessões HTTP: $http_code_sessions_3001\n";
echo "Resposta: $response_sessions_3001\n\n";

// 3. QR Code comercial
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/qr/comercial");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_qr_3001 = curl_exec($ch);
$http_code_qr_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "QR Code Comercial HTTP: $http_code_qr_3001\n";
echo "Resposta: $response_qr_3001\n\n";

echo "💡 DIAGNÓSTICO:\n";
echo "===============\n";
echo "1. Se os servidores estão online mas QR não aparece:\n";
echo "   - As sessões podem não estar iniciadas\n";
echo "   - Pode ser necessário criar as sessões primeiro\n\n";

echo "🔧 SOLUÇÕES:\n";
echo "===========\n";
echo "1. No VPS, execute:\n";
echo "   curl -X POST http://localhost:3000/session/start/default\n";
echo "   curl -X POST http://localhost:3001/session/start/comercial\n\n";

echo "2. Depois verifique os QR Codes:\n";
echo "   curl http://localhost:3000/qr/default\n";
echo "   curl http://localhost:3001/qr/comercial\n\n";

echo "3. Se ainda não funcionar, reinicie os servidores:\n";
echo "   pm2 restart whatsapp-api\n";
echo "   pm2 restart whatsapp-3001\n";
?> 