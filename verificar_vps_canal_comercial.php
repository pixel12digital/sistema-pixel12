<?php
/**
 * VERIFICAR VPS CANAL COMERCIAL
 * 
 * Este script verifica se o VPS está configurado corretamente
 * para o canal comercial na porta 3001
 */

echo "🔍 VERIFICANDO VPS CANAL COMERCIAL\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';

// 1. Verificar se a porta 3001 está ativa
echo "🔍 TESTE 1: VERIFICANDO PORTA 3001\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Porta 3001 está ativa!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "  📄 Resposta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        if (isset($data['ready'])) {
            echo "  📱 WhatsApp conectado: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
        }
    }
} else {
    echo "  ❌ Porta 3001 não está ativa!\n";
    echo "     HTTP Code: $http_code\n";
    if ($error) {
        echo "     Erro: $error\n";
    }
}

// 2. Verificar se a porta 3000 ainda está funcionando (canal financeiro)
echo "\n🔍 TESTE 2: VERIFICANDO PORTA 3000 (FINANCEIRO)\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Porta 3000 está ativa (Canal Financeiro)\n";
} else {
    echo "  ❌ Porta 3000 não está ativa!\n";
    echo "     HTTP Code: $http_code\n";
}

// 3. Testar webhook específico do canal comercial
echo "\n🔍 TESTE 3: TESTANDO WEBHOOK CANAL COMERCIAL\n";
$webhook_url = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";

$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste VPS canal comercial - ' . date('H:i:s'),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "  URL: $webhook_url\n";
echo "  HTTP Code: $http_code\n";
if ($error) {
    echo "  ❌ Erro cURL: $error\n";
} else {
    echo "  ✅ Resposta: $response\n";
}

// 4. Verificar configuração do canal no banco
echo "\n🔍 TESTE 4: VERIFICANDO CONFIGURAÇÃO NO BANCO\n";
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT * FROM canais_comunicacao WHERE porta = 3001 OR nome_exibicao LIKE '%Comercial%'";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  📋 Canais encontrados:\n";
    while ($canal = $result->fetch_assoc()) {
        echo "    ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "    Identificador: {$canal['identificador']}\n";
    }
} else {
    echo "  ❌ Nenhum canal comercial encontrado no banco\n";
}

// 5. Verificar se o webhook está configurado no VPS
echo "\n🔍 TESTE 5: VERIFICANDO CONFIGURAÇÃO DO WEBHOOK NO VPS\n";
echo "  💡 Para verificar se o webhook está configurado no VPS, você precisa:\n";
echo "  1. Acessar o VPS via SSH: ssh root@212.85.11.238\n";
echo "  2. Verificar o arquivo de configuração do servidor WhatsApp\n";
echo "  3. Confirmar se o webhook aponta para: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";

echo "\n🎯 RESULTADO:\n";
if ($http_code === 200) {
    echo "✅ O VPS está configurado para o canal comercial!\n";
    echo "📋 Próximos passos:\n";
    echo "1. Testar recebimento de mensagens reais\n";
    echo "2. Verificar se as mensagens aparecem no chat do painel\n";
    echo "3. Configurar automações específicas do canal\n";
} else {
    echo "❌ O VPS precisa ser configurado para o canal comercial!\n";
    echo "📋 Comandos para executar no VPS:\n";
    echo "1. ssh root@212.85.11.238\n";
    echo "2. cd /var/whatsapp-api\n";
    echo "3. Verificar se existe configuração para porta 3001\n";
    echo "4. Configurar webhook para apontar para o canal comercial\n";
}

echo "\n🌐 ACESSO AO VPS:\n";
echo "• SSH: ssh root@212.85.11.238\n";
echo "• Status API: http://212.85.11.238:3001/status\n";
echo "• Webhook: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
?> 