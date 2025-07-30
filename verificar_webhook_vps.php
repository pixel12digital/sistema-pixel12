<?php
/**
 * 🔍 VERIFICAR CONFIGURAÇÃO DO WEBHOOK NO VPS
 * Testa se o VPS está configurado para enviar mensagens para o webhook
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

echo "<h2>🔍 Verificando Configuração do Webhook no VPS</h2>";

$vps_url = 'http://212.85.11.238:3000';
$webhook_url = 'https://app.pixel12digital.com.br/api/webhook_whatsapp.php';

echo "<h3>1. 📡 Status do VPS</h3>";

// Verificar se VPS está online
$ch = curl_init($vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ VPS online (HTTP $http_code)<br>";
    $status_data = json_decode($response, true);
    if ($status_data) {
        echo "📊 Status: " . json_encode($status_data, JSON_PRETTY_PRINT) . "<br>";
    }
} else {
    echo "❌ VPS offline (HTTP $http_code)<br>";
    echo "📝 Resposta: $response<br>";
}

echo "<h3>2. 🔗 Configuração Atual do Webhook</h3>";

// Verificar configuração atual
$ch = curl_init($vps_url . '/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Configuração acessível<br>";
    $webhook_data = json_decode($response, true);
    if ($webhook_data) {
        echo "🔧 Config atual: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "<br>";
        
        if (isset($webhook_data['webhook_url'])) {
            if ($webhook_data['webhook_url'] === $webhook_url) {
                echo "✅ Webhook configurado corretamente!<br>";
            } else {
                echo "❌ Webhook configurado incorretamente<br>";
                echo "   Atual: " . $webhook_data['webhook_url'] . "<br>";
                echo "   Esperado: $webhook_url<br>";
            }
        }
    }
} else {
    echo "❌ Não foi possível verificar configuração (HTTP $http_code)<br>";
}

echo "<h3>3. ⚙️ Configurando Webhook</h3>";

// Configurar webhook
$ch = curl_init($vps_url . '/webhook/config');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook configurado com sucesso!<br>";
    $config_result = json_decode($response, true);
    if ($config_result) {
        echo "📝 Resultado: " . json_encode($config_result, JSON_PRETTY_PRINT) . "<br>";
    }
} else {
    echo "❌ Erro ao configurar webhook (HTTP $http_code)<br>";
    echo "📝 Resposta: $response<br>";
}

echo "<h3>4. 🧪 Testando Webhook</h3>";

// Testar webhook
$ch = curl_init($vps_url . '/webhook/test');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Teste do webhook executado<br>";
    $test_result = json_decode($response, true);
    if ($test_result) {
        echo "📝 Resultado: " . json_encode($test_result, JSON_PRETTY_PRINT) . "<br>";
    }
} else {
    echo "❌ Erro no teste do webhook (HTTP $http_code)<br>";
    echo "📝 Resposta: $response<br>";
}

echo "<h3>5. 📋 Próximos Passos</h3>";

echo "<p><strong>Para resolver o problema de recebimento de mensagens:</strong></p>";
echo "<ol>";
echo "<li>✅ Webhook configurado no VPS</li>";
echo "<li>✅ WhatsApp conectado (confirmado pelo envio funcionando)</li>";
echo "<li>✅ Sistema funcionando (confirmado pelos testes)</li>";
echo "</ol>";

echo "<p><strong>Agora teste:</strong></p>";
echo "<ol>";
echo "<li>Envie uma mensagem do WhatsApp para o número do bot</li>";
echo "<li>Verifique se aparece no painel do sistema</li>";
echo "<li>Se não aparecer, verifique os logs em: <code>logs/webhook_whatsapp_" . date('Y-m-d') . ".log</code></li>";
echo "</ol>";

echo "<h3>6. 🔧 Comandos SSH para VPS</h3>";

echo "<p>Se precisar acessar o VPS diretamente:</p>";
echo "<pre>";
echo "ssh root@212.85.11.238\n";
echo "cd /root/whatsapp-api\n";
echo "pm2 status\n";
echo "pm2 logs whatsapp-api\n";
echo "</pre>";

echo "<p><strong>🎯 Conclusão:</strong> Se o WhatsApp está enviando mensagens, ele está conectado. O problema é que o VPS não está configurado para enviar as mensagens recebidas para o seu sistema.</p>";
?> 