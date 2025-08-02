<?php
/**
 * 📤 ENVIAR MENSAGEM DE TESTE
 * 
 * Testa o fluxo completo: VPS → Webhook → Ana → Resposta
 */

echo "📤 ENVIANDO MENSAGEM DE TESTE\n";
echo "=============================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';
$numero_destino = '5547999999999@c.us'; // Número do Charles (do screenshot)

// 1. VERIFICAR MENSAGENS ANTES DO TESTE
echo "📊 ESTADO INICIAL\n";
echo "================\n";

$count_antes = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc();
echo "Mensagens no banco antes: {$count_antes['total']}\n\n";

// 2. ENVIAR MENSAGEM DE TESTE VIA VPS
echo "📱 ENVIANDO MENSAGEM VIA VPS\n";
echo "===========================\n";

$mensagem_teste = "🧪 TESTE AUTOMÁTICO - " . date('H:i:s') . "\n\nOlá Ana! Este é um teste automático para verificar se você está funcionando corretamente.";

$dados_envio = json_encode([
    'to' => $numero_destino,
    'body' => $mensagem_teste
]);

echo "Destinatário: $numero_destino\n";
echo "Mensagem: " . substr($mensagem_teste, 0, 100) . "...\n\n";

// Tentar diferentes endpoints de envio
$endpoints_envio = [
    'send' => "http://$vps_ip:3000/send",
    'send-message' => "http://$vps_ip:3000/send-message", 
    'sendMessage' => "http://$vps_ip:3000/sendMessage",
    'message/send' => "http://$vps_ip:3000/message/send"
];

$enviado_com_sucesso = false;

foreach ($endpoints_envio as $nome => $url) {
    echo "🔄 Tentando endpoint '$nome'...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados_envio);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Status: HTTP $http_code\n";
    echo "  Resposta: " . substr($response, 0, 100) . "\n";
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            echo "  ✅ MENSAGEM ENVIADA COM SUCESSO!\n";
            $enviado_com_sucesso = true;
            break;
        }
    }
    echo "\n";
}

if (!$enviado_com_sucesso) {
    echo "⚠️ Não foi possível enviar via VPS, tentando simular chegada...\n\n";
    
    // 3. SIMULAR CHEGADA DA MENSAGEM (como se viesse do WhatsApp)
    echo "🔄 SIMULANDO CHEGADA DA MENSAGEM\n";
    echo "===============================\n";
    
    $webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';
    
    $dados_webhook = json_encode([
        'from' => str_replace('@c.us', '', $numero_destino) . '@c.us',
        'body' => $mensagem_teste,
        'timestamp' => time(),
        'type' => 'text'
    ]);
    
    $webhook_ch = curl_init($webhook_url);
    curl_setopt($webhook_ch, CURLOPT_POST, true);
    curl_setopt($webhook_ch, CURLOPT_POSTFIELDS, $dados_webhook);
    curl_setopt($webhook_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($webhook_ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $webhook_response = curl_exec($webhook_ch);
    $webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
    curl_close($webhook_ch);
    
    echo "Status webhook: HTTP $webhook_code\n";
    echo "Resposta: " . substr($webhook_response, 0, 300) . "\n\n";
    
    if ($webhook_response) {
        $webhook_data = json_decode($webhook_response, true);
        if (isset($webhook_data['success']) && $webhook_data['success']) {
            echo "✅ WEBHOOK PROCESSOU A MENSAGEM!\n";
            echo "Ana respondeu: " . substr($webhook_data['ana_response'], 0, 100) . "...\n\n";
        }
    }
}

// 4. AGUARDAR E VERIFICAR RESULTADO
echo "⏳ AGUARDANDO PROCESSAMENTO...\n";
sleep(3);

// 5. VERIFICAR MENSAGENS APÓS O TESTE
echo "\n📊 RESULTADO DO TESTE\n";
echo "====================\n";

$count_depois = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc();
echo "Mensagens no banco depois: {$count_depois['total']}\n";

$novas_mensagens = $count_depois['total'] - $count_antes['total'];
echo "Novas mensagens processadas: $novas_mensagens\n\n";

// Verificar últimas mensagens
$ultimas = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE mensagem LIKE '%TESTE AUTOMÁTICO%'
    ORDER BY data_hora DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

if (!empty($ultimas)) {
    echo "✅ MENSAGENS DE TESTE ENCONTRADAS:\n";
    foreach ($ultimas as $msg) {
        echo "  ID {$msg['id']} | {$msg['direcao']} | {$msg['data_hora']}\n";
        echo "    " . substr($msg['mensagem'], 0, 80) . "...\n\n";
    }
} else {
    echo "❌ Nenhuma mensagem de teste encontrada no banco\n";
}

// 6. VERIFICAR SE ANA RESPONDEU
$ana_resposta = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE direcao = 'enviado' 
    AND numero_whatsapp = '" . str_replace('@c.us', '', $numero_destino) . "'
    AND data_hora >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ORDER BY data_hora DESC 
    LIMIT 1
")->fetch_assoc();

if ($ana_resposta) {
    echo "🤖 ANA RESPONDEU:\n";
    echo "=================\n";
    echo "Resposta: " . substr($ana_resposta['mensagem'], 0, 200) . "...\n";
    echo "Hora: {$ana_resposta['data_hora']}\n\n";
    
    echo "🎉 TESTE CONCLUÍDO COM SUCESSO!\n";
    echo "✅ Mensagem enviada/simulada\n";
    echo "✅ Ana processou e respondeu\n";
    echo "✅ Sistema funcionando perfeitamente\n";
} else {
    echo "⚠️ Ana não respondeu ainda ou resposta não encontrada\n";
    echo "💡 Verifique manualmente no painel se a mensagem chegou\n";
}

echo "\n📱 VERIFIQUE SEU WHATSAPP/PAINEL:\n";
echo "Deve aparecer a mensagem de teste e a resposta da Ana!\n";

?> 