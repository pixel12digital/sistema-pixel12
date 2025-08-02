<?php
/**
 * Script de Teste - Endpoints WhatsApp
 * Diagnóstico completo do problema 404 no QR Code
 */

echo "🔍 DIAGNÓSTICO COMPLETO - ENDPOINTS WHATSAPP\n";
echo "============================================\n\n";

// Função para fazer requisição HTTP
function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsApp-Diagnostic/1.0');
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    return [
        'http_code' => $http_code,
        'response' => $response,
        'curl_error' => $curl_error,
        'total_time' => $total_time
    ];
}

// Função para extrair headers e body
function parseResponse($response) {
    $parts = explode("\r\n\r\n", $response, 2);
    $headers = $parts[0] ?? '';
    $body = $parts[1] ?? '';
    return ['headers' => $headers, 'body' => $body];
}

echo "📋 1. TESTE DE CONECTIVIDADE VPS\n";
echo "--------------------------------\n";

$endpoints = [
    'http://212.85.11.238:3000/status',
    'http://212.85.11.238:3000/qr',
    'http://212.85.11.238:3001/status', 
    'http://212.85.11.238:3001/qr'
];

foreach ($endpoints as $endpoint) {
    echo "\n🔗 Testando: $endpoint\n";
    $result = testEndpoint($endpoint);
    
    echo "   Status: " . $result['http_code'] . "\n";
    echo "   Tempo: " . number_format($result['total_time'], 3) . "s\n";
    
    if ($result['curl_error']) {
        echo "   ❌ Erro: " . $result['curl_error'] . "\n";
    } else {
        $parsed = parseResponse($result['response']);
        echo "   📄 Headers: " . count(explode("\n", $parsed['headers'])) . " linhas\n";
        
        if ($result['http_code'] == 200) {
            $body = $parsed['body'];
            $json = json_decode($body, true);
            if ($json) {
                echo "   ✅ JSON válido\n";
                if (isset($json['success'])) {
                    echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
                }
                if (isset($json['status'])) {
                    echo "   📱 Status: " . $json['status'] . "\n";
                }
                if (isset($json['qr'])) {
                    echo "   🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
                }
            } else {
                echo "   ⚠️ Resposta não é JSON válido\n";
                echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
            }
        } elseif ($result['http_code'] == 404) {
            echo "   ❌ 404 - Endpoint não encontrado\n";
        } elseif ($result['http_code'] == 503) {
            echo "   ⚠️ 503 - Serviço indisponível\n";
        } else {
            echo "   ❓ Status inesperado\n";
        }
    }
}

echo "\n\n📋 2. TESTE DE INICIALIZAÇÃO DE SESSÕES\n";
echo "----------------------------------------\n";

$sessions = [
    ['url' => 'http://212.85.11.238:3000/session/start/default', 'name' => 'default'],
    ['url' => 'http://212.85.11.238:3001/session/start/comercial', 'name' => 'comercial']
];

foreach ($sessions as $session) {
    echo "\n🚀 Iniciando sessão: " . $session['name'] . "\n";
    echo "   URL: " . $session['url'] . "\n";
    
    $result = testEndpoint($session['url'], 'POST');
    
    echo "   Status: " . $result['http_code'] . "\n";
    
    if ($result['curl_error']) {
        echo "   ❌ Erro: " . $result['curl_error'] . "\n";
    } else {
        $parsed = parseResponse($result['response']);
        $body = $parsed['body'];
        $json = json_decode($body, true);
        
        if ($json) {
            echo "   ✅ Resposta JSON válida\n";
            if (isset($json['success'])) {
                echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
            }
            if (isset($json['message'])) {
                echo "   💬 Message: " . $json['message'] . "\n";
            }
        } else {
            echo "   ⚠️ Resposta não é JSON\n";
            echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
        }
    }
}

echo "\n\n📋 3. TESTE DO PROXY PHP LOCAL\n";
echo "-------------------------------\n";

echo "\n🔗 Testando proxy local: http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php\n";

$proxy_data = "action=qr&porta=3000";
$result = testEndpoint('http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php', 'POST', $proxy_data);

echo "   Status: " . $result['http_code'] . "\n";

if ($result['curl_error']) {
    echo "   ❌ Erro: " . $result['curl_error'] . "\n";
} else {
    $parsed = parseResponse($result['response']);
    $body = $parsed['body'];
    $json = json_decode($body, true);
    
    if ($json) {
        echo "   ✅ Resposta JSON válida\n";
        if (isset($json['success'])) {
            echo "   📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            echo "   🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
        }
        if (isset($json['debug'])) {
            echo "   🔍 Debug info disponível\n";
        }
        if (isset($json['error'])) {
            echo "   ❌ Error: " . $json['error'] . "\n";
        }
    } else {
        echo "   ⚠️ Resposta não é JSON\n";
        echo "   📄 Body: " . substr($body, 0, 200) . "...\n";
    }
}

echo "\n\n📋 4. ANÁLISE E RECOMENDAÇÕES\n";
echo "-----------------------------\n";

echo "\n🎯 DIAGNÓSTICO:\n";
echo "1. Se VPS retorna 404: Sessão não inicializada ou endpoint não existe\n";
echo "2. Se VPS retorna 503: Serviço rodando mas sessão não pronta\n";
echo "3. Se VPS não responde: Firewall ou serviço parado\n";
echo "4. Se proxy falha: Configuração incorreta ou VPS inacessível\n";

echo "\n🔧 PRÓXIMOS PASSOS:\n";
echo "1. Verificar se PM2 está rodando na VPS\n";
echo "2. Inicializar sessões com POST /session/start/\n";
echo "3. Verificar logs da VPS: ./logs/error.log\n";
echo "4. Testar conectividade: ping 212.85.11.238\n";

echo "\n📞 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "ssh root@212.85.11.238\n";
echo "pm2 status\n";
echo "pm2 logs whatsapp-api\n";
echo "netstat -tlnp | grep :3000\n";
echo "netstat -tlnp | grep :3001\n";

echo "\n✅ Diagnóstico completo finalizado!\n";
?> 