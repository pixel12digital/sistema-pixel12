<?php
/**
 * 🔍 VERIFICAR SE DEBUG WEBHOOK ESTÁ ACESSÍVEL
 * 
 * Este script verifica se o arquivo debug_webhook_real.php está acessível no servidor
 */

echo "🔍 VERIFICANDO SE DEBUG WEBHOOK ESTÁ ACESSÍVEL\n";
echo "==============================================\n\n";

$debug_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/debug_webhook_real.php";

// ===== 1. TESTAR ACESSIBILIDADE =====
echo "1️⃣ TESTANDO ACESSIBILIDADE:\n";
echo "============================\n";

$ch = curl_init($debug_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
} elseif ($http_code === 200) {
    echo "✅ Arquivo debug_webhook_real.php está acessível!\n";
    echo "📡 HTTP Code: $http_code\n";
    
    // Verificar se é JSON válido
    $json_data = json_decode($response, true);
    if ($json_data) {
        echo "✅ Resposta JSON válida\n";
        if (isset($json_data['debug']) && $json_data['debug']) {
            echo "✅ Modo debug ativo\n";
        }
    } else {
        echo "⚠️ Resposta não é JSON válido\n";
        echo "📄 Resposta: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "❌ Arquivo não está acessível (HTTP: $http_code)\n";
    echo "📄 Resposta: " . substr($response, 0, 200) . "...\n";
}

echo "\n";

// ===== 2. TESTAR COM DADOS =====
echo "2️⃣ TESTANDO COM DADOS:\n";
echo "======================\n";

$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699@c.us',
        'text' => 'Teste debug webhook - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($debug_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($ch);
$test_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$test_curl_error = curl_error($ch);
curl_close($ch);

if ($test_curl_error) {
    echo "❌ Erro cURL no teste: $test_curl_error\n";
} elseif ($test_http_code === 200) {
    echo "✅ Teste com dados funcionou!\n";
    echo "📡 HTTP Code: $test_http_code\n";
    
    $test_json = json_decode($test_response, true);
    if ($test_json && isset($test_json['input_json'])) {
        echo "✅ Dados capturados corretamente\n";
        echo "📄 Dados recebidos: " . json_encode($test_json['input_json'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "⚠️ Dados não foram capturados corretamente\n";
        echo "📄 Resposta: " . substr($test_response, 0, 300) . "...\n";
    }
} else {
    echo "❌ Teste com dados falhou (HTTP: $test_http_code)\n";
    echo "📄 Resposta: " . substr($test_response, 0, 200) . "...\n";
}

echo "\n";

// ===== 3. VERIFICAR LOGS =====
echo "3️⃣ VERIFICANDO LOGS:\n";
echo "====================\n";

$log_url = "https://app.pixel12digital.com.br/logs/debug_webhook_" . date('Y-m-d') . ".log";

$ch = curl_init($log_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$log_response = curl_exec($ch);
$log_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($log_http_code === 200) {
    echo "✅ Arquivo de log acessível!\n";
    $log_lines = explode("\n", $log_response);
    $recent_lines = array_slice($log_lines, -3);
    
    echo "📄 Logs recentes:\n";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "⚠️ Arquivo de log não acessível (HTTP: $log_http_code)\n";
}

echo "\n🎯 CONCLUSÃO:\n";
echo "=============\n";

if ($http_code === 200 && $test_http_code === 200) {
    echo "✅ DEBUG WEBHOOK ESTÁ FUNCIONANDO PERFEITAMENTE!\n";
    echo "🎉 Agora você pode:\n";
    echo "1. Enviar uma mensagem real para o WhatsApp\n";
    echo "2. Acessar: $debug_url\n";
    echo "3. Ver exatamente os dados que chegam\n";
    echo "4. Analisar os logs para identificar problemas\n";
} else {
    echo "⚠️ AINDA HÁ PROBLEMAS COM O DEBUG WEBHOOK!\n";
    if ($http_code !== 200) {
        echo "   - Arquivo debug_webhook_real.php não está acessível\n";
    }
    if ($test_http_code !== 200) {
        echo "   - Teste com dados falhou\n";
    }
    echo "\n🔧 PRÓXIMOS PASSOS:\n";
    echo "1. Verificar se o arquivo foi enviado para o servidor\n";
    echo "2. Verificar permissões do arquivo\n";
    echo "3. Verificar se há erros no servidor\n";
}
?> 