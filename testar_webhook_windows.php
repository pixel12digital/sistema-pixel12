<?php
/**
 * 🧪 TESTE DE WEBHOOK - WINDOWS
 * 
 * Script para testar o webhook após a correção da coluna
 */

echo "=== 🧪 TESTE DE WEBHOOK - WINDOWS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// ===== 1. TESTE COM FORMATO CORRETO =====
echo "1. 🧪 TESTANDO FORMATO CORRETO:\n";

$url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";
$dados = [
    "from" => "554796164699@c.us",
    "body" => "Teste pós-correção - " . date('Y-m-d H:i:s'),
    "timestamp" => time()
];

echo "   📡 URL: $url\n";
echo "   📄 Dados: " . json_encode($dados) . "\n";

// Fazer requisição
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Teste-Windows/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
if ($error) {
    echo "   ❌ Erro cURL: $error\n";
} else {
    echo "   ✅ Resposta: " . substr($response, 0, 200) . "...\n";
}

echo "\n";

// ===== 2. TESTE COM FORMATO WHATSAPP ROBOT =====
echo "2. 🧪 TESTANDO FORMATO WHATSAPP ROBOT:\n";

$dados_robot = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699",
        "text" => "Teste formato robot - " . date('Y-m-d H:i:s'),
        "type" => "chat",
        "timestamp" => time(),
        "session" => "default"
    ]
];

echo "   📄 Dados Robot: " . json_encode($dados_robot) . "\n";

// Fazer requisição
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_robot));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Teste-Robot/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response_robot = curl_exec($ch);
$http_code_robot = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_robot = curl_error($ch);
curl_close($ch);

echo "   📊 HTTP Code: $http_code_robot\n";
if ($error_robot) {
    echo "   ❌ Erro cURL: $error_robot\n";
} else {
    echo "   ✅ Resposta: " . substr($response_robot, 0, 200) . "...\n";
}

echo "\n";

// ===== 3. ANÁLISE DOS RESULTADOS =====
echo "3. 📊 ANÁLISE DOS RESULTADOS:\n";

if ($http_code == 200) {
    echo "   ✅ Formato correto: FUNCIONANDO\n";
} else {
    echo "   ❌ Formato correto: ERRO HTTP $http_code\n";
}

if ($http_code_robot == 200) {
    echo "   ✅ Formato robot: FUNCIONANDO\n";
} else {
    echo "   ❌ Formato robot: ERRO HTTP $http_code_robot\n";
}

echo "\n";

// ===== 4. SUGESTÕES =====
echo "4. 🔧 SUGESTÕES:\n";

if ($http_code == 200 && $http_code_robot != 200) {
    echo "   ✅ Webhook aceita formato correto\n";
    echo "   ❌ Webhook NÃO aceita formato robot\n";
    echo "   🔧 Ação: Ajustar webhook para aceitar formato robot\n";
} elseif ($http_code != 200 && $http_code_robot == 200) {
    echo "   ❌ Webhook NÃO aceita formato correto\n";
    echo "   ✅ Webhook aceita formato robot\n";
    echo "   🔧 Ação: Ajustar webhook para aceitar formato correto\n";
} elseif ($http_code == 200 && $http_code_robot == 200) {
    echo "   ✅ Webhook aceita AMBOS os formatos\n";
    echo "   🎉 SISTEMA FUNCIONANDO PERFEITAMENTE!\n";
} else {
    echo "   ❌ Webhook NÃO aceita nenhum formato\n";
    echo "   🔧 Ação: Verificar configuração do webhook\n";
}

echo "\n";

// ===== 5. PRÓXIMOS PASSOS =====
echo "5. 🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Se webhook funcionando: Enviar mensagem real\n";
echo "   2. Se webhook com erro: Verificar logs\n";
echo "   3. Testar integração com Ana AI\n";
echo "   4. Monitorar funcionamento por 24h\n";

echo "\n=== FIM DO TESTE ===\n";
echo "Status: " . (($http_code == 200 || $http_code_robot == 200) ? "✅ SUCESSO" : "❌ FALHA") . "\n";
?> 