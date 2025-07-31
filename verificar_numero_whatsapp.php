<?php
echo "🔍 VERIFICANDO NÚMERO DO WHATSAPP\n";
echo "=================================\n\n";

$vps_ip = '212.85.11.238';

// Endpoints para testar
$endpoints = [
    '/status',
    '/sessions',
    '/clients',
    '/info'
];

foreach ($endpoints as $endpoint) {
    echo "🔍 Testando {$endpoint}...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001{$endpoint}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP Code: $http_code\n";
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "   Resposta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            
            // Procurar por número em diferentes caminhos
            $numero_encontrado = null;
            
            if (isset($data['clients_status']['default']['number'])) {
                $numero_encontrado = $data['clients_status']['default']['number'];
            } elseif (isset($data['number'])) {
                $numero_encontrado = $data['number'];
            } elseif (isset($data['client']['number'])) {
                $numero_encontrado = $data['client']['number'];
            } elseif (isset($data['sessions'][0]['number'])) {
                $numero_encontrado = $data['sessions'][0]['number'];
            }
            
            if ($numero_encontrado) {
                echo "   ✅ Número encontrado: $numero_encontrado\n";
            } else {
                echo "   ❌ Número não encontrado\n";
            }
        }
    } else {
        echo "   ❌ Endpoint não disponível\n";
    }
    
    echo "\n";
}

echo "🎯 VERIFICAÇÃO CONCLUÍDA!\n";
?> 