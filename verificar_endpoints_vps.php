<?php
/**
 * Verificar Endpoints VPS
 * Testa todos os endpoints disponíveis na VPS para identificar o correto
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "<h2>🔍 Verificação de Endpoints VPS</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

$vps_url = WHATSAPP_ROBOT_URL; // http://212.85.11.238:3000

// Lista de endpoints para testar
$endpoints = [
    'GET /status' => '/status',
    'GET /qr' => '/qr',
    'POST /send' => '/send',
    'POST /send/text' => '/send/text',
    'POST /send-message' => '/send-message',
    'POST /message' => '/message',
    'GET /' => '/',
    'GET /health' => '/health',
    'GET /ping' => '/ping'
];

echo "<h3>Testando Endpoints Disponíveis</h3>";

foreach ($endpoints as $name => $endpoint) {
    echo "<h4>Testando: $name</h4>";
    
    $test_url = $vps_url . $endpoint;
    echo "<p>URL: <code>$test_url</code></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    // Configurar método e dados para POST
    if (strpos($name, 'POST') !== false) {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($endpoint === '/send' || $endpoint === '/send/text') {
            $test_data = [
                'to' => '554797146908',
                'message' => 'Teste endpoint - ' . date('H:i:s')
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: Teste-Endpoints/1.0'
            ]);
        }
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Teste-Endpoints/1.0',
            'Accept: application/json'
        ]);
    }
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    $latency = round(($end_time - $start_time) * 1000, 2);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status_color = ($http_code === 200) ? '#d1fae5' : '#fee2e2';
    $status_icon = ($http_code === 200) ? '✅' : '❌';
    
    echo "<div style='background: $status_color; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>$status_icon $name</strong><br>";
    echo "HTTP Code: $http_code<br>";
    echo "Latência: {$latency}ms<br>";
    echo "Erro cURL: " . ($error ?: 'Nenhum') . "<br>";
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "<br><strong>Resposta JSON:</strong><br>";
            echo "<pre style='font-size: 12px; background: #f3f4f6; padding: 5px; border-radius: 3px;'>";
            echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT));
            echo "</pre>";
        } else {
            echo "<br><strong>Resposta (não JSON):</strong><br>";
            echo "<pre style='font-size: 12px; background: #f3f4f6; padding: 5px; border-radius: 3px;'>";
            echo htmlspecialchars(substr($response, 0, 300)) . (strlen($response) > 300 ? '...' : '');
            echo "</pre>";
        }
    }
    echo "</div>";
}

// Teste específico para envio com diferentes formatos
echo "<h3>Teste Específico de Envio</h3>";

$send_formats = [
    'Formato 1 (to/message)' => ['to' => '554797146908', 'message' => 'Teste formato 1'],
    'Formato 2 (number/message)' => ['number' => '554797146908', 'message' => 'Teste formato 2'],
    'Formato 3 (phone/message)' => ['phone' => '554797146908', 'message' => 'Teste formato 3'],
    'Formato 4 (sessionName/number/message)' => ['sessionName' => 'default', 'number' => '554797146908', 'message' => 'Teste formato 4']
];

foreach ($send_formats as $format_name => $data) {
    echo "<h4>Testando: $format_name</h4>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Teste-Formatos/1.0'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status_color = ($http_code === 200) ? '#d1fae5' : '#fee2e2';
    $status_icon = ($http_code === 200) ? '✅' : '❌';
    
    echo "<div style='background: $status_color; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>$status_icon $format_name</strong><br>";
    echo "HTTP Code: $http_code<br>";
    echo "Dados enviados: " . json_encode($data) . "<br>";
    echo "Erro cURL: " . ($error ?: 'Nenhum') . "<br>";
    
    if ($response) {
        $response_data = json_decode($response, true);
        if ($response_data) {
            echo "<br><strong>Resposta:</strong><br>";
            echo "<pre style='font-size: 12px; background: #f3f4f6; padding: 5px; border-radius: 3px;'>";
            echo htmlspecialchars(json_encode($response_data, JSON_PRETTY_PRINT));
            echo "</pre>";
        } else {
            echo "<br><strong>Resposta bruta:</strong><br>";
            echo htmlspecialchars(substr($response, 0, 200)) . (strlen($response) > 200 ? '...' : '');
        }
    }
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 