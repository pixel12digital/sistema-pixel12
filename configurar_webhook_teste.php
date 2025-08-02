<?php
echo "🧪 CONFIGURANDO WEBHOOK DE TESTE\n";
echo "================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_teste = 'https://app.pixel12digital.com.br/webhook_teste_simples.php';

echo "📡 VPS IP: $vps_ip\n";
echo "🧪 Webhook Teste: $webhook_teste\n\n";

// Verificar se VPS está online
echo "🔍 Verificando VPS...\n";
$status_check = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_check, CURLOPT_TIMEOUT, 10);
curl_setopt($status_check, CURLOPT_NOBODY, true);

$response = curl_exec($status_check);
$http_code = curl_getinfo($status_check, CURLINFO_HTTP_CODE);
curl_close($status_check);

if ($http_code === 200) {
    echo "✅ VPS online!\n\n";
    
    // Testar webhook de teste primeiro
    echo "🧪 Testando webhook de teste...\n";
    $test_data = json_encode([
        'from' => '5547999999999',
        'body' => 'Quero um site para teste'
    ]);
    
    $test_webhook = curl_init($webhook_teste);
    curl_setopt($test_webhook, CURLOPT_POST, true);
    curl_setopt($test_webhook, CURLOPT_POSTFIELDS, $test_data);
    curl_setopt($test_webhook, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($test_webhook, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($test_webhook, CURLOPT_TIMEOUT, 15);
    curl_setopt($test_webhook, CURLOPT_SSL_VERIFYPEER, false);
    
    $test_response = curl_exec($test_webhook);
    $test_code = curl_getinfo($test_webhook, CURLINFO_HTTP_CODE);
    curl_close($test_webhook);
    
    echo "Status webhook teste: HTTP $test_code\n";
    echo "Resposta: " . substr($test_response, 0, 200) . "\n\n";
    
    if ($test_code === 200) {
        echo "✅ WEBHOOK DE TESTE FUNCIONANDO!\n\n";
        
        // Configurar no VPS
        echo "⚙️ Configurando webhook de teste no VPS...\n";
        
        $webhook_config = [
            'url' => $webhook_teste
        ];
        
        $ch = curl_init("http://$vps_ip:3000/webhook/config");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_config));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $config_response = curl_exec($ch);
        $config_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Configuração VPS: HTTP $config_code\n";
        echo "Resposta: " . substr($config_response, 0, 200) . "\n\n";
        
        if ($config_code === 200) {
            echo "✅ WEBHOOK DE TESTE CONFIGURADO NO VPS!\n\n";
            
            echo "🎉 SISTEMA DE TESTE ATIVO!\n";
            echo "=========================\n\n";
            
            echo "📱 TESTE AGORA:\n";
            echo "• Envie WhatsApp: 'Olá' → Ana teste responde\n";
            echo "• Envie WhatsApp: 'Quero um site' → Detecta comercial\n";
            echo "• Envie WhatsApp: 'Meu site quebrou' → Detecta suporte\n\n";
            
            echo "📊 MONITORAMENTO:\n";
            echo "• Logs: grep 'WEBHOOK_TESTE' /var/log/apache2/error.log\n";
            echo "• Dashboard: https://app.pixel12digital.com.br/painel/gestao_transferencias.php\n\n";
            
            echo "🔄 VOLTAR AO WEBHOOK PRINCIPAL:\n";
            echo "Quando confirmar que teste funciona, execute:\n";
            echo "php configurar_webhook_vps.php\n";
            
        } else {
            echo "❌ Falha na configuração VPS\n";
        }
        
    } else {
        echo "❌ Webhook de teste não funciona - problema no servidor\n";
        echo "Resposta completa: $test_response\n";
    }
    
} else {
    echo "❌ VPS offline (HTTP: $http_code)\n";
}

echo "\n📋 RESUMO:\n";
echo "=========\n";
echo "1. Se webhook teste funcionar = problema é no webhook principal\n";
echo "2. Se webhook teste falhar = problema é no servidor/PHP\n";
echo "3. Monitor logs para identificar erros específicos\n";
?> 