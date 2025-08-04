<?php
/**
 * 🔧 CORRIGIR WEBHOOK CANAL 3000
 * 
 * Configura corretamente o webhook no VPS para receber mensagens do WhatsApp
 */

echo "🔧 CONFIGURANDO WEBHOOK CANAL 3000\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// 1. VERIFICAR STATUS ATUAL
echo "📊 1. VERIFICANDO STATUS ATUAL\n";
echo "==============================\n";

$endpoints_status = [
    'status' => "http://$vps_ip:3000/status",
    'webhook' => "http://$vps_ip:3000/webhook", 
    'config' => "http://$vps_ip:3000/config",
    'settings' => "http://$vps_ip:3000/settings"
];

foreach ($endpoints_status as $nome => $url) {
    echo "🔄 Testando $nome...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Status: HTTP $http_code\n";
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "  Dados: " . json_encode($data, JSON_UNESCAPED_SLASHES) . "\n";
        } else {
            echo "  Resposta: " . substr($response, 0, 100) . "\n";
        }
    }
    echo "\n";
}

// 2. TENTAR DIFERENTES MÉTODOS DE CONFIGURAÇÃO
echo "🔧 2. CONFIGURANDO WEBHOOK\n";
echo "==========================\n";

$config_attempts = [
    // Método 1: POST /webhook
    [
        'method' => 'POST',
        'url' => "http://$vps_ip:3000/webhook",
        'data' => json_encode(['webhook' => $webhook_url])
    ],
    // Método 2: PUT /webhook  
    [
        'method' => 'PUT',
        'url' => "http://$vps_ip:3000/webhook",
        'data' => json_encode(['webhook' => $webhook_url])
    ],
    // Método 3: POST /config
    [
        'method' => 'POST', 
        'url' => "http://$vps_ip:3000/config",
        'data' => json_encode(['webhook' => $webhook_url])
    ],
    // Método 4: POST /settings
    [
        'method' => 'POST',
        'url' => "http://$vps_ip:3000/settings", 
        'data' => json_encode(['webhook' => $webhook_url])
    ],
    // Método 5: POST /set-webhook
    [
        'method' => 'POST',
        'url' => "http://$vps_ip:3000/set-webhook",
        'data' => json_encode(['url' => $webhook_url])
    ]
];

$configurado = false;

foreach ($config_attempts as $i => $attempt) {
    $num = $i + 1;
    echo "🔄 Tentativa $num: {$attempt['method']} {$attempt['url']}\n";
    
    $ch = curl_init($attempt['url']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $attempt['method']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attempt['data']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Status: HTTP $http_code\n";
    echo "  Resposta: " . substr($response, 0, 150) . "\n";
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data && (isset($data['success']) || isset($data['webhook']))) {
            echo "  ✅ CONFIGURAÇÃO ACEITA!\n";
            $configurado = true;
            break;
        }
    }
    echo "\n";
}

// 3. VERIFICAR SE WEBHOOK FOI CONFIGURADO
echo "✅ 3. VERIFICANDO CONFIGURAÇÃO\n";
echo "==============================\n";

sleep(2); // Aguardar aplicação da configuração

$verify_ch = curl_init("http://$vps_ip:3000/webhook");
curl_setopt($verify_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($verify_ch, CURLOPT_TIMEOUT, 10);
$verify_response = curl_exec($verify_ch);
$verify_code = curl_getinfo($verify_ch, CURLINFO_HTTP_CODE);
curl_close($verify_ch);

echo "Status verificação: HTTP $verify_code\n";
if ($verify_response) {
    $verify_data = json_decode($verify_response, true);
    if ($verify_data && isset($verify_data['webhook'])) {
        echo "✅ Webhook configurado: {$verify_data['webhook']}\n";
        if ($verify_data['webhook'] === $webhook_url) {
            echo "🎉 WEBHOOK CONFIGURADO CORRETAMENTE!\n";
        } else {
            echo "⚠️ URL diferente da esperada\n";
        }
    } else {
        echo "❌ Webhook não configurado ou resposta inválida\n";
        echo "Resposta: $verify_response\n";
    }
} else {
    echo "❌ Sem resposta na verificação\n";
}

// 4. TESTE FINAL COM MENSAGEM REAL
echo "\n🧪 4. TESTE FINAL\n";
echo "=================\n";

echo "💡 PRÓXIMOS PASSOS:\n";
echo "1. Envie uma mensagem do seu WhatsApp para o número do canal 3000\n";
echo "2. Aguarde 30 segundos\n";
echo "3. Execute: php verificar_mensagens_recentes.php\n";
echo "4. Verifique se a mensagem aparece no chat centralizado\n\n";

if ($configurado) {
    echo "✅ WEBHOOK RECONFIGURADO COM SUCESSO!\n";
    echo "📱 Agora teste enviando uma mensagem real do WhatsApp\n";
} else {
    echo "❌ NÃO FOI POSSÍVEL CONFIGURAR O WEBHOOK\n";
    echo "💡 O VPS pode usar uma API diferente\n";
    echo "🔧 Tente acessar manualmente: http://$vps_ip:3000\n";
}

?> 