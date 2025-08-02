<?php
/**
 * 🧪 TESTE DO WEBHOOK REDIRECIONADO PARA ANA
 */

echo "🧪 TESTE DE WEBHOOK REDIRECIONADO PARA ANA\n";
echo "==========================================\n\n";

// Simular mensagem WhatsApp no formato do webhook antigo
$dados_webhook = [
    'text' => 'Oi, preciso de ajuda com sites',
    'from_number' => '5547999888777',
    'timestamp' => time(),
    'type' => 'text'
];

echo "📤 Simulando mensagem via webhook antigo:\n";
echo "- De: " . $dados_webhook['from_number'] . "\n";
echo "- Mensagem: " . $dados_webhook['text'] . "\n";
echo "- Tipo: " . $dados_webhook['type'] . "\n\n";

// Chamar webhook antigo
$url = 'http://localhost:8080/loja-virtual-revenda/webhook_sem_redirect/webhook.php';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_webhook));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

echo "🔗 Enviando para webhook antigo: $url\n\n";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "📊 RESULTADO:\n";
echo "- HTTP Code: $http_code\n";

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
} else {
    echo "✅ Resposta recebida:\n";
    echo "-----\n";
    echo $response . "\n";
    echo "-----\n\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "📋 Dados estruturados:\n";
        foreach ($data as $key => $value) {
            echo "- $key: $value\n";
        }
    }
}

echo "\n🎯 ANÁLISE:\n";
if ($http_code === 200) {
    echo "✅ Webhook respondeu\n";
    
    if (isset($data['source']) && $data['source'] === 'webhook_redirect_ana') {
        echo "🎉 SUCESSO! Mensagem foi redirecionada para Ana\n";
    } else {
        echo "⚠️ Mensagem não foi redirecionada - pode ser outro canal\n";
    }
} else {
    echo "❌ Problema no webhook (HTTP $http_code)\n";
}

echo "\n🔍 PRÓXIMOS PASSOS:\n";
echo "1. Testar mensagem real no WhatsApp\n";
echo "2. Verificar se Ana responde corretamente\n";
echo "3. Monitorar logs para transferências\n";
?> 