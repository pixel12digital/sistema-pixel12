<?php
/**
 * Inicializar Sessões WhatsApp no VPS
 */

echo "=== INICIALIZAÇÃO DE SESSÕES WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

$sessions = ['default', 'comercial'];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- VPS PORTA $porta ($vps_url) ---\n";
    
    foreach ($sessions as $session) {
        echo "   📱 Tentando inicializar sessão: $session\n";
        
        // Tentar diferentes endpoints de inicialização
        $init_endpoints = [
            "/session/$session/init",
            "/session/$session/start", 
            "/session/$session/create",
            "/init?session=$session",
            "/start?session=$session",
            "/create?session=$session"
        ];
        
        $session_initialized = false;
        
        foreach ($init_endpoints as $endpoint) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo "      ✅ Endpoint $endpoint funcionou (HTTP $http_code)\n";
                $session_initialized = true;
                break;
            } else {
                echo "      ❌ Endpoint $endpoint falhou (HTTP $http_code)\n";
            }
        }
        
        if ($session_initialized) {
            echo "      🎉 Sessão $session inicializada com sucesso!\n";
            
            // Aguardar 3 segundos e verificar QR
            sleep(3);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . "/qr?session=$session");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $qr_response = curl_exec($ch);
            $qr_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($qr_http == 200) {
                $qr_data = json_decode($qr_response, true);
                if ($qr_data && !empty($qr_data['qr'])) {
                    echo "      📱 QR Code disponível para sessão $session!\n";
                } else {
                    echo "      ⚠️ QR Code não disponível ainda para sessão $session\n";
                }
            } else {
                echo "      ❌ Erro ao obter QR Code (HTTP $qr_http)\n";
            }
        } else {
            echo "      ❌ Falha ao inicializar sessão $session\n";
        }
        
        echo "\n";
    }
}

echo "=== INSTRUÇÕES MANUAIS PARA O VPS ===\n";
echo "Se a inicialização automática não funcionou, execute no VPS:\n\n";

echo "1. 🔍 Verificar se o serviço está rodando:\n";
echo "   ps aux | grep whatsapp-api-server\n\n";

echo "2. 🔄 Reiniciar o serviço completamente:\n";
echo "   cd /var/whatsapp-api\n";
echo "   pkill -f whatsapp-api-server\n";
echo "   sleep 3\n";
echo "   node whatsapp-api-server.js &\n\n";

echo "3. 🧪 Testar endpoints manualmente:\n";
echo "   curl -X POST http://localhost:3000/session/default/init\n";
echo "   curl -X POST http://localhost:3001/session/comercial/init\n\n";

echo "4. 📱 Verificar QR Code:\n";
echo "   curl http://localhost:3000/qr?session=default\n";
echo "   curl http://localhost:3001/qr?session=comercial\n\n";

echo "5. 📋 Verificar logs em tempo real:\n";
echo "   tail -f /var/whatsapp-api/monitoramento.log\n\n";

echo "=== RESULTADO ESPERADO ===\n";
echo "Após inicializar corretamente, você deve ver:\n";
echo "   {\n";
echo "     \"success\": true,\n";
echo "     \"ready\": true,\n";
echo "     \"clients_status\": {\n";
echo "       \"default\": {\"status\": \"disconnected\"},\n";
echo "       \"comercial\": {\"status\": \"disconnected\"}\n";
echo "     }\n";
echo "   }\n\n";

echo "=== FIM DA INICIALIZAÇÃO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 