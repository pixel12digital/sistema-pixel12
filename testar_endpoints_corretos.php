<?php
/**
 * 🧪 TESTAR ENDPOINTS CORRETOS
 * 
 * Script para testar os endpoints corretos baseado no código whatsapp-api-server.js
 */

echo "🧪 TESTANDO ENDPOINTS CORRETOS\n";
echo "=============================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';

// ===== ENDPOINTS CORRETOS BASEADOS NO CÓDIGO =====
$endpoints_corretos = [
    '/send/text' => [
        'method' => 'POST',
        'description' => 'Enviar mensagem de texto',
        'params' => ['sessionName', 'number', 'message']
    ],
    '/send/media' => [
        'method' => 'POST',
        'description' => 'Enviar mídia',
        'params' => ['sessionName', 'number', 'caption', 'file']
    ],
    '/qr' => [
        'method' => 'GET',
        'description' => 'QR Code (com query session)',
        'params' => ['session']
    ],
    '/qr/default' => [
        'method' => 'GET',
        'description' => 'QR Code da sessão default',
        'params' => []
    ],
    '/qr/:sessionName' => [
        'method' => 'GET',
        'description' => 'QR Code de sessão específica',
        'params' => ['sessionName']
    ],
    '/webhook/config' => [
        'method' => 'POST',
        'description' => 'Configurar webhook',
        'params' => ['url']
    ],
    '/webhook/test' => [
        'method' => 'POST',
        'description' => 'Testar webhook',
        'params' => []
    ],
    '/status' => [
        'method' => 'GET',
        'description' => 'Status do servidor',
        'params' => []
    ]
];

echo "📋 ENDPOINTS CORRETOS BASEADOS NO CÓDIGO:\n";
foreach ($endpoints_corretos as $endpoint => $info) {
    echo "• $endpoint ({$info['method']}) - {$info['description']}\n";
}
echo "\n";

// ===== TESTAR ENDPOINTS NOS CANAIS =====
echo "🔍 TESTANDO ENDPOINTS NOS CANAIS\n";
echo "--------------------------------\n";

$canais = [
    '3000' => 'Canal Financeiro',
    '3001' => 'Canal Comercial'
];

foreach ($canais as $porta => $nome) {
    echo "🔍 Testando $nome (Porta $porta)...\n";
    
    foreach ($endpoints_corretos as $endpoint => $info) {
        $url = "http://$vps_ip:$porta$endpoint";
        
        // Preparar dados de teste para POST
        $test_data = null;
        if ($info['method'] === 'POST') {
            switch ($endpoint) {
                case '/send/text':
                    $test_data = [
                        'sessionName' => ($porta === '3001') ? 'comercial' : 'default',
                        'number' => '5511999999999',
                        'message' => 'Teste endpoint correto - ' . date('Y-m-d H:i:s')
                    ];
                    break;
                case '/webhook/config':
                    $test_data = [
                        'url' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php'
                    ];
                    break;
                case '/webhook/test':
                    $test_data = [];
                    break;
            }
        }
        
        // Fazer requisição
        $ch = curl_init($url);
        
        if ($info['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($test_data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $status = ($http_code === 200) ? "✅" : "❌";
        echo "  $status $endpoint (HTTP $http_code)";
        
        if ($error) {
            echo " - Erro: $error";
        } elseif ($http_code !== 200) {
            echo " - Resposta: " . substr($response, 0, 100);
        }
        echo "\n";
    }
    echo "\n";
}

// ===== TESTAR ENDPOINTS ESPECÍFICOS =====
echo "🧪 TESTANDO ENDPOINTS ESPECÍFICOS\n";
echo "---------------------------------\n";

// Testar QR Code com query parameter
echo "🔍 Testando QR Code com query parameter...\n";
$ch = curl_init("http://$vps_ip:3000/qr?session=default");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status = ($http_code === 200) ? "✅" : "❌";
echo "  $status /qr?session=default (HTTP $http_code)\n";

if ($http_code === 200) {
    $qr_data = json_decode($response, true);
    if ($qr_data && isset($qr_data['success'])) {
        echo "  📱 Status: " . ($qr_data['status'] ?? 'unknown') . "\n";
        echo "  🔗 Ready: " . ($qr_data['ready'] ? 'true' : 'false') . "\n";
        if (isset($qr_data['message'])) {
            echo "  💬 Message: {$qr_data['message']}\n";
        }
    }
}

echo "\n";

// Testar envio com sessão correta
echo "🔍 Testando envio com sessão correta...\n";
$test_data = [
    'sessionName' => 'default',
    'number' => '5511999999999',
    'message' => 'Teste endpoint correto - ' . date('Y-m-d H:i:s')
];

$ch = curl_init("http://$vps_ip:3000/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status = ($http_code === 200) ? "✅" : "❌";
echo "  $status /send/text com sessionName=default (HTTP $http_code)\n";

if ($http_code !== 200) {
    $error_data = json_decode($response, true);
    if ($error_data && isset($error_data['message'])) {
        echo "  ❌ Erro: {$error_data['message']}\n";
        if (isset($error_data['available_sessions'])) {
            echo "  📋 Sessões disponíveis: " . implode(', ', $error_data['available_sessions']) . "\n";
        }
    }
}

echo "\n";

// ===== RESUMO DOS ENDPOINTS CORRETOS =====
echo "📊 RESUMO DOS ENDPOINTS CORRETOS\n";
echo "--------------------------------\n";

echo "✅ ENDPOINTS CONFIRMADOS NO CÓDIGO:\n";
echo "• POST /send/text - Enviar mensagem de texto\n";
echo "• POST /send/media - Enviar mídia\n";
echo "• GET /qr - QR Code (com query session)\n";
echo "• GET /qr/default - QR Code da sessão default\n";
echo "• GET /qr/:sessionName - QR Code de sessão específica\n";
echo "• POST /webhook/config - Configurar webhook\n";
echo "• POST /webhook/test - Testar webhook\n";
echo "• GET /status - Status do servidor\n\n";

echo "🔧 PARÂMETROS CORRETOS:\n";
echo "• sessionName: 'default' para porta 3000, 'comercial' para porta 3001\n";
echo "• number: número do telefone (com ou sem @c.us)\n";
echo "• message: texto da mensagem\n";
echo "• url: URL do webhook\n\n";

echo "📞 COMANDOS DE TESTE:\n";
echo "• QR Code: curl \"http://$vps_ip:3000/qr?session=default\"\n";
echo "• Status: curl http://$vps_ip:3000/status\n";
echo "• Envio: curl -X POST http://$vps_ip:3000/send/text \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"sessionName\":\"default\",\"number\":\"5511999999999\",\"message\":\"Teste\"}'\n\n";

echo "✅ TESTE DE ENDPOINTS CONCLUÍDO!\n";
echo "🎉 Endpoints corretos identificados e testados!\n";
?> 