<?php
// Diagnóstico do status da sessão comercial na porta 3001

echo "🔍 DIAGNÓSTICO SESSÃO COMERCIAL (PORTA 3001)\n";
echo "==========================================\n\n";

$vps_url = 'http://212.85.11.238:3001';

// 1. Consultar /sessions
echo "1️⃣ /sessions:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . '/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_sessions = curl_exec($ch);
$http_code_sessions = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $http_code_sessions\n";
echo "Resposta: $response_sessions\n\n";

// 2. Consultar /session/comercial/status
echo "2️⃣ /session/comercial/status:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . '/session/comercial/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_status = curl_exec($ch);
$http_code_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $http_code_status\n";
echo "Resposta: $response_status\n\n";

// 3. Consultar /status
echo "3️⃣ /status:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_status2 = curl_exec($ch);
$http_code_status2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $http_code_status2\n";
echo "Resposta: $response_status2\n\n";

// 4. Consultar /qr?session=comercial
echo "4️⃣ /qr?session=comercial:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . '/qr?session=comercial');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_qr = curl_exec($ch);
$http_code_qr = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $http_code_qr\n";
echo "Resposta: $response_qr\n\n";

?> 