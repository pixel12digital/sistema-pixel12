<?php
/**
 * 🔍 DIAGNÓSTICO DE MENSAGENS NÃO REGISTRADAS
 * 
 * Este script diagnostica por que as mensagens não estão sendo registradas no chat
 */

echo "🔍 DIAGNÓSTICO DE MENSAGENS NÃO REGISTRADAS\n";
echo "===========================================\n\n";

// ===== 1. VERIFICAR CONFIGURAÇÃO DO BANCO =====
echo "1️⃣ VERIFICANDO CONFIGURAÇÃO DO BANCO:\n";
echo "=====================================\n";

require_once 'config.php';
require_once 'painel/db.php';

if ($mysqli->connect_errno) {
    echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
    exit;
} else {
    echo "✅ Conexão com banco OK\n";
}

// ===== 2. VERIFICAR ESTRUTURA DA TABELA =====
echo "\n2️⃣ VERIFICANDO ESTRUTURA DA TABELA:\n";
echo "====================================\n";

$sql_estrutura = "DESCRIBE mensagens_comunicacao";
$result_estrutura = $mysqli->query($sql_estrutura);

if ($result_estrutura) {
    echo "✅ Tabela mensagens_comunicacao existe\n";
    echo "📋 Campos:\n";
    while ($row = $result_estrutura->fetch_assoc()) {
        echo "   - {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']}\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}

// ===== 3. VERIFICAR ÚLTIMAS MENSAGENS =====
echo "\n3️⃣ VERIFICANDO ÚLTIMAS MENSAGENS:\n";
echo "==================================\n";

$sql_ultimas = "SELECT id, canal_id, numero_whatsapp, mensagem, data_hora, direcao, status 
                FROM mensagens_comunicacao 
                ORDER BY id DESC 
                LIMIT 5";

$result_ultimas = $mysqli->query($sql_ultimas);

if ($result_ultimas && $result_ultimas->num_rows > 0) {
    echo "✅ Últimas mensagens encontradas:\n";
    while ($row = $result_ultimas->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Canal: {$row['canal_id']} | Número: {$row['numero_whatsapp']} | Direção: {$row['direcao']} | Status: {$row['status']} | Data: {$row['data_hora']}\n";
        echo "     Mensagem: " . substr($row['mensagem'], 0, 50) . "...\n";
    }
} else {
    echo "⚠️ Nenhuma mensagem encontrada na tabela\n";
}

// ===== 4. TESTAR INSERÇÃO DE MENSAGEM =====
echo "\n4️⃣ TESTANDO INSERÇÃO DE MENSAGEM:\n";
echo "==================================\n";

$test_numero = "554796164699@c.us";
$test_mensagem = "Teste de diagnóstico - " . date('Y-m-d H:i:s');
$test_canal_id = 36;
$test_data_hora = date('Y-m-d H:i:s');

$sql_teste = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
              VALUES (?, ?, ?, 'texto', ?, 'recebido', 'recebido')";

$stmt = $mysqli->prepare($sql_teste);
if ($stmt) {
    $stmt->bind_param("isss", $test_canal_id, $test_numero, $test_mensagem, $test_data_hora);
    
    if ($stmt->execute()) {
        $test_id = $mysqli->insert_id;
        echo "✅ Teste de inserção OK - ID: $test_id\n";
        
        // Remover mensagem de teste
        $mysqli->query("DELETE FROM mensagens_comunicacao WHERE id = $test_id");
        echo "   🧹 Mensagem de teste removida\n";
    } else {
        echo "❌ Erro no teste de inserção: " . $stmt->error . "\n";
    }
    $stmt->close();
} else {
    echo "❌ Erro ao preparar statement: " . $mysqli->error . "\n";
}

// ===== 5. VERIFICAR WEBHOOK =====
echo "\n5️⃣ VERIFICANDO WEBHOOK:\n";
echo "========================\n";

$webhook_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";
echo "🔗 Webhook URL: $webhook_url\n";

// Testar webhook com dados simulados
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699@c.us',
        'text' => 'Teste de diagnóstico webhook - ' . date('Y-m-d H:i:s'),
        'type' => 'text',
        'session' => 'default'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$webhook_response = curl_exec($ch);
$webhook_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$webhook_error = curl_error($ch);
curl_close($ch);

if ($webhook_error) {
    echo "❌ Erro cURL webhook: $webhook_error\n";
} elseif ($webhook_http_code === 200) {
    $webhook_data = json_decode($webhook_response, true);
    if ($webhook_data) {
        echo "✅ Webhook respondeu corretamente\n";
        echo "📄 Resposta: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "⚠️ Webhook respondeu mas JSON inválido\n";
        echo "📄 Resposta bruta: " . substr($webhook_response, 0, 200) . "...\n";
    }
} else {
    echo "❌ Webhook não respondeu - HTTP: $webhook_http_code\n";
    echo "📄 Resposta: " . substr($webhook_response, 0, 200) . "...\n";
}

// ===== 6. VERIFICAR LOGS =====
echo "\n6️⃣ VERIFICANDO LOGS:\n";
echo "====================\n";

$log_file = 'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -3);
    
    echo "📄 Logs recentes ($log_file):\n";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "⚠️ Arquivo de log não encontrado: $log_file\n";
}

// ===== 7. VERIFICAR CONFIGURAÇÃO DEBUG =====
echo "\n7️⃣ VERIFICANDO CONFIGURAÇÃO DEBUG:\n";
echo "===================================\n";

if (defined('DEBUG_MODE')) {
    echo "✅ DEBUG_MODE definido: " . (DEBUG_MODE ? 'ATIVO' : 'INATIVO') . "\n";
} else {
    echo "⚠️ DEBUG_MODE não definido\n";
}

// ===== 8. VERIFICAR CANAIS =====
echo "\n8️⃣ VERIFICANDO CANAIS:\n";
echo "======================\n";

$sql_canais = "SELECT id, nome, porta, status FROM canais_comunicacao WHERE id IN (36, 37)";
$result_canais = $mysqli->query($sql_canais);

if ($result_canais && $result_canais->num_rows > 0) {
    echo "✅ Canais encontrados:\n";
    while ($row = $result_canais->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Nome: {$row['nome']} | Porta: {$row['porta']} | Status: {$row['status']}\n";
    }
} else {
    echo "⚠️ Canais não encontrados\n";
}

// ===== 9. DIAGNÓSTICO FINAL =====
echo "\n9️⃣ DIAGNÓSTICO FINAL:\n";
echo "=====================\n";

$problemas = [];

// Verificar se webhook está funcionando
if ($webhook_http_code !== 200) {
    $problemas[] = "Webhook não está respondendo (HTTP: $webhook_http_code)";
}

// Verificar se tabela existe e tem estrutura correta
if (!$result_estrutura) {
    $problemas[] = "Tabela mensagens_comunicacao não existe ou não está acessível";
}

// Verificar se há mensagens recentes
if (!$result_ultimas || $result_ultimas->num_rows === 0) {
    $problemas[] = "Nenhuma mensagem encontrada na tabela";
}

if (empty($problemas)) {
    echo "✅ Sistema parece estar funcionando corretamente\n";
    echo "🔍 Verificar se as mensagens estão chegando ao webhook\n";
    echo "🔍 Verificar se o WhatsApp está enviando as mensagens corretamente\n";
} else {
    echo "❌ Problemas detectados:\n";
    foreach ($problemas as $problema) {
        echo "   - $problema\n";
    }
}

echo "\n🎯 RECOMENDAÇÕES:\n";
echo "1. Verificar se o WhatsApp está enviando mensagens para o webhook\n";
echo "2. Verificar logs do servidor para erros específicos\n";
echo "3. Testar webhook manualmente com dados simulados\n";
echo "4. Verificar se a URL do webhook está correta no WhatsApp API\n";
?> 