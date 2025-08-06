<?php
// Testar e corrigir webhook
$vps_url = "http://212.85.11.238:3000";
$webhook_correto = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";

echo "🔧 Testando webhook...\n";

// 1. Verificar atual
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$config = json_decode($response, true);
echo "Webhook atual: " . ($config['webhook'] ?? 'N/A') . "\n";

// 2. Se estiver errado, corrigir
if (($config['webhook'] ?? '') !== $webhook_correto) {
    echo "Corrigindo webhook...\n";
    
    $data = json_encode(['url' => $webhook_correto]);
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    echo "Resultado: $result\n";
    
    // 3. Verificar se foi corrigido
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $config = json_decode($response, true);
    echo "Webhook após correção: " . ($config['webhook'] ?? 'N/A') . "\n";
    
    if (($config['webhook'] ?? '') === $webhook_correto) {
        echo "✅ SUCESSO: Webhook corrigido!\n";
    } else {
        echo "❌ ERRO: Webhook não foi corrigido\n";
    }
} else {
    echo "✅ Webhook já está correto!\n";
}

echo "\n🎯 URL CORRETA: $webhook_correto\n";
?> 