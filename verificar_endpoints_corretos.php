<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação dos Endpoints Corretos da API</h1>";
echo "<p>Testando endpoints baseado na documentação da API...</p>";

// Configurações
$vps_url = 'http://212.85.11.238:3001';
$sessionName = 'comercial';
$numero_destino = '554796164699';

echo "<h2>📋 Configurações</h2>";
echo "<p><strong>VPS URL:</strong> $vps_url</p>";
echo "<p><strong>Sessão:</strong> $sessionName</p>";
echo "<p><strong>Número Destino:</strong> $numero_destino</p>";

// Baseado nos logs da API, vamos testar os endpoints corretos
echo "<h2>📤 Teste 1: Endpoints de Envio (baseado na documentação)</h2>";

$endpoints_envio = [
    "/message/send",
    "/message/text",
    "/send-message",
    "/text/send",
    "/api/message/send",
    "/api/send-message"
];

$mensagem_teste = "🧪 Teste endpoints corretos - " . date('H:i:s');

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th><th>Resposta</th></tr>";

foreach ($endpoints_envio as $endpoint) {
    $post_data = [
        'session' => $sessionName,
        'number' => $numero_destino,
        'message' => $mensagem_teste
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code == 200) ? '✅ Funciona' : '❌ Não funciona';
    $color = ($http_code == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$endpoint</td>";
    echo "<td>$http_code</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "<td><pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre></td>";
    echo "</tr>";
}
echo "</table>";

// Teste 2: Endpoints de mensagens
echo "<h2>📥 Teste 2: Endpoints de Mensagens</h2>";

$endpoints_mensagens = [
    "/message/list",
    "/message/history",
    "/messages/list",
    "/api/messages",
    "/api/message/list"
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th><th>Resposta</th></tr>";

foreach ($endpoints_mensagens as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint . "?session={$sessionName}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code == 200) ? '✅ Funciona' : '❌ Não funciona';
    $color = ($http_code == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$endpoint</td>";
    echo "<td>$http_code</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "<td><pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre></td>";
    echo "</tr>";
}
echo "</table>";

// Teste 3: Verificar documentação da API
echo "<h2>📚 Teste 3: Verificar Documentação da API</h2>";

$endpoints_docs = [
    "/",
    "/help",
    "/docs",
    "/api",
    "/info",
    "/endpoints"
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th><th>Conteúdo</th></tr>";

foreach ($endpoints_docs as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code == 200) ? '✅ Disponível' : '❌ Não disponível';
    $color = ($http_code == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$endpoint</td>";
    echo "<td>$http_code</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "<td><pre>" . htmlspecialchars(substr($response, 0, 300)) . "</pre></td>";
    echo "</tr>";
}
echo "</table>";

// Teste 4: Verificar se há outros endpoints baseados nos logs
echo "<h2>🔍 Teste 4: Endpoints Específicos da API</h2>";

// Baseado nos logs que você mostrou, vamos testar endpoints específicos
$endpoints_especificos = [
    "/session/{$sessionName}/send",
    "/session/{$sessionName}/message/send",
    "/session/{$sessionName}/text/send",
    "/api/session/{$sessionName}/send",
    "/api/session/{$sessionName}/message/send"
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th><th>Resposta</th></tr>";

foreach ($endpoints_especificos as $endpoint) {
    $post_data = [
        'number' => $numero_destino,
        'message' => $mensagem_teste
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code == 200) ? '✅ Funciona' : '❌ Não funciona';
    $color = ($http_code == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$endpoint</td>";
    echo "<td>$http_code</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "<td><pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre></td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 Comandos para executar na VPS</h2>";
echo "<p>Execute estes comandos na VPS para verificar os endpoints corretos:</p>";
echo "<pre>";
echo "# 1. Verificar todos os endpoints disponíveis\n";
echo "grep -n 'app\\.' whatsapp-api-server.js | grep -E '(get|post|put|delete)'\n\n";
echo "# 2. Verificar rotas específicas\n";
echo "grep -n 'router\\.' whatsapp-api-server.js\n\n";
echo "# 3. Verificar endpoints de envio\n";
echo "grep -n -i 'send' whatsapp-api-server.js\n\n";
echo "# 4. Verificar endpoints de mensagens\n";
echo "grep -n -i 'message' whatsapp-api-server.js\n\n";
echo "# 5. Testar endpoint localmente\n";
echo "curl -X POST http://localhost:3001/message/send -H 'Content-Type: application/json' -d '{\"session\":\"comercial\",\"number\":\"554796164699\",\"message\":\"teste\"}'\n";
echo "</pre>";

echo "<h2>🔧 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Execute os comandos acima na VPS</li>";
echo "<li>Identifique os endpoints corretos no código</li>";
echo "<li>Teste os endpoints localmente</li>";
echo "<li>Atualize o código com os endpoints corretos</li>";
echo "</ol>";

echo "<p><a href='painel/comunicacao.php'>← Voltar para a interface de comunicação</a></p>";
?> 