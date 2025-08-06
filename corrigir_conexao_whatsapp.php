<?php
/**
 * 🔧 CORREÇÃO DA CONEXÃO WHATSAPP
 * 
 * Este script corrige problemas de conexão do WhatsApp
 */

echo "🔧 CORREÇÃO DA CONEXÃO WHATSAPP\n";
echo "===============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

echo "🎯 PROBLEMAS IDENTIFICADOS:\n";
echo "==========================\n";
echo "1. ✅ VPS 3000: running mas ready: false\n";
echo "2. ✅ VPS 3001: running e ready: true\n";
echo "3. ❌ QR Codes não estão disponíveis\n";
echo "4. ❌ Sessões não estão sendo inicializadas corretamente\n\n";

echo "🔧 APLICANDO CORREÇÕES:\n";
echo "======================\n\n";

foreach ($portas as $porta) {
    echo "🔍 CORRIGINDO PORTA $porta\n";
    echo "==========================\n";
    
    $vps_url = "http://$vps_ip:$porta";
    $session_name = ($porta == 3001) ? 'comercial' : 'default';
    
    // 1. Verificar se a sessão existe
    echo "1️⃣ Verificando sessão $session_name...\n";
    $ch = curl_init($vps_url . "/sessions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        $total_sessions = $data['total'] ?? 0;
        echo "   📊 Total de sessões: $total_sessions\n";
        
        if ($total_sessions == 0) {
            echo "   ❌ Nenhuma sessão ativa - iniciando...\n";
            
            // 2. Iniciar sessão
            echo "2️⃣ Iniciando sessão $session_name...\n";
            $ch = curl_init($vps_url . "/session/start");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['session' => $session_name]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                echo "   ✅ Sessão iniciada com sucesso!\n";
                echo "   📋 Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "   ❌ Erro ao iniciar sessão (HTTP: $http_code)\n";
                echo "   📋 Response: $response\n";
            }
        } else {
            echo "   ✅ Sessão já existe\n";
        }
    }
    
    // 3. Aguardar um pouco para a sessão inicializar
    echo "3️⃣ Aguardando inicialização...\n";
    sleep(3);
    
    // 4. Verificar QR Code
    echo "4️⃣ Verificando QR Code...\n";
    $ch = curl_init($vps_url . "/qr?session=$session_name");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if ($data['success'] && $data['qr']) {
            echo "   ✅ QR Code disponível!\n";
            echo "   📱 QR: " . substr($data['qr'], 0, 50) . "...\n";
        } else {
            echo "   ⏳ QR Code ainda não disponível\n";
            echo "   📋 Message: " . ($data['message'] ?? 'N/A') . "\n";
            
            // 5. Tentar forçar novo QR
            echo "5️⃣ Forçando novo QR Code...\n";
            $ch = curl_init($vps_url . "/qr?session=$session_name&force=true");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                if ($data['success'] && $data['qr']) {
                    echo "   ✅ Novo QR Code gerado!\n";
                    echo "   📱 QR: " . substr($data['qr'], 0, 50) . "...\n";
                } else {
                    echo "   ❌ Falha ao gerar novo QR Code\n";
                }
            } else {
                echo "   ❌ Erro ao forçar novo QR (HTTP: $http_code)\n";
            }
        }
    } else {
        echo "   ❌ Erro ao verificar QR (HTTP: $http_code)\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

// 6. Verificar status final
echo "6️⃣ VERIFICAÇÃO FINAL:\n";
echo "=====================\n";

foreach ($portas as $porta) {
    $vps_url = "http://$vps_ip:$porta";
    $session_name = ($porta == 3001) ? 'comercial' : 'default';
    
    echo "🔍 Status final porta $porta:\n";
    
    $ch = curl_init($vps_url . "/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        echo "   ✅ Status: " . ($data['status'] ?? 'N/A') . "\n";
        echo "   ✅ Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
        
        if (isset($data['clients_status'][$session_name])) {
            $status = $data['clients_status'][$session_name];
            echo "   📱 Sessão $session_name:\n";
            echo "      - Ready: " . ($status['ready'] ? 'SIM' : 'NÃO') . "\n";
            echo "      - HasQR: " . ($status['hasQR'] ? 'SIM' : 'NÃO') . "\n";
            echo "      - QR: " . ($status['qr'] ? 'DISPONÍVEL' : 'NÃO') . "\n";
        }
    }
    
    echo "\n";
}

echo "🎯 CORREÇÃO CONCLUÍDA!\n";
echo "=====================\n";
echo "📋 PRÓXIMOS PASSOS:\n";
echo "1. Verificar se os QR Codes apareceram\n";
echo "2. Escanear os QR Codes com o WhatsApp\n";
echo "3. Aguardar a conexão ser estabelecida\n";
echo "4. Testar envio de mensagens\n";

echo "\n🔧 SE OS PROBLEMAS PERSISTIREM:\n";
echo "==============================\n";
echo "1. Reiniciar os serviços na VPS:\n";
echo "   ssh root@212.85.11.238\n";
echo "   cd /var/whatsapp-api\n";
echo "   pm2 restart all\n";
echo "\n";
echo "2. Verificar logs:\n";
echo "   pm2 logs whatsapp-3000 --lines 50\n";
echo "   pm2 logs whatsapp-3001 --lines 50\n";
echo "\n";
echo "3. Forçar reinicialização completa:\n";
echo "   pm2 delete all\n";
echo "   pm2 start whatsapp-api-server.js --name whatsapp-3000 --env PORT=3000\n";
echo "   pm2 start whatsapp-api-server.js --name whatsapp-3001 --env PORT=3001\n";

echo "\n🎯 SCRIPT FINALIZADO!\n";
?> 