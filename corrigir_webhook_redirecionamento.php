<?php
/**
 * Corrigir Problema de Redirecionamento do Webhook
 * O webhook está retornando HTTP 301, o que pode estar causando problemas
 */

echo "🔧 CORRIGINDO PROBLEMA DE REDIRECIONAMENTO DO WEBHOOK\n";
echo "====================================================\n\n";

// 1. Testar webhook com redirecionamento
echo "1️⃣ TESTANDO WEBHOOK COM REDIRECIONAMENTO\n";
echo "----------------------------------------\n";

$webhook_url = 'http://212.85.11.238:8080/api/webhook.php';

$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste correção redirecionamento - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

// Teste 1: Sem seguir redirecionamentos
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Não seguir redirecionamentos
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Teste SEM redirecionamento:\n";
echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n";
echo "  Error: " . ($error ?: 'Nenhum') . "\n\n";

// Teste 2: Seguindo redirecionamentos
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecionamentos
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response2 = curl_exec($ch);
$http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$error2 = curl_error($ch);
curl_close($ch);

echo "Teste COM redirecionamento:\n";
echo "  HTTP Code: $http_code2\n";
echo "  Final URL: $final_url\n";
echo "  Response: $response2\n";
echo "  Error: " . ($error2 ?: 'Nenhum') . "\n\n";

// 2. Verificar se a mensagem foi salva
echo "2️⃣ VERIFICANDO SE MENSAGEM FOI SALVA\n";
echo "-------------------------------------\n";

// Conectar ao banco
$mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');

if ($mysqli->connect_error) {
    echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n\n";
} else {
    echo "✅ Conectado ao banco de dados\n";
    
    // Buscar mensagens recentes
    $sql = "SELECT m.*, c.nome as cliente_nome 
            FROM mensagens_comunicacao m 
            LEFT JOIN clientes c ON m.cliente_id = c.id 
            WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            ORDER BY m.data_hora DESC 
            LIMIT 5";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "📨 Mensagens recentes (últimos 2 minutos):\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - ID: {$row['id']} | Cliente: {$row['cliente_nome']} | Mensagem: {$row['mensagem']} | Data: {$row['data_hora']}\n";
        }
    } else {
        echo "❌ Nenhuma mensagem encontrada nos últimos 2 minutos\n";
    }
    
    $mysqli->close();
}

// 3. Configurar webhook com URL interna
echo "\n3️⃣ CONFIGURANDO WEBHOOK COM URL INTERNA\n";
echo "----------------------------------------\n";

// Tentar URL interna
$webhook_interno = 'http://localhost:8080/api/webhook.php';

$servers = [
    'default' => 'http://212.85.11.238:3000',
    'comercial' => 'http://212.85.11.238:3001'
];

foreach ($servers as $name => $server_url) {
    echo "📱 Configurando servidor $name com URL interna...\n";
    
    $ch = curl_init($server_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_interno]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($ch);
    $config_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Configuração: HTTP $config_http_code\n";
    if ($config_response) {
        $result = json_decode($config_response, true);
        if ($result) {
            echo "  Resultado: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    // Testar webhook interno
    echo "  🧪 Testando webhook interno...\n";
    
    $ch = curl_init($server_url . '/webhook/test');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $test_response = curl_exec($ch);
    $test_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Teste: HTTP $test_code\n";
    if ($test_response) {
        $test_data = json_decode($test_response, true);
        if ($test_data) {
            echo "  Resultado: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    echo "\n";
}

// 4. Voltar para URL externa se necessário
echo "4️⃣ VOLTANDO PARA URL EXTERNA\n";
echo "-----------------------------\n";

foreach ($servers as $name => $server_url) {
    echo "📱 Restaurando servidor $name para URL externa...\n";
    
    $ch = curl_init($server_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($ch);
    $config_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Configuração: HTTP $config_http_code\n";
}

echo "\n✅ CORREÇÃO CONCLUÍDA!\n";
echo "\n💡 RECOMENDAÇÕES:\n";
echo "1. O problema pode ser o redirecionamento HTTP 301\n";
echo "2. O servidor WhatsApp pode não estar seguindo redirecionamentos\n";
echo "3. Considere usar URL interna (localhost) se disponível\n";
echo "4. Verifique logs do servidor WhatsApp para mais detalhes\n";
?> 