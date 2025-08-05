<?php
/**
 * 🚀 SCRIPT PARA INICIAR SESSÕES DOS CANAIS
 * 
 * Este script inicia as sessões WhatsApp para os canais 3000 e 3001
 */

echo "🚀 INICIANDO SESSÕES DOS CANAIS WHATSAPP\n";
echo "=======================================\n\n";

$vps_ip = '212.85.11.238';
$canais = [
    ['porta' => 3000, 'sessao' => 'default'],
    ['porta' => 3001, 'sessao' => 'comercial']
];

foreach ($canais as $canal) {
    $porta = $canal['porta'];
    $sessao = $canal['sessao'];
    
    echo "🔄 CANAL $porta (sessão: $sessao)\n";
    echo "----------------------------\n";
    
    // 1. Iniciar sessão
    echo "1. Iniciando sessão...\n";
    $url = "http://$vps_ip:$porta/session/start/$sessao";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Sessão iniciada com sucesso!\n";
        echo "   Resposta: $response\n";
    } else {
        echo "   ❌ Erro ao iniciar sessão (código: $http_code)\n";
        echo "   Resposta: $response\n";
    }
    
    // 2. Aguardar alguns segundos
    echo "2. Aguardando 3 segundos...\n";
    sleep(3);
    
    // 3. Verificar status
    echo "3. Verificando status...\n";
    $status_url = "http://$vps_ip:$porta/status";
    
    $ch = curl_init($status_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $status_response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status_code === 200) {
        $status_data = json_decode($status_response, true);
        echo "   📊 Status: " . ($status_data['status'] ?? 'N/A') . "\n";
        echo "   📱 QR disponível: " . ($status_data['hasQR'] ? 'SIM' : 'NÃO') . "\n";
    }
    
    // 4. Tentar obter QR Code
    echo "4. Obtendo QR Code...\n";
    $qr_url = "http://$vps_ip:$porta/qr";
    
    $ch = curl_init($qr_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $qr_response = curl_exec($ch);
    $qr_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($qr_code === 200) {
        $qr_data = json_decode($qr_response, true);
        if (isset($qr_data['qr'])) {
            echo "   ✅ QR Code obtido com sucesso!\n";
            echo "   📋 QR Code: " . substr($qr_data['qr'], 0, 50) . "...\n";
        } else {
            echo "   ⚠️ QR Code não disponível ainda\n";
            echo "   📝 Resposta: $qr_response\n";
        }
    } else {
        echo "   ❌ Erro ao obter QR Code\n";
        echo "   📝 Resposta: $qr_response\n";
    }
    
    echo "\n";
}

echo "🎯 RESULTADO FINAL:\n";
echo "==================\n";
echo "✅ Sessões iniciadas para ambos os canais\n";
echo "📱 Agora clique em 'Atualizar QR' ou 'Forçar Novo QR' no painel\n";
echo "🔄 Se ainda não aparecer, aguarde 30 segundos e tente novamente\n\n";

echo "🔗 URLS PARA TESTE:\n";
echo "- Canal 3000 QR: http://$vps_ip:3000/qr\n";
echo "- Canal 3001 QR: http://$vps_ip:3001/qr\n";
?> 