<?php
/**
 * Reconectar sessões WhatsApp no VPS
 */

echo "=== RECONECTANDO WHATSAPP NO VPS ===\n\n";

$vps_urls = [
    'Canal 3000 (Ana)' => 'http://212.85.11.238:3000',
    'Canal 3001 (Humano)' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $nome => $vps_url) {
    echo "=== $nome ===\n";
    
    // 1. Verificar sessões existentes
    echo "1. VERIFICANDO SESSÕES:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $sessions = json_decode($response, true);
        echo "   Sessões encontradas: " . count($sessions) . "\n";
        
        foreach ($sessions as $session) {
            $name = isset($session['name']) ? $session['name'] : 'unnamed';
            $status = isset($session['status']) ? $session['status'] : 'unknown';
            echo "   - $name: $status\n";
            
            // Se status não é "connected", tentar reconectar
            if ($status !== 'connected') {
                echo "   ⚠️ Sessão $name não conectada, tentando reconectar...\n";
                
                // Verificar QR Code
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $vps_url . '/qr?session=' . urlencode($name));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $qr_response = curl_exec($ch);
                $qr_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($qr_code == 200) {
                    $qr_data = json_decode($qr_response, true);
                    if (isset($qr_data['qr'])) {
                        echo "   📱 QR Code disponível. Escaneie pelo WhatsApp:\n";
                        echo "   URL QR: $vps_url/qr?session=" . urlencode($name) . "\n";
                    } else {
                        echo "   ℹ️ QR Code não disponível: " . ($qr_data['message'] ?? 'Status indefinido') . "\n";
                    }
                }
            }
        }
    } else {
        echo "   ❌ Erro ao verificar sessões\n";
    }
    
    // 2. Tentar iniciar sessão padrão se não existir
    echo "\n2. INICIANDO/VERIFICANDO SESSÃO PADRÃO:\n";
    $session_name = ($nome === 'Canal 3000 (Ana)') ? 'default' : 'comercial';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/session/start');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['sessionName' => $session_name]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        echo "   ✅ Sessão $session_name: " . ($data['message'] ?? 'Iniciada') . "\n";
        
        // Verificar se precisa de QR
        if (isset($data['qr']) || strpos($response, 'qr') !== false) {
            echo "   📱 QR Code necessário para $session_name\n";
            echo "   Acesse: $vps_url/qr?session=$session_name\n";
        }
    } else {
        echo "   ❌ Erro ao iniciar sessão $session_name (HTTP $http_code)\n";
        echo "   Resposta: $response\n";
    }
    
    // 3. Verificar status final
    echo "\n3. STATUS FINAL:\n";
    sleep(2); // Aguardar um pouco
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $sessions = json_decode($response, true);
    foreach ($sessions as $session) {
        $name = $session['name'] ?? 'unnamed';
        $status = $session['status'] ?? 'unknown';
        
        if ($status === 'connected') {
            echo "   ✅ $name: CONECTADO\n";
        } else {
            echo "   ❌ $name: $status\n";
            echo "      → Pode precisar escanear QR Code\n";
            echo "      → URL: $vps_url/qr?session=" . urlencode($name) . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "=== INSTRUÇÕES FINAIS ===\n";
echo "1. Se alguma sessão mostrar QR Code, acesse a URL indicada\n";
echo "2. Escaneie o QR Code com seu WhatsApp\n";
echo "3. Aguarde a conexão ser estabelecida\n";
echo "4. Teste enviando uma mensagem\n\n";

echo "🔗 URLs de QR Code:\n";
echo "   Canal 3000 (Ana): http://212.85.11.238:3000/qr?session=default\n";
echo "   Canal 3001 (Humano): http://212.85.11.238:3001/qr?session=comercial\n";
?> 