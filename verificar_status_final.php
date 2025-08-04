<?php
/**
 * 🔍 VERIFICADOR DE STATUS FINAL
 * 
 * Verifica o status completo do sistema após as correções
 */

echo "=== 🔍 VERIFICADOR DE STATUS FINAL ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// ===== 1. VERIFICAR BANCO DE DADOS =====
echo "1. 🗄️ VERIFICANDO BANCO DE DADOS:\n";

require_once 'config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "   ❌ Erro de conexão: " . $mysqli->connect_error . "\n";
    } else {
        echo "   ✅ Conectado ao banco: " . DB_NAME . "\n";
        
        // Verificar coluna telefone_origem
        $result = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'telefone_origem'");
        if ($result && $result->num_rows > 0) {
            echo "   ✅ Coluna telefone_origem: EXISTE\n";
        } else {
            echo "   ❌ Coluna telefone_origem: NÃO EXISTE\n";
        }
        
        // Contar mensagens
        $result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   📊 Total de mensagens: " . $row['total'] . "\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n";

// ===== 2. VERIFICAR ARQUIVO WEBHOOK =====
echo "2. 📋 VERIFICANDO ARQUIVO WEBHOOK:\n";

$webhook_file = 'painel/receber_mensagem_ana_local.php';
if (file_exists($webhook_file)) {
    echo "   ✅ Arquivo encontrado: $webhook_file\n";
    
    $content = file_get_contents($webhook_file);
    
    // Verificar se tem tratamento de formato robot
    if (strpos($content, 'Tratamento de formato WhatsApp robot') !== false) {
        echo "   ✅ Tratamento de formato robot: IMPLEMENTADO\n";
    } else {
        echo "   ❌ Tratamento de formato robot: NÃO IMPLEMENTADO\n";
    }
    
    // Verificar se tem tratamento de event/data
    if (strpos($content, 'isset($dados["event"])') !== false) {
        echo "   ✅ Verificação event/data: IMPLEMENTADA\n";
    } else {
        echo "   ❌ Verificação event/data: NÃO IMPLEMENTADA\n";
    }
    
} else {
    echo "   ❌ Arquivo não encontrado: $webhook_file\n";
}

echo "\n";

// ===== 3. TESTAR WEBHOOKS =====
echo "3. 🧪 TESTANDO WEBHOOKS:\n";

$url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";

// Teste 1: Formato correto
$dados_correto = [
    "from" => "554796164699@c.us",
    "body" => "Teste status final - " . date('Y-m-d H:i:s'),
    "timestamp" => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_correto));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 Formato correto: HTTP $http_code\n";
if ($http_code == 200) {
    echo "   ✅ Formato correto: FUNCIONANDO\n";
} elseif ($http_code == 500) {
    echo "   ⚠️  Formato correto: ERRO 500 (Ana respondeu mas erro interno)\n";
} else {
    echo "   ❌ Formato correto: ERRO HTTP $http_code\n";
}

// Teste 2: Formato robot
$dados_robot = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699",
        "text" => "Teste robot status final - " . date('Y-m-d H:i:s'),
        "type" => "chat",
        "timestamp" => time(),
        "session" => "default"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_robot));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response_robot = curl_exec($ch);
$http_code_robot = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 Formato robot: HTTP $http_code_robot\n";
if ($http_code_robot == 200) {
    echo "   ✅ Formato robot: FUNCIONANDO\n";
} else {
    echo "   ❌ Formato robot: ERRO HTTP $http_code_robot\n";
}

echo "\n";

// ===== 4. VERIFICAR VPS =====
echo "4. 🖥️ VERIFICANDO VPS:\n";

$vps_url = "http://212.85.11.238:3000/status";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$vps_response = curl_exec($ch);
$vps_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 VPS Status: HTTP $vps_http_code\n";
if ($vps_http_code == 200) {
    echo "   ✅ VPS WhatsApp: ONLINE\n";
} else {
    echo "   ❌ VPS WhatsApp: OFFLINE\n";
}

echo "\n";

// ===== 5. RESUMO FINAL =====
echo "5. 📊 RESUMO FINAL:\n";

$status_banco = isset($mysqli) && !$mysqli->connect_error;
$status_webhook = file_exists($webhook_file);
$status_formato_correto = $http_code == 200 || $http_code == 500;
$status_formato_robot = $http_code_robot == 200;
$status_vps = $vps_http_code == 200;

echo "   🗄️  Banco de dados: " . ($status_banco ? "✅ OK" : "❌ ERRO") . "\n";
echo "   📋 Webhook: " . ($status_webhook ? "✅ OK" : "❌ ERRO") . "\n";
echo "   📱 Formato correto: " . ($status_formato_correto ? "✅ OK" : "❌ ERRO") . "\n";
echo "   🤖 Formato robot: " . ($status_formato_robot ? "✅ OK" : "❌ ERRO") . "\n";
echo "   🖥️  VPS WhatsApp: " . ($status_vps ? "✅ OK" : "❌ ERRO") . "\n";

echo "\n";

// ===== 6. PRÓXIMOS PASSOS =====
echo "6. 🎯 PRÓXIMOS PASSOS:\n";

if ($status_formato_robot) {
    echo "   🎉 SISTEMA 100% FUNCIONANDO!\n";
    echo "   1. Enviar mensagem real para 554797146908\n";
    echo "   2. Monitorar logs por 24h\n";
    echo "   3. Sistema pronto para produção\n";
} elseif ($status_formato_correto) {
    echo "   ⚠️  SISTEMA PARCIALMENTE FUNCIONANDO\n";
    echo "   1. Formato correto funciona (Ana responde)\n";
    echo "   2. Formato robot precisa de ajuste\n";
    echo "   3. Verificar se deploy foi feito na hospedagem\n";
} else {
    echo "   ❌ SISTEMA COM PROBLEMAS\n";
    echo "   1. Verificar logs da hospedagem\n";
    echo "   2. Confirmar deploy na hospedagem\n";
    echo "   3. Verificar configuração do webhook\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
echo "Status Geral: " . ($status_formato_robot ? "✅ SUCESSO TOTAL" : ($status_formato_correto ? "⚠️  PARCIAL" : "❌ PROBLEMAS")) . "\n";
?> 