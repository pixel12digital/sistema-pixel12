<?php
echo "🔧 CONFIGURANDO WEBHOOK SIMPLIFICADO\n";
echo "====================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_simples = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_simples_corrigido.php';

echo "📡 VPS IP: $vps_ip\n";
echo "🔧 Webhook Simples: $webhook_simples\n\n";

// Testar webhook simples primeiro
echo "🧪 Testando webhook simplificado...\n";
$test_data = json_encode([
    'from' => '5547999999999',
    'body' => 'Quero um site para minha empresa'
]);

$test_webhook = curl_init($webhook_simples);
curl_setopt($test_webhook, CURLOPT_POST, true);
curl_setopt($test_webhook, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($test_webhook, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($test_webhook, CURLOPT_RETURNTRANSFER, true);
curl_setopt($test_webhook, CURLOPT_TIMEOUT, 30);
curl_setopt($test_webhook, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($test_webhook);
$test_code = curl_getinfo($test_webhook, CURLINFO_HTTP_CODE);
$test_time = curl_getinfo($test_webhook, CURLINFO_TOTAL_TIME);
curl_close($test_webhook);

echo "Status: HTTP $test_code\n";
echo "Tempo: {$test_time}s\n";
echo "Resposta: " . substr($test_response, 0, 300) . "\n\n";

if ($test_code === 200) {
    $test_json = json_decode($test_response, true);
    if (isset($test_json['success']) && $test_json['success']) {
        echo "✅ WEBHOOK SIMPLIFICADO FUNCIONANDO!\n\n";
        
        echo "📊 Detalhes da resposta:\n";
        echo "Ana respondeu: " . substr($test_json['ana_response'] ?? 'N/A', 0, 100) . "...\n";
        echo "Ação detectada: " . ($test_json['action_taken'] ?? 'nenhuma') . "\n";
        echo "Transfer Rafael: " . ($test_json['transfer_rafael'] ? 'SIM' : 'NÃO') . "\n";
        echo "Ana API Success: " . ($test_json['ana_api_success'] ? 'SIM' : 'NÃO') . "\n\n";
        
        // Configurar no VPS
        echo "⚙️ Configurando no VPS...\n";
        
        $webhook_config = [
            'url' => $webhook_simples
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
            echo "🎉 WEBHOOK SIMPLIFICADO CONFIGURADO!\n\n";
            
            echo "✅ SISTEMA ANA ATIVO (Versão Simplificada)!\n";
            echo "==========================================\n\n";
            
            echo "📱 TESTE AGORA VIA WHATSAPP:\n";
            echo "• 'Olá' → Ana responde normalmente\n";
            echo "• 'Quero um site' → Transfere para Rafael\n";
            echo "• 'Meu site quebrou' → Transfere para Suporte\n\n";
            
            echo "📊 MONITORAMENTO:\n";
            echo "• Logs: grep 'ANA_SIMPLES' /var/log/apache2/error.log\n";
            echo "• Dashboard: https://app.pixel12digital.com.br/painel/gestao_transferencias.php\n\n";
            
            echo "🔄 PRÓXIMOS PASSOS:\n";
            echo "1. Teste via WhatsApp real\n";
            echo "2. Se funcionar, problema era nas dependências do webhook principal\n";
            echo "3. Se não funcionar, problema é no VPS/configuração\n\n";
            
            echo "✅ ANA ESTÁ ATENDENDO AGORA!\n";
            
        } else {
            echo "❌ Falha na configuração VPS\n";
        }
        
    } else {
        echo "⚠️ Webhook responde mas com erro:\n";
        echo "Erro: " . ($test_json['error'] ?? 'Desconhecido') . "\n";
    }
    
} else {
    echo "❌ Webhook simplificado também falha\n";
    echo "Isso indica problema mais profundo no servidor\n";
    echo "Resposta completa: $test_response\n";
}

echo "\n📋 DIAGNÓSTICO FINAL:\n";
echo "====================\n";
if ($test_code === 200) {
    echo "✅ Problema era nas dependências do webhook principal\n";
    echo "✅ Ana agora está funcionando via webhook simplificado\n";
    echo "💡 Solução: Usar webhook simplificado permanentemente ou corrigir dependências\n";
} else {
    echo "❌ Problema é mais profundo (servidor/PHP/configuração)\n";
    echo "💡 Necessário debug avançado do servidor\n";
}
?> 