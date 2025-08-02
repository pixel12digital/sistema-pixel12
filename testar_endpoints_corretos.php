<?php
/**
 * Teste Específico - Endpoints Corretos do Servidor Node.js
 */

echo "🔍 TESTE ESPECÍFICO - ENDPOINTS CORRETOS\n";
echo "========================================\n\n";

function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsApp-Diagnostic/1.0');
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    return [
        'http_code' => $http_code,
        'response' => $response,
        'curl_error' => $curl_error,
        'total_time' => $total_time
    ];
}

function parseResponse($response) {
    $parts = explode("\r\n\r\n", $response, 2);
    $headers = $parts[0] ?? '';
    $body = $parts[1] ?? '';
    return ['headers' => $headers, 'body' => $body];
}

echo "📋 1. TESTE DOS ENDPOINTS CORRETOS DO SERVIDOR NODE.JS\n";
echo "------------------------------------------------------\n";

$vps_url = 'http://212.85.11.238:3001'; // VPS 3001 que está funcionando

$endpoints = [
    // Status geral (contém todas as sessões)
    $vps_url . '/status',
    
    // QR Code com query parameter
    $vps_url . '/qr?session=comercial',
    $vps_url . '/qr?session=default',
    
    // Endpoints que NÃO existem (para confirmar)
    $vps_url . '/session/comercial/status',
    $vps_url . '/session/default/status',
    $vps_url . '/session/comercial/qr',
    $vps_url . '/session/default/qr'
];

foreach ($endpoints as $endpoint) {
    echo "\n🔗 Testando: $endpoint\n";
    $result = testEndpoint($endpoint);
    
    echo "   Status: " . $result['http_code'] . "\n";
    echo "   Tempo: " . number_format($result['total_time'], 3) . "s\n";
    
    if ($result['curl_error']) {
        echo "   ❌ Erro: " . $result['curl_error'] . "\n";
    } else {
        $parsed = parseResponse($result['response']);
        $body = $parsed['body'];
        $json = json_decode($body, true);
        
        if ($result['http_code'] == 200) {
            echo "   ✅ 200 OK\n";
            if ($json) {
                echo "   📄 JSON válido\n";
                if (isset($json['success'])) {
                    echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
                }
                if (isset($json['qr'])) {
                    echo "   🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
                }
                if (isset($json['clients_status'])) {
                    echo "   📱 Sessões: " . implode(', ', array_keys($json['clients_status'])) . "\n";
                }
                if (isset($json['status'])) {
                    echo "   📋 Status: " . $json['status'] . "\n";
                }
            } else {
                echo "   ⚠️ Resposta não é JSON\n";
                echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
            }
        } elseif ($result['http_code'] == 404) {
            echo "   ❌ 404 - Endpoint não existe\n";
        } elseif ($result['http_code'] == 503) {
            echo "   ⚠️ 503 - Serviço indisponível\n";
        } else {
            echo "   ❓ Status inesperado\n";
        }
    }
}

echo "\n\n📋 2. TESTE DE INICIALIZAÇÃO DE SESSÃO (POST)\n";
echo "-----------------------------------------------\n";

$session_endpoints = [
    $vps_url . '/session/start/comercial',
    $vps_url . '/session/start/default'
];

foreach ($session_endpoints as $endpoint) {
    echo "\n🚀 Testando POST: $endpoint\n";
    $result = testEndpoint($endpoint, 'POST');
    
    echo "   Status: " . $result['http_code'] . "\n";
    
    if ($result['curl_error']) {
        echo "   ❌ Erro: " . $result['curl_error'] . "\n";
    } else {
        $parsed = parseResponse($result['response']);
        $body = $parsed['body'];
        $json = json_decode($body, true);
        
        if ($json) {
            echo "   ✅ Resposta JSON válida\n";
            if (isset($json['success'])) {
                echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
            }
            if (isset($json['message'])) {
                echo "   💬 Message: " . $json['message'] . "\n";
            }
        } else {
            echo "   ⚠️ Resposta não é JSON\n";
            echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
        }
    }
}

echo "\n\n📋 3. TESTE DO PROXY CORRIGIDO\n";
echo "-------------------------------\n";

echo "\n🔗 Testando proxy com action=status:\n";
$proxy_data = "action=status&porta=3001";
$result = testEndpoint('http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php', 'POST', $proxy_data);

echo "   Status: " . $result['http_code'] . "\n";

if ($result['curl_error']) {
    echo "   ❌ Erro: " . $result['curl_error'] . "\n";
} else {
    $parsed = parseResponse($result['response']);
    $body = $parsed['body'];
    $json = json_decode($body, true);
    
    if ($json) {
        echo "   ✅ Resposta JSON válida\n";
        if (isset($json['success'])) {
            echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['status'])) {
            echo "   📋 Status: " . $json['status'] . "\n";
        }
        if (isset($json['debug'])) {
            echo "   🔍 Debug info disponível\n";
            if (isset($json['debug']['available_sessions'])) {
                echo "   📱 Sessões disponíveis: " . implode(', ', $json['debug']['available_sessions']) . "\n";
            }
        }
        if (isset($json['error'])) {
            echo "   ❌ Error: " . $json['error'] . "\n";
        }
    } else {
        echo "   ⚠️ Resposta não é JSON\n";
        echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
    }
}

echo "\n🔗 Testando proxy com action=qr:\n";
$proxy_data = "action=qr&porta=3001";
$result = testEndpoint('http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php', 'POST', $proxy_data);

echo "   Status: " . $result['http_code'] . "\n";

if ($result['curl_error']) {
    echo "   ❌ Erro: " . $result['curl_error'] . "\n";
} else {
    $parsed = parseResponse($result['response']);
    $body = $parsed['body'];
    $json = json_decode($body, true);
    
    if ($json) {
        echo "   ✅ Resposta JSON válida\n";
        if (isset($json['success'])) {
            echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            echo "   🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
        }
        if (isset($json['debug'])) {
            echo "   🔍 Debug info disponível\n";
        }
        if (isset($json['error'])) {
            echo "   ❌ Error: " . $json['error'] . "\n";
        }
    } else {
        echo "   ⚠️ Resposta não é JSON\n";
        echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
    }
}

echo "\n\n✅ Teste específico finalizado!\n";
?> 