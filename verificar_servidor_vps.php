<?php
echo "🔍 VERIFICANDO SERVIDOR VPS - QR ENDPOINTS\n";
echo "==========================================\n\n";

$canais = [
    3000 => 'default',
    3001 => 'comercial'
];

foreach ($canais as $porta => $sessao) {
    echo "🔍 TESTANDO CANAL $porta (sessão: $sessao)\n";
    echo str_repeat("-", 50) . "\n";
    
    // 1. Testar endpoint /qr direto
    $url_qr = "http://212.85.11.238:$porta/qr?session=$sessao";
    echo "📱 Testando: $url_qr\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_qr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP Code: $http_code\n";
    
    if ($http_code == 200 && $response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "   ✅ Resposta válida\n";
            echo "   - Success: " . ($data['success'] ? 'SIM' : 'NÃO') . "\n";
            echo "   - Message: " . ($data['message'] ?? 'N/A') . "\n";
            
            if (isset($data['qr']) && $data['qr']) {
                $qr = $data['qr'];
                echo "   - QR encontrado: SIM\n";
                echo "   - Tamanho QR: " . strlen($qr) . " caracteres\n";
                echo "   - Início QR: " . substr($qr, 0, 20) . "...\n";
                
                // Verificar se é o QR problemático
                if (str_starts_with($qr, '2@qJaXRo')) {
                    echo "   ⚠️  QR PROBLEMÁTICO DETECTADO!\n";
                } else {
                    echo "   ✅ QR parece válido\n";
                }
            } else {
                echo "   ❌ QR não encontrado na resposta\n";
            }
        } else {
            echo "   ❌ Resposta JSON inválida\n";
        }
    } else {
        echo "   ❌ Erro na requisição\n";
        if ($curl_error) {
            echo "   - Curl Error: $curl_error\n";
        }
    }
    
    // 2. Testar endpoint /status
    echo "\n📊 Testando /status:\n";
    $url_status = "http://212.85.11.238:$porta/status";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_status);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && $response) {
        $data = json_decode($response, true);
        if ($data && isset($data['clients_status'][$sessao])) {
            $session_data = $data['clients_status'][$sessao];
            echo "   - Ready: " . ($session_data['ready'] ? 'SIM' : 'NÃO') . "\n";
            echo "   - Has QR: " . ($session_data['hasQR'] ? 'SIM' : 'NÃO') . "\n";
            
            if (isset($session_data['qr']) && $session_data['qr']) {
                $qr = $session_data['qr'];
                echo "   - QR no status: SIM (" . strlen($qr) . " chars)\n";
                echo "   - Início: " . substr($qr, 0, 20) . "...\n";
            } else {
                echo "   - QR no status: NÃO\n";
            }
        } else {
            echo "   ❌ Sessão não encontrada no status\n";
        }
    } else {
        echo "   ❌ Erro ao obter status\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "🎯 DIAGNÓSTICO:\n";
echo "===============\n";
echo "Se os endpoints /qr retornam 404, o servidor ainda usa código antigo\n";
echo "Se retornam 200 mas QR é '2@qJaXRo...', há problema na geração\n";
echo "Se retornam 200 com QR válido, o problema está no painel\n\n";

echo "🔧 PRÓXIMAS AÇÕES:\n";
echo "==================\n";
echo "1. Se endpoints retornam 404: Aplicar correção na VPS\n";
echo "2. Se QR é problemático: Reiniciar sessões na VPS\n";
echo "3. Se tudo OK: Testar painel novamente\n\n";

echo "✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 