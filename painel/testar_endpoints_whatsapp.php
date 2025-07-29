<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Teste de Endpoints WhatsApp</h1>";
echo "<h2>Descobrindo endpoint correto para envio de mensagens</h2>";

$base_url = "http://212.85.11.238:3000";
$numero_teste = "554796164699@c.us";
$mensagem_teste = "Teste de endpoint - " . date('H:i:s');

// Lista de possíveis endpoints para testar
$endpoints = [
    '/send',
    '/send-message',
    '/message',
    '/sendMessage',
    '/api/send',
    '/api/message',
    '/api/send-message',
    '/whatsapp/send',
    '/whatsapp/message',
    '/chat/send',
    '/chat/message',
    '/v1/send',
    '/v1/message',
    '/v2/send',
    '/v2/message'
];

echo "<h3>Testando diferentes endpoints...</h3>";

foreach ($endpoints as $endpoint) {
    echo "<h4>Testando: $endpoint</h4>";
    
    $url = $base_url . $endpoint;
    $payload = json_encode([
        'to' => $numero_teste,
        'message' => $mensagem_teste
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<ul>";
    echo "<li><strong>URL:</strong> $url</li>";
    echo "<li><strong>HTTP Code:</strong> $http_code</li>";
    echo "<li><strong>Resposta:</strong> " . htmlspecialchars(substr($response, 0, 200)) . "...</li>";
    if ($error) {
        echo "<li style='color: red;'><strong>Erro:</strong> $error</li>";
    }
    echo "</ul>";
    
    if ($http_code === 200) {
        echo "<p style='color: green;'>✅ <strong>ENDPOINT FUNCIONANDO: $endpoint</strong></p>";
        break;
    } elseif ($http_code === 404) {
        echo "<p style='color: red;'>❌ Endpoint não encontrado</p>";
    } elseif ($http_code === 405) {
        echo "<p style='color: orange;'>⚠️ Método não permitido (tentar GET)</p>";
        
        // Tentar com GET
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response_get = curl_exec($ch);
        $http_code_get = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>GET $endpoint:</strong> HTTP $http_code_get</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP $http_code</p>";
    }
    
    echo "<hr>";
}

echo "<h3>Testando métodos alternativos...</h3>";

// Testar com diferentes métodos HTTP
$endpoints_metodos = ['/send', '/message', '/api/send'];

foreach ($endpoints_metodos as $endpoint) {
    echo "<h4>Testando métodos para: $endpoint</h4>";
    
    $url = $base_url . $endpoint;
    
    // Testar GET
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response_get = curl_exec($ch);
    $http_code_get = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>GET $endpoint:</strong> HTTP $http_code_get</p>";
    
    // Testar PUT
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response_put = curl_exec($ch);
    $http_code_put = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>PUT $endpoint:</strong> HTTP $http_code_put</p>";
}

echo "<h3>Verificando documentação da API...</h3>";

// Tentar acessar endpoints de documentação
$docs_endpoints = [
    '/',
    '/docs',
    '/api',
    '/api/docs',
    '/help',
    '/info',
    '/status',
    '/health'
];

foreach ($docs_endpoints as $endpoint) {
    $url = $base_url . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "<p><strong>$endpoint (HTTP $http_code):</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($response, 0, 500));
        echo "</pre>";
    }
}

echo "<h3>Resumo...</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
echo "<p><strong>Problema identificado:</strong> O endpoint para envio de mensagens não está correto.</p>";
echo "<p><strong>Solução:</strong> É necessário descobrir o endpoint correto ou verificar a documentação da API do WhatsApp.</p>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Verificar documentação da API do WhatsApp</li>";
echo "<li>Consultar logs do servidor WhatsApp</li>";
echo "<li>Verificar configuração do servidor</li>";
echo "<li>Testar com ferramentas como Postman</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Teste concluído em:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 