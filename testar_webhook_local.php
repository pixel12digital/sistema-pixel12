<?php
/**
 * 🧪 TESTE LOCAL DO WEBHOOK ANA
 * 
 * Simula uma mensagem do WhatsApp para testar o receptor
 */

echo "🧪 TESTANDO WEBHOOK ANA LOCALMENTE\n\n";

// Diferentes formatos de dados que o WhatsApp pode enviar
$testes = [
    [
        'name' => 'Formato Padrão',
        'data' => [
            'from' => '5547999999999',
            'body' => 'Preciso de um site para minha empresa'
        ]
    ],
    [
        'name' => 'Formato Alternativo 1',
        'data' => [
            'number' => '5547999999999',
            'message' => 'Quero falar com uma pessoa'
        ]
    ],
    [
        'name' => 'Formato Alternativo 2',
        'data' => [
            'phone' => '5547999999999',
            'text' => 'Preciso de suporte técnico'
        ]
    ]
];

$webhook_url = 'http://localhost/loja-virtual-revenda/painel/receber_mensagem_ana_local.php';

foreach ($testes as $teste) {
    echo "📤 Testando: " . $teste['name'] . "\n";
    echo "   Dados: " . json_encode($teste['data']) . "\n";
    
    // Enviar requisição POST
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($teste['data']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP: $http_code\n";
    echo "   Resposta: " . substr($response, 0, 200) . "\n";
    
    if ($http_code == 200) {
        $json_response = json_decode($response, true);
        if (isset($json_response['success']) && $json_response['success']) {
            echo "   ✅ SUCESSO!\n";
        } else {
            echo "   ❌ FALHA\n";
        }
    } else {
        echo "   ❌ ERRO HTTP\n";
    }
    
    echo "\n";
}

echo "🎯 TESTE DIRETO (como GET para debug):\n";
$test_url = $webhook_url . '?from=5547999999999&body=' . urlencode('Teste direto via GET');
echo "URL: $test_url\n";

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $http_code\n";
echo "Resposta: $response\n\n";

echo "✅ Testes concluídos!\n";
echo "📝 Verifique os logs em: painel/logs/ ou error_log do servidor\n";
?> 