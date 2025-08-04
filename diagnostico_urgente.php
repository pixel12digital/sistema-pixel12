<?php
echo "🚨 DIAGNÓSTICO URGENTE - AMBOS CANAIS FALHARAM\n";
echo "===============================================\n\n";

$vps_ip = '212.85.11.238';

// 1. VERIFICAR STATUS AMBOS CANAIS
echo "📡 1. STATUS VPS URGENTE:\n";
echo "=========================\n";

$canais = [3000, 3001];
foreach ($canais as $porta) {
    echo "🔄 Verificando Canal $porta...\n";
    
    // Status geral
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ VPS Canal $porta: ONLINE\n";
        $data = json_decode($response, true);
        if ($data && isset($data['ready']) && $data['ready']) {
            echo "  ✅ WhatsApp: CONECTADO\n";
        } else {
            echo "  ❌ WhatsApp: DESCONECTADO\n";
        }
    } else {
        echo "  ❌ VPS Canal $porta: OFFLINE (HTTP $http_code)\n";
    }
    
    // Verificar webhook
    $webhook_ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 5);
    $webhook_response = curl_exec($webhook_ch);
    $webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
    curl_close($webhook_ch);
    
    if ($webhook_code === 200) {
        $webhook_data = json_decode($webhook_response, true);
        if ($webhook_data && isset($webhook_data['webhook_url'])) {
            echo "  ✅ Webhook: CONFIGURADO\n";
        } else {
            echo "  ❌ Webhook: PERDIDO\n";
        }
    } else {
        echo "  ❌ Webhook: ERRO (HTTP $webhook_code)\n";
    }
    
    // Verificar QR Code
    $qr_ch = curl_init("http://$vps_ip:$porta/qr");
    curl_setopt($qr_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($qr_ch, CURLOPT_TIMEOUT, 5);
    $qr_response = curl_exec($qr_ch);
    $qr_code = curl_getinfo($qr_ch, CURLINFO_HTTP_CODE);
    curl_close($qr_ch);
    
    if ($qr_code === 200) {
        $qr_data = json_decode($qr_response, true);
        if ($qr_data && isset($qr_data['qr']) && $qr_data['qr']) {
            echo "  ⚠️ QR CODE NECESSÁRIO!\n";
        } else {
            echo "  ✅ QR Code: OK\n";
        }
    }
    
    echo "\n";
}

// 2. TESTAR WEBHOOK DIRETAMENTE
echo "🧪 2. TESTE WEBHOOK DIRETO:\n";
echo "===========================\n";

require_once 'config.php';
require_once 'painel/db.php';

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

$teste_dados = json_encode([
    'from' => '5547999999999@c.us',
    'body' => '🚨 TESTE URGENTE - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

$teste_ch = curl_init($webhook_url);
curl_setopt($teste_ch, CURLOPT_POST, true);
curl_setopt($teste_ch, CURLOPT_POSTFIELDS, $teste_dados);
curl_setopt($teste_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($teste_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($teste_ch, CURLOPT_TIMEOUT, 15);
curl_setopt($teste_ch, CURLOPT_SSL_VERIFYPEER, false);

$teste_response = curl_exec($teste_ch);
$teste_code = curl_getinfo($teste_ch, CURLINFO_HTTP_CODE);
curl_close($teste_ch);

echo "Status: HTTP $teste_code\n";
if ($teste_code === 200) {
    echo "✅ Nosso webhook: FUNCIONANDO\n";
} else {
    echo "❌ Nosso webhook: FALHA\n";
    echo "Erro: $teste_response\n";
}

// 3. VERIFICAR ÚLTIMAS MENSAGENS
sleep(2);
$ultima_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao ORDER BY id DESC LIMIT 1")->fetch_assoc();
if ($ultima_msg) {
    echo "\n📊 ÚLTIMA MENSAGEM NO BANCO:\n";
    echo "============================\n";
    echo "ID: {$ultima_msg['id']}\n";
    echo "Data: {$ultima_msg['data_hora']}\n";
    echo "Número: {$ultima_msg['numero_whatsapp']}\n";
    echo "Direção: {$ultima_msg['direcao']}\n";
    echo "Mensagem: " . substr($ultima_msg['mensagem'], 0, 100) . "...\n";
}

// 4. DIAGNÓSTICO E SOLUÇÃO
echo "\n🎯 DIAGNÓSTICO:\n";
echo "===============\n";

$problemas_criticos = [];

// Verificar se algum canal está completamente fora
$canal_3000_ok = false;
$canal_3001_ok = false;

$status_3000 = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_3000, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_3000, CURLOPT_TIMEOUT, 5);
$resp_3000 = curl_exec($status_3000);
$code_3000 = curl_getinfo($status_3000, CURLINFO_HTTP_CODE);
curl_close($status_3000);

$status_3001 = curl_init("http://$vps_ip:3001/status");
curl_setopt($status_3001, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_3001, CURLOPT_TIMEOUT, 5);
$resp_3001 = curl_exec($status_3001);
$code_3001 = curl_getinfo($status_3001, CURLINFO_HTTP_CODE);
curl_close($status_3001);

if ($code_3000 !== 200) $problemas_criticos[] = "Canal 3000 OFFLINE";
if ($code_3001 !== 200) $problemas_criticos[] = "Canal 3001 OFFLINE";

if (empty($problemas_criticos)) {
    echo "⚠️ PROBLEMA: WhatsApp desconectado ou webhooks perdidos\n";
    echo "\n🔧 SOLUÇÃO URGENTE:\n";
    echo "===================\n";
    echo "1. Verificar QR Code em: http://$vps_ip:3000/qr\n";
    echo "2. Verificar QR Code em: http://$vps_ip:3001/qr\n";
    echo "3. Reconfigurar webhooks via SSH\n";
} else {
    echo "🚨 PROBLEMA CRÍTICO:\n";
    foreach ($problemas_criticos as $problema) {
        echo "❌ $problema\n";
    }
    echo "\n🆘 SOLUÇÃO DE EMERGÊNCIA:\n";
    echo "=========================\n";
    echo "ACESSE SSH IMEDIATAMENTE:\n";
    echo "ssh root@$vps_ip\n";
    echo "pm2 restart all\n";
    echo "pm2 logs --lines 20\n";
}

?> 