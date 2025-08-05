<?php
/**
 * Script para inicializar sessões WhatsApp no VPS
 * Cria as sessões necessárias para os canais
 */

echo "=== INICIALIZAÇÃO DE SESSÕES WHATSAPP ===\n";
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
    echo "--- INICIALIZANDO VPS PORTA $porta ($vps_url) ---\n";
    $session = $sessions[$porta];
    
    // 1. Verificar status atual
    echo "1. Verificando status atual...\n";
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
            echo "   📊 Sessões atuais: " . implode(', ', array_keys($data['clients_status'])) . "\n";
            
            if (isset($data['clients_status'][$session])) {
                $session_status = $data['clients_status'][$session];
                echo "   📱 Sessão '$session' já existe: " . ($session_status['status'] ?? 'unknown') . "\n";
                
                // Se já está conectada, não precisa inicializar
                if (in_array($session_status['status'], ['connected', 'ready', 'authenticated'])) {
                    echo "   ✅ Sessão já está conectada, pulando inicialização\n";
                    continue;
                }
            } else {
                echo "   ❌ Sessão '$session' não encontrada, criando...\n";
            }
        }
    } else {
        echo "   ❌ VPS $porta não está respondendo (HTTP $http_code)\n";
        continue;
    }
    
    // 2. Criar/Inicializar sessão
    echo "2. Criando/Inicializando sessão '$session'...\n";
    $init_endpoint = "/session/{$session}/init";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . $init_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'sessionName' => $session,
        'headless' => true,
        'useChrome' => false,
        'debug' => false
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $init_response = curl_exec($ch);
    $init_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($init_http_code == 200) {
        $init_data = json_decode($init_response, true);
        echo "   ✅ Sessão criada com sucesso\n";
        if (isset($init_data['message'])) {
            echo "   📝 Mensagem: " . $init_data['message'] . "\n";
        }
    } else {
        echo "   ❌ Erro ao criar sessão (HTTP $init_http_code)\n";
        echo "   📄 Resposta: " . substr($init_response, 0, 200) . "...\n";
        continue;
    }
    
    // 3. Aguardar um pouco para a sessão inicializar
    echo "3. Aguardando inicialização da sessão...\n";
    sleep(3);
    
    // 4. Verificar se a sessão foi criada
    echo "4. Verificando se a sessão foi criada...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['clients_status'][$session])) {
            $session_status = $data['clients_status'][$session];
            echo "   ✅ Sessão '$session' criada com sucesso\n";
            echo "   📱 Status: " . ($session_status['status'] ?? 'unknown') . "\n";
            echo "   📝 Mensagem: " . ($session_status['message'] ?? 'N/A') . "\n";
            
            // Verificar se tem QR Code
            if (isset($session_status['qr']) && !empty($session_status['qr'])) {
                echo "   ✅ QR Code disponível (tamanho: " . strlen($session_status['qr']) . " chars)\n";
            } else {
                echo "   ❌ QR Code não disponível ainda\n";
            }
        } else {
            echo "   ❌ Sessão não foi criada corretamente\n";
        }
    } else {
        echo "   ❌ Erro ao verificar status (HTTP $http_code)\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// 5. Teste final do proxy PHP
echo "--- TESTE FINAL DO PROXY PHP ---\n";
echo "5. Testando proxy PHP após inicialização...\n";

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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
            }
        } else {
            echo "   ❌ Erro ao obter QR Code via proxy (HTTP $qr_proxy_http_code)\n";
        }
    }
} else {
    echo "   ❌ Proxy PHP não está respondendo (HTTP $proxy_http_code)\n";
}

echo "\n=== FIM DA INICIALIZAÇÃO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "\n💡 DICA: Agora você pode tentar conectar o WhatsApp no painel!\n";
?> 