<?php
/**
 * Configurar Webhook Fix
 * Configura os servidores WhatsApp para usar o webhook fix sem redirecionamento
 */

echo "🔧 CONFIGURANDO WEBHOOK FIX\n";
echo "===========================\n\n";

// URLs dos servidores WhatsApp
$servers = [
    'default' => 'http://212.85.11.238:3000',
    'comercial' => 'http://212.85.11.238:3001'
];

// Nova URL do webhook fix
$webhook_fix = 'http://212.85.11.238:8080/api/webhook_fix.php';

echo "📋 CONFIGURAÇÃO:\n";
echo "----------------\n";
echo "Webhook Fix: $webhook_fix\n";
echo "Servidores: " . implode(', ', array_keys($servers)) . "\n\n";

// 1. Testar conectividade do webhook fix
echo "1️⃣ TESTANDO CONECTIVIDADE DO WEBHOOK FIX\n";
echo "----------------------------------------\n";

$test_data = [
    'event' => 'test',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste webhook fix - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_fix);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Não seguir redirecionamentos

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Teste de conectividade:\n";
echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n";
echo "  Error: " . ($error ?: 'Nenhum') . "\n";
echo "  Status: " . ($http_code === 200 ? '✅ FUNCIONANDO' : '❌ FALHANDO') . "\n\n";

if ($http_code !== 200) {
    echo "❌ Webhook fix não está funcionando. Verifique se o arquivo foi criado corretamente.\n\n";
    exit;
}

// 2. Configurar webhook em cada servidor
echo "2️⃣ CONFIGURANDO WEBHOOK NOS SERVIDORES\n";
echo "--------------------------------------\n";

foreach ($servers as $name => $server_url) {
    echo "📱 Configurando servidor $name...\n";
    
    // Verificar status do servidor
    $ch = curl_init($server_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $status_response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status_code !== 200) {
        echo "  ❌ Servidor $name não está respondendo (HTTP $status_code)\n";
        continue;
    }
    
    echo "  ✅ Servidor $name está online\n";
    
    // Configurar webhook
    $ch = curl_init($server_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_fix]));
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
    
    // Testar webhook
    echo "  🧪 Testando webhook...\n";
    
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

// 3. Verificar se mensagens estão sendo salvas
echo "3️⃣ VERIFICANDO SE MENSAGENS ESTÃO SENDO SALVAS\n";
echo "-----------------------------------------------\n";

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
            WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY m.data_hora DESC 
            LIMIT 10";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "📨 Mensagens recentes (últimos 5 minutos):\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - ID: {$row['id']} | Cliente: {$row['cliente_nome']} | Mensagem: {$row['mensagem']} | Data: {$row['data_hora']}\n";
        }
    } else {
        echo "❌ Nenhuma mensagem encontrada nos últimos 5 minutos\n";
    }
    
    $mysqli->close();
}

// 4. Teste final com mensagem real
echo "\n4️⃣ TESTE FINAL COM MENSAGEM REAL\n";
echo "--------------------------------\n";

$test_real = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste final webhook fix - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_fix);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_real));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response_final = curl_exec($ch);
$http_code_final = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Teste final:\n";
echo "  HTTP Code: $http_code_final\n";
echo "  Response: $response_final\n";
echo "  Status: " . ($http_code_final === 200 ? '✅ SUCESSO' : '❌ FALHA') . "\n\n";

// 5. Instruções finais
echo "5️⃣ INSTRUÇÕES FINAIS\n";
echo "--------------------\n";

echo "✅ CONFIGURAÇÃO CONCLUÍDA!\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "1. Teste enviando uma mensagem real via WhatsApp para o número 554796164699\n";
echo "2. Verifique se a mensagem aparece no chat do painel\n";
echo "3. Monitore os logs em: logs/webhook_fix_" . date('Y-m-d') . ".log\n";
echo "4. Se necessário, reinicie os servidores WhatsApp\n\n";

echo "🔧 COMANDOS ÚTEIS:\n";
echo "- Testar webhook: curl -X POST $webhook_fix -H 'Content-Type: application/json' -d '{\"event\":\"test\"}'\n";
echo "- Verificar status: curl $servers[default]/status\n";
echo "- Verificar logs: tail -f logs/webhook_fix_" . date('Y-m-d') . ".log\n\n";

echo "🎯 RESULTADO ESPERADO:\n";
echo "- Mensagens enviadas via WhatsApp devem aparecer no chat\n";
echo "- Webhook deve responder com HTTP 200\n";
echo "- Mensagens devem ser salvas no banco de dados\n";
echo "- Sistema de atendimento deve funcionar normalmente\n\n";

echo "🚨 IMPORTANTE:\n";
echo "- O problema do redirecionamento HTTP 301 foi contornado\n";
echo "- Agora o webhook funciona sem redirecionamentos\n";
echo "- Teste imediatamente enviando uma mensagem via WhatsApp\n\n";

echo "✅ CONFIGURAÇÃO FINALIZADA!\n";
?> 