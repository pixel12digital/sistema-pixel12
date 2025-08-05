<?php
/**
 * 🧪 TESTE E CORREÇÃO DE ENDPOINTS - VPS WHATSAPP
 * 
 * Script para testar e corrigir os endpoints dos canais 3000 e 3001
 */

echo "🧪 TESTE E CORREÇÃO DE ENDPOINTS - VPS WHATSAPP\n";
echo "===============================================\n\n";

$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

foreach ($portas as $porta) {
    echo "🔍 TESTANDO CANAL $porta\n";
    echo "------------------------\n";
    
    // 1. Testar status
    echo "1. Testando status...\n";
    $status_url = "http://$vps_ip:$porta/status";
    $ch = curl_init($status_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Status OK (HTTP $http_code)\n";
        $status_data = json_decode($response, true);
        if ($status_data) {
            echo "   📊 Ready: " . ($status_data['ready'] ? 'true' : 'false') . "\n";
            echo "   📊 Port: " . ($status_data['port'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ❌ Status falhou (HTTP $http_code)\n";
    }
    
    // 2. Testar webhook config
    echo "2. Testando webhook config...\n";
    $webhook_url = "http://$vps_ip:$porta/webhook/config";
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Webhook config OK (HTTP $http_code)\n";
        $webhook_data = json_decode($response, true);
        if ($webhook_data) {
            echo "   🔗 URL: " . ($webhook_data['webhook_url'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ❌ Webhook config falhou (HTTP $http_code)\n";
        echo "   📝 Resposta: " . substr($response, 0, 200) . "\n";
    }
    
    // 3. Configurar webhook
    echo "3. Configurando webhook...\n";
    $webhook_config_url = "http://$vps_ip:$porta/webhook/config";
    $webhook_payload = json_encode([
        'url' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php'
    ]);
    
    $ch = curl_init($webhook_config_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $webhook_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($webhook_payload)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Webhook configurado com sucesso\n";
        $config_data = json_decode($response, true);
        if ($config_data) {
            echo "   🔗 URL configurada: " . ($config_data['webhook_url'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ❌ Erro ao configurar webhook (HTTP $http_code)\n";
        echo "   📝 Resposta: " . substr($response, 0, 200) . "\n";
    }
    
    // 4. Testar envio de mensagem
    echo "4. Testando envio de mensagem...\n";
    $send_url = "http://$vps_ip:$porta/send/text";
    $send_payload = json_encode([
        'sessionName' => 'default',
        'number' => '554796164699',
        'message' => "Teste canal $porta - " . date('Y-m-d H:i:s')
    ]);
    
    $ch = curl_init($send_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $send_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($send_payload)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Envio de mensagem OK\n";
        $send_data = json_decode($response, true);
        if ($send_data) {
            echo "   📤 Mensagem enviada para: " . ($send_data['to'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ❌ Erro ao enviar mensagem (HTTP $http_code)\n";
        echo "   📝 Resposta: " . substr($response, 0, 200) . "\n";
    }
    
    echo "\n";
}

echo "🎯 RESUMO DOS TESTES\n";
echo "===================\n";

// Verificar se os processos estão rodando
echo "📊 Verificando processos PM2...\n";
$pm2_status = shell_exec('pm2 status 2>/dev/null');
if ($pm2_status) {
    echo $pm2_status;
} else {
    echo "❌ Não foi possível verificar status do PM2\n";
}

echo "\n✅ Testes concluídos!\n";
echo "💡 Se algum endpoint falhou, verifique:\n";
echo "   1. Se o processo PM2 está rodando\n";
echo "   2. Se as portas estão abertas\n";
echo "   3. Se o servidor está respondendo\n";
echo "   4. Se há erros nos logs do PM2\n";
?> 