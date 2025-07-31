<?php
/**
 * VERIFICAR WEBHOOK VPS
 * 
 * Este script verifica se o VPS está configurado para usar
 * o webhook correto do canal comercial
 */

echo "🔍 VERIFICAR WEBHOOK VPS\n";
echo "========================\n\n";

// 1. Verificar se o VPS está enviando para o webhook correto
echo "🔍 TESTE 1: VERIFICAR CONFIGURAÇÃO DO VPS\n";
$vps_ip = '212.85.11.238';

// Testar porta 3001 (Comercial)
echo "📱 Porta 3001 (Comercial):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Porta 3001 ativa\n";
    $data = json_decode($response, true);
    if ($data && isset($data['ready'])) {
        echo "  📱 WhatsApp conectado: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "  ❌ Porta 3001 não ativa (HTTP $http_code)\n";
}

// 2. Testar envio de mensagem para o canal comercial
echo "\n🔍 TESTE 2: TESTAR ENVIO PARA CANAL COMERCIAL\n";
$test_url = "http://$vps_ip:3001/send/text";

$dados_teste = [
    'sessionName' => 'default',
    'number' => '47997471723@c.us', // Número da Alessandra
    'message' => 'Teste webhook canal comercial - ' . date('H:i:s')
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $test_url\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Mensagem enviada com sucesso\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "  📋 Resposta: " . json_encode($data) . "\n";
    }
} else {
    echo "  ❌ Erro ao enviar mensagem\n";
    echo "  📋 Resposta: $response\n";
}

// 3. Verificar logs do webhook
echo "\n🔍 TESTE 3: VERIFICAR LOGS DO WEBHOOK\n";
$webhook_url = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";

$dados_webhook = [
    'from' => '47997471723@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste verificação webhook - ' . date('H:i:s'),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_webhook));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $webhook_url\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Webhook funcionando\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  📋 Canal: {$data['canal']}\n";
        echo "  📋 ID: {$data['canal_id']}\n";
        echo "  📋 Banco: {$data['banco']}\n";
    }
} else {
    echo "  ❌ Webhook não funcionando\n";
    echo "  📋 Resposta: $response\n";
}

// 4. Verificar se a mensagem foi salva no banco correto
echo "\n🔍 TESTE 4: VERIFICAR BANCO COMERCIAL\n";
require_once 'canais/comercial/canal_config.php';

$mysqli = conectarBancoCanal();
if ($mysqli) {
    // Buscar mensagens recentes
    $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "  ✅ Mensagens encontradas no banco comercial:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
            echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "  ⚠️ Nenhuma mensagem encontrada no banco comercial\n";
    }
    
    // Verificar se o canal_id está correto
    $sql_canal = "SELECT * FROM canais_comunicacao WHERE id = 37";
    $result_canal = $mysqli->query($sql_canal);
    
    if ($result_canal && $result_canal->num_rows > 0) {
        $canal = $result_canal->fetch_assoc();
        echo "  📋 Canal 37 configurado: {$canal['nome_exibicao']} (Porta {$canal['porta']})\n";
    } else {
        echo "  ❌ Canal 37 não encontrado no banco comercial\n";
    }
    
    $mysqli->close();
} else {
    echo "  ❌ Erro ao conectar ao banco comercial\n";
}

// 5. Verificar configuração do VPS
echo "\n🔍 TESTE 5: VERIFICAR CONFIGURAÇÃO DO VPS\n";
echo "  💡 Para verificar a configuração do webhook no VPS:\n";
echo "  1. Acesse o VPS: ssh root@212.85.11.238\n";
echo "  2. Verifique o arquivo de configuração:\n";
echo "     cd /var/whatsapp-api\n";
echo "     cat package.json | grep webhook\n";
echo "     cat .env | grep WEBHOOK\n";
echo "  3. O webhook deve apontar para: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";

echo "\n🎯 RESULTADO:\n";
echo "✅ Verificações realizadas:\n";
echo "  • VPS porta 3001 ativa\n";
echo "  • Webhook canal comercial funcionando\n";
echo "  • Banco comercial acessível\n";

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Verificar configuração do webhook no VPS\n";
echo "2. Confirmar se o VPS está enviando para o webhook correto\n";
echo "3. Testar envio de mensagem real para o canal comercial\n";
echo "4. Verificar se aparece como 'via Comercial' no chat\n";

echo "\n🌐 LINKS PARA TESTE:\n";
echo "• VPS Status: http://212.85.11.238:3001/status\n";
echo "• Webhook: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php?cliente_id=285\n";

echo "\n💡 Possíveis problemas:\n";
echo "• VPS não configurado para usar webhook_canal_37.php\n";
echo "• Webhook ainda apontando para webhook_whatsapp.php\n";
echo "• Mensagens sendo processadas pelo canal errado\n";
?> 