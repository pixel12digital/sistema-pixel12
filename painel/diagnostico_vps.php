<?php
/**
 * Diagnóstico do VPS WhatsApp
 * Descobre endpoints e configurações corretas
 */

echo "<h1>🔍 Diagnóstico do VPS WhatsApp</h1>";
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
$endpoints_teste = [
    '/',
    '/send',
    '/message',
    '/api/send',
    '/api/message',
    '/whatsapp/send',
    '/whatsapp/message',
    '/status',
    '/health',
    '/ping'
];

echo "<div class='test'>";
echo "<h3>🌐 Testando Conectividade com VPS</h3>";

// Teste básico de conectividade
$ch = curl_init($vps_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code > 0) {
    echo "<p class='success'>✅ VPS acessível (HTTP $http_code)</p>";
} else {
    echo "<p class='error'>❌ VPS inacessível: $error</p>";
    echo "<p class='info'>ℹ️ Verifique se o servidor está online</p>";
}
echo "</div>";

echo "<div class='test'>";
echo "<h3>🔍 Testando Endpoints</h3>";

$endpoints_funcionais = [];

foreach ($endpoints_teste as $endpoint) {
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
        echo "<div class='endpoint error'>❌ $endpoint - HTTP $http_code (Não encontrado)</div>";
    } elseif ($http_code > 0) {
        echo "<div class='endpoint warning'>⚠️ $endpoint - HTTP $http_code</div>";
    } else {
        echo "<div class='endpoint error'>❌ $endpoint - Sem resposta</div>";
    }
}
echo "</div>";

echo "<div class='test'>";
echo "<h3>📡 Testando Métodos HTTP</h3>";

// Testar diferentes métodos no endpoint raiz
$metodos = ['GET', 'POST', 'OPTIONS'];
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

echo "<div class='test'>";
echo "<h3>🔧 Testando Payload de Mensagem</h3>";

// Testar diferentes formatos de payload
$payloads_teste = [
    'formato1' => ['to' => '5547996164699@c.us', 'message' => 'Teste 1'],
    'formato2' => ['number' => '5547996164699', 'text' => 'Teste 2'],
    'formato3' => ['phone' => '5547996164699', 'message' => 'Teste 3'],
    'formato4' => ['recipient' => '5547996164699@c.us', 'content' => 'Teste 4']
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
        echo "<p class='warning'>⚠️ $nome - Payload inválido</p>";
    } else {
        echo "<p class='error'>❌ $nome - HTTP $http_code</p>";
    }
}
echo "</div>";

echo "<div class='test'>";
echo "<h3>📋 Resumo do Diagnóstico</h3>";

if (count($endpoints_funcionais) > 0) {
    echo "<p class='success'>✅ Endpoints funcionais encontrados:</p>";
    foreach ($endpoints_funcionais as $endpoint) {
        echo "<div class='endpoint'>• $endpoint</div>";
    }
} else {
    echo "<p class='error'>❌ Nenhum endpoint funcional encontrado</p>";
}

echo "<p class='info'>ℹ️ <strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Verificar se o servidor VPS está rodando</li>";
echo "<li>Confirmar a porta correta (3000)</li>";
echo "<li>Verificar logs do servidor WhatsApp</li>";
echo "<li>Testar com diferentes formatos de payload</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
?> 