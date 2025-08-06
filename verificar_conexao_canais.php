<?php
/**
 * 🔍 VERIFICAR CONEXÃO DOS CANAIS WHATSAPP
 * 
 * Este script verifica se os canais estão realmente conectados
 */

echo "🔍 VERIFICAR CONEXÃO DOS CANAIS WHATSAPP\n";
echo "========================================\n\n";

$vps_ip = "45.79.199.138";
$portas = [3000, 3001];

echo "🎯 VERIFICANDO STATUS DOS CANAIS:\n";
echo "=================================\n\n";

foreach ($portas as $porta) {
    $vps_url = "http://$vps_ip:$porta";
    
    echo "📡 Verificando porta $porta...\n";
    echo "URL: $vps_url\n";
    
    // Testar status geral
    $ch = curl_init($vps_url . "/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $status_data = json_decode($response, true);
        echo "✅ Status HTTP: $http_code\n";
        echo "📊 Status: " . ($status_data['status'] ?? 'N/A') . "\n";
        echo "🔗 Ready: " . ($status_data['ready'] ? 'SIM' : 'NÃO') . "\n";
        
        // Verificar clientes
        if (isset($status_data['clients_status'])) {
            foreach ($status_data['clients_status'] as $session => $client) {
                echo "📱 Sessão: $session\n";
                echo "   - Ready: " . ($client['ready'] ? 'SIM' : 'NÃO') . "\n";
                echo "   - HasQR: " . ($client['hasQR'] ? 'SIM' : 'NÃO') . "\n";
                echo "   - QR: " . (isset($client['qr']) ? 'DISPONÍVEL' : 'NÃO DISPONÍVEL') . "\n";
            }
        }
    } else {
        echo "❌ Erro HTTP: $http_code\n";
        echo "📄 Resposta: $response\n";
    }
    
    echo "\n";
}

echo "🔧 VERIFICANDO ENDPOINTS ESPECÍFICOS:\n";
echo "=====================================\n\n";

// Verificar endpoint de sessões
foreach ($portas as $porta) {
    $vps_url = "http://$vps_ip:$porta";
    
    echo "📡 Verificando sessões na porta $porta...\n";
    
    $ch = curl_init($vps_url . "/sessions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $sessions_data = json_decode($response, true);
        echo "✅ Sessões encontradas: " . count($sessions_data) . "\n";
        foreach ($sessions_data as $session) {
            echo "   - " . $session['name'] . " (" . ($session['ready'] ? 'Conectado' : 'Desconectado') . ")\n";
        }
    } else {
        echo "❌ Erro ao verificar sessões: $http_code\n";
    }
    
    echo "\n";
}

echo "🎯 VERIFICANDO QR CODES:\n";
echo "=======================\n\n";

// Verificar QR codes
$sessions = ['default', 'comercial'];
foreach ($portas as $porta) {
    $vps_url = "http://$vps_ip:$porta";
    
    foreach ($sessions as $session) {
        echo "📡 Verificando QR para sessão '$session' na porta $porta...\n";
        
        $ch = curl_init($vps_url . "/qr?session=$session");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $qr_data = json_decode($response, true);
            if (isset($qr_data['success']) && $qr_data['success']) {
                echo "✅ QR Code disponível para sessão '$session'\n";
                echo "   - QR: " . substr($qr_data['qr'], 0, 50) . "...\n";
            } else {
                echo "⚠️ QR Code não disponível para sessão '$session'\n";
                echo "   - Mensagem: " . ($qr_data['message'] ?? 'N/A') . "\n";
            }
        } else {
            echo "❌ Erro ao verificar QR: $http_code\n";
        }
    }
    
    echo "\n";
}

echo "🔍 VERIFICANDO WEBHOOK:\n";
echo "=======================\n\n";

// Verificar webhook
$webhook_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";
echo "📡 Verificando webhook: $webhook_url\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "✅ Webhook acessível\n";
    echo "📄 Resposta: " . substr($response, 0, 100) . "...\n";
} else {
    echo "❌ Erro no webhook: $http_code\n";
    echo "📄 Resposta: $response\n";
}

echo "\n🎯 DIAGNÓSTICO FINAL:\n";
echo "=====================\n\n";

echo "1️⃣ Verificar se os serviços estão rodando na VPS:\n";
echo "   ssh root@$vps_ip\n";
echo "   cd /var/whatsapp-api\n";
echo "   pm2 status\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n\n";

echo "2️⃣ Verificar se as portas estão acessíveis:\n";
echo "   netstat -tlnp | grep :3000\n";
echo "   netstat -tlnp | grep :3001\n\n";

echo "3️⃣ Testar conectividade local:\n";
echo "   curl -s http://127.0.0.1:3000/status | jq .\n";
echo "   curl -s http://127.0.0.1:3001/status | jq .\n\n";

echo "4️⃣ Verificar se há problemas de firewall:\n";
echo "   iptables -L | grep 3000\n";
echo "   iptables -L | grep 3001\n\n";

echo "5️⃣ Verificar logs de erro:\n";
echo "   tail -f /root/.pm2/logs/whatsapp-3000-error.log\n";
echo "   tail -f /root/.pm2/logs/whatsapp-3001-error.log\n\n";

echo "🔧 POSSÍVEIS SOLUÇÕES:\n";
echo "=====================\n\n";

echo "1️⃣ Se os serviços estão rodando mas não conectam:\n";
echo "   - Verificar se os QR Codes foram escaneados\n";
echo "   - Verificar se as sessões estão ativas\n";
echo "   - Reiniciar os serviços se necessário\n\n";

echo "2️⃣ Se há problemas de rede:\n";
echo "   - Verificar firewall\n";
echo "   - Verificar se as portas estão abertas\n";
echo "   - Testar conectividade externa\n\n";

echo "3️⃣ Se há problemas de autenticação:\n";
echo "   - Verificar se as sessões estão válidas\n";
echo "   - Limpar cache e sessões antigas\n";
echo "   - Regerar QR Codes\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos de diagnóstico na VPS para identificar o problema!\n";
?> 