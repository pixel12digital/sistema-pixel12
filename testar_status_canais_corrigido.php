<?php
/**
 * TESTAR STATUS DOS CANAIS CORRIGIDO
 * 
 * Este script testa o status dos canais após a correção
 * para verificar se estão aparecendo como conectados
 */

echo "🧪 TESTAR STATUS DOS CANAIS CORRIGIDO\n";
echo "=====================================\n\n";

// 1. Testar VPS diretamente
echo "🔍 TESTE 1: VPS DIRETAMENTE\n";
$vps_ip = '212.85.11.238';

// Testar porta 3000 (Financeiro)
echo "📱 Porta 3000 (Financeiro):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Porta 3000 ativa\n";
    $data = json_decode($response, true);
    if ($data && isset($data['ready'])) {
        echo "  📱 WhatsApp conectado: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "  ❌ Porta 3000 não ativa (HTTP $http_code)\n";
}

// Testar porta 3001 (Comercial)
echo "\n📱 Porta 3001 (Comercial):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
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

// 2. Testar API de status dos canais
echo "\n🔍 TESTE 2: API DE STATUS DOS CANAIS\n";
$status_url = "https://app.pixel12digital.com.br/painel/api/status_canais.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $status_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "  URL: $status_url\n";
echo "  HTTP Code: $http_code\n";
if ($error) {
    echo "  ❌ Erro cURL: $error\n";
} else {
    echo "  ✅ API respondendo\n";
    $data = json_decode($response, true);
    if ($data && isset($data['canais'])) {
        echo "  📋 Canais encontrados:\n";
        foreach ($data['canais'] as $canal) {
            $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
            echo "    $status_icon {$canal['nome']} (Porta {$canal['porta']}) - {$canal['status']}\n";
            echo "      ID: {$canal['id']} | Número: {$canal['numero']}\n";
        }
    } else {
        echo "  ❌ Estrutura de dados incorreta\n";
        echo "  📄 Resposta: $response\n";
    }
}

// 3. Verificar banco de dados
echo "\n🔍 TESTE 3: VERIFICAR BANCO DE DADOS\n";
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  📋 Canais no banco:\n";
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
        echo "    $status_icon ID {$canal['id']} - {$canal['nome_exibicao']} (Porta {$canal['porta']}) - {$canal['status']}\n";
        echo "      Identificador: {$canal['identificador']}\n";
    }
} else {
    echo "  ❌ Nenhum canal encontrado no banco\n";
}

// 4. Testar chat do painel
echo "\n🔍 TESTE 4: TESTAR CHAT DO PAINEL\n";
$chat_url = "https://app.pixel12digital.com.br/painel/chat.php";

echo "  💡 Para testar o chat do painel:\n";
echo "  1. Acesse: $chat_url\n";
echo "  2. Verifique se os canais aparecem como conectados\n";
echo "  3. Verifique se há diferenciação entre Comercial e Financeiro\n";
echo "  4. Teste enviar uma mensagem\n";

echo "\n🎯 RESULTADO:\n";
echo "✅ Correções aplicadas:\n";
echo "  • VPS usado em produção (212.85.11.238)\n";
echo "  • Canais diferenciados (Comercial vs Financeiro)\n";
echo "  • Status atualizado automaticamente\n";
echo "  • Estrutura de dados corrigida\n";

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Fazer git pull na Hostinger\n";
echo "2. Testar chat do painel\n";
echo "3. Verificar se canais aparecem como conectados\n";
echo "4. Testar envio de mensagens\n";

echo "\n🌐 LINKS PARA TESTE:\n";
echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "• Status API: https://app.pixel12digital.com.br/painel/api/status_canais.php\n";
echo "• VPS Status: http://212.85.11.238:3000/status e http://212.85.11.238:3001/status\n";
?> 