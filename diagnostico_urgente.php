<?php
/**
 * 🚨 DIAGNÓSTICO URGENTE - CHAT NÃO FUNCIONANDO
 * 
 * Verifica o que realmente está acontecendo
 */

echo "=== 🚨 DIAGNÓSTICO URGENTE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

require_once 'config.php';

// ===== 1. VERIFICAR ÚLTIMAS MENSAGENS =====
echo "1. 📋 VERIFICANDO ÚLTIMAS MENSAGENS NO BANCO:\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "   ❌ Erro de conexão: " . $mysqli->connect_error . "\n";
    } else {
        // Últimas 10 mensagens
        $result = $mysqli->query("
            SELECT id, canal_id, cliente_id, numero_whatsapp, mensagem, direcao, data_hora, tipo 
            FROM mensagens_comunicacao 
            ORDER BY data_hora DESC 
            LIMIT 10
        ");
        
        if ($result && $result->num_rows > 0) {
            echo "   📊 Últimas 10 mensagens:\n";
            while ($row = $result->fetch_assoc()) {
                echo "      ID: {$row['id']} | Canal: {$row['canal_id']} | Cliente: {$row['cliente_id']}\n";
                echo "      Número: {$row['numero_whatsapp']} | Direção: {$row['direcao']}\n";
                echo "      Mensagem: " . substr($row['mensagem'], 0, 50) . "...\n";
                echo "      Data: {$row['data_hora']}\n";
                echo "      ---\n";
            }
        } else {
            echo "   ❌ Nenhuma mensagem encontrada\n";
        }
        
        // Verificar clientes
        echo "\n   📋 Verificando clientes:\n";
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   📊 Total de clientes: " . $row['total'] . "\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n";

// ===== 2. VERIFICAR CONFIGURAÇÃO DO VPS =====
echo "2. 🖥️ VERIFICANDO CONFIGURAÇÃO DO VPS:\n";

// Verificar se o VPS está enviando para o webhook correto
$vps_config_url = "http://212.85.11.238:3000/webhook/config";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_config_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 VPS Config: HTTP $http_code\n";
if ($http_code == 200) {
    $config = json_decode($response, true);
    if ($config) {
        echo "   🔗 URL configurada: " . ($config['url'] ?? $config['webhook_url'] ?? 'N/A') . "\n";
        
        $expected_url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";
        $configured_url = $config['url'] ?? $config['webhook_url'] ?? '';
        
        if ($configured_url === $expected_url) {
            echo "   ✅ URL correta configurada\n";
        } else {
            echo "   ❌ URL INCORRETA! Esperada: $expected_url\n";
            echo "   ❌ Configurada: $configured_url\n";
        }
    }
} else {
    echo "   ❌ Erro ao obter configuração (HTTP $http_code)\n";
}

echo "\n";

// ===== 3. TESTAR ENVIO DIRETO VIA VPS =====
echo "3. 📤 TESTANDO ENVIO DIRETO VIA VPS:\n";

$vps_send_url = "http://212.85.11.238:3000/send-message";
$test_message = [
    "chatId" => "554796164699@c.us",
    "message" => "🧪 TESTE DIRETO VPS - " . date('Y-m-d H:i:s'),
    "session" => "default"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_send_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_message));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 Envio VPS: HTTP $http_code\n";
echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n";

echo "\n";

// ===== 4. VERIFICAR LOGS DO WEBHOOK =====
echo "4. 📋 VERIFICANDO LOGS DO WEBHOOK:\n";

$log_files = [
    'painel/debug_ajax_whatsapp.log',
    'logs/webhook.log',
    'webhook.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "   ✅ Log encontrado: $log_file\n";
        $lines = file($log_file);
        if ($lines) {
            echo "   📊 Últimas 5 linhas:\n";
            $recent_lines = array_slice($lines, -5);
            foreach ($recent_lines as $line) {
                echo "      " . trim($line) . "\n";
            }
        }
        echo "\n";
    }
}

// ===== 5. VERIFICAR WEBHOOK ATUAL =====
echo "5. 🔗 VERIFICANDO WEBHOOK ATUAL:\n";

$webhook_url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";
$test_data = [
    "from" => "554796164699@c.us",
    "body" => "🧪 TESTE DIAGNÓSTICO - " . date('Y-m-d H:i:s'),
    "timestamp" => time()
];

echo "   🧪 Testando webhook com dados reais...\n";
echo "   📄 Dados: " . json_encode($test_data) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
if ($error) {
    echo "   ❌ Erro cURL: $error\n";
}
echo "   📄 Resposta completa: $response\n";

// Verificar se foi salvo no banco
if ($http_code == 200 || $http_code == 500) {
    echo "\n   🔍 Verificando se foi salvo no banco...\n";
    
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $result = $mysqli->query("
            SELECT id, mensagem, data_hora 
            FROM mensagens_comunicacao 
            WHERE mensagem LIKE '%TESTE DIAGNÓSTICO%' 
            ORDER BY data_hora DESC 
            LIMIT 1
        ");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "   ✅ Mensagem salva no banco: ID {$row['id']}, Data: {$row['data_hora']}\n";
        } else {
            echo "   ❌ Mensagem NÃO foi salva no banco\n";
        }
        
        $mysqli->close();
    } catch (Exception $e) {
        echo "   ❌ Erro ao verificar banco: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ===== 6. DIAGNÓSTICO FINAL =====
echo "6. 🎯 DIAGNÓSTICO FINAL:\n";

echo "   PROBLEMAS POSSÍVEIS:\n";
echo "   1. VPS não está configurado para enviar para o webhook correto\n";
echo "   2. Webhook não está processando mensagens reais\n";
echo "   3. Ana não está enviando respostas de volta\n";
echo "   4. Chat web não está mostrando mensagens\n";
echo "   5. WhatsApp API não está conectado\n";

echo "\n   PRÓXIMAS AÇÕES:\n";
echo "   1. Verificar configuração do webhook no VPS\n";
echo "   2. Configurar webhook corretamente\n";
echo "   3. Testar com mensagem real\n";
echo "   4. Verificar logs do VPS\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?> 