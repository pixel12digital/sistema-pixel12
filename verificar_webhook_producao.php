<?php
echo "🔍 VERIFICANDO WEBHOOK EM PRODUÇÃO\n";
echo "==================================\n\n";

$webhook_url = "https://pixel12digital.com.br/loja-virtual-revenda/api/webhook_whatsapp.php";

// 1. Testar acesso ao webhook
echo "🌐 TESTANDO ACESSO AO WEBHOOK:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook acessível (HTTP 200)\n";
} else {
    echo "   ❌ Webhook não acessível (HTTP $http_code)\n";
    if ($error) {
        echo "   🔧 Erro: $error\n";
    }
    if ($response) {
        echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n";
    }
}

echo "\n";

// 2. Testar com payload simples
echo "🧪 TESTANDO COM PAYLOAD SIMPLES:\n";
$payload_simples = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'teste',
        'type' => 'text'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_simples));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook processou payload (HTTP 200)\n";
    echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n";
} else {
    echo "   ❌ Erro ao processar payload (HTTP $http_code)\n";
    echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
}

echo "\n";

// 3. Verificar se o arquivo existe
echo "📁 VERIFICANDO SE O ARQUIVO EXISTE:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true); // Apenas verificar se existe

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Arquivo webhook existe\n";
} else {
    echo "   ❌ Arquivo webhook não encontrado (HTTP $http_code)\n";
    echo "   🔧 Verifique se o arquivo foi enviado para produção\n";
}

echo "\n";

// 4. Verificar configuração da VPS
echo "📡 VERIFICANDO CONFIGURAÇÃO DA VPS:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ VPS configurada\n";
    echo "   🔗 Webhook URL: " . ($data['webhook_url'] ?? 'não definida') . "\n";
} else {
    echo "   ❌ Erro na VPS (HTTP $http_code)\n";
}

echo "\n";

// 5. Sugestões de correção
echo "🔧 SUGESTÕES DE CORREÇÃO:\n";
echo "   1. Verificar se o arquivo webhook_whatsapp.php existe em produção\n";
echo "   2. Verificar configuração do banco de dados em produção\n";
echo "   3. Verificar logs de erro do servidor\n";
echo "   4. Testar com payload mais simples\n";

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 