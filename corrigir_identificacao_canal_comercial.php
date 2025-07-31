<?php
/**
 * CORRIGIR IDENTIFICAÇÃO CANAL COMERCIAL
 * 
 * Este script corrige a identificação do canal comercial para que
 * as mensagens apareçam como "COMERCIAL" em vez de "FINANCEIRO"
 */

echo "🔧 CORRIGINDO IDENTIFICAÇÃO CANAL COMERCIAL\n";
echo "==========================================\n\n";

// 1. Verificar configuração atual dos canais
echo "🔍 VERIFICANDO CONFIGURAÇÃO DOS CANAIS:\n";
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT id, nome_exibicao, identificador, porta, status FROM canais_comunicacao ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  📋 Canais configurados:\n";
    while ($canal = $result->fetch_assoc()) {
        echo "    ID {$canal['id']}: {$canal['nome_exibicao']} (Porta: {$canal['porta']}, Status: {$canal['status']})\n";
        echo "      Identificador: {$canal['identificador']}\n";
    }
} else {
    echo "  ❌ Nenhum canal encontrado\n";
}

// 2. Verificar mensagens recentes do canal 37
echo "\n🔍 VERIFICANDO MENSAGENS DO CANAL 37:\n";
$sql = "SELECT id, canal_id, mensagem, data_hora, direcao FROM mensagens_comunicacao WHERE canal_id = 37 ORDER BY data_hora DESC LIMIT 5";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  ⚠️ Mensagens do canal 37 encontradas no banco principal:\n";
    while ($msg = $result->fetch_assoc()) {
        echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
    echo "  💡 Isso indica que o webhook geral está sendo usado\n";
} else {
    echo "  ✅ Nenhuma mensagem do canal 37 no banco principal\n";
}

// 3. Verificar se o canal 37 está configurado corretamente
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO DO CANAL 37:\n";
$sql = "SELECT * FROM canais_comunicacao WHERE id = 37";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "  ✅ Canal 37 encontrado:\n";
    echo "    Nome: {$canal['nome_exibicao']}\n";
    echo "    Identificador: {$canal['identificador']}\n";
    echo "    Porta: {$canal['porta']}\n";
    echo "    Status: {$canal['status']}\n";
    
    // Verificar se o nome está correto
    if ($canal['nome_exibicao'] !== 'Comercial - Pixel') {
        echo "  🔧 Corrigindo nome do canal...\n";
        $sql_update = "UPDATE canais_comunicacao SET nome_exibicao = 'Comercial - Pixel' WHERE id = 37";
        if ($mysqli->query($sql_update)) {
            echo "  ✅ Nome do canal corrigido!\n";
        } else {
            echo "  ❌ Erro ao corrigir nome: " . $mysqli->error . "\n";
        }
    }
} else {
    echo "  ❌ Canal 37 não encontrado - criando...\n";
    $sql_insert = "INSERT INTO canais_comunicacao (id, tipo, identificador, nome_exibicao, status, porta, data_conexao) 
                   VALUES (37, 'whatsapp', '4797309525@c.us', 'Comercial - Pixel', 'conectado', 3001, NOW())";
    if ($mysqli->query($sql_insert)) {
        echo "  ✅ Canal 37 criado com sucesso!\n";
    } else {
        echo "  ❌ Erro ao criar canal: " . $mysqli->error . "\n";
    }
}

// 4. Verificar banco comercial
echo "\n🔍 VERIFICANDO BANCO COMERCIAL:\n";
require_once 'canais/comercial/canal_config.php';

$mysqli_comercial = conectarBancoCanal();
if ($mysqli_comercial) {
    // Verificar mensagens no banco comercial
    $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5";
    $result = $mysqli_comercial->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "  ✅ Mensagens encontradas no banco comercial:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
            echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "  ⚠️ Nenhuma mensagem na tabela mensagens_comunicacao do banco comercial\n";
        
        // Verificar mensagens pendentes
        $sql_pendentes = "SELECT * FROM mensagens_pendentes ORDER BY data_hora DESC LIMIT 5";
        $result_pendentes = $mysqli_comercial->query($sql_pendentes);
        
        if ($result_pendentes && $result_pendentes->num_rows > 0) {
            echo "  ✅ Mensagens encontradas na tabela mensagens_pendentes:\n";
            while ($msg = $result_pendentes->fetch_assoc()) {
                echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
                echo "      Número: {$msg['numero']}\n";
                echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
            }
        }
    }
    
    // Verificar configuração do canal no banco comercial
    $sql_canal = "SELECT * FROM canais_comunicacao WHERE id = 37";
    $result_canal = $mysqli_comercial->query($sql_canal);
    
    if ($result_canal && $result_canal->num_rows > 0) {
        $canal = $result_canal->fetch_assoc();
        echo "  📋 Canal 37 no banco comercial:\n";
        echo "    Nome: {$canal['nome_exibicao']}\n";
        echo "    Identificador: {$canal['identificador']}\n";
        echo "    Porta: {$canal['porta']}\n";
    }
    
    $mysqli_comercial->close();
} else {
    echo "  ❌ Não foi possível conectar ao banco comercial\n";
}

// 5. Testar webhook específico
echo "\n🧪 TESTANDO WEBHOOK ESPECÍFICO:\n";
$webhook_url = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";

$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste correção identificação canal comercial - ' . date('H:i:s'),
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
curl_close($ch);

echo "  URL: $webhook_url\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Webhook específico funcionando!\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  📋 Canal: {$data['canal']}\n";
        echo "  📋 ID: {$data['canal_id']}\n";
        echo "  📋 Banco: {$data['banco']}\n";
    }
} else {
    echo "  ❌ Webhook específico não funcionando!\n";
    echo "  📋 Resposta: $response\n";
}

// 6. Verificar se a VPS está usando o webhook correto
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO DA VPS:\n";
$vps_ip = "212.85.11.238";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data["webhook_url"])) {
        echo "  📋 Webhook configurado na VPS: " . $data["webhook_url"] . "\n";
        
        $webhook_correto = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";
        if ($data["webhook_url"] === $webhook_correto) {
            echo "  ✅ VPS está usando webhook correto!\n";
        } else {
            echo "  ❌ VPS está usando webhook incorreto!\n";
            echo "  🔧 Deve ser: $webhook_correto\n";
        }
    }
} else {
    echo "  ⚠️ Não foi possível verificar configuração da VPS\n";
}

echo "\n🎯 DIAGNÓSTICO FINAL:\n";
echo "📋 Se as mensagens ainda aparecem como 'FINANCEIRO':\n";
echo "1. ❌ VPS não está usando webhook específico\n";
echo "2. ❌ Mensagens estão sendo salvas no banco principal\n";
echo "3. ❌ Sistema está usando canal ID 36 (Financeiro)\n";
echo "\n📋 Para corrigir:\n";
echo "1. ✅ Configurar VPS para usar webhook_canal_37.php\n";
echo "2. ✅ Garantir que mensagens sejam salvas no banco comercial\n";
echo "3. ✅ Verificar se canal 37 está configurado corretamente\n";

echo "\n🌐 LINKS ÚTEIS:\n";
echo "• VPS Status: http://$vps_ip:3001/status\n";
echo "• Webhook Correto: $webhook_url\n";
echo "• phpMyAdmin Comercial: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel\n";
?> 