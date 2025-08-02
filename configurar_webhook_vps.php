<?php
echo "🔧 CONFIGURANDO WEBHOOK NA VPS\n";
echo "==============================\n\n";

$vps_ip = '212.85.11.238';
$novo_webhook = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📡 VPS IP: $vps_ip\n";
echo "🎯 Novo Webhook: $novo_webhook\n\n";

// Verificar se VPS está online
echo "🔍 Verificando se VPS está online...\n";
$status_check = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_check, CURLOPT_TIMEOUT, 10);
curl_setopt($status_check, CURLOPT_NOBODY, true);

$response = curl_exec($status_check);
$http_code = curl_getinfo($status_check, CURLINFO_HTTP_CODE);
curl_close($status_check);

if ($http_code === 200) {
    echo "✅ VPS online! HTTP $http_code\n\n";
    
    // Configurar webhook
    echo "⚙️ Configurando webhook...\n";
    
    $webhook_config = [
        'url' => $novo_webhook
    ];
    
    $ch = curl_init("http://$vps_ip:3000/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_config));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📤 Resposta VPS: HTTP $http_code\n";
    echo "📄 Conteúdo: " . substr($response, 0, 200) . "\n\n";
    
    if ($http_code === 200) {
        echo "✅ WEBHOOK CONFIGURADO COM SUCESSO!\n\n";
        
        // Testar webhook
        echo "🧪 Testando webhook...\n";
        $test_ch = curl_init("http://$vps_ip:3000/webhook/test");
        curl_setopt($test_ch, CURLOPT_POST, true);
        curl_setopt($test_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($test_ch, CURLOPT_TIMEOUT, 10);
        
        $test_response = curl_exec($test_ch);
        $test_code = curl_getinfo($test_ch, CURLINFO_HTTP_CODE);
        curl_close($test_ch);
        
        echo "🧪 Teste: HTTP $test_code\n";
        if ($test_code === 200) {
            echo "✅ TESTE PASSOU!\n";
        } else {
            echo "⚠️ Teste falhou, mas configuração pode estar ativa\n";
        }
        
    } else {
        echo "❌ Falha na configuração\n";
        echo "💡 Tente via SSH ou contate suporte da VPS\n";
    }
    
} else {
    echo "❌ VPS offline ou inacessível (HTTP: $http_code)\n";
    echo "💡 Verifique se a VPS está funcionando\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "================\n";
echo "1. Se configuração passou, teste enviando mensagem para WhatsApp\n";
echo "2. Monitore: https://app.pixel12digital.com.br/painel/gestao_transferencias.php\n";
echo "3. Verifique logs de webhook se necessário\n\n";

echo "🧪 TESTE RÁPIDO:\n";
echo "Envie para o WhatsApp: 'Quero um site'\n";
echo "Resultado esperado: Rafael recebe notificação automática\n";
?> 