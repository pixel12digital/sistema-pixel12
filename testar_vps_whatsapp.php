<?php
/**
 * Script de teste para verificar conectividade com VPS WhatsApp
 * Identifica problemas de conexão e QR Code
 */

echo "=== TESTE DE CONECTIVIDADE VPS WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

$sessions = [
    '3000' => 'default',
    '3001' => 'comercial'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- TESTANDO VPS PORTA $porta ($vps_url) ---\n";
    $session = $sessions[$porta];
    
    // 1. Teste de conectividade básica
    echo "1. Teste de conectividade básica...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ VPS $porta está respondendo (HTTP $http_code)\n";
        $data = json_decode($response, true);
        
        if ($data && isset($data['clients_status'])) {
            echo "   📊 Sessões disponíveis: " . implode(', ', array_keys($data['clients_status'])) . "\n";
            
            if (isset($data['clients_status'][$session])) {
                $session_status = $data['clients_status'][$session];
                echo "   📱 Sessão '$session': " . ($session_status['status'] ?? 'unknown') . "\n";
                echo "   📝 Mensagem: " . ($session_status['message'] ?? 'N/A') . "\n";
                
                // Verificar se tem QR Code
                if (isset($session_status['qr']) && !empty($session_status['qr'])) {
                    echo "   ✅ QR Code disponível (tamanho: " . strlen($session_status['qr']) . " chars)\n";
                } else {
                    echo "   ❌ QR Code não disponível\n";
                }
            } else {
                echo "   ❌ Sessão '$session' não encontrada\n";
            }
        } else {
            echo "   ⚠️ Resposta inválida do VPS\n";
        }
    } else {
        echo "   ❌ VPS $porta não está respondendo (HTTP $http_code)\n";
        if ($curl_error) {
            echo "   🔍 Erro cURL: $curl_error\n";
        }
    }
    
    // 2. Teste específico de QR Code
    echo "\n2. Teste específico de QR Code...\n";
    $qr_endpoint = "/qr?session=" . urlencode($session);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $qr_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $qr_response = curl_exec($ch);
    $qr_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($qr_http_code == 200) {
        $qr_data = json_decode($qr_response, true);
        if ($qr_data && isset($qr_data['qr']) && !empty($qr_data['qr'])) {
            echo "   ✅ QR Code obtido via endpoint /qr (tamanho: " . strlen($qr_data['qr']) . " chars)\n";
        } else {
            echo "   ❌ QR Code não disponível via endpoint /qr\n";
            echo "   📄 Resposta: " . substr($qr_response, 0, 200) . "...\n";
        }
    } else {
        echo "   ❌ Erro ao obter QR Code (HTTP $qr_http_code)\n";
    }
    
    // 3. Teste de logout (para forçar nova sessão)
    echo "\n3. Teste de logout...\n";
    $logout_endpoint = "/session/{$session}/disconnect";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $logout_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_POST, true);
    $logout_response = curl_exec($ch);
    $logout_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($logout_http_code == 200) {
        echo "   ✅ Logout realizado com sucesso\n";
    } else {
        echo "   ⚠️ Logout falhou (HTTP $logout_http_code) - pode ser normal se não estava conectado\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// 4. Teste do proxy PHP
echo "--- TESTANDO PROXY PHP ---\n";
echo "4. Teste do ajax_whatsapp.php...\n";

$proxy_url = 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php';
$test_data = [
    'action' => 'status',
    'porta' => '3001'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $proxy_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$proxy_response = curl_exec($ch);
$proxy_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$proxy_curl_error = curl_error($ch);
curl_close($ch);

if ($proxy_http_code == 200) {
    $proxy_data = json_decode($proxy_response, true);
    if ($proxy_data) {
        echo "   ✅ Proxy PHP funcionando\n";
        echo "   📊 Status: " . ($proxy_data['status'] ?? 'N/A') . "\n";
        echo "   📝 Mensagem: " . ($proxy_data['message'] ?? 'N/A') . "\n";
        if (isset($proxy_data['debug'])) {
            echo "   🔍 Debug: " . json_encode($proxy_data['debug']) . "\n";
        }
    } else {
        echo "   ❌ Proxy PHP retornou JSON inválido\n";
        echo "   📄 Resposta: " . substr($proxy_response, 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Proxy PHP não está respondendo (HTTP $proxy_http_code)\n";
    if ($proxy_curl_error) {
        echo "   🔍 Erro cURL: $proxy_curl_error\n";
    }
}

echo "\n=== FIM DO TESTE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 