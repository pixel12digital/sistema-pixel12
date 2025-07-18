<?php
echo "<h1>🔍 Descoberta Automática de Endpoints da VPS</h1>";
echo "<p>Testando endpoints comuns para envio de mensagens WhatsApp...</p>";

$vps_base = 'http://212.85.11.238:3000';

// Funções auxiliares (definidas antes de serem usadas)
function getContentType($headers) {
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Type:') === 0) {
            return trim(substr($header, 13));
        }
    }
    return 'unknown';
}

function isPotentialSendEndpoint($http_code, $response) {
    // Endpoints que retornam 200, 201, 400, 422 são candidatos (aceitam a requisição)
    if (in_array($http_code, [200, 201, 400, 422])) {
        return true;
    }
    
    // Se retornou algum conteúdo, pode ser um endpoint válido
    if ($response && strlen($response) > 0) {
        return true;
    }
    
    return false;
}

// Lista de endpoints possíveis para envio de mensagens
$endpoints_to_test = [
    // Endpoints básicos
    '/send',
    '/message',
    '/message/send',
    '/messages',
    '/messages/send',
    '/api/send',
    '/api/message',
    '/api/messages',
    
    // Endpoints com sessão
    '/session/send',
    '/session/message',
    '/session/messages',
    '/default/send',
    '/default/message',
    '/default/messages',
    
    // Endpoints alternativos
    '/whatsapp/send',
    '/whatsapp/message',
    '/whatsapp/messages',
    '/bot/send',
    '/bot/message',
    '/bot/messages',
    
    // Endpoints REST
    '/api/v1/send',
    '/api/v1/message',
    '/api/v1/messages',
    '/v1/send',
    '/v1/message',
    '/v1/messages',
    
    // Endpoints específicos
    '/text',
    '/text/send',
    '/chat/send',
    '/chat/message',
    '/chat/messages'
];

// Endpoints de informação (para comparação)
$info_endpoints = [
    '/status',
    '/sessions',
    '/qr',
    '/info',
    '/health',
    '/ping'
];

echo "<h2>📊 Testando Endpoints de Informação (Referência)</h2>";
$info_results = [];
foreach ($info_endpoints as $endpoint) {
    $url = $vps_base . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Endpoint-Discovery/1.0',
            'timeout' => 3
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $end_time = microtime(true);
    $latency = round(($end_time - $start_time) * 1000, 2);
    
    $http_response_header = $http_response_header ?? [];
    $http_code = 0;
    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
            $http_code = (int)$matches[1];
            break;
        }
    }
    
    $info_results[$endpoint] = [
        'url' => $url,
        'http_code' => $http_code,
        'success' => $response !== false,
        'latency_ms' => $latency,
        'response_preview' => $response ? substr($response, 0, 200) : null,
        'content_type' => getContentType($http_response_header)
    ];
}

// Exibir resultados dos endpoints de informação
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th><th>Latência</th><th>Resposta</th></tr>";
foreach ($info_results as $endpoint => $result) {
    $status_color = $result['success'] ? 'green' : 'red';
    $status_text = $result['success'] ? '✅ OK' : '❌ Erro';
    echo "<tr>";
    echo "<td><strong>$endpoint</strong></td>";
    echo "<td>{$result['http_code']}</td>";
    echo "<td style='color: $status_color'>$status_text</td>";
    echo "<td>{$result['latency_ms']}ms</td>";
    echo "<td>" . htmlspecialchars($result['response_preview'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>📤 Testando Endpoints de Envio (POST)</h2>";
$send_results = [];
$test_data = json_encode([
    'to' => '5511999999999',
    'message' => 'Teste de descoberta de endpoint'
]);

foreach ($endpoints_to_test as $endpoint) {
    $url = $vps_base . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'User-Agent: Endpoint-Discovery/1.0'
            ],
            'content' => $test_data,
            'timeout' => 5
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $end_time = microtime(true);
    $latency = round(($end_time - $start_time) * 1000, 2);
    
    $http_response_header = $http_response_header ?? [];
    $http_code = 0;
    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
            $http_code = (int)$matches[1];
            break;
        }
    }
    
    $send_results[$endpoint] = [
        'url' => $url,
        'http_code' => $http_code,
        'success' => $response !== false,
        'latency_ms' => $latency,
        'response_preview' => $response ? substr($response, 0, 300) : null,
        'content_type' => getContentType($http_response_header),
        'is_potential_send' => isPotentialSendEndpoint($http_code, $response)
    ];
}

