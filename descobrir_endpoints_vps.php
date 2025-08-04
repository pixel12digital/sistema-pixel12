<?php
/**
 * 🔍 DESCOBRIR ENDPOINTS DISPONÍVEIS VPS
 * 
 * Tenta descobrir quais endpoints estão disponíveis no VPS canal 3000
 */

echo "🔍 DESCOBRINDO ENDPOINTS VPS CANAL 3000\n";
echo "=======================================\n\n";

$vps_ip = '212.85.11.238';
$porta = 3000;

// Endpoints comuns para testar
$endpoints_teste = [
    // Endpoints de informação
    '', 'status', 'info', 'health', 'ping',
    // Endpoints de configuração
    'config', 'settings', 'webhook', 'set-webhook', 'webhooks',
    // Endpoints de ação
    'send', 'send-message', 'sendMessage', 'message', 'chat',
    // Endpoints de autenticação
    'qr', 'qrcode', 'auth', 'login', 'connect',
    // Outros possíveis
    'api', 'v1', 'whatsapp', 'session', 'instance'
];

echo "📡 TESTANDO ENDPOINTS DISPONÍVEIS:\n";
echo "==================================\n";

$endpoints_funcionais = [];

foreach ($endpoints_teste as $endpoint) {
    $url = "http://$vps_ip:$porta" . ($endpoint ? "/$endpoint" : '');
    
    echo "🔄 GET $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ HTTP $http_code - FUNCIONAL\n";
        $endpoints_funcionais[] = $endpoint ?: 'root';
        
        // Mostrar resposta se for JSON
        $data = json_decode($response, true);
        if ($data) {
            echo "  📄 " . json_encode($data, JSON_UNESCAPED_SLASHES) . "\n";
        } else {
            echo "  📄 " . substr(trim($response), 0, 100) . "...\n";
        }
    } elseif ($http_code === 404) {
        echo "  ❌ HTTP $http_code - NÃO EXISTE\n";
    } else {
        echo "  ⚠️ HTTP $http_code\n";
        if ($response) {
            echo "  📄 " . substr(trim($response), 0, 80) . "...\n";
        }
    }
    echo "\n";
}

echo "📋 RESUMO - ENDPOINTS FUNCIONAIS:\n";
echo "=================================\n";

if (!empty($endpoints_funcionais)) {
    foreach ($endpoints_funcionais as $ep) {
        echo "✅ /$ep\n";
    }
} else {
    echo "❌ Nenhum endpoint funcional encontrado além de /status\n";
}

// Se temos /status, vamos analisar melhor
echo "\n🔍 ANÁLISE DETALHADA DO /status:\n";
echo "===============================\n";

$status_ch = curl_init("http://$vps_ip:$porta/status");
curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_ch, CURLOPT_TIMEOUT, 10);
$status_response = curl_exec($status_ch);
$status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
curl_close($status_ch);

if ($status_code === 200 && $status_response) {
    echo "Status completo:\n";
    echo $status_response . "\n\n";
    
    $status_data = json_decode($status_response, true);
    if ($status_data) {
        echo "🔍 ANÁLISE DOS DADOS:\n";
        foreach ($status_data as $key => $value) {
            echo "  $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }
}

echo "\n💡 POSSÍVEIS SOLUÇÕES:\n";
echo "======================\n";
echo "1. 🔧 O VPS pode precisar ser configurado via SSH\n";
echo "2. 📝 Pode ser uma API personalizada sem endpoints padrão\n";
echo "3. 🌐 Talvez precise configurar via interface web\n";
echo "4. ⚙️ Ou o webhook é configurado em arquivo de configuração\n\n";

echo "🚀 PRÓXIMO PASSO:\n";
echo "Acesse via SSH e verifique:\n";
echo "ssh root@$vps_ip\n";
echo "pm2 logs whatsapp-3000\n";
echo "pm2 show whatsapp-3000\n";

?> 