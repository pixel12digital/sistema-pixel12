<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Configurações da VPS
$vps_url = 'http://212.85.11.238:3000';
$session_name = $_POST['session_name'] ?? 'default';

function makeVPSRequest($endpoint, $method = 'GET', $data = null) {
    global $vps_url;
    
    $url = $vps_url . $endpoint;
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'WhatsApp-Manager/1.0'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => !$error && $http_code < 400,
        'http_code' => $http_code,
        'data' => $response ? json_decode($response, true) : null,
        'raw_response' => $response,
        'error' => $error
    ];
}

// Primeiro, verificar sessões existentes
$sessions_result = makeVPSRequest('/sessions');

// Tentar iniciar nova sessão
$start_result = makeVPSRequest("/session/start/$session_name", 'POST');

// Se não funcionou, tentar endpoint alternativo
if (!$start_result['success']) {
    $start_result = makeVPSRequest("/session/$session_name/start", 'POST');
}

// Se ainda não funcionou, tentar endpoint genérico
if (!$start_result['success']) {
    $start_result = makeVPSRequest("/start", 'POST', ['session' => $session_name]);
}

// Aguardar um pouco e verificar se QR está disponível
sleep(2);
$qr_result = makeVPSRequest("/session/$session_name/qr");
if (!$qr_result['success']) {
    $qr_result = makeVPSRequest('/qr');
}

echo json_encode([
    'success' => $start_result['success'],
    'session_name' => $session_name,
    'start_session' => [
        'success' => $start_result['success'],
        'http_code' => $start_result['http_code'],
        'data' => $start_result['data'],
        'error' => $start_result['error']
    ],
    'existing_sessions' => [
        'success' => $sessions_result['success'],
        'data' => $sessions_result['data']
    ],
    'qr_check' => [
        'success' => $qr_result['success'],
        'http_code' => $qr_result['http_code'],
        'data' => $qr_result['data'],
        'has_qr' => isset($qr_result['data']['qr']) && !empty($qr_result['data']['qr'])
    ],
    'instructions' => $start_result['success'] ? 
        'Sessão iniciada! Tente abrir o modal QR Code novamente.' : 
        'Falha ao iniciar sessão. Verifique se o serviço WhatsApp está funcionando.',
    'debug' => [
        'vps_url' => $vps_url,
        'timestamp' => date('Y-m-d H:i:s'),
        'attempted_endpoints' => [
            "/session/start/$session_name",
            "/session/$session_name/start",
            "/start"
        ]
    ]
]);
?> 