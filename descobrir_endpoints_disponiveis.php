<?php
/**
 * Descobrir Endpoints Disponíveis no VPS
 */

echo "=== DESCOBRINDO ENDPOINTS DISPONÍVEIS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

// Lista de endpoints para testar
$endpoints_to_test = [
    // Endpoints básicos
    '/' => 'GET',
    '/status' => 'GET',
    '/health' => 'GET',
    '/ping' => 'GET',
    
    // Endpoints de sessão
    '/session' => 'GET',
    '/sessions' => 'GET',
    '/clients' => 'GET',
    
    // Endpoints de QR
    '/qr' => 'GET',
    '/qrcode' => 'GET',
    
    // Endpoints de inicialização (diferentes variações)
    '/init' => 'POST',
    '/start' => 'POST',
    '/create' => 'POST',
    '/connect' => 'POST',
    '/login' => 'POST',
    
    // Endpoints com sessão específica
    '/session/default' => 'GET',
    '/session/comercial' => 'GET',
    '/session/default/status' => 'GET',
    '/session/comercial/status' => 'GET',
    '/session/default/qr' => 'GET',
    '/session/comercial/qr' => 'GET',
    
    // Endpoints de logout/disconnect
    '/logout' => 'POST',
    '/disconnect' => 'POST',
    '/session/default/logout' => 'POST',
    '/session/comercial/logout' => 'POST',
    '/session/default/disconnect' => 'POST',
    '/session/comercial/disconnect' => 'POST',
    
    // Endpoints de envio de mensagem
    '/send' => 'POST',
    '/message' => 'POST',
    '/send-message' => 'POST',
    '/session/default/send' => 'POST',
    '/session/comercial/send' => 'POST',
    
    // Endpoints de webhook
    '/webhook' => 'GET',
    '/webhook' => 'POST',
    
    // Endpoints de configuração
    '/config' => 'GET',
    '/settings' => 'GET',
    '/info' => 'GET'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- VPS PORTA $porta ($vps_url) ---\n";
    
    $available_endpoints = [];
    
    foreach ($endpoints_to_test as $endpoint => $method) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Considerar disponível se não for 404 ou erro de conexão
        if ($http_code != 404 && !$curl_error) {
            $available_endpoints[] = [
                'endpoint' => $endpoint,
                'method' => $method,
                'http_code' => $http_code,
                'response_preview' => substr($response, 0, 100)
            ];
            
            $status_icon = $http_code == 200 ? '✅' : ($http_code < 400 ? '⚠️' : '❌');
            echo "   $status_icon $method $endpoint (HTTP $http_code)\n";
            
            if ($http_code == 200) {
                echo "      📝 Resposta: " . substr($response, 0, 50) . "...\n";
            }
        }
    }
    
    echo "\n   📊 RESUMO VPS $porta:\n";
    echo "   Total de endpoints testados: " . count($endpoints_to_test) . "\n";
    echo "   Endpoints disponíveis: " . count($available_endpoints) . "\n";
    
    if (!empty($available_endpoints)) {
        echo "   ✅ Endpoints funcionais:\n";
        foreach ($available_endpoints as $ep) {
            if ($ep['http_code'] == 200) {
                echo "      - $ep[method] $ep[endpoint]\n";
            }
        }
    }
    
    echo "\n";
}

echo "=== INSTRUÇÕES PARA O VPS ===\n";
echo "Com base nos endpoints disponíveis, execute no VPS:\n\n";

echo "1. 🔍 Verificar documentação da API:\n";
echo "   cat /var/whatsapp-api/README.md\n";
echo "   cat /var/whatsapp-api/whatsapp-api-server.js | head -50\n\n";

echo "2. 🔍 Verificar logs para entender como inicializar:\n";
echo "   tail -f /var/whatsapp-api/monitoramento.log\n\n";

echo "3. 🧪 Testar endpoints que funcionaram:\n";
echo "   # Se /qr funcionou:\n";
echo "   curl http://localhost:3000/qr\n";
echo "   curl http://localhost:3001/qr\n\n";

echo "4. 🔄 Se necessário, reiniciar o serviço:\n";
echo "   cd /var/whatsapp-api\n";
echo "   pkill -f whatsapp-api-server\n";
echo "   node whatsapp-api-server.js &\n\n";

echo "=== FIM DA DESCOBERTA ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 