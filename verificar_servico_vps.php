<?php
/**
 * Script para verificar e reiniciar o serviço WhatsApp no VPS
 */

echo "=== VERIFICAÇÃO E REINICIALIZAÇÃO DO SERVIÇO VPS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- VERIFICANDO VPS PORTA $porta ($vps_url) ---\n";
    
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
        if ($data) {
            echo "   📊 Estrutura: " . json_encode(array_keys($data)) . "\n";
            if (isset($data['ready'])) {
                echo "   🔧 Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
            }
            if (isset($data['clients_status'])) {
                echo "   📱 Sessões: " . implode(', ', array_keys($data['clients_status'])) . "\n";
            }
        }
    } else {
        echo "   ❌ VPS $porta não está respondendo (HTTP $http_code)\n";
        if ($curl_error) {
            echo "   🔍 Erro cURL: $curl_error\n";
        }
        continue;
    }
    
    // 2. Teste de QR Code com timeout maior
    echo "\n2. Teste de QR Code com timeout maior...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/qr');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout maior
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $qr_response = curl_exec($ch);
    $qr_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $qr_curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($qr_http_code == 200) {
        echo "   ✅ QR Code endpoint responde (HTTP $qr_http_code)\n";
        $qr_data = json_decode($qr_response, true);
        if ($qr_data && isset($qr_data['qr']) && !empty($qr_data['qr'])) {
            echo "   📱 QR Code disponível (tamanho: " . strlen($qr_data['qr']) . " chars)\n";
        } else {
            echo "   ❌ QR Code não disponível na resposta\n";
        }
    } elseif ($qr_http_code == 0) {
        echo "   ⚠️ QR Code endpoint timeout (HTTP $qr_http_code)\n";
        if ($qr_curl_error) {
            echo "   🔍 Erro cURL: $qr_curl_error\n";
        }
    } else {
        echo "   ❌ QR Code endpoint erro (HTTP $qr_http_code)\n";
    }
    
    // 3. Tentar criar uma sessão simples
    echo "\n3. Tentando criar sessão simples...\n";
    
    // Primeiro, tentar endpoint simples
    $simple_endpoints = [
        '/init' => ['sessionName' => 'default'],
        '/start' => ['sessionName' => 'default'],
        '/create' => ['sessionName' => 'default']
    ];
    
    foreach ($simple_endpoints as $endpoint => $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $init_response = curl_exec($ch);
        $init_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($init_http_code == 200) {
            echo "   ✅ $endpoint funcionou (HTTP $init_http_code)\n";
            $init_data = json_decode($init_response, true);
            if ($init_data) {
                echo "   📝 Resposta: " . json_encode($init_data) . "\n";
            }
            break;
        } elseif ($init_http_code == 404) {
            echo "   ❌ $endpoint não encontrado (HTTP $init_http_code)\n";
        } else {
            echo "   ⚠️ $endpoint retorna HTTP $init_http_code\n";
        }
    }
    
    // 4. Verificar se alguma sessão foi criada
    echo "\n4. Verificando se alguma sessão foi criada...\n";
    sleep(3); // Aguardar um pouco
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['clients_status'])) {
            $sessions = array_keys($data['clients_status']);
            if (!empty($sessions)) {
                echo "   ✅ Sessões encontradas: " . implode(', ', $sessions) . "\n";
                foreach ($sessions as $session) {
                    $session_status = $data['clients_status'][$session];
                    echo "   📱 Sessão '$session': " . ($session_status['status'] ?? 'unknown') . "\n";
                    if (isset($session_status['qr']) && !empty($session_status['qr'])) {
                        echo "   ✅ QR Code disponível para '$session' (tamanho: " . strlen($session_status['qr']) . " chars)\n";
                    }
                }
            } else {
                echo "   ❌ Nenhuma sessão encontrada\n";
            }
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// 5. Teste final do proxy PHP
echo "--- TESTE FINAL DO PROXY PHP ---\n";
echo "5. Testando proxy PHP...\n";

$proxy_url = 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php';

// Testar porta 3001
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
curl_close($ch);

if ($proxy_http_code == 200) {
    $proxy_data = json_decode($proxy_response, true);
    if ($proxy_data) {
        echo "   ✅ Proxy PHP funcionando\n";
        echo "   📊 Status: " . ($proxy_data['status'] ?? 'N/A') . "\n";
        echo "   📝 Mensagem: " . ($proxy_data['message'] ?? 'N/A') . "\n";
        
        // Testar QR Code
        $qr_test_data = [
            'action' => 'qr',
            'porta' => '3001'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $proxy_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout maior
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($qr_test_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $qr_proxy_response = curl_exec($ch);
        $qr_proxy_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($qr_proxy_http_code == 200) {
            $qr_proxy_data = json_decode($qr_proxy_response, true);
            if ($qr_proxy_data && isset($qr_proxy_data['qr']) && !empty($qr_proxy_data['qr'])) {
                echo "   ✅ QR Code disponível via proxy (tamanho: " . strlen($qr_proxy_data['qr']) . " chars)\n";
            } else {
                echo "   ❌ QR Code não disponível via proxy\n";
                if (isset($qr_proxy_data['debug'])) {
                    echo "   🔍 Debug: " . json_encode($qr_proxy_data['debug']) . "\n";
                }
            }
        } else {
            echo "   ❌ Erro ao obter QR Code via proxy (HTTP $qr_proxy_http_code)\n";
        }
    }
} else {
    echo "   ❌ Proxy PHP não está respondendo (HTTP $proxy_http_code)\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "\n💡 RECOMENDAÇÕES:\n";
echo "1. Se o VPS não está respondendo, o serviço pode estar parado\n";
echo "2. Se o QR Code está dando timeout, o serviço pode estar sobrecarregado\n";
echo "3. Tente reiniciar o serviço WhatsApp no VPS\n";
echo "4. Verifique se há recursos suficientes (CPU, RAM) no VPS\n";
?> 