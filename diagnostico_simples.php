<?php
echo "🔍 DIAGNÓSTICO SIMPLES - WHATSAPP REAL\n";
echo "======================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';

// 1. VERIFICAR CANAIS
echo "📊 1. CANAIS NO BANCO:\n";
echo "=====================\n";

$canais = $mysqli->query("SELECT * FROM canais_comunicacao")->fetch_all(MYSQLI_ASSOC);
foreach ($canais as $canal) {
    echo "📱 {$canal['nome_exibicao']} (Porta: {$canal['porta']})\n";
}
echo "\n";

// 2. VERIFICAR WEBHOOK VPS
echo "🔧 2. WEBHOOK VPS CANAL 3000:\n";
echo "=============================\n";

$webhook_ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 5);
$webhook_response = curl_exec($webhook_ch);
$webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
curl_close($webhook_ch);

if ($webhook_code === 200) {
    $webhook_data = json_decode($webhook_response, true);
    if ($webhook_data && isset($webhook_data['webhook_url'])) {
        echo "✅ Webhook configurado: {$webhook_data['webhook_url']}\n";
    } else {
        echo "❌ Webhook NÃO configurado\n";
    }
} else {
    echo "❌ Erro ao verificar webhook (HTTP $webhook_code)\n";
}
echo "\n";

// 3. VERIFICAR STATUS WHATSAPP
echo "📱 3. STATUS WHATSAPP VPS:\n";
echo "=========================\n";

$status_ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_ch, CURLOPT_TIMEOUT, 5);
$status_response = curl_exec($status_ch);
$status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
curl_close($status_ch);

if ($status_code === 200) {
    echo "✅ VPS Canal 3000: ATIVO\n";
    $status_data = json_decode($status_response, true);
    if ($status_data && isset($status_data['ready']) && $status_data['ready']) {
        echo "✅ WhatsApp: CONECTADO\n";
    } else {
        echo "⚠️ WhatsApp: Status desconhecido\n";
    }
} else {
    echo "❌ VPS Canal 3000: FALHA\n";
}
echo "\n";

// 4. CONTAR MENSAGENS
echo "📊 4. RESUMO MENSAGENS:\n";
echo "======================\n";

$total_hoje = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];
echo "Total mensagens hoje: $total_hoje\n";

$total_teste = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '5547999999999'")->fetch_assoc()['total'];
echo "Mensagens de teste: $total_teste\n";

$outros_numeros = $mysqli->query("
    SELECT DISTINCT numero_whatsapp, COUNT(*) as total 
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp != '5547999999999'
    GROUP BY numero_whatsapp
")->fetch_all(MYSQLI_ASSOC);

echo "Outros números: " . count($outros_numeros) . "\n";
foreach ($outros_numeros as $num) {
    echo "  📞 {$num['numero_whatsapp']}: {$num['total']} mensagens\n";
}
echo "\n";

// 5. VERIFICAR QR CODE
echo "🔍 5. VERIFICAR QR CODE:\n";
echo "=======================\n";

$qr_ch = curl_init("http://$vps_ip:3000/qr");
curl_setopt($qr_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($qr_ch, CURLOPT_TIMEOUT, 5);
$qr_response = curl_exec($qr_ch);
$qr_code = curl_getinfo($qr_ch, CURLINFO_HTTP_CODE);
curl_close($qr_ch);

if ($qr_code === 200) {
    $qr_data = json_decode($qr_response, true);
    if ($qr_data) {
        if (isset($qr_data['qr']) && $qr_data['qr']) {
            echo "⚠️ QR CODE NECESSÁRIO!\n";
            echo "   Acesse: http://$vps_ip:3000/qr\n";
            echo "   E escaneie o QR Code com seu WhatsApp\n";
        } elseif (isset($qr_data['ready']) && $qr_data['ready']) {
            echo "✅ WhatsApp já conectado\n";
        } else {
            echo "🔄 WhatsApp inicializando...\n";
        }
    }
} else {
    echo "❌ Erro ao verificar QR Code\n";
}
echo "\n";

// 6. DIAGNÓSTICO FINAL
echo "🎯 DIAGNÓSTICO FINAL:\n";
echo "====================\n";

$problemas = [];

if ($webhook_code !== 200 || !isset($webhook_data['webhook_url'])) {
    $problemas[] = "Webhook não está configurado no VPS";
}

if ($status_code !== 200) {
    $problemas[] = "VPS Canal 3000 não está respondendo";
}

if ($qr_code === 200 && isset($qr_data['qr']) && $qr_data['qr']) {
    $problemas[] = "QR Code precisa ser escaneado";
}

if ($total_hoje === $total_teste) {
    $problemas[] = "Apenas mensagens de teste - nenhuma mensagem real";
}

if (empty($problemas)) {
    echo "✅ SISTEMA PARECE CONFIGURADO CORRETAMENTE\n";
    echo "💡 O problema pode ser:\n";
    echo "   - Número do WhatsApp incorreto\n";
    echo "   - Mensagem enviada para canal errado\n";
    echo "   - Delay de sincronização\n";
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n";
    foreach ($problemas as $problema) {
        echo "   • $problema\n";
    }
}

echo "\n🔧 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Confirme que está enviando para o NÚMERO CORRETO do Canal 3000\n";
echo "2. Se há QR Code, escaneie em: http://$vps_ip:3000/qr\n";
echo "3. Aguarde 1-2 minutos após enviar mensagem\n";
echo "4. Verifique se a mensagem aparece no painel\n";

?> 