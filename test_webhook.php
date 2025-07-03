<?php
require_once 'config.php';

echo "=== TESTE DO WEBHOOK ===\n\n";

// Simular dados de um webhook do Asaas
$webhookData = [
    'event' => 'PAYMENT_RECEIVED',
    'payment' => [
        'id' => 'pay_test_' . time(),
        'customer' => 'cus_test_customer',
        'value' => 100.00,
        'status' => 'RECEIVED',
        'dueDate' => date('Y-m-d'),
        'paymentDate' => date('Y-m-d'),
        'description' => 'Teste de webhook',
        'billingType' => 'BOLETO',
        'invoiceUrl' => 'https://example.com/invoice.pdf'
    ]
];

echo "Dados do webhook:\n";
echo json_encode($webhookData, JSON_PRETTY_PRINT) . "\n\n";

// Fazer requisição para o webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/loja-virtual-revenda/api/webhooks.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Asaas-Access-Token: PAYMENT_RECEIVED'
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Resposta do webhook (HTTP $httpCode):\n";
echo $result . "\n\n";

// Verificar se os dados foram salvos no banco
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    // Verificar se a cobrança foi criada
    $asaas_id = $webhookData['payment']['id'];
    $stmt = $mysqli->prepare("SELECT * FROM cobrancas WHERE asaas_payment_id = ? LIMIT 1");
    $stmt->bind_param('s', $asaas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cobranca = $result->fetch_assoc();
    $stmt->close();
    
    if ($cobranca) {
        echo "✅ Cobrança encontrada no banco:\n";
        echo "- ID: " . $cobranca['id'] . "\n";
        echo "- Status: " . $cobranca['status'] . "\n";
        echo "- Valor: R$ " . $cobranca['valor'] . "\n";
        echo "- Data de criação: " . $cobranca['data_criacao'] . "\n";
    } else {
        echo "❌ Cobrança não encontrada no banco\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar banco: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?> 