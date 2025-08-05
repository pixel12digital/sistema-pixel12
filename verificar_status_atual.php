<?php
/**
 * Verificar Status Atual do VPS e Tentar Reiniciar
 */

echo "=== VERIFICAÇÃO DE STATUS ATUAL ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- VPS PORTA $porta ($vps_url) ---\n";
    
    // Teste básico de conectividade
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
        
        // Tentar fazer parse da resposta
        $data = json_decode($response, true);
        if ($data) {
            echo "   📊 Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
            if (isset($data['clients_status'])) {
                $sessions = array_keys($data['clients_status']);
                echo "   📱 Sessões: " . (empty($sessions) ? 'nenhuma' : implode(', ', $sessions)) . "\n";
            }
            
            // Se não está ready, tentar reiniciar
            if (!$data['ready']) {
                echo "   ⚠️ Serviço não está pronto - tentando reiniciar...\n";
                
                // Tentar reiniciar via logout
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $vps_url . '/session/default/disconnect');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_POST, true);
                $logout_response = curl_exec($ch);
                $logout_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                echo "   🔄 Logout: HTTP $logout_http\n";
                
                // Aguardar 3 segundos e verificar novamente
                sleep(3);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response2 = curl_exec($ch);
                $http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code2 == 200) {
                    $data2 = json_decode($response2, true);
                    if ($data2 && $data2['ready']) {
                        echo "   ✅ Serviço reiniciado com sucesso!\n";
                    } else {
                        echo "   ❌ Serviço ainda não está pronto após reinicialização\n";
                    }
                }
            }
        } else {
            echo "   ⚠️ Resposta não é JSON válido: " . substr($response, 0, 100) . "...\n";
        }
    } else {
        echo "   ❌ VPS $porta não está respondendo (HTTP $http_code)\n";
        if ($curl_error) {
            echo "   🔍 Erro cURL: $curl_error\n";
        }
    }
    
    echo "\n";
}

echo "=== INSTRUÇÕES PARA O VPS ===\n";
echo "Se o serviço não está funcionando corretamente, execute no VPS:\n\n";

echo "1. 🔍 Verificar se o processo está rodando:\n";
echo "   ps aux | grep whatsapp-api-server\n\n";

echo "2. 🔄 Reiniciar o serviço:\n";
echo "   cd /var/whatsapp-api\n";
echo "   pkill -f whatsapp-api-server\n";
echo "   sleep 2\n";
echo "   node whatsapp-api-server.js &\n\n";

echo "3. 🧪 Testar após reiniciar:\n";
echo "   curl http://localhost:3000/status\n";
echo "   curl http://localhost:3001/status\n\n";

echo "4. 📋 Se ainda não funcionar, verificar logs:\n";
echo "   tail -f /var/whatsapp-api/monitoramento.log\n";
echo "   tail -f /var/whatsapp-api/logs/*.log\n\n";

echo "=== FIM DA VERIFICAÇÃO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 