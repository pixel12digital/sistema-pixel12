<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação da API WhatsApp na VPS</h1>";
echo "<p>Diagnosticando configuração e endpoints disponíveis...</p>";

// Configurações
$vps_url = 'http://212.85.11.238:3001';
$sessionName = 'comercial';

echo "<h2>📋 Configurações</h2>";
echo "<p><strong>VPS URL:</strong> $vps_url</p>";
echo "<p><strong>Sessão:</strong> $sessionName</p>";

// Teste 1: Verificar se a API está rodando
echo "<h2>🔍 Teste 1: Status da API</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
echo "<p><strong>Resposta:</strong> <pre>" . htmlspecialchars($response) . "</pre></p>";

// Teste 2: Verificar status da sessão comercial
echo "<h2>🔍 Teste 2: Status da Sessão Comercial</h2>";
$status_endpoint = "/session/{$sessionName}/status";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . $status_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
echo "<p><strong>Resposta:</strong> <pre>" . htmlspecialchars($response) . "</pre></p>";

// Teste 3: Tentar diferentes endpoints de envio
echo "<h2>📤 Teste 3: Endpoints de Envio</h2>";

$endpoints_envio = [
    "/send",
    "/send-message", 
    "/message/send",
    "/session/{$sessionName}/send",
    "/api/send",
    "/send-text",
    "/text/send",
    "/message",
    "/api/message/send"
];

$post_data = [
    'session' => $sessionName,
    'number' => '554796164699',
    'message' => 'Teste API VPS - ' . date('H:i:s')
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Método</th><th>Status</th></tr>";

foreach ($endpoints_envio as $endpoint) {
    // Teste POST
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code == 200) ? '✅ Funciona' : '❌ Não funciona';
    $color = ($http_code == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$endpoint</td>";
    echo "<td>$http_code</td>";
    echo "<td>POST</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
    
    // Se funcionou, mostrar resposta
    if ($http_code == 200) {
        echo "<tr><td colspan='4'><strong>Resposta:</strong> <pre>" . htmlspecialchars($response) . "</pre></td></tr>";
    }
}
echo "</table>";

// Teste 4: Verificar endpoints de mensagens
echo "<h2>📥 Teste 4: Endpoints de Mensagens</h2>";

$endpoints_mensagens = [
    "/messages",
    "/messages?session={$sessionName}",
    "/session/{$sessionName}/messages",
    "/chat/{$sessionName}/messages",
    "/api/messages",
    "/api/messages?session={$sessionName}",
    "/message/list",
    "/chat/messages"
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th></tr>";

foreach ($endpoints_mensagens as $endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code == 200) ? '✅ Funciona' : '❌ Não funciona';
    $color = ($http_code == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$endpoint</td>";
    echo "<td>$http_code</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
    
    if ($http_code == 200) {
        echo "<tr><td colspan='3'><strong>Resposta:</strong> <pre>" . htmlspecialchars($response) . "</pre></td></tr>";
    }
}
echo "</table>";

// Teste 5: Verificar documentação/ajuda
echo "<h2>📚 Teste 5: Documentação/Endpoints de Ajuda</h2>";

$endpoints_ajuda = [
    "/",
    "/help",
    "/docs",
    "/api",
    "/info",
    "/health",
    "/ping",
    "/version"
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th></tr>";

foreach ($endpoints_ajuda as $endpoint) {
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
    echo "</tr>";
    
    if ($http_code == 200) {
        echo "<tr><td colspan='3'><strong>Conteúdo:</strong> <pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre></td></tr>";
    }
}
echo "</table>";

echo "<h2>🎯 Comandos para executar na VPS</h2>";
echo "<p>Execute estes comandos na VPS para verificar a configuração:</p>";
echo "<pre>";
echo "# 1. Verificar se o processo está rodando\n";
echo "ps aux | grep whatsapp\n\n";
echo "# 2. Verificar logs do serviço\n";
echo "journalctl -u whatsapp-api -f\n\n";
echo "# 3. Verificar portas em uso\n";
echo "netstat -tlnp | grep :3001\n\n";
echo "# 4. Verificar arquivos de configuração\n";
echo "ls -la /var/whatsapp-api/\n\n";
echo "# 5. Verificar se há documentação\n";
echo "find /var/whatsapp-api/ -name '*.md' -o -name 'README*' -o -name '*.txt'\n\n";
echo "# 6. Verificar variáveis de ambiente\n";
echo "env | grep -i whatsapp\n\n";
echo "# 7. Testar endpoint localmente\n";
echo "curl -X GET http://localhost:3001/status\n";
echo "curl -X GET http://localhost:3001/session/comercial/status\n";
echo "</pre>";

echo "<h2>🔧 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Execute os comandos acima na VPS</li>";
echo "<li>Verifique se há documentação da API</li>";
echo "<li>Confirme se os endpoints estão configurados corretamente</li>";
echo "<li>Verifique se há logs de erro</li>";
echo "<li>Teste os endpoints localmente na VPS</li>";
echo "</ol>";

echo "<p><a href='painel/comunicacao.php'>← Voltar para a interface de comunicação</a></p>";
?> 