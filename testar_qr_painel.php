<?php
echo "🎯 TESTE FINAL DO QR CODE NO PAINEL\n";
echo "=====================================\n\n";

// Testar ambos os canais
$canais = [
    3000 => 'default',
    3001 => 'comercial'
];

foreach ($canais as $porta => $sessao) {
    echo "🔍 TESTANDO CANAL $porta (sessão: $sessao)\n";
    echo "-------------------------------------------\n";
    
    // 1. Testar status
    $url_status = "http://212.85.11.238:$porta/status";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_status);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 Status endpoint ($porta): HTTP $http_code\n";
    
    if ($http_code == 200 && $response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "   - Status geral: " . ($data['status'] ?? 'N/A') . "\n";
            echo "   - Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
            
            // Verificar clients_status
            if (isset($data['clients_status'])) {
                foreach ($data['clients_status'] as $session => $info) {
                    echo "   - Sessão '$session':\n";
                    echo "     * Ready: " . ($info['ready'] ? 'SIM' : 'NÃO') . "\n";
                    echo "     * Has QR: " . ($info['hasQR'] ? 'SIM' : 'NÃO') . "\n";
                    echo "     * QR disponível: " . (isset($info['qr']) && $info['qr'] ? 'SIM ✅' : 'NÃO ❌') . "\n";
                    
                    if (isset($info['qr']) && $info['qr']) {
                        echo "     * Tamanho do QR: " . strlen($info['qr']) . " caracteres\n";
                    }
                }
            } else {
                echo "   ❌ clients_status não encontrado\n";
            }
        } else {
            echo "   ❌ Resposta inválida\n";
        }
    } else {
        echo "   ❌ Erro na conexão\n";
    }
    
    // 2. Testar endpoint QR direto
    echo "\n🎯 Testando endpoint /qr:\n";
    $url_qr = "http://212.85.11.238:$porta/qr?session=$sessao";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_qr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response_qr = curl_exec($ch);
    $http_code_qr = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   - Endpoint /qr: HTTP $http_code_qr\n";
    if ($http_code_qr == 200 && $response_qr) {
        $qr_data = json_decode($response_qr, true);
        if ($qr_data && isset($qr_data['success']) && $qr_data['success']) {
            echo "   ✅ QR Code disponível!\n";
            echo "   - Mensagem: " . ($qr_data['message'] ?? 'N/A') . "\n";
            if (isset($qr_data['qr'])) {
                echo "   - Tamanho: " . strlen($qr_data['qr']) . " caracteres\n";
            }
        } else {
            echo "   ❌ QR não disponível: " . ($qr_data['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "   ❌ Erro no endpoint /qr\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

echo "🔗 PRÓXIMOS PASSOS:\n";
echo "1. Acesse o painel: https://app.pixel12digital.com.br/painel/comunicacao.php\n";
echo "2. Os QR codes devem aparecer automaticamente!\n";
echo "3. Se não aparecerem, verifique o console do navegador (F12)\n\n";

echo "✅ TESTE CONCLUÍDO!\n";
?> 