<?php
/**
 * 🔧 CONFIGURAR VPS PARA ENVIO DE MENSAGENS
 * 
 * Corrige o problema de envio de mensagens do VPS
 */

echo "=== 🔧 CONFIGURAR VPS PARA ENVIO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

$vps_ip = "212.85.11.238";
$vps_port = "3000";

// ===== 1. VERIFICAR ENDPOINTS DISPONÍVEIS =====
echo "1. 🔍 VERIFICANDO ENDPOINTS DISPONÍVEIS:\n";

$endpoints_test = [
    "/status",
    "/send",
    "/send-message", 
    "/sendText",
    "/sendMessage",
    "/api/send",
    "/api/sendMessage",
    "/session/default/send",
    "/webhook/send"
];

foreach ($endpoints_test as $endpoint) {
    $url = "http://$vps_ip:$vps_port$endpoint";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   📊 $endpoint: HTTP $http_code\n";
}

echo "\n";

// ===== 2. TESTAR DIFERENTES FORMATOS DE ENVIO =====
echo "2. 🧪 TESTANDO FORMATOS DE ENVIO:\n";

$message_formats = [
    [
        "endpoint" => "/send",
        "data" => [
            "chatId" => "554796164699@c.us",
            "text" => "🧪 Teste formato 1 - " . date('H:i:s')
        ]
    ],
    [
        "endpoint" => "/sendText", 
        "data" => [
            "chatId" => "554796164699@c.us", 
            "text" => "🧪 Teste formato 2 - " . date('H:i:s')
        ]
    ],
    [
        "endpoint" => "/sendMessage",
        "data" => [
            "chatId" => "554796164699@c.us",
            "message" => "🧪 Teste formato 3 - " . date('H:i:s')
        ]
    ],
    [
        "endpoint" => "/session/default/send",
        "data" => [
            "chatId" => "554796164699@c.us",
            "text" => "🧪 Teste formato 4 - " . date('H:i:s')
        ]
    ]
];

foreach ($message_formats as $i => $format) {
    echo "   🧪 Testando formato " . ($i + 1) . " ({$format['endpoint']}):\n";
    
    $url = "http://$vps_ip:$vps_port{$format['endpoint']}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($format['data']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "      📊 HTTP: $http_code\n";
    echo "      📄 Resposta: " . substr($response, 0, 100) . "\n";
    
    if ($http_code == 200) {
        echo "      ✅ FORMATO FUNCIONANDO!\n";
        
        // Salvar o formato que funciona
        file_put_contents('vps_working_format.json', json_encode([
            'endpoint' => $format['endpoint'],
            'working_format' => $format['data'],
            'tested_at' => date('Y-m-d H:i:s')
        ]));
        
        break;
    } else {
        echo "      ❌ Formato não funciona\n";
    }
    
    echo "\n";
}

echo "\n";

// ===== 3. VERIFICAR DOCUMENTAÇÃO DA API =====
echo "3. 📋 VERIFICANDO DOCUMENTAÇÃO DA API:\n";

$doc_endpoints = [
    "/docs",
    "/api-docs", 
    "/swagger",
    "/help",
    "/info"
];

foreach ($doc_endpoints as $doc) {
    $url = "http://$vps_ip:$vps_port$doc";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ Documentação encontrada: $doc\n";
        echo "   📄 URL: http://$vps_ip:$vps_port$doc\n";
    }
}

echo "\n";

// ===== 4. CRIAR SCRIPT DE ENVIO FUNCIONAL =====
echo "4. 🔧 CRIANDO SCRIPT DE ENVIO FUNCIONAL:\n";

if (file_exists('vps_working_format.json')) {
    $working_format = json_decode(file_get_contents('vps_working_format.json'), true);
    echo "   ✅ Formato funcional encontrado: {$working_format['endpoint']}\n";
    
    // Criar função de envio
    $send_function = '<?php
function enviarMensagemVPS($numero, $mensagem) {
    $url = "http://212.85.11.238:3000' . $working_format['endpoint'] . '";
    
    $data = [
        "chatId" => $numero,
        "text" => $mensagem
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        "success" => $http_code == 200,
        "http_code" => $http_code,
        "response" => $response
    ];
}

// Teste da função
$resultado = enviarMensagemVPS("554796164699@c.us", "🎉 Teste de envio funcional - " . date("Y-m-d H:i:s"));
echo "Resultado: " . json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
?>';

    file_put_contents('enviar_vps_funcional.php', $send_function);
    echo "   ✅ Script criado: enviar_vps_funcional.php\n";
    
} else {
    echo "   ❌ Nenhum formato funcional encontrado\n";
    echo "   🔧 Verifique os logs do VPS ou documentação\n";
}

echo "\n";

// ===== 5. PRÓXIMOS PASSOS =====
echo "5. 🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Integrar função de envio no webhook\n";
echo "   2. Testar envio de resposta da Ana\n";
echo "   3. Verificar se mensagem chega no WhatsApp\n";
echo "   4. Testar com mensagem real\n";

echo "\n=== FIM DA CONFIGURAÇÃO ===\n";
?> 