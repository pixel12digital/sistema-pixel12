<?php
echo "🚨 CONFIGURANDO WEBHOOK FALLBACK\n";
echo "================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_fallback = 'https://app.pixel12digital.com.br/painel/webhook_fallback.php';

echo "📡 VPS: $vps_ip\n";
echo "🎯 Webhook Fallback: $webhook_fallback\n\n";

// 1. Testar webhook fallback primeiro
echo "🧪 TESTE 1: Webhook Fallback Responde?\n";
echo "--------------------------------------\n";

$test_fallback = curl_init($webhook_fallback);
curl_setopt($test_fallback, CURLOPT_POST, true);
curl_setopt($test_fallback, CURLOPT_POSTFIELDS, json_encode([
    'from' => '5547999999999',
    'body' => 'olá'
]));
curl_setopt($test_fallback, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($test_fallback, CURLOPT_RETURNTRANSFER, true);
curl_setopt($test_fallback, CURLOPT_TIMEOUT, 10);
curl_setopt($test_fallback, CURLOPT_SSL_VERIFYPEER, false);

$fallback_response = curl_exec($test_fallback);
$fallback_code = curl_getinfo($test_fallback, CURLINFO_HTTP_CODE);
curl_close($test_fallback);

echo "Teste Fallback: HTTP $fallback_code\n";
if ($fallback_response) {
    echo "Resposta: " . substr($fallback_response, 0, 200) . "...\n";
    
    $fallback_data = json_decode($fallback_response, true);
    if (isset($fallback_data['success']) && $fallback_data['success']) {
        echo "✅ WEBHOOK FALLBACK FUNCIONANDO!\n";
    } else {
        echo "❌ Webhook fallback com problema\n";
    }
} else {
    echo "❌ Sem resposta do webhook fallback\n";
}
echo "\n";

// 2. Configurar na VPS se o teste passou
if ($fallback_code === 200) {
    echo "🔧 CONFIGURANDO NA VPS...\n";
    echo "--------------------------\n";
    
    $config_data = ['url' => $webhook_fallback];
    
    $config_ch = curl_init("http://$vps_ip:3000/webhook/config");
    curl_setopt($config_ch, CURLOPT_POST, true);
    curl_setopt($config_ch, CURLOPT_POSTFIELDS, json_encode($config_data));
    curl_setopt($config_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($config_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($config_ch, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($config_ch);
    $config_code = curl_getinfo($config_ch, CURLINFO_HTTP_CODE);
    curl_close($config_ch);
    
    echo "Configuração VPS: HTTP $config_code\n";
    if ($config_response) {
        echo "Resposta: " . substr($config_response, 0, 200) . "\n";
        
        if ($config_code === 200) {
            echo "✅ WEBHOOK FALLBACK CONFIGURADO NA VPS!\n\n";
            
            // 3. Teste final - simular envio da VPS
            echo "🧪 TESTE FINAL: Simulando mensagem da VPS...\n";
            echo "---------------------------------------------\n";
            
            $vps_test = curl_init("http://$vps_ip:3000/send/text");
            curl_setopt($vps_test, CURLOPT_POST, true);
            curl_setopt($vps_test, CURLOPT_POSTFIELDS, json_encode([
                'sessionName' => 'default',
                'number' => '5547999999999',
                'message' => 'Teste sistema fallback - ' . date('H:i:s')
            ]));
            curl_setopt($vps_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($vps_test, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($vps_test, CURLOPT_TIMEOUT, 10);
            
            $vps_response = curl_exec($vps_test);
            $vps_code = curl_getinfo($vps_test, CURLINFO_HTTP_CODE);
            curl_close($vps_test);
            
            echo "Envio teste VPS: HTTP $vps_code\n";
            if ($vps_response) {
                $vps_data = json_decode($vps_response, true);
                if (isset($vps_data['success']) && $vps_data['success']) {
                    echo "✅ VPS ENVIOU MENSAGEM COM SUCESSO!\n";
                }
            }
            
        } else {
            echo "❌ Falha na configuração\n";
        }
    }
} else {
    echo "❌ Webhook fallback não funciona, não configurando na VPS\n";
}

echo "\n🎯 RESULTADO:\n";
echo "============\n";

if ($fallback_code === 200 && $config_code === 200) {
    echo "✅ SISTEMA FALLBACK ATIVO!\n\n";
    
    echo "📱 AGORA VOCÊ PODE TESTAR:\n";
    echo "1. Envie 'olá' para o WhatsApp\n";
    echo "2. Ana deve responder (modo fallback)\n";
    echo "3. Sistema detecta transferências automaticamente\n\n";
    
    echo "🔧 FUNCIONALIDADES ATIVAS:\n";
    echo "• ✅ Resposta automática\n";
    echo "• ✅ Detecção de sites → Rafael\n";
    echo "• ✅ Detecção de problemas → Suporte\n";
    echo "• ✅ Detecção humano → Atendimento\n";
    echo "• ✅ Logs de transferências\n\n";
    
    echo "⚠️ MODO FALLBACK (SEM ANA AI):\n";
    echo "• Respostas pré-definidas inteligentes\n";
    echo "• Detecção por palavras-chave\n";
    echo "• Logs de transferências nos arquivos de log\n\n";
    
    echo "🔄 PARA REATIVAR ANA AI:\n";
    echo "1. Corrigir problema na Ana AI\n";
    echo "2. Voltar webhook principal\n";
    echo "3. Sistema inteligente completo\n";
    
} else {
    echo "❌ SISTEMA AINDA COM PROBLEMAS\n";
    echo "Verifique logs do servidor\n";
}
?> 