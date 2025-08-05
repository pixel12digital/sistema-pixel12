<?php
/**
 * Configurar webhook para canal 3001
 */

echo "🔧 CONFIGURANDO WEBHOOK CANAL 3001\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 Configurações:\n";
echo "VPS: $vps_ip:3001\n";
echo "Webhook: $webhook_url\n\n";

// Verificar se o canal está funcionando
echo "1️⃣ Verificando status do canal 3001...\n";
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Canal 3001 está funcionando\n";
    $status = json_decode($response, true);
    echo "📊 Status: " . ($status['status'] ?? 'unknown') . "\n";
    echo "🔗 Porta: " . ($status['port'] ?? 'unknown') . "\n\n";
} else {
    echo "❌ Canal 3001 não está respondendo (HTTP $http_code)\n";
    exit(1);
}

// Tentar diferentes endpoints de webhook
$endpoints = [
    '/webhook/config',
    '/webhook',
    '/hook/config',
    '/hook'
];

$webhook_configurado = false;

foreach ($endpoints as $endpoint) {
    echo "2️⃣ Tentando endpoint: $endpoint\n";
    
    $ch = curl_init("http://$vps_ip:3001$endpoint");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        echo "✅ Webhook configurado com sucesso via $endpoint\n";
        echo "📝 Resposta: $response\n";
        $webhook_configurado = true;
        break;
    } else {
        echo "❌ Falhou (HTTP $http_code): $error\n";
    }
}

if (!$webhook_configurado) {
    echo "\n⚠️ Não foi possível configurar webhook automaticamente\n";
    echo "🔧 O canal 3001 pode estar usando uma versão diferente da API\n";
    echo "📋 Verifique a documentação da API ou configure manualmente\n\n";
}

// Testar envio de mensagem
echo "3️⃣ Testando envio de mensagem...\n";
$ch = curl_init("http://$vps_ip:3001/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => '5511999999999',
    'message' => 'Teste webhook canal 3001 - ' . date('Y-m-d H:i:s')
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200 && !$error) {
    echo "✅ Envio de mensagem funcionando\n";
    echo "📝 Resposta: $response\n";
} else {
    echo "❌ Erro no envio (HTTP $http_code): $error\n";
    echo "📝 Resposta: $response\n";
}

echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n";
echo "==========================\n";
echo "📋 Status:\n";
echo "• Canal 3001: ✅ Funcionando\n";
echo "• Webhook: " . ($webhook_configurado ? "✅ Configurado" : "⚠️ Necessita configuração manual") . "\n";
echo "• Envio: " . ($http_code === 200 ? "✅ Funcionando" : "❌ Com problemas") . "\n\n";

echo "🔧 Próximos passos:\n";
echo "1. Verifique o painel de comunicação\n";
echo "2. Teste envio de mensagem real\n";
echo "3. Monitore os logs se necessário\n\n";

echo "📚 Comandos úteis:\n";
echo "• Status: curl http://$vps_ip:3001/status\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs whatsapp-3001 --lines 10'\n";
?> 