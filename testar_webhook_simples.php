<?php
/**
 * 🧪 TESTE SIMPLES DO WEBHOOK
 * 
 * Este script testa o webhook de forma simples
 */

echo "🧪 TESTE SIMPLES DO WEBHOOK\n";
echo "===========================\n\n";

// ===== 1. TESTAR WEBHOOK COM DADOS SIMULADOS =====
echo "1️⃣ TESTANDO WEBHOOK:\n";
echo "====================\n";

$webhook_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";
echo "🔗 Webhook URL: $webhook_url\n";

// Dados de teste
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699@c.us',
        'text' => 'Teste simples - ' . date('Y-m-d H:i:s'),
        'type' => 'text',
        'session' => 'default'
    ]
];

echo "📤 Enviando dados de teste...\n";
echo "📄 Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$webhook_response = curl_exec($ch);
$webhook_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$webhook_error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do webhook:\n";
echo "   HTTP Code: $webhook_http_code\n";

if ($webhook_error) {
    echo "   ❌ Erro cURL: $webhook_error\n";
} else {
    echo "   ✅ Sem erros cURL\n";
}

if ($webhook_response) {
    echo "   📄 Resposta: " . substr($webhook_response, 0, 500) . "...\n";
    
    $webhook_data = json_decode($webhook_response, true);
    if ($webhook_data) {
        echo "   ✅ JSON válido\n";
        echo "   📋 Dados: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "   ⚠️ JSON inválido\n";
    }
} else {
    echo "   ⚠️ Sem resposta\n";
}

// ===== 2. VERIFICAR LOGS =====
echo "\n2️⃣ VERIFICANDO LOGS:\n";
echo "====================\n";

$log_file = 'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -5);
    
    echo "📄 Logs recentes ($log_file):\n";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "⚠️ Arquivo de log não encontrado: $log_file\n";
}

// ===== 3. VERIFICAR BANCO =====
echo "\n3️⃣ VERIFICANDO BANCO:\n";
echo "=====================\n";

require_once 'config.php';
require_once 'painel/db.php';

if ($mysqli->connect_errno) {
    echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
} else {
    echo "✅ Conexão com banco OK\n";
    
    // Verificar última mensagem
    $sql_ultima = "SELECT id, canal_id, numero_whatsapp, mensagem, data_hora, direcao, status 
                   FROM mensagens_comunicacao 
                   ORDER BY id DESC 
                   LIMIT 1";
    
    $result_ultima = $mysqli->query($sql_ultima);
    
    if ($result_ultima && $result_ultima->num_rows > 0) {
        $ultima = $result_ultima->fetch_assoc();
        echo "✅ Última mensagem encontrada:\n";
        echo "   - ID: {$ultima['id']}\n";
        echo "   - Canal: {$ultima['canal_id']}\n";
        echo "   - Número: {$ultima['numero_whatsapp']}\n";
        echo "   - Direção: {$ultima['direcao']}\n";
        echo "   - Status: {$ultima['status']}\n";
        echo "   - Data: {$ultima['data_hora']}\n";
        echo "   - Mensagem: " . substr($ultima['mensagem'], 0, 50) . "...\n";
    } else {
        echo "⚠️ Nenhuma mensagem encontrada na tabela\n";
    }
}

echo "\n🎯 CONCLUSÃO:\n";
if ($webhook_http_code === 200) {
    echo "✅ Webhook está funcionando\n";
} else {
    echo "❌ Webhook não está respondendo corretamente\n";
}

if ($result_ultima && $result_ultima->num_rows > 0) {
    echo "✅ Mensagens estão sendo salvas no banco\n";
} else {
    echo "❌ Mensagens não estão sendo salvas no banco\n";
}
?> 