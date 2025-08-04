<?php
/**
 * Configurar webhook no VPS para usar o arquivo corrigido
 */

echo "=== CONFIGURAÇÃO DO WEBHOOK NO VPS ===\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';
$vps_urls = [
    'http://212.85.11.238:3000',  // Canal Ana
    'http://212.85.11.238:3001'   // Canal Humano
];

foreach ($vps_urls as $vps_url) {
    echo "Configurando webhook em: $vps_url\n";
    
    $config_data = [
        'url' => $webhook_url
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Erro cURL: $error\n";
    } else {
        echo "   Status HTTP: $http_code\n";
        echo "   Resposta: $response\n";
        
        if ($http_code == 200) {
            echo "   ✅ Webhook configurado com sucesso!\n";
        } else {
            echo "   ❌ Falha na configuração\n";
        }
    }
    
    echo "\n";
}

// Testar se os webhooks estão funcionando
echo "TESTANDO WEBHOOKS CONFIGURADOS:\n\n";

foreach ($vps_urls as $vps_url) {
    echo "Testando webhook em: $vps_url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/webhook');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Erro: $error\n";
    } else {
        echo "   Status: $http_code\n";
        echo "   Config atual: $response\n";
    }
    
    echo "\n";
}

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "1. Envie uma mensagem real do WhatsApp para testar\n";
echo "2. Verifique se aparece no chat do sistema\n";
echo "3. Confirme se Ana responde corretamente\n";
?> 