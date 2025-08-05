<?php
/**
 * DIAGNÓSTICO URGENTE VPS WHATSAPP
 * Identifica e tenta resolver problemas de conectividade
 */

echo "🚨 === DIAGNÓSTICO URGENTE VPS WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

$problemas_encontrados = [];
$solucoes_aplicadas = [];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- ANALISANDO VPS PORTA $porta ($vps_url) ---\n";
    
    // 1. Teste de conectividade básica
    echo "1. 🔍 Teste de conectividade básica...\n";
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
        $data = json_decode($response, true);
        
        if ($data) {
            $ready = $data['ready'] ?? false;
            $sessoes = array_keys($data['clients_status'] ?? []);
            
            echo "   📊 Ready: " . ($ready ? '✅ true' : '❌ false') . "\n";
            echo "   📱 Sessões ativas: " . (count($sessoes) > 0 ? implode(', ', $sessoes) : '❌ Nenhuma') . "\n";
            
            if (!$ready) {
                $problemas_encontrados[] = "VPS $porta: Serviço não está pronto (ready: false)";
            }
            if (empty($sessoes)) {
                $problemas_encontrados[] = "VPS $porta: Nenhuma sessão ativa";
            }
        }
    } else {
        echo "   ❌ VPS $porta não está respondendo (HTTP $http_code)\n";
        if ($curl_error) {
            echo "   🔍 Erro cURL: $curl_error\n";
        }
        $problemas_encontrados[] = "VPS $porta: Não responde (HTTP $http_code)";
        continue;
    }
    
    // 2. Teste de QR Code
    echo "\n2. 📱 Teste de QR Code...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/qr');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $qr_response = curl_exec($ch);
    $qr_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $qr_curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($qr_http_code == 200) {
        echo "   ✅ QR Code endpoint responde (HTTP $qr_http_code)\n";
        $qr_data = json_decode($qr_response, true);
        if ($qr_data && isset($qr_data['qr']) && !empty($qr_data['qr'])) {
            echo "   📱 QR Code disponível (tamanho: " . strlen($qr_data['qr']) . " chars)\n";
        } else {
            echo "   ❌ QR Code não disponível na resposta\n";
            $problemas_encontrados[] = "VPS $porta: QR Code não disponível";
        }
    } elseif ($qr_http_code == 0) {
        echo "   ⚠️ QR Code endpoint timeout (HTTP $qr_http_code)\n";
        if ($qr_curl_error) {
            echo "   🔍 Erro cURL: $qr_curl_error\n";
        }
        $problemas_encontrados[] = "VPS $porta: QR Code timeout";
    } else {
        echo "   ❌ QR Code endpoint erro (HTTP $qr_http_code)\n";
        $problemas_encontrados[] = "VPS $porta: QR Code erro (HTTP $qr_http_code)";
    }
    
    echo "\n";
}

// RESUMO DOS PROBLEMAS
echo "=== RESUMO DOS PROBLEMAS ENCONTRADOS ===\n";
if (empty($problemas_encontrados)) {
    echo "✅ Nenhum problema encontrado! VPS funcionando corretamente.\n";
} else {
    echo "❌ Problemas encontrados:\n";
    foreach ($problemas_encontrados as $problema) {
        echo "   • $problema\n";
    }
}

// SOLUÇÕES RECOMENDADAS
echo "\n=== SOLUÇÕES RECOMENDADAS ===\n";

if (!empty($problemas_encontrados)) {
    echo "🔧 Para resolver os problemas, execute no VPS (212.85.11.238):\n\n";
    
    echo "1. 🔍 Verificar processos:\n";
    echo "   ssh root@212.85.11.238\n";
    echo "   ps aux | grep -i whatsapp\n";
    echo "   ps aux | grep -i node\n";
    echo "   netstat -tlnp | grep :300\n\n";
    
    echo "2. 🔄 Reiniciar serviço:\n";
    echo "   # Se estiver usando PM2:\n";
    echo "   pm2 restart whatsapp-multi-session\n";
    echo "   pm2 save\n\n";
    
    echo "   # Se estiver usando systemd:\n";
    echo "   systemctl restart whatsapp-multi-session\n";
    echo "   systemctl status whatsapp-multi-session\n\n";
    
    echo "3. 🔧 Iniciar manualmente (se necessário):\n";
    echo "   cd /var/whatsapp-api\n";
    echo "   npm start\n";
    echo "   # ou\n";
    echo "   node index.js\n\n";
    
    echo "4. 📊 Verificar recursos:\n";
    echo "   top\n";
    echo "   free -h\n";
    echo "   df -h\n\n";
    
    echo "5. ✅ Testar após correção:\n";
    echo "   curl http://localhost:3000/status\n";
    echo "   curl http://localhost:3001/status\n";
    echo "   curl http://212.85.11.238:3000/status\n";
    echo "   curl http://212.85.11.238:3001/status\n\n";
}

// TESTE AUTOMÁTICO DE CORREÇÃO
echo "=== TESTE AUTOMÁTICO DE CORREÇÃO ===\n";
echo "Tentando reinicializar sessões...\n\n";

foreach ($vps_urls as $porta => $vps_url) {
    $session_name = ($porta == '3000') ? 'default' : 'comercial';
    
    echo "🔄 Tentando inicializar sessão '$session_name' na porta $porta...\n";
    
    // Tentar inicializar sessão
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/init');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['sessionName' => $session_name]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $init_response = curl_exec($ch);
    $init_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($init_http_code == 200) {
        echo "   ✅ Sessão '$session_name' inicializada com sucesso!\n";
        $solucoes_aplicadas[] = "Sessão $session_name inicializada";
    } else {
        echo "   ❌ Falha ao inicializar sessão '$session_name' (HTTP $init_http_code)\n";
    }
    
    // Aguardar um pouco
    sleep(2);
}

// VERIFICAÇÃO FINAL
echo "\n=== VERIFICAÇÃO FINAL ===\n";
echo "Verificando se os problemas foram resolvidos...\n\n";

foreach ($vps_urls as $porta => $vps_url) {
    echo "🔍 Verificando VPS $porta...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        $ready = $data['ready'] ?? false;
        $sessoes = array_keys($data['clients_status'] ?? []);
        
        echo "   📊 Ready: " . ($ready ? '✅ true' : '❌ false') . "\n";
        echo "   📱 Sessões: " . (count($sessoes) > 0 ? implode(', ', $sessoes) : '❌ Nenhuma') . "\n";
        
        if ($ready && !empty($sessoes)) {
            echo "   ✅ VPS $porta funcionando corretamente!\n";
        } else {
            echo "   ⚠️ VPS $porta ainda com problemas\n";
        }
    } else {
        echo "   ❌ VPS $porta não responde\n";
    }
}

echo "\n=== INSTRUÇÕES PARA O USUÁRIO ===\n";
echo "1. Execute os comandos SSH listados acima no VPS\n";
echo "2. Reinicie o serviço WhatsApp\n";
echo "3. Teste novamente no painel\n";
echo "4. Se o problema persistir, verifique recursos do VPS\n\n";

echo "📞 Para suporte adicional, verifique:\n";
echo "   • Logs do VPS: journalctl -u whatsapp-multi-session -f\n";
echo "   • Recursos: top, free -h\n";
echo "   • Conectividade: telnet 212.85.11.238 3000\n\n";

echo "✅ Diagnóstico concluído em " . date('H:i:s') . "\n";
?> 