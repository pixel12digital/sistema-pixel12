<?php
echo "🔍 DIAGNÓSTICO COMPLETO DO WEBHOOK\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📡 VPS: $vps_ip\n";
echo "🎯 Webhook: $webhook_url\n\n";

// 1. Verificar se VPS está online
echo "🔍 TESTE 1: VPS Online?\n";
echo "------------------------\n";

$vps_check = curl_init("http://$vps_ip:3000/status");
curl_setopt($vps_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($vps_check, CURLOPT_TIMEOUT, 10);
curl_setopt($vps_check, CURLOPT_NOBODY, true);

$response = curl_exec($vps_check);
$http_code = curl_getinfo($vps_check, CURLINFO_HTTP_CODE);
curl_close($vps_check);

if ($http_code === 200) {
    echo "✅ VPS online (HTTP $http_code)\n\n";
} else {
    echo "❌ VPS offline/problema (HTTP $http_code)\n\n";
}

// 2. Verificar configuração atual do webhook na VPS
echo "🔍 TESTE 2: Webhook Configurado na VPS?\n";
echo "---------------------------------------\n";

$webhook_status = curl_init("http://$vps_ip:3000/webhook/status");
curl_setopt($webhook_status, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_status, CURLOPT_TIMEOUT, 10);

$webhook_response = curl_exec($webhook_status);
$webhook_code = curl_getinfo($webhook_status, CURLINFO_HTTP_CODE);
curl_close($webhook_status);

echo "Status Webhook VPS: HTTP $webhook_code\n";
if ($webhook_response) {
    echo "Resposta: " . substr($webhook_response, 0, 200) . "\n";
}
echo "\n";

// 3. Verificar se nosso webhook responde
echo "🔍 TESTE 3: Nosso Webhook Responde?\n";
echo "-----------------------------------\n";

$our_webhook = curl_init($webhook_url);
curl_setopt($our_webhook, CURLOPT_RETURNTRANSFER, true);
curl_setopt($our_webhook, CURLOPT_TIMEOUT, 10);
curl_setopt($our_webhook, CURLOPT_NOBODY, true);
curl_setopt($our_webhook, CURLOPT_SSL_VERIFYPEER, false);

$our_response = curl_exec($our_webhook);
$our_code = curl_getinfo($our_webhook, CURLINFO_HTTP_CODE);
curl_close($our_webhook);

if ($our_code === 200) {
    echo "✅ Nosso webhook responde (HTTP $our_code)\n\n";
} else {
    echo "❌ Nosso webhook com problema (HTTP $our_code)\n\n";
}

// 4. Testar envio de mensagem simulada
echo "🔍 TESTE 4: Envio de Mensagem da VPS?\n";
echo "-------------------------------------\n";

$test_message = [
    'sessionName' => 'default',
    'number' => '5547999999999',
    'message' => 'Teste de conectividade - ' . date('H:i:s')
];

$send_test = curl_init("http://$vps_ip:3000/send/text");
curl_setopt($send_test, CURLOPT_POST, true);
curl_setopt($send_test, CURLOPT_POSTFIELDS, json_encode($test_message));
curl_setopt($send_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($send_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($send_test, CURLOPT_TIMEOUT, 10);

$send_response = curl_exec($send_test);
$send_code = curl_getinfo($send_test, CURLINFO_HTTP_CODE);
curl_close($send_test);

echo "Teste de envio: HTTP $send_code\n";
if ($send_response) {
    $send_data = json_decode($send_response, true);
    if (isset($send_data['success'])) {
        echo "Resultado: " . ($send_data['success'] ? "✅ Sucesso" : "❌ Falha") . "\n";
    }
}
echo "\n";

// 5. Verificar sessões ativas na VPS
echo "🔍 TESTE 5: Sessões Ativas na VPS?\n";
echo "----------------------------------\n";

$sessions_check = curl_init("http://$vps_ip:3000/sessions");
curl_setopt($sessions_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($sessions_check, CURLOPT_TIMEOUT, 10);

$sessions_response = curl_exec($sessions_check);
$sessions_code = curl_getinfo($sessions_check, CURLINFO_HTTP_CODE);
curl_close($sessions_check);

echo "Sessões: HTTP $sessions_code\n";
if ($sessions_response) {
    $sessions_data = json_decode($sessions_response, true);
    if (is_array($sessions_data)) {
        echo "Sessões encontradas: " . count($sessions_data) . "\n";
        foreach ($sessions_data as $session) {
            if (isset($session['sessionName'], $session['hasClient'])) {
                echo "  • {$session['sessionName']}: " . ($session['hasClient'] ? "✅ Conectada" : "❌ Desconectada") . "\n";
            }
        }
    }
}
echo "\n";

// 6. Testar POST direto no nosso webhook
echo "🔍 TESTE 6: POST Direto no Nosso Webhook?\n";
echo "------------------------------------------\n";

$test_post_data = [
    'from' => '5547999999999',
    'body' => 'Teste diagnóstico - ' . date('H:i:s')
];

$direct_test = curl_init($webhook_url);
curl_setopt($direct_test, CURLOPT_POST, true);
curl_setopt($direct_test, CURLOPT_POSTFIELDS, json_encode($test_post_data));
curl_setopt($direct_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($direct_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($direct_test, CURLOPT_TIMEOUT, 15);
curl_setopt($direct_test, CURLOPT_SSL_VERIFYPEER, false);

$direct_response = curl_exec($direct_test);
$direct_code = curl_getinfo($direct_test, CURLINFO_HTTP_CODE);
curl_close($direct_test);

echo "POST direto: HTTP $direct_code\n";
if ($direct_response) {
    echo "Resposta: " . substr($direct_response, 0, 300) . "\n";
    
    $direct_data = json_decode($direct_response, true);
    if (isset($direct_data['success'])) {
        echo "Status: " . ($direct_data['success'] ? "✅ Sucesso" : "❌ Falha") . "\n";
        if (isset($direct_data['ana_response'])) {
            echo "Ana respondeu: " . substr($direct_data['ana_response'], 0, 100) . "...\n";
        }
    }
}
echo "\n";

// 7. Reconfigurar webhook se necessário
echo "🔧 TESTE 7: Reconfigurar Webhook?\n";
echo "---------------------------------\n";

$reconfig_data = ['url' => $webhook_url];
$reconfig = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($reconfig, CURLOPT_POST, true);
curl_setopt($reconfig, CURLOPT_POSTFIELDS, json_encode($reconfig_data));
curl_setopt($reconfig, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($reconfig, CURLOPT_RETURNTRANSFER, true);
curl_setopt($reconfig, CURLOPT_TIMEOUT, 10);

$reconfig_response = curl_exec($reconfig);
$reconfig_code = curl_getinfo($reconfig, CURLINFO_HTTP_CODE);
curl_close($reconfig);

echo "Reconfiguração: HTTP $reconfig_code\n";
if ($reconfig_response) {
    echo "Resultado: " . substr($reconfig_response, 0, 200) . "\n";
}

echo "\n🎯 RESUMO DO DIAGNÓSTICO:\n";
echo "=======================\n";
echo "VPS Online: " . ($http_code === 200 ? "✅" : "❌") . "\n";
echo "Nosso Webhook: " . ($our_code === 200 ? "✅" : "❌") . "\n";
echo "POST Direto: " . ($direct_code === 200 ? "✅" : "❌") . "\n";
echo "Reconfiguração: " . ($reconfig_code === 200 ? "✅" : "❌") . "\n\n";

echo "💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
if ($http_code !== 200) {
    echo "1. ❌ VPS offline - verifique se PM2 está rodando\n";
    echo "   Comando: ssh root@$vps_ip 'pm2 status'\n";
} elseif ($our_code !== 200) {
    echo "1. ❌ Problema no nosso webhook - verifique servidor web\n";
} elseif ($direct_code === 200) {
    echo "1. ✅ Sistema funcionando - problema pode ser na configuração VPS\n";
    echo "2. Teste enviando mensagem real novamente\n";
} else {
    echo "1. ❌ Múltiplos problemas detectados\n";
    echo "2. Verifique logs do servidor\n";
}
?> 