// Exibir resultados dos endpoints de envio
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>HTTP Code</th><th>Status</th><th>Latência</th><th>Potencial</th><th>Resposta</th></tr>";
foreach ($send_results as $endpoint => $result) {
    $status_color = $result['success'] ? 'green' : 'red';
    $status_text = $result['success'] ? '✅ OK' : '❌ Erro';
    $potential_color = $result['is_potential_send'] ? 'blue' : 'gray';
    $potential_text = $result['is_potential_send'] ? '🎯 Candidato' : '❌ Não';
    
    echo "<tr>";
    echo "<td><strong>$endpoint</strong></td>";
    echo "<td>{$result['http_code']}</td>";
    echo "<td style='color: $status_color'>$status_text</td>";
    echo "<td>{$result['latency_ms']}ms</td>";
    echo "<td style='color: $potential_color'>$potential_text</td>";
    echo "<td>" . htmlspecialchars($result['response_preview'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Análise e recomendações
echo "<h2>🎯 Análise e Recomendações</h2>";

$potential_endpoints = array_filter($send_results, function($result) {
    return $result['is_potential_send'];
});

if (!empty($potential_endpoints)) {
    echo "<p>✅ <strong>Endpoints potenciais encontrados:</strong></p>";
    echo "<ul>";
    foreach ($potential_endpoints as $endpoint => $result) {
        echo "<li><strong>$endpoint</strong> (HTTP {$result['http_code']}) - {$result['latency_ms']}ms</li>";
    }
    echo "</ul>";
    
    // Recomendar o melhor endpoint
    $best_endpoint = array_keys($potential_endpoints)[0];
    echo "<p>🎯 <strong>Recomendação:</strong> Use o endpoint <code>$best_endpoint</code></p>";
    
    echo "<h3>🔧 Como aplicar a correção:</h3>";
    echo "<p>No arquivo <code>painel/ajax_whatsapp.php</code>, linha ~280, altere:</p>";
    echo "<pre>";
    echo "// ANTES:\n";
    echo "\$endpoint = '/send';\n\n";
    echo "// DEPOIS:\n";
    echo "\$endpoint = '$best_endpoint';\n";
    echo "</pre>";
} else {
    echo "<p>❌ <strong>Nenhum endpoint de envio encontrado.</strong></p>";
    echo "<p>Possíveis causas:</p>";
    echo "<ul>";
    echo "<li>A API não tem endpoint de envio implementado</li>";
    echo "<li>O endpoint usa um padrão diferente</li>";
    echo "<li>É necessário autenticação específica</li>";
    echo "<li>A API usa WebSocket em vez de HTTP</li>";
    echo "</ul>";
}

// Teste adicional: verificar se há documentação da API
echo "<h2>📚 Verificando Documentação da API</h2>";
$doc_endpoints = ['/docs', '/api', '/help', '/readme', '/swagger', '/openapi'];
$found_docs = false;

foreach ($doc_endpoints as $doc_endpoint) {
    $url = $vps_base . $doc_endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Endpoint-Discovery/1.0',
            'timeout' => 3
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "<p>✅ <strong>Documentação encontrada:</strong> <a href='$url' target='_blank'>$url</a></p>";
        $found_docs = true;
        break;
    }
}

if (!$found_docs) {
    echo "<p>❌ Nenhuma documentação encontrada nos endpoints comuns.</p>";
}

echo "<h2>✅ Descoberta Concluída!</h2>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ul>";
if (!empty($potential_endpoints)) {
    echo "<li>🎯 Aplicar a correção no arquivo ajax_whatsapp.php</li>";
    echo "<li>🧪 Testar o envio de mensagem com o novo endpoint</li>";
} else {
    echo "<li>📞 Verificar com o desenvolvedor da API WhatsApp</li>";
    echo "<li>📚 Consultar documentação da biblioteca WhatsApp usada</li>";
}
echo "<li>🔄 Testar novamente o chat após as correções</li>";
echo "</ul>";
?> 