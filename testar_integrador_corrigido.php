<?php
echo "🧪 TESTANDO INTEGRADOR ANA CORRIGIDO\n";
echo "===================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';
require_once 'painel/api/integrador_ana_local.php';

// Testar integrador
$integrador = new IntegradorAnaLocal($mysqli);

$dados_teste = [
    'from' => '5547999999999',
    'body' => 'Olá Ana, você está funcionando com a URL corrigida?'
];

echo "📱 Testando mensagem: {$dados_teste['body']}\n";
echo "📞 Número: {$dados_teste['from']}\n\n";

echo "🔄 Processando via integrador...\n";
$resultado = $integrador->processarMensagem($dados_teste);

echo "📊 RESULTADO:\n";
echo "Success: " . ($resultado['success'] ? 'SIM' : 'NÃO') . "\n";
echo "Resposta Ana: " . substr($resultado['resposta_ana'], 0, 150) . "...\n";
echo "Ação Sistema: {$resultado['acao_sistema']}\n";
echo "Transfer Rafael: " . ($resultado['transfer_para_rafael'] ? 'SIM' : 'NÃO') . "\n";
echo "Transfer Humano: " . ($resultado['transfer_para_humano'] ? 'SIM' : 'NÃO') . "\n";

if (!empty($resultado['debug'])) {
    echo "\n🐛 DEBUG:\n";
    foreach ($resultado['debug'] as $debug) {
        echo "- $debug\n";
    }
}

// Teste direto da URL
echo "\n🌐 TESTE DIRETO DA URL CORRIGIDA:\n";
$url_ana = 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php';
$payload = json_encode([
    'question' => 'Teste direto da Ana',
    'agent_id' => '3'
]);

$ch = curl_init($url_ana);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $http_code\n";
echo "Resposta: " . substr($response, 0, 200) . "\n";

if ($http_code === 200) {
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'ok') {
        echo "✅ ANA FUNCIONANDO PERFEITAMENTE!\n";
        echo "Ana disse: " . substr($data['response'], 0, 100) . "...\n";
    }
}

echo "\n🎯 CONCLUSÃO:\n";
echo "=============\n";
if ($resultado['success'] && $resultado['acao_sistema'] !== 'fallback_local') {
    echo "✅ INTEGRADOR FUNCIONANDO COM ANA REAL!\n";
} elseif ($resultado['success']) {
    echo "⚠️ Integrador funcionando mas usando fallback\n";
    echo "💡 Ana externa pode estar temporariamente indisponível\n";
} else {
    echo "❌ Problema no integrador\n";
}

?> 