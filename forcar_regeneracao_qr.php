<?php
/**
 * 🔄 FORÇAR REGENERAÇÃO DE QR CODES
 */

echo "🔄 FORÇANDO REGENERAÇÃO DE QR CODES\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';
$canais = [
    ['porta' => 3000, 'sessao' => 'default'],
    ['porta' => 3001, 'sessao' => 'comercial']
];

foreach ($canais as $canal) {
    $porta = $canal['porta'];
    $sessao = $canal['sessao'];
    
    echo "🔄 CANAL $porta (sessão: $sessao)\n";
    echo "--------------------------------\n";
    
    // 1. Parar/destruir sessão atual
    echo "1. Parando sessão atual...\n";
    $endpoints_parar = [
        "/session/stop/$sessao",
        "/session/destroy/$sessao",
        "/logout/$sessao",
        "/disconnect/$sessao"
    ];
    
    foreach ($endpoints_parar as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "   ✅ $endpoint: Executado\n";
            break;
        }
    }
    
    // 2. Aguardar
    echo "2. Aguardando 3 segundos...\n";
    sleep(3);
    
    // 3. Iniciar nova sessão
    echo "3. Iniciando nova sessão...\n";
    $endpoints_iniciar = [
        "/session/start/$sessao",
        "/session/create/$sessao",
        "/start/$sessao",
        "/init/$sessao"
    ];
    
    foreach ($endpoints_iniciar as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   Endpoint: $endpoint\n";
        echo "   Código: $http_code\n";
        echo "   Resposta: $response\n";
        
        if ($http_code === 200) {
            echo "   ✅ Sessão iniciada!\n";
            break;
        }
    }
    
    // 4. Aguardar QR ser gerado
    echo "4. Aguardando 5 segundos para QR ser gerado...\n";
    sleep(5);
    
    // 5. Verificar status e QR
    echo "5. Verificando status final...\n";
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $status = json_decode($response, true);
    if (isset($status['clients_status'][$sessao])) {
        $dados = $status['clients_status'][$sessao];
        echo "   📊 Ready: " . ($dados['ready'] ? 'true' : 'false') . "\n";
        echo "   📊 HasQR: " . ($dados['hasQR'] ? 'true' : 'false') . "\n";
        
        if (isset($dados['qr'])) {
            echo "   ✅ QR DISPONÍVEL: " . substr($dados['qr'], 0, 30) . "...\n";
            
            // Salvar QR
            $arquivo = "qr_regenerado_canal_{$porta}.txt";
            file_put_contents($arquivo, $dados['qr']);
            echo "   💾 QR salvo em: $arquivo\n";
        } else {
            echo "   ❌ QR ainda não disponível\n";
        }
    }
    
    echo "\n";
}

echo "🎯 REGENERAÇÃO CONCLUÍDA!\n";
echo "========================\n";
echo "Agora teste novamente no painel de comunicação.\n";
?> 