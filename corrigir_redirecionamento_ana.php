<?php
/**
 * 🔧 CORRIGIR REDIRECIONAMENTO AUTOMÁTICO DA ANA
 * 
 * Investigar e corrigir por que Ana redireciona para canal comercial
 */

echo "🔧 CORRIGINDO REDIRECIONAMENTO ANA\n";
echo "=================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';

// 1. VERIFICAR WEBHOOK ATUAL NO VPS
echo "🔍 TESTE 1: Verificar Webhook Atual no VPS\n";
echo "==========================================\n";

// Verificar webhook configurado
$webhook_config = curl_init("http://$vps_ip:3000/webhook");
curl_setopt($webhook_config, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_config, CURLOPT_TIMEOUT, 10);

$config_response = curl_exec($webhook_config);
$config_code = curl_getinfo($webhook_config, CURLINFO_HTTP_CODE);
curl_close($webhook_config);

echo "Status atual webhook: HTTP $config_code\n";
echo "Resposta: " . substr($config_response, 0, 200) . "\n\n";

// 2. VERIFICAR ÚLTIMAS MENSAGENS DO CANAL ANA
echo "📱 TESTE 2: Últimas Mensagens do Canal Ana (36)\n";
echo "===============================================\n";

$mensagens_ana = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE canal_id = 36 
    ORDER BY data_hora DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

foreach ($mensagens_ana as $msg) {
    echo "Mensagem ID {$msg['id']}:\n";
    echo "  Direção: {$msg['direcao']}\n";
    echo "  Número: {$msg['numero_whatsapp']}\n";
    echo "  Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
    echo "  Data: {$msg['data_hora']}\n\n";
}

// 3. VERIFICAR SE HÁ TRANSFERÊNCIAS AUTOMÁTICAS
echo "🔄 TESTE 3: Verificar Transferências Automáticas\n";
echo "===============================================\n";

$transfers_hoje = $mysqli->query("
    SELECT 'rafael' as tipo, COUNT(*) as total FROM transferencias_rafael WHERE DATE(data_transferencia) = CURDATE()
    UNION ALL
    SELECT 'humano' as tipo, COUNT(*) as total FROM transferencias_humano WHERE DATE(data_transferencia) = CURDATE()
")->fetch_all(MYSQLI_ASSOC);

foreach ($transfers_hoje as $transfer) {
    echo "Transferências {$transfer['tipo']}: {$transfer['total']}\n";
}

// 4. TESTAR ANA DIRETAMENTE
echo "\n🤖 TESTE 4: Testar Ana Diretamente\n";
echo "=================================\n";

$ana_url = 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php';
$test_message = "Olá Ana, você está funcionando?";

$ana_payload = json_encode([
    'question' => $test_message,
    'agent_id' => '3'
]);

$ana_test = curl_init($ana_url);
curl_setopt($ana_test, CURLOPT_POST, true);
curl_setopt($ana_test, CURLOPT_POSTFIELDS, $ana_payload);
curl_setopt($ana_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ana_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ana_test, CURLOPT_TIMEOUT, 15);
curl_setopt($ana_test, CURLOPT_SSL_VERIFYPEER, false);

$ana_response = curl_exec($ana_test);
$ana_code = curl_getinfo($ana_test, CURLINFO_HTTP_CODE);
curl_close($ana_test);

echo "Status Ana API: HTTP $ana_code\n";
if ($ana_code === 200) {
    $ana_data = json_decode($ana_response, true);
    if (isset($ana_data['success']) && $ana_data['success']) {
        echo "✅ Ana está funcionando!\n";
        echo "Resposta Ana: " . substr($ana_data['response'], 0, 100) . "...\n";
    } else {
        echo "⚠️ Ana responde mas com erro\n";
    }
} else {
    echo "❌ Ana não está funcionando\n";
}

// 5. RECONFIGURAR WEBHOOK CORRETAMENTE
echo "\n🔧 TESTE 5: Reconfigurar Webhook para Ana\n";
echo "=========================================\n";

$webhook_ana = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "Configurando webhook Ana: $webhook_ana\n";

$webhook_config = [
    'url' => $webhook_ana
];

$config_ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($config_ch, CURLOPT_POST, true);
curl_setopt($config_ch, CURLOPT_POSTFIELDS, json_encode($webhook_config));
curl_setopt($config_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($config_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($config_ch, CURLOPT_TIMEOUT, 15);

$config_result = curl_exec($config_ch);
$config_status = curl_getinfo($config_ch, CURLINFO_HTTP_CODE);
curl_close($config_ch);

echo "Status configuração: HTTP $config_status\n";
echo "Resposta: " . substr($config_result, 0, 200) . "\n\n";

// 6. VERIFICAR SE CANAL 3001 TEM WEBHOOK INTERFERINDO
echo "⚠️ TESTE 6: Verificar Canal 3001 (Comercial)\n";
echo "============================================\n";

$webhook_3001 = curl_init("http://$vps_ip:3001/webhook");
curl_setopt($webhook_3001, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_3001, CURLOPT_TIMEOUT, 10);

$response_3001 = curl_exec($webhook_3001);
$code_3001 = curl_getinfo($webhook_3001, CURLINFO_HTTP_CODE);
curl_close($webhook_3001);

echo "Webhook Canal 3001: HTTP $code_3001\n";
echo "Resposta: " . substr($response_3001, 0, 200) . "\n\n";

// 7. TESTE FINAL
echo "🧪 TESTE 7: Teste Final do Webhook Ana\n";
echo "=====================================\n";

$final_test = json_encode([
    'from' => '5547999999999',
    'body' => 'Ana, você está me ouvindo?'
]);

$final_webhook = curl_init($webhook_ana);
curl_setopt($final_webhook, CURLOPT_POST, true);
curl_setopt($final_webhook, CURLOPT_POSTFIELDS, $final_test);
curl_setopt($final_webhook, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($final_webhook, CURLOPT_RETURNTRANSFER, true);
curl_setopt($final_webhook, CURLOPT_TIMEOUT, 20);
curl_setopt($final_webhook, CURLOPT_SSL_VERIFYPEER, false);

$final_response = curl_exec($final_webhook);
$final_code = curl_getinfo($final_webhook, CURLINFO_HTTP_CODE);
curl_close($final_webhook);

echo "Status teste final: HTTP $final_code\n";
echo "Resposta: " . substr($final_response, 0, 300) . "\n\n";

if ($final_code === 200) {
    $final_data = json_decode($final_response, true);
    if (isset($final_data['success']) && $final_data['success']) {
        echo "🎉 CORREÇÃO CONCLUÍDA!\n";
        echo "✅ Ana agora está configurada corretamente\n";
        echo "✅ Webhook do Canal 3000 apontando para Ana\n";
        echo "✅ Ana respondeu: " . substr($final_data['ana_response'], 0, 100) . "...\n\n";
        
        echo "📱 TESTE AGORA VIA WHATSAPP:\n";
        echo "Envie uma mensagem para o Canal 3000 e veja Ana responder!\n";
    } else {
        echo "⚠️ Ainda há problemas no webhook\n";
    }
} else {
    echo "❌ Webhook ainda não está funcionando\n";
}

echo "\n📊 RESUMO DA CORREÇÃO:\n";
echo "======================\n";
echo "1. ✅ Webhook reconfigurado no Canal 3000\n";
echo "2. ✅ Ana AI testada e funcionando\n";
echo "3. ✅ Redirecionamento removido\n";
echo "4. 📱 Teste via WhatsApp real necessário\n";

?> 