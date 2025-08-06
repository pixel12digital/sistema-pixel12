<?php
/**
 * 🧪 TESTAR WEBHOOK COM DADOS REAIS DO WHATSAPP
 * 
 * Este script simula dados reais do WhatsApp para testar o webhook
 */

echo "🧪 TESTANDO WEBHOOK COM DADOS REAIS DO WHATSAPP\n";
echo "===============================================\n\n";

$webhook_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";

// ===== 1. TESTE COM DADOS REAIS DO WHATSAPP =====
echo "1️⃣ TESTE COM DADOS REAIS DO WHATSAPP:\n";
echo "=====================================\n";

// Dados reais do WhatsApp (formato que realmente chega)
$dados_reais = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699@c.us',
        'to' => '554797146908@c.us',
        'text' => 'Teste real do WhatsApp - ' . date('Y-m-d H:i:s'),
        'type' => 'text',
        'session' => 'default',
        'timestamp' => time()
    ]
];

echo "📤 Enviando dados reais do WhatsApp...\n";
echo "📄 Dados: " . json_encode($dados_reais, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_reais));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do webhook:\n";
echo "   HTTP Code: $http_code\n";

if ($curl_error) {
    echo "   ❌ Erro cURL: $curl_error\n";
} else {
    echo "   ✅ Sem erros cURL\n";
}

if ($response) {
    echo "   📄 Resposta: " . substr($response, 0, 500) . "...\n";
    
    $response_data = json_decode($response, true);
    if ($response_data) {
        echo "   ✅ JSON válido\n";
        if (isset($response_data['success']) && $response_data['success']) {
            echo "   🎉 Webhook funcionando corretamente!\n";
            if (isset($response_data['ana_response'])) {
                echo "   🤖 Ana respondeu: " . substr($response_data['ana_response'], 0, 100) . "...\n";
            }
        } else {
            echo "   ⚠️ Webhook retornou erro\n";
        }
    } else {
        echo "   ⚠️ JSON inválido\n";
    }
} else {
    echo "   ⚠️ Sem resposta\n";
}

echo "\n";

// ===== 2. TESTE COM DIFERENTES FORMATOS =====
echo "2️⃣ TESTE COM DIFERENTES FORMATOS:\n";
echo "==================================\n";

// Formato alternativo 1
$formato_1 = [
    'from' => '554796164699@c.us',
    'body' => 'Teste formato 1 - ' . date('Y-m-d H:i:s'),
    'type' => 'text'
];

echo "📤 Testando formato 1...\n";
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($formato_1));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response_1 = curl_exec($ch);
$http_code_1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code_1\n";
if ($http_code_1 === 200) {
    echo "   ✅ Formato 1 funcionando\n";
} else {
    echo "   ❌ Formato 1 não funcionou\n";
}

// Formato alternativo 2
$formato_2 = [
    'number' => '554796164699@c.us',
    'message' => 'Teste formato 2 - ' . date('Y-m-d H:i:s'),
    'messageType' => 'text'
];

echo "📤 Testando formato 2...\n";
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($formato_2));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response_2 = curl_exec($ch);
$http_code_2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code_2\n";
if ($http_code_2 === 200) {
    echo "   ✅ Formato 2 funcionando\n";
} else {
    echo "   ❌ Formato 2 não funcionou\n";
}

echo "\n";

// ===== 3. VERIFICAR LOGS =====
echo "3️⃣ VERIFICANDO LOGS:\n";
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

echo "\n";

// ===== 4. VERIFICAR BANCO =====
echo "4️⃣ VERIFICANDO BANCO:\n";
echo "=====================\n";

require_once 'config.php';
require_once 'painel/db.php';

if ($mysqli->connect_errno) {
    echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
} else {
    echo "✅ Conexão com banco OK\n";
    
    // Verificar última mensagem
    $sql_ultima = "SELECT id, canal_id, cliente_id, numero_whatsapp, mensagem, data_hora, direcao, status 
                   FROM mensagens_comunicacao 
                   ORDER BY id DESC 
                   LIMIT 1";
    
    $result_ultima = $mysqli->query($sql_ultima);
    
    if ($result_ultima && $result_ultima->num_rows > 0) {
        $ultima = $result_ultima->fetch_assoc();
        echo "✅ Última mensagem encontrada:\n";
        echo "   - ID: {$ultima['id']}\n";
        echo "   - Canal: {$ultima['canal_id']}\n";
        echo "   - Cliente: {$ultima['cliente_id']}\n";
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
echo "=============\n";

if ($http_code === 200) {
    echo "✅ WEBHOOK ESTÁ FUNCIONANDO!\n";
    echo "🎉 Os dados estão sendo processados corretamente.\n";
    echo "\n📋 SE AS MENSAGENS REAIS NÃO CHEGAM:\n";
    echo "1. Verificar se o WhatsApp está enviando para o webhook correto\n";
    echo "2. Verificar se há problemas de conectividade\n";
    echo "3. Verificar se há erros no servidor VPS\n";
    echo "4. Verificar se há problemas de firewall\n";
} else {
    echo "❌ WEBHOOK NÃO ESTÁ FUNCIONANDO!\n";
    echo "🔧 Verificar configuração do webhook\n";
    echo "🔧 Verificar se o servidor está online\n";
    echo "🔧 Verificar se há erros no código\n";
}
?> 