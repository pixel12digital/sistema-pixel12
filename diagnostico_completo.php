<?php
/**
 * 🔍 DIAGNÓSTICO COMPLETO DO SISTEMA WHATSAPP
 */

echo "🔍 DIAGNÓSTICO COMPLETO DO SISTEMA WHATSAPP\n";
echo "==========================================\n\n";

$vps_ip = '212.85.11.238';
$canais = [3000, 3001];

foreach ($canais as $porta) {
    echo "🔄 CANAL $porta\n";
    echo "==============\n";
    
    // 1. Status detalhado
    echo "1. STATUS DETALHADO:\n";
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n\n";
    
    if ($http_code === 200) {
        $status = json_decode($response, true);
        
        // Verificar se há QR no status
        if (isset($status['clients_status'])) {
            echo "   🔍 CLIENTS STATUS:\n";
            foreach ($status['clients_status'] as $sessao => $dados) {
                echo "     Sessão: $sessao\n";
                echo "     Ready: " . ($dados['ready'] ? 'true' : 'false') . "\n";
                echo "     HasQR: " . ($dados['hasQR'] ? 'true' : 'false') . "\n";
                if (isset($dados['qr'])) {
                    echo "     QR: " . substr($dados['qr'], 0, 30) . "...\n";
                }
                echo "\n";
            }
        }
    }
    
    // 2. Tentar endpoints diretos de QR
    echo "2. TESTANDO ENDPOINTS QR:\n";
    $endpoints = [
        "/qr",
        "/qr?session=default",
        "/qr?session=comercial"
    ];
    
    foreach ($endpoints as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $qr_response = curl_exec($ch);
        $qr_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   $endpoint: HTTP $qr_code\n";
        if ($qr_code === 200) {
            $qr_data = json_decode($qr_response, true);
            if (isset($qr_data['qr']) && !empty($qr_data['qr'])) {
                echo "     ✅ QR ENCONTRADO: " . substr($qr_data['qr'], 0, 30) . "...\n";
            } else {
                echo "     ❌ Sem QR: $qr_response\n";
            }
        } else {
            echo "     ❌ Erro: " . substr($qr_response, 0, 100) . "...\n";
        }
    }
    
    echo "\n";
}

// 3. Testar o painel diretamente
echo "3. TESTANDO PAINEL (AJAX):\n";
echo "========================\n";

foreach ([3000, 3001] as $porta) {
    echo "Canal $porta:\n";
    
    // Simular requisição do painel
    $ch = curl_init("http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php?action=qr&porta=$porta");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $ajax_response = curl_exec($ch);
    $ajax_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP Code: $ajax_code\n";
    echo "   Resposta: $ajax_response\n\n";
}

echo "🎯 DIAGNÓSTICO CONCLUÍDO!\n";
echo "========================\n";
echo "Verifique se algum QR foi encontrado acima.\n";
echo "Se sim, o problema pode estar no frontend do painel.\n";
?> 