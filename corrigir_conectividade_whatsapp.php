<?php
/**
 * CORREÇÃO DE CONECTIVIDADE WHATSAPP
 * Testa e corrige problemas de conectividade com o VPS
 */

echo "🔧 === CORREÇÃO DE CONECTIVIDADE WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- TESTANDO VPS $porta ---\n";
    
    // 1. Testar status
    echo "1. 🔍 Testando status...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $status_data = json_decode($response, true);
        echo "   ✅ VPS respondendo (HTTP $http_code)\n";
        echo "   📊 Ready: " . ($status_data['ready'] ? '✅' : '❌') . "\n";
        echo "   📱 Sessões: " . $status_data['sessions'] . "\n";
        
        // 2. Iniciar sessão se necessário
        if ($status_data['sessions'] == 0) {
            echo "2. 🚀 Iniciando sessão...\n";
            $session_name = ($porta == '3000') ? 'default' : 'comercial';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . "/session/start/$session_name");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo "   ✅ Sessão $session_name iniciada com sucesso!\n";
            } else {
                echo "   ❌ Erro ao iniciar sessão (HTTP $http_code)\n";
            }
        }
        
        // 3. Testar QR Code
        echo "3. 📱 Testando QR Code...\n";
        $session_name = ($porta == '3000') ? 'default' : 'comercial';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vps_url . "/qr?session=$session_name");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $qr_data = json_decode($response, true);
            if ($qr_data['success']) {
                echo "   ✅ QR Code disponível!\n";
                echo "   📋 Status: " . $qr_data['data']['status'] . "\n";
            } else {
                echo "   ⚠️ QR Code não disponível: " . $qr_data['message'] . "\n";
            }
        } else {
            echo "   ❌ Erro ao obter QR Code (HTTP $http_code)\n";
        }
        
    } else {
        echo "   ❌ VPS não respondendo (HTTP $http_code)\n";
    }
    
    echo "\n";
}

echo "=== INSTRUÇÕES PARA O PAINEL ===\n";
echo "1. Acesse: http://localhost/loja-virtual-revenda/painel/comunicacao.php\n";
echo "2. Atualize a página (F5)\n";
echo "3. Clique em 'Conectar' em qualquer canal\n";
echo "4. Se ainda houver erro, execute este script novamente\n";
?> 