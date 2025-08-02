<?php
/**
 * Teste do Webhook em Produção
 * Verifica se as mensagens estão chegando corretamente
 */

echo "🧪 Testando Webhook em Produção...\n\n";

// URL do webhook
$webhook_url = 'http://212.85.11.238:8080/api/webhook.php';

// Dados de teste
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste webhook produção - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

echo "📤 Enviando dados para: $webhook_url\n";
echo "📋 Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

// Fazer requisição
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 Resultado:\n";
echo "   HTTP Code: $http_code\n";
echo "   Response: $response\n";
if ($error) {
    echo "   Error: $error\n";
}

// Verificar se a mensagem foi salva no banco
echo "\n🔍 Verificando se mensagem foi salva no banco...\n";

// Conectar ao banco
$mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');

if ($mysqli->connect_error) {
    echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
    exit;
}

// Buscar mensagem recente
$numero_limpo = '554796164699';
$sql = "SELECT m.*, c.nome as cliente_nome 
        FROM mensagens_comunicacao m 
        LEFT JOIN clientes c ON m.cliente_id = c.id 
        WHERE c.celular LIKE '%$numero_limpo%' 
        ORDER BY m.data_hora DESC 
        LIMIT 5";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "✅ Mensagens encontradas:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Cliente: {$row['cliente_nome']} | Mensagem: {$row['mensagem']} | Data: {$row['data_hora']}\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada para o número $numero_limpo\n";
}

$mysqli->close();

echo "\n✅ Teste concluído!\n";
?> 