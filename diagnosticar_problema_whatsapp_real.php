<?php
/**
 * 🔍 DIAGNÓSTICO - WHATSAPP REAL NÃO REGISTRANDO
 * 
 * Analisa por que mensagens do WhatsApp particular não chegam ao chat centralizado
 */

echo "🔍 DIAGNÓSTICO WHATSAPP REAL\n";
echo "===========================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// 1. VERIFICAR CONFIGURAÇÃO ATUAL DO WEBHOOK NO VPS CANAL 3000
echo "📡 1. VERIFICANDO WEBHOOK CANAL 3000\n";
echo "====================================\n";

$ch = curl_init("http://$vps_ip:3000/webhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $http_code\n";
if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "Webhook configurado: " . (isset($data['webhook']) ? $data['webhook'] : 'NÃO ENCONTRADO') . "\n";
        echo "Status: " . (isset($data['webhookEnabled']) ? ($data['webhookEnabled'] ? 'ATIVADO' : 'DESATIVADO') : 'DESCONHECIDO') . "\n";
    } else {
        echo "Resposta: $response\n";
    }
} else {
    echo "❌ Sem resposta do VPS\n";
}
echo "\n";

// 2. VERIFICAR LOGS RECENTES DE MENSAGENS NO BANCO
echo "📊 2. MENSAGENS RECENTES NO BANCO\n";
echo "=================================\n";

$ultimas_msgs = $mysqli->query("
    SELECT id, numero_whatsapp, direcao, SUBSTRING(mensagem, 1, 50) as msg, data_hora
    FROM mensagens_comunicacao 
    WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ORDER BY data_hora DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

if (!empty($ultimas_msgs)) {
    echo "✅ Mensagens dos últimos 30 minutos:\n";
    foreach ($ultimas_msgs as $msg) {
        echo "  ID {$msg['id']} | {$msg['numero_whatsapp']} | {$msg['direcao']} | {$msg['data_hora']}\n";
        echo "    {$msg['msg']}...\n\n";
    }
} else {
    echo "❌ Nenhuma mensagem nos últimos 30 minutos\n";
}
echo "\n";

// 3. VERIFICAR STATUS DO VPS CANAL 3000
echo "🖥️ 3. STATUS VPS CANAL 3000\n";
echo "===========================\n";

$status_ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_ch, CURLOPT_TIMEOUT, 10);
$status_response = curl_exec($status_ch);
$status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
curl_close($status_ch);

echo "Status: HTTP $status_code\n";
if ($status_response) {
    $status_data = json_decode($status_response, true);
    if ($status_data) {
        echo "WhatsApp conectado: " . (isset($status_data['connected']) ? ($status_data['connected'] ? 'SIM' : 'NÃO') : 'DESCONHECIDO') . "\n";
        echo "QR Code necessário: " . (isset($status_data['qrCode']) ? 'SIM' : 'NÃO') . "\n";
        if (isset($status_data['instance'])) {
            echo "Instância: {$status_data['instance']}\n";
        }
    } else {
        echo "Resposta: $status_response\n";
    }
} else {
    echo "❌ VPS não respondeu\n";
}
echo "\n";

// 4. TESTAR WEBHOOK DIRETAMENTE
echo "🔧 4. TESTANDO WEBHOOK DIRETAMENTE\n";
echo "==================================\n";

$teste_webhook = json_encode([
    'from' => '5547999999999@c.us',
    'body' => '🔍 TESTE DIAGNÓSTICO - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

$webhook_ch = curl_init($webhook_url);
curl_setopt($webhook_ch, CURLOPT_POST, true);
curl_setopt($webhook_ch, CURLOPT_POSTFIELDS, $teste_webhook);
curl_setopt($webhook_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 20);
curl_setopt($webhook_ch, CURLOPT_SSL_VERIFYPEER, false);

$webhook_response = curl_exec($webhook_ch);
$webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
curl_close($webhook_ch);

echo "Status webhook: HTTP $webhook_code\n";
echo "Resposta: " . substr($webhook_response, 0, 200) . "\n\n";

// 5. VERIFICAR SE A MENSAGEM DE TESTE FOI SALVA
sleep(2);
$teste_salvo = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE mensagem LIKE '%TESTE DIAGNÓSTICO%'
    ORDER BY data_hora DESC LIMIT 1
")->fetch_assoc();

if ($teste_salvo) {
    echo "✅ Webhook funcionando - mensagem salva (ID: {$teste_salvo['id']})\n";
} else {
    echo "❌ Webhook não salvou a mensagem de teste\n";
}
echo "\n";

// 6. VERIFICAR CONFIGURAÇÃO DO CANAL NO BANCO
echo "🗄️ 5. CONFIGURAÇÃO DO CANAL NO BANCO\n";
echo "====================================\n";

$canal_info = $mysqli->query("
    SELECT * FROM canais_comunicacao 
    WHERE porta = 3000 OR nome_exibicao LIKE '%3000%'
    LIMIT 1
")->fetch_assoc();

if ($canal_info) {
    echo "✅ Canal encontrado:\n";
    echo "  ID: {$canal_info['id']}\n";
    echo "  Nome: {$canal_info['nome_exibicao']}\n";
    echo "  Porta: {$canal_info['porta']}\n";
    echo "  Webhook: {$canal_info['webhook_url']}\n";
    echo "  Ativo: " . ($canal_info['ativo'] ? 'SIM' : 'NÃO') . "\n";
} else {
    echo "❌ Canal 3000 não encontrado no banco\n";
}
echo "\n";

// 7. RECONFIGURAR WEBHOOK NO VPS (TENTATIVA DE CORREÇÃO)
echo "🔧 6. TENTANDO RECONFIGURAR WEBHOOK\n";
echo "===================================\n";

$config_data = json_encode(['webhook' => $webhook_url]);
$config_ch = curl_init("http://$vps_ip:3000/webhook");
curl_setopt($config_ch, CURLOPT_POST, true);
curl_setopt($config_ch, CURLOPT_POSTFIELDS, $config_data);
curl_setopt($config_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($config_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($config_ch, CURLOPT_TIMEOUT, 15);

$config_response = curl_exec($config_ch);
$config_code = curl_getinfo($config_ch, CURLINFO_HTTP_CODE);
curl_close($config_ch);

echo "Configuração: HTTP $config_code\n";
echo "Resposta: $config_response\n\n";

// 8. RESUMO E DIAGNÓSTICO
echo "📋 RESUMO DO DIAGNÓSTICO\n";
echo "========================\n";

$problemas = [];
if ($http_code !== 200) $problemas[] = "VPS canal 3000 não responde";
if ($status_code !== 200) $problemas[] = "Status do VPS inacessível";
if ($webhook_code !== 200) $problemas[] = "Webhook retorna erro HTTP $webhook_code";
if (!$teste_salvo) $problemas[] = "Webhook não processa mensagens";
if (!$canal_info) $problemas[] = "Canal 3000 não configurado no banco";

if (empty($problemas)) {
    echo "✅ SISTEMA PARECE FUNCIONAL\n";
    echo "💡 Problema pode ser:\n";
    echo "  - WhatsApp não conectado no VPS\n";
    echo "  - Número não vinculado ao canal correto\n";
    echo "  - Delay na sincronização\n";
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n";
    foreach ($problemas as $problema) {
        echo "  • $problema\n";
    }
}

echo "\n🔧 PRÓXIMOS PASSOS RECOMENDADOS:\n";
echo "1. Verificar se WhatsApp está conectado no VPS\n";
echo "2. Confirmar se o número está vinculado ao canal 3000\n";
echo "3. Enviar nova mensagem de teste após 1 minuto\n";
echo "4. Verificar logs do PM2 no VPS\n";

?> 