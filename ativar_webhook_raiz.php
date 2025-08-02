<?php
echo "🚨 ATIVANDO WEBHOOK DA RAIZ\n";
echo "===========================\n\n";

$webhook_raiz = 'https://app.pixel12digital.com.br/webhook_ana.php';
$vps_ip = '212.85.11.238';

echo "🎯 Webhook Raiz: $webhook_raiz\n\n";

// 1. Testar webhook da raiz
echo "🧪 TESTE: Webhook da Raiz\n";
echo "-------------------------\n";

$test_raiz = curl_init($webhook_raiz);
curl_setopt($test_raiz, CURLOPT_POST, true);
curl_setopt($test_raiz, CURLOPT_POSTFIELDS, json_encode([
    'from' => '5547999999999',
    'body' => 'olá'
]));
curl_setopt($test_raiz, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($test_raiz, CURLOPT_RETURNTRANSFER, true);
curl_setopt($test_raiz, CURLOPT_TIMEOUT, 10);
curl_setopt($test_raiz, CURLOPT_SSL_VERIFYPEER, false);

$raiz_response = curl_exec($test_raiz);
$raiz_code = curl_getinfo($test_raiz, CURLINFO_HTTP_CODE);
curl_close($test_raiz);

echo "HTTP Code: $raiz_code\n";
echo "Resposta: " . substr($raiz_response, 0, 300) . "\n\n";

if ($raiz_code === 200) {
    echo "✅ WEBHOOK DA RAIZ FUNCIONA!\n\n";
    
    // Testar diferentes cenários
    echo "🧪 TESTANDO CENÁRIOS:\n";
    echo "---------------------\n";
    
    $cenarios = [
        ['msg' => 'Quero um site', 'esperado' => 'transfer_rafael'],
        ['msg' => 'Meu site está com problema', 'esperado' => 'transfer_suporte'],
        ['msg' => 'Falar com pessoa', 'esperado' => 'transfer_humano']
    ];
    
    foreach ($cenarios as $cenario) {
        echo "📝 Testando: \"{$cenario['msg']}\"\n";
        
        $test_cenario = curl_init($webhook_raiz);
        curl_setopt($test_cenario, CURLOPT_POST, true);
        curl_setopt($test_cenario, CURLOPT_POSTFIELDS, json_encode([
            'from' => '5547999999998',
            'body' => $cenario['msg']
        ]));
        curl_setopt($test_cenario, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($test_cenario, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($test_cenario, CURLOPT_TIMEOUT, 10);
        curl_setopt($test_cenario, CURLOPT_SSL_VERIFYPEER, false);
        
        $cenario_response = curl_exec($test_cenario);
        $cenario_code = curl_getinfo($test_cenario, CURLINFO_HTTP_CODE);
        curl_close($test_cenario);
        
        if ($cenario_code === 200) {
            $cenario_data = json_decode($cenario_response, true);
            $acao = $cenario_data['action_taken'] ?? 'erro';
            
            if ($acao === $cenario['esperado']) {
                echo "   ✅ Detectou: $acao\n";
            } else {
                echo "   ⚠️ Detectou: $acao (esperado: {$cenario['esperado']})\n";
            }
        } else {
            echo "   ❌ Erro HTTP $cenario_code\n";
        }
    }
    
    echo "\n🔧 CONFIGURANDO NA VPS:\n";
    echo "------------------------\n";
    
    // Configurar na VPS
    $config_vps = curl_init("http://$vps_ip:3000/webhook/config");
    curl_setopt($config_vps, CURLOPT_POST, true);
    curl_setopt($config_vps, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_raiz]));
    curl_setopt($config_vps, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($config_vps, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($config_vps, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($config_vps);
    $config_code = curl_getinfo($config_vps, CURLINFO_HTTP_CODE);
    curl_close($config_vps);
    
    echo "Configuração VPS: HTTP $config_code\n";
    echo "Resposta: " . substr($config_response, 0, 200) . "\n\n";
    
    if ($config_code === 200) {
        echo "✅ WEBHOOK CONFIGURADO NA VPS!\n\n";
        
        echo "🎉 SISTEMA REESTABELECIDO!\n";
        echo "==========================\n\n";
        
        echo "📱 AGORA FUNCIONA:\n";
        echo "• Envie 'olá' → Ana responde\n";
        echo "• Envie 'quero um site' → Detecta Rafael\n";
        echo "• Envie 'problema no site' → Detecta Suporte\n";
        echo "• Envie 'falar com pessoa' → Detecta Humano\n\n";
        
        echo "📊 MONITORAMENTO:\n";
        echo "• Logs salvos no error_log do servidor\n";
        echo "• Busque por '[WEBHOOK_RAIZ]' nos logs\n\n";
        
        echo "⚠️ MODO EMERGÊNCIA ATIVO:\n";
        echo "• Webhook na raiz (sem subdiretorios)\n";
        echo "• Respostas inteligentes básicas\n";
        echo "• Detecção de transferências ativa\n";
        echo "• Logs de todas as ações\n\n";
        
        echo "🔄 PRÓXIMOS PASSOS:\n";
        echo "1. Teste real enviando WhatsApp\n";
        echo "2. Monitore logs do servidor\n";
        echo "3. Depois corrigir problema do path\n";
        
    } else {
        echo "❌ Falha na configuração VPS\n";
    }
    
} else {
    echo "❌ Webhook da raiz não funciona\n";
    echo "Problema pode ser no servidor web\n";
}
?> 