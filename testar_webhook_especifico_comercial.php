<?php
/**
 * TESTAR WEBHOOK ESPECÍFICO - CANAL COMERCIAL
 * 
 * Este script testa diretamente o webhook específico do canal comercial
 * para verificar se está salvando no banco correto
 */

echo "🧪 TESTANDO WEBHOOK ESPECÍFICO - CANAL COMERCIAL\n";
echo "===============================================\n\n";

// 1. Testar webhook específico diretamente
echo "🔍 TESTE 1: TESTANDO WEBHOOK ESPECÍFICO\n";
$webhook_url = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";

$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste webhook específico canal comercial - ' . date('H:i:s'),
    'timestamp' => time()
];

echo "  📋 Dados de teste:\n";
echo "    From: {$dados_teste['from']}\n";
echo "    To: {$dados_teste['to']}\n";
echo "    Body: {$dados_teste['body']}\n";
echo "    URL: $webhook_url\n";

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

echo "  HTTP Code: $http_code\n";
if ($error) {
    echo "  ❌ Erro cURL: $error\n";
} else {
    echo "  ✅ Resposta: $response\n";
    
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  ✅ Webhook específico funcionando!\n";
        echo "  📋 Canal: {$data['canal']}\n";
        echo "  📋 ID: {$data['canal_id']}\n";
        echo "  📋 Banco: {$data['banco']}\n";
    } else {
        echo "  ❌ Webhook específico não funcionando!\n";
        if (isset($data['error'])) {
            echo "  📋 Erro: {$data['error']}\n";
        }
    }
}

// 2. Verificar banco comercial após o teste
echo "\n🔍 TESTE 2: VERIFICANDO BANCO COMERCIAL\n";
require_once 'canais/comercial/canal_config.php';

$mysqli = conectarBancoCanal();
if ($mysqli) {
    // Buscar mensagens na tabela mensagens_comunicacao
    $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "  ✅ Mensagens encontradas na tabela mensagens_comunicacao:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
            echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "  ⚠️ Nenhuma mensagem encontrada na tabela mensagens_comunicacao\n";
    }
    
    // Buscar mensagens na tabela mensagens_pendentes
    $sql_pendentes = "SELECT * FROM mensagens_pendentes ORDER BY data_hora DESC LIMIT 5";
    $result_pendentes = $mysqli->query($sql_pendentes);
    
    if ($result_pendentes && $result_pendentes->num_rows > 0) {
        echo "  ✅ Mensagens encontradas na tabela mensagens_pendentes:\n";
        while ($msg = $result_pendentes->fetch_assoc()) {
            echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
            echo "      Número: {$msg['numero']}\n";
            echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "  ⚠️ Nenhuma mensagem encontrada na tabela mensagens_pendentes\n";
    }
    
    // Verificar configuração do canal
    $sql_canal = "SELECT * FROM canais_comunicacao WHERE id = 37";
    $result_canal = $mysqli->query($sql_canal);
    
    if ($result_canal && $result_canal->num_rows > 0) {
        $canal = $result_canal->fetch_assoc();
        echo "  📋 Canal 37 configurado: {$canal['nome_exibicao']} (Porta {$canal['porta']})\n";
        echo "  📋 Identificador: {$canal['identificador']}\n";
    }
    
    $mysqli->close();
} else {
    echo "  ❌ Não foi possível conectar ao banco comercial\n";
}

// 3. Verificar banco principal para comparação
echo "\n🔍 TESTE 3: VERIFICANDO BANCO PRINCIPAL\n";
require_once 'config.php';

$sql = "SELECT * FROM mensagens_comunicacao WHERE canal_id = 37 ORDER BY data_hora DESC LIMIT 3";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  ⚠️ Mensagens do canal 37 encontradas no banco principal:\n";
    while ($msg = $result->fetch_assoc()) {
        echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
    echo "  💡 Isso confirma que o webhook geral está sendo usado\n";
} else {
    echo "  ✅ Nenhuma mensagem do canal 37 no banco principal\n";
}

// 4. Testar webhook geral para comparação
echo "\n🔍 TESTE 4: TESTANDO WEBHOOK GERAL\n";
$webhook_geral_url = "https://app.pixel12digital.com.br/api/webhook_whatsapp.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_geral_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $webhook_geral_url\n";
echo "  HTTP Code: $http_code\n";
echo "  ✅ Resposta: $response\n";

echo "\n🎯 DIAGNÓSTICO FINAL:\n";
echo "📋 Se o webhook específico não está sendo usado:\n";
echo "1. ❌ VPS não está configurado corretamente\n";
echo "2. ❌ Webhook específico não está sendo chamado\n";
echo "3. ❌ Mensagens vão para banco principal\n";
echo "\n📋 Se o webhook específico está sendo usado:\n";
echo "1. ✅ VPS configurado corretamente\n";
echo "2. ✅ Webhook específico funcionando\n";
echo "3. ✅ Mensagens vão para banco comercial (pendentes ou normais)\n";

echo "\n🌐 LINKS ÚTEIS:\n";
echo "• Webhook Específico: $webhook_url\n";
echo "• Webhook Geral: $webhook_geral_url\n";
echo "• VPS Status: http://212.85.11.238:3001/status\n";
echo "• phpMyAdmin Comercial: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel&table=mensagens_comunicacao\n";
?> 