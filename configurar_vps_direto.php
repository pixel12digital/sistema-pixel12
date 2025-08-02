<?php
echo "🔧 CONFIGURANDO WEBHOOK DIRETAMENTE NA VPS\n";
echo "==========================================\n\n";

$vps_ip = '212.85.11.238';
$vps_port = '3000';

// URLs para testar
$webhook_urls = [
    'https://app.pixel12digital.com.br/webhook.php',
    'https://app.pixel12digital.com.br/webhook',
    'https://app.pixel12digital.com.br/index.php?path=webhook',
    'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php'
];

echo "📡 VPS: $vps_ip:$vps_port\n\n";

foreach ($webhook_urls as $index => $webhook_url) {
    echo "🔍 TESTE " . ($index + 1) . ": $webhook_url\n";
    echo str_repeat('-', 60) . "\n";
    
    // Configurar webhook
    $config_data = ['url' => $webhook_url];
    
    $config_ch = curl_init("http://$vps_ip:$vps_port/webhook/config");
    curl_setopt($config_ch, CURLOPT_POST, true);
    curl_setopt($config_ch, CURLOPT_POSTFIELDS, json_encode($config_data));
    curl_setopt($config_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($config_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($config_ch, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($config_ch);
    $config_code = curl_getinfo($config_ch, CURLINFO_HTTP_CODE);
    curl_close($config_ch);
    
    echo "Configuração: HTTP $config_code\n";
    if ($config_response) {
        echo "Resposta: " . substr($config_response, 0, 200) . "\n";
    }
    
    if ($config_code === 200) {
        echo "✅ CONFIGURADO COM SUCESSO!\n";
        
        // Testar envio de mensagem da VPS
        echo "\n🧪 Testando envio da VPS...\n";
        
        $test_message = [
            'sessionName' => 'default',
            'number' => '5547999999999',
            'message' => 'Teste webhook configurado - ' . date('H:i:s')
        ];
        
        $send_test = curl_init("http://$vps_ip:$vps_port/send/text");
        curl_setopt($send_test, CURLOPT_POST, true);
        curl_setopt($send_test, CURLOPT_POSTFIELDS, json_encode($test_message));
        curl_setopt($send_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($send_test, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($send_test, CURLOPT_TIMEOUT, 10);
        
        $send_response = curl_exec($send_test);
        $send_code = curl_getinfo($send_test, CURLINFO_HTTP_CODE);
        curl_close($send_test);
        
        echo "Envio teste: HTTP $send_code\n";
        if ($send_code === 200) {
            $send_data = json_decode($send_response, true);
            if (isset($send_data['success']) && $send_data['success']) {
                echo "✅ VPS ENVIOU MENSAGEM COM SUCESSO!\n\n";
                
                echo "🎉 SISTEMA CONFIGURADO E FUNCIONANDO!\n";
                echo "=====================================\n\n";
                
                echo "📱 WEBHOOK ATIVO:\n";
                echo "• URL: $webhook_url\n";
                echo "• VPS: $vps_ip:$vps_port\n";
                echo "• Status: ✅ OPERACIONAL\n\n";
                
                echo "📋 TESTE REAL:\n";
                echo "1. Envie 'olá' para WhatsApp\n";
                echo "2. Ana deve responder automaticamente\n";
                echo "3. Teste 'quero um site' → Rafael\n";
                echo "4. Teste 'problema' → Suporte\n";
                echo "5. Teste 'pessoa' → Humano\n\n";
                
                echo "📊 MONITORAMENTO:\n";
                echo "• Logs VPS: Procure por '[WEBHOOK_FISICO]' ou '[WEBHOOK_ROTEAMENTO]'\n";
                echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php\n";
                echo "• Dashboard: https://app.pixel12digital.com.br/painel/gestao_transferencias.php\n\n";
                
                echo "✅ CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!\n";
                exit(0);
            } else {
                echo "⚠️ VPS enviou mas houve problema na resposta\n";
            }
        } else {
            echo "❌ Falha no envio: HTTP $send_code\n";
        }
    } else {
        echo "❌ Falha na configuração\n";
    }
    
    echo "\n" . str_repeat('=', 70) . "\n\n";
}

echo "❌ NENHUMA URL FUNCIONOU\n";
echo "Todas as tentativas falharam\n\n";

echo "💡 SOLUÇÕES ALTERNATIVAS:\n";
echo "1. Configurar manualmente via SSH:\n";
echo "   ssh root@$vps_ip\n";
echo "   curl -X POST http://localhost:3000/webhook/config \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"url\":\"https://app.pixel12digital.com.br/webhook.php\"}'\n\n";

echo "2. Verificar se servidor permite webhooks externos\n";
echo "3. Contactar suporte técnico do hosting\n";
?> 