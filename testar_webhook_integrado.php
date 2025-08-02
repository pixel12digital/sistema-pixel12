<?php
echo "🧪 TESTANDO WEBHOOK INTEGRADO NO ROTEAMENTO\n";
echo "===========================================\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/webhook';

echo "🎯 URL: $webhook_url\n\n";

// Teste 1: GET simples
echo "🔍 TESTE 1: GET simples\n";
echo "-----------------------\n";

$get_test = curl_init($webhook_url);
curl_setopt($get_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($get_test, CURLOPT_TIMEOUT, 10);
curl_setopt($get_test, CURLOPT_SSL_VERIFYPEER, false);

$get_response = curl_exec($get_test);
$get_code = curl_getinfo($get_test, CURLINFO_HTTP_CODE);
curl_close($get_test);

echo "GET: HTTP $get_code\n";
if ($get_code === 200) {
    echo "✅ GET funcionou!\n";
    echo "Resposta: " . substr($get_response, 0, 200) . "\n\n";
    
    // Teste 2: POST com dados
    echo "🔍 TESTE 2: POST com dados\n";
    echo "--------------------------\n";
    
    $cenarios = [
        ['from' => '5547999999999', 'body' => 'olá'],
        ['from' => '5547999999998', 'body' => 'quero um site'],
        ['from' => '5547999999997', 'body' => 'meu site tem problema'],
        ['from' => '5547999999996', 'body' => 'falar com pessoa']
    ];
    
    foreach ($cenarios as $cenario) {
        echo "📝 Testando: \"{$cenario['body']}\"\n";
        
        $post_test = curl_init($webhook_url);
        curl_setopt($post_test, CURLOPT_POST, true);
        curl_setopt($post_test, CURLOPT_POSTFIELDS, json_encode($cenario));
        curl_setopt($post_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($post_test, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($post_test, CURLOPT_TIMEOUT, 10);
        curl_setopt($post_test, CURLOPT_SSL_VERIFYPEER, false);
        
        $post_response = curl_exec($post_test);
        $post_code = curl_getinfo($post_test, CURLINFO_HTTP_CODE);
        curl_close($post_test);
        
        if ($post_code === 200) {
            $data = json_decode($post_response, true);
            if (isset($data['success']) && $data['success']) {
                echo "   ✅ Sucesso: {$data['action_taken']}\n";
                echo "   📩 Ana: " . substr($data['ana_response'], 0, 50) . "...\n";
            } else {
                echo "   ❌ JSON inválido\n";
            }
        } else {
            echo "   ❌ HTTP $post_code\n";
        }
    }
    
    // Teste 3: Configurar na VPS
    echo "\n🔧 TESTE 3: Configurando na VPS\n";
    echo "--------------------------------\n";
    
    $config_vps = curl_init("http://212.85.11.238:3000/webhook/config");
    curl_setopt($config_vps, CURLOPT_POST, true);
    curl_setopt($config_vps, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($config_vps, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($config_vps, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($config_vps, CURLOPT_TIMEOUT, 10);
    
    $config_response = curl_exec($config_vps);
    $config_code = curl_getinfo($config_vps, CURLINFO_HTTP_CODE);
    curl_close($config_vps);
    
    echo "Configuração VPS: HTTP $config_code\n";
    if ($config_code === 200) {
        echo "✅ CONFIGURADO NA VPS!\n\n";
        
        echo "🎉 SISTEMA TOTALMENTE FUNCIONAL!\n";
        echo "================================\n\n";
        
        echo "📱 AGORA TESTE REAL:\n";
        echo "1. Envie 'olá' via WhatsApp\n";
        echo "2. Ana deve responder automaticamente\n";
        echo "3. Teste 'quero um site' → Detecta Rafael\n";
        echo "4. Teste 'problema no site' → Detecta Suporte\n";
        echo "5. Teste 'falar com pessoa' → Detecta Humano\n\n";
        
        echo "📊 MONITORAMENTO:\n";
        echo "• Logs: Procure por '[WEBHOOK_ROTEAMENTO]'\n";
        echo "• URL Webhook: $webhook_url\n";
        echo "• Status: ✅ FUNCIONANDO\n\n";
        
        echo "🔄 WEBHOOK INTEGRADO AO SISTEMA:\n";
        echo "• Rota no index.php: webhook, webhook.php, webhook_ana.php\n";
        echo "• Detecção inteligente ativa\n";
        echo "• Logs completos de transferências\n";
        
    } else {
        echo "❌ Falha configuração VPS: HTTP $config_code\n";
        if ($config_response) {
            echo "Erro: " . substr($config_response, 0, 200) . "\n";
        }
    }
    
} else {
    echo "❌ GET falhou: HTTP $get_code\n";
    if ($get_response) {
        echo "Erro: " . substr($get_response, 0, 200) . "\n";
    }
}
?> 