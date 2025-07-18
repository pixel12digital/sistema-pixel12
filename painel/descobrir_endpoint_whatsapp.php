<?php
/**
 * Descobrir Endpoint Correto do WhatsApp
 * Testa diferentes possibilidades baseadas no endpoint /status funcionar
 */

echo "<h1>🔍 Descobrindo Endpoint Correto do WhatsApp</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .test{background:#f8f9fa;padding:15px;margin:15px 0;border-radius:8px;border-left:4px solid #007bff;}
    .success{color:#28a745;border-left-color:#28a745;}
    .error{color:#dc3545;border-left-color:#dc3545;}
    .warning{color:#ffc107;border-left-color:#ffc107;}
    .info{color:#17a2b8;border-left-color:#17a2b8;}
    .endpoint{background:#e9ecef;padding:10px;margin:5px 0;border-radius:5px;font-family:monospace;}
</style>";

echo "<div class='container'>";

$vps_url = "http://212.85.11.238:3000";

// 1. Primeiro, vamos ver o que o endpoint /status retorna
echo "<div class='test'>";
echo "<h3>1. 📊 Verificando Endpoint /status</h3>";

$ch = curl_init($vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "<p class='success'>✅ /status retornou HTTP $http_code</p>";
    echo "<div class='endpoint'>";
    echo "<strong>Resposta do /status:</strong><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";
    
    // Tentar decodificar JSON para entender a estrutura
    $data = json_decode($response, true);
    if ($data) {
        echo "<p class='info'>📋 Estrutura da resposta:</p>";
        echo "<div class='endpoint'>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<p class='error'>❌ /status retornou HTTP $http_code</p>";
}
echo "</div>";

// 2. Testar endpoints baseados em padrões comuns
echo "<div class='test'>";
echo "<h3>2. 🔍 Testando Endpoints Alternativos</h3>";

$endpoints_alternativos = [
    '/send-message',
    '/sendMessage',
    '/send_message',
    '/message/send',
    '/messages/send',
    '/whatsapp/send-message',
    '/whatsapp/sendMessage',
    '/api/v1/send',
    '/api/v1/message',
    '/api/v1/send-message',
    '/v1/send',
    '/v1/message',
    '/bot/send',
    '/bot/message',
    '/webhook/send',
    '/webhook/message'
];

$endpoints_funcionais = [];

foreach ($endpoints_alternativos as $endpoint) {
    $url = $vps_url . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "<div class='endpoint success'>✅ $endpoint - HTTP $http_code</div>";
        $endpoints_funcionais[] = $endpoint;
    } elseif ($http_code === 404) {
        echo "<div class='endpoint error'>❌ $endpoint - HTTP $http_code</div>";
    } elseif ($http_code === 405) {
        echo "<div class='endpoint warning'>⚠️ $endpoint - Método não permitido (HTTP $http_code)</div>";
        $endpoints_funcionais[] = $endpoint . " (método incorreto)";
    } else {
        echo "<div class='endpoint warning'>⚠️ $endpoint - HTTP $http_code</div>";
    }
}
echo "</div>";

// 3. Testar diferentes métodos no endpoint raiz
echo "<div class='test'>";
echo "<h3>3. 📡 Testando Métodos no Endpoint Raiz</h3>";

$metodos = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
foreach ($metodos as $metodo) {
    $ch = curl_init($vps_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "<p class='success'>✅ $metodo - HTTP $http_code</p>";
    } elseif ($http_code === 405) {
        echo "<p class='warning'>⚠️ $metodo - Método não permitido</p>";
    } else {
        echo "<p class='error'>❌ $metodo - HTTP $http_code</p>";
    }
}
echo "</div>";

// 4. Testar payload no endpoint raiz com POST
echo "<div class='test'>";
echo "<h3>4. 🔧 Testando Payload no Endpoint Raiz</h3>";

$payloads_teste = [
    'formato1' => ['to' => '5547996164699@c.us', 'message' => 'Teste endpoint raiz'],
    'formato2' => ['number' => '5547996164699', 'text' => 'Teste endpoint raiz'],
    'formato3' => ['phone' => '5547996164699', 'message' => 'Teste endpoint raiz'],
    'formato4' => ['recipient' => '5547996164699@c.us', 'content' => 'Teste endpoint raiz'],
    'formato5' => ['to' => '5547996164699@c.us', 'text' => 'Teste endpoint raiz'],
    'formato6' => ['number' => '5547996164699', 'message' => 'Teste endpoint raiz']
];

foreach ($payloads_teste as $nome => $payload) {
    $ch = curl_init($vps_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "<p class='success'>✅ $nome - HTTP $http_code</p>";
        echo "<p class='info'>Resposta: " . substr($response, 0, 100) . "...</p>";
    } elseif ($http_code === 400) {
        echo "<p class='warning'>⚠️ $nome - Payload inválido (HTTP $http_code)</p>";
    } elseif ($http_code === 404) {
        echo "<p class='error'>❌ $nome - Endpoint não encontrado (HTTP $http_code)</p>";
    } else {
        echo "<p class='error'>❌ $nome - HTTP $http_code</p>";
    }
}
echo "</div>";

// 5. Resumo e recomendações
echo "<div class='test'>";
echo "<h3>5. 📋 Resumo e Recomendações</h3>";

if (count($endpoints_funcionais) > 0) {
    echo "<p class='success'>✅ Endpoints funcionais encontrados:</p>";
    foreach ($endpoints_funcionais as $endpoint) {
        echo "<div class='endpoint'>• $endpoint</div>";
    }
} else {
    echo "<p class='error'>❌ Nenhum endpoint de envio encontrado</p>";
}

echo "<p class='info'>ℹ️ <strong>Análise:</strong></p>";
echo "<ul>";
echo "<li>O servidor está rodando (endpoint /status funciona)</li>";
echo "<li>O endpoint de envio pode estar na raiz (/)</li>";
echo "<li>Pode ser necessário um formato específico de payload</li>";
echo "<li>O servidor pode ter autenticação ou headers específicos</li>";
echo "</ul>";

echo "<p class='info'>ℹ️ <strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Verificar documentação do servidor WhatsApp</li>";
echo "<li>Testar com headers de autenticação</li>";
echo "<li>Verificar logs do servidor para erros</li>";
echo "<li>Contatar administrador do VPS</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
?> 