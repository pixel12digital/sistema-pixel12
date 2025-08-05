<?php
/**
 * Script para descobrir os endpoints disponíveis na API do VPS
 */

echo "=== DESCOBRINDO API DO VPS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- DESCOBRINDO VPS PORTA $porta ($vps_url) ---\n";
    
    // 1. Testar endpoint raiz
    echo "1. Testando endpoint raiz...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ Endpoint raiz responde (HTTP $http_code)\n";
        echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
    } else {
        echo "   ❌ Endpoint raiz não responde (HTTP $http_code)\n";
    }
    
    // 2. Testar endpoint /status
    echo "\n2. Testando endpoint /status...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ Endpoint /status responde (HTTP $http_code)\n";
        $data = json_decode($response, true);
        if ($data) {
            echo "   📊 Estrutura da resposta: " . json_encode(array_keys($data)) . "\n";
            if (isset($data['clients_status'])) {
                echo "   📱 Sessões: " . implode(', ', array_keys($data['clients_status'])) . "\n";
            }
        }
    } else {
        echo "   ❌ Endpoint /status não responde (HTTP $http_code)\n";
    }
    
    // 3. Testar diferentes endpoints de inicialização
    $init_endpoints = [
        '/init',
        '/session/init',
        '/session/default/init',
        '/session/comercial/init',
        '/start',
        '/session/start',
        '/create',
        '/session/create'
    ];
    
    echo "\n3. Testando endpoints de inicialização...\n";
    foreach ($init_endpoints as $endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['sessionName' => 'test']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            echo "   ✅ $endpoint responde (HTTP $http_code)\n";
        } elseif ($http_code == 404) {
            echo "   ❌ $endpoint não encontrado (HTTP $http_code)\n";
        } else {
            echo "   ⚠️ $endpoint retorna HTTP $http_code\n";
        }
    }
    
    // 4. Testar endpoint de QR
    echo "\n4. Testando endpoint de QR...\n";
    $qr_endpoints = [
        '/qr',
        '/qr?session=default',
        '/qr?session=comercial',
        '/session/default/qr',
        '/session/comercial/qr'
    ];
    
    foreach ($qr_endpoints as $endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            echo "   ✅ $endpoint responde (HTTP $http_code)\n";
            $data = json_decode($response, true);
            if ($data && isset($data['qr'])) {
                echo "   📱 QR Code disponível (tamanho: " . strlen($data['qr']) . " chars)\n";
            }
        } elseif ($http_code == 404) {
            echo "   ❌ $endpoint não encontrado (HTTP $http_code)\n";
        } else {
            echo "   ⚠️ $endpoint retorna HTTP $http_code\n";
        }
    }
    
    // 5. Testar endpoint de logout
    echo "\n5. Testando endpoint de logout...\n";
    $logout_endpoints = [
        '/logout',
        '/session/logout',
        '/session/default/logout',
        '/session/comercial/logout',
        '/disconnect',
        '/session/default/disconnect',
        '/session/comercial/disconnect'
    ];
    
    foreach ($logout_endpoints as $endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            echo "   ✅ $endpoint responde (HTTP $http_code)\n";
        } elseif ($http_code == 404) {
            echo "   ❌ $endpoint não encontrado (HTTP $http_code)\n";
        } else {
            echo "   ⚠️ $endpoint retorna HTTP $http_code\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "=== FIM DA DESCOBERTA ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 