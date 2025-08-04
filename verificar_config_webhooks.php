<?php
echo "🔧 VERIFICAÇÃO CONFIGURAÇÃO WEBHOOKS\n";
echo "====================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "🎯 NÚMEROS CORRETOS:\n";
echo "====================\n";
echo "🤖 Canal 3000 (Ana): 554797146908\n";
echo "👥 Canal 3001 (Humano): 554797309525\n";
echo "📱 Seu WhatsApp: 554796164699\n\n";

// Verificar webhooks configurados
echo "📡 WEBHOOKS CONFIGURADOS:\n";
echo "=========================\n";

$canais = [3000, 3001];
foreach ($canais as $porta) {
    echo "🔍 Canal $porta:\n";
    
    $webhook_ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 5);
    $webhook_response = curl_exec($webhook_ch);
    $webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
    curl_close($webhook_ch);
    
    if ($webhook_code === 200) {
        $webhook_data = json_decode($webhook_response, true);
        if ($webhook_data && isset($webhook_data['webhook_url'])) {
            if ($webhook_data['webhook_url'] === $webhook_url) {
                echo "  ✅ Webhook: CORRETO ($webhook_url)\n";
            } else {
                echo "  ⚠️ Webhook: DIFERENTE ({$webhook_data['webhook_url']})\n";
            }
        } else {
            echo "  ❌ Webhook: NÃO CONFIGURADO\n";
        }
    } else {
        echo "  ❌ Webhook: ERRO (HTTP $webhook_code)\n";
    }
    
    // Verificar status
    $status_ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($status_ch, CURLOPT_TIMEOUT, 5);
    $status_response = curl_exec($status_ch);
    $status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
    curl_close($status_ch);
    
    if ($status_code === 200) {
        $status_data = json_decode($status_response, true);
        if ($status_data && isset($status_data['ready']) && $status_data['ready']) {
            echo "  ✅ WhatsApp: CONECTADO\n";
        } else {
            echo "  ❌ WhatsApp: DESCONECTADO\n";
        }
    }
    echo "\n";
}

// Testar envio direto para canal Ana
echo "🧪 TESTE DIRETO CANAL ANA:\n";
echo "==========================\n";

require_once 'config.php';
require_once 'painel/db.php';

// Simular mensagem chegando DO seu número PARA o canal Ana
$teste_ana = json_encode([
    'from' => '554796164699@c.us',  // SEU número
    'body' => '🧪 TESTE CANAL ANA - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text',
    'to' => '554797146908@c.us'     // Canal Ana
]);

$teste_ch = curl_init($webhook_url);
curl_setopt($teste_ch, CURLOPT_POST, true);
curl_setopt($teste_ch, CURLOPT_POSTFIELDS, $teste_ana);
curl_setopt($teste_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($teste_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($teste_ch, CURLOPT_TIMEOUT, 15);
curl_setopt($teste_ch, CURLOPT_SSL_VERIFYPEER, false);

$teste_response = curl_exec($teste_ch);
$teste_code = curl_getinfo($teste_ch, CURLINFO_HTTP_CODE);
curl_close($teste_ch);

echo "Status: HTTP $teste_code\n";
if ($teste_code === 200) {
    echo "✅ Webhook respondeu\n";
    $response_data = json_decode($teste_response, true);
    if ($response_data && isset($response_data['success']) && $response_data['success']) {
        echo "✅ Ana processou mensagem\n";
    }
} else {
    echo "❌ Webhook falhou\n";
}

// Verificar se mensagem foi salva no canal Ana
sleep(2);
$nova_msg_ana = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '554797146908' 
    AND mensagem LIKE '%TESTE CANAL ANA%'
    ORDER BY id DESC LIMIT 1
")->fetch_assoc();

if ($nova_msg_ana) {
    echo "✅ Mensagem salva no Canal Ana (ID: {$nova_msg_ana['id']})\n";
} else {
    echo "❌ Mensagem NÃO foi salva no Canal Ana\n";
    
    // Verificar se foi salva no seu número
    $msg_seu_numero = $mysqli->query("
        SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp = '554796164699' 
        AND mensagem LIKE '%TESTE CANAL ANA%'
        ORDER BY id DESC LIMIT 1
    ")->fetch_assoc();
    
    if ($msg_seu_numero) {
        echo "⚠️ Mensagem foi salva no SEU número - PROBLEMA DE CONFIGURAÇÃO!\n";
    }
}

echo "\n💡 SOLUÇÃO:\n";
echo "===========\n";
echo "Se o teste acima falhou, o problema está na lógica do webhook.\n";
echo "O webhook deve identificar corretamente:\n";
echo "- REMETENTE: Quem enviou (seu número: 554796164699)\n";
echo "- DESTINATÁRIO: Para qual canal (Ana: 554797146908 ou Humano: 554797309525)\n";
echo "\nE salvar as mensagens no número do DESTINATÁRIO, não do remetente.\n";

?> 