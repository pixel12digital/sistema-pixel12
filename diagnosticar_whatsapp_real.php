<?php
/**
 * 🔍 DIAGNÓSTICO - MENSAGENS REAIS WHATSAPP
 * 
 * Por que mensagens reais não chegam ao banco?
 */

echo "🔍 DIAGNÓSTICO WHATSAPP REAL\n";
echo "===========================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';

// 1. VERIFICAR CANAIS CONFIGURADOS
echo "📊 1. CANAIS CONFIGURADOS NO BANCO:\n";
echo "===================================\n";

$canais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE ativo = 1")->fetch_all(MYSQLI_ASSOC);

if (!empty($canais)) {
    foreach ($canais as $canal) {
        echo "📱 {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "   Porta: {$canal['porta']}\n";
        echo "   Webhook: " . ($canal['webhook_url'] ?: 'NÃO CONFIGURADO') . "\n";
        echo "   Status: " . ($canal['ativo'] ? 'ATIVO' : 'INATIVO') . "\n\n";
    }
} else {
    echo "❌ Nenhum canal encontrado\n\n";
}

// 2. VERIFICAR STATUS VPS
echo "🖥️ 2. STATUS VPS CANAIS:\n";
echo "========================\n";

$portas = [3000, 3001];
foreach ($portas as $porta) {
    echo "🔄 Verificando porta $porta...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Porta $porta: ATIVO\n";
        $data = json_decode($response, true);
        if ($data && isset($data['ready']) && $data['ready']) {
            echo "  ✅ WhatsApp: CONECTADO\n";
        }
        
        // Verificar webhook configurado
        $webhook_ch = curl_init("http://$vps_ip:$porta/webhook/config");
        curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 5);
        $webhook_response = curl_exec($webhook_ch);
        $webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
        curl_close($webhook_ch);
        
        if ($webhook_code === 200) {
            $webhook_data = json_decode($webhook_response, true);
            if ($webhook_data && isset($webhook_data['webhook_url'])) {
                echo "  ✅ Webhook: {$webhook_data['webhook_url']}\n";
            } else {
                echo "  ❌ Webhook: NÃO CONFIGURADO\n";
            }
        } else {
            echo "  ⚠️ Webhook: Status desconhecido\n";
        }
    } else {
        echo "  ❌ Porta $porta: FALHA\n";
    }
    echo "\n";
}

// 3. VERIFICAR MENSAGENS POR NÚMERO
echo "📱 3. ÚLTIMAS MENSAGENS POR NÚMERO:\n";
echo "===================================\n";

$numeros = $mysqli->query("
    SELECT numero_whatsapp, COUNT(*) as total, MAX(data_hora) as ultima
    FROM mensagens_comunicacao 
    WHERE DATE(data_hora) = CURDATE()
    GROUP BY numero_whatsapp
    ORDER BY ultima DESC
")->fetch_all(MYSQLI_ASSOC);

if (!empty($numeros)) {
    foreach ($numeros as $numero) {
        echo "📞 {$numero['numero_whatsapp']}: {$numero['total']} mensagens\n";
        echo "   Última: {$numero['ultima']}\n\n";
    }
} else {
    echo "❌ Nenhuma mensagem hoje\n\n";
}

// 4. VERIFICAR MENSAGENS ÚLTIMAS 2 HORAS (todas)
echo "⏰ 4. TODAS AS MENSAGENS ÚLTIMAS 2 HORAS:\n";
echo "=========================================\n";

$recentes = $mysqli->query("
    SELECT id, numero_whatsapp, direcao, SUBSTRING(mensagem, 1, 60) as msg, data_hora
    FROM mensagens_comunicacao 
    WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ORDER BY data_hora DESC
")->fetch_all(MYSQLI_ASSOC);

if (!empty($recentes)) {
    foreach ($recentes as $msg) {
        $tipo = $msg['direcao'] === 'recebido' ? '📩' : '📤';
        echo "$tipo ID {$msg['id']} | {$msg['numero_whatsapp']} | {$msg['data_hora']}\n";
        echo "   {$msg['msg']}...\n\n";
    }
} else {
    echo "❌ Nenhuma mensagem nas últimas 2 horas\n\n";
}

// 5. NÚMEROS DE TESTE vs REAL
echo "🧪 5. ANÁLISE DE NÚMEROS:\n";
echo "=========================\n";

$numero_teste = '5547999999999';
echo "Número de teste: $numero_teste\n";
echo "Mensagens do teste: " . $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$numero_teste'")->fetch_assoc()['total'] . "\n\n";

// Buscar outros números
$outros_numeros = $mysqli->query("
    SELECT DISTINCT numero_whatsapp, COUNT(*) as total 
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp != '$numero_teste'
    GROUP BY numero_whatsapp
    ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);

if (!empty($outros_numeros)) {
    echo "Outros números encontrados:\n";
    foreach ($outros_numeros as $num) {
        echo "  📞 {$num['numero_whatsapp']}: {$num['total']} mensagens\n";
    }
} else {
    echo "Apenas número de teste encontrado\n";
}
echo "\n";

// 6. DIAGNÓSTICO FINAL
echo "🎯 6. DIAGNÓSTICO:\n";
echo "==================\n";

$webhook_3000_ok = false;
$whatsapp_3000_ok = false;

// Verificar se webhook 3000 está configurado
$webhook_ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 5);
$webhook_response = curl_exec($webhook_ch);
$webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
curl_close($webhook_ch);

if ($webhook_code === 200) {
    $webhook_data = json_decode($webhook_response, true);
    if ($webhook_data && isset($webhook_data['webhook_url'])) {
        $webhook_3000_ok = true;
        echo "✅ Webhook Canal 3000: CONFIGURADO\n";
    }
}

// Verificar se WhatsApp 3000 está conectado
$status_ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_ch, CURLOPT_TIMEOUT, 5);
$status_response = curl_exec($status_ch);
$status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
curl_close($status_ch);

if ($status_code === 200) {
    $status_data = json_decode($status_response, true);
    if ($status_data && isset($status_data['ready']) && $status_data['ready']) {
        $whatsapp_3000_ok = true;
        echo "✅ WhatsApp Canal 3000: CONECTADO\n";
    }
}

echo "\n💡 POSSÍVEIS CAUSAS:\n";
if (!$webhook_3000_ok) {
    echo "❌ Webhook não configurado no VPS\n";
}
if (!$whatsapp_3000_ok) {
    echo "❌ WhatsApp não conectado no VPS\n";
}

echo "⚠️ Número do WhatsApp diferente do configurado\n";
echo "⚠️ QR Code não escaneado / sessão desconectada\n";
echo "⚠️ Mensagem enviada para canal errado (3001 em vez de 3000)\n\n";

echo "🔧 PRÓXIMOS PASSOS:\n";
echo "===================\n";
echo "1. Confirme o NÚMERO CORRETO do Canal 3000\n";
echo "2. Verifique se WhatsApp Web está conectado\n";
echo "3. Teste enviar mensagem para o número exato\n";
echo "4. Verifique se não há QR Code para escanear no VPS\n";

?> 