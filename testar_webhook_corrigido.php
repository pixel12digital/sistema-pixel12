<?php
/**
 * 🧪 TESTE WEBHOOK CORRIGIDO
 * 
 * Script para testar a versão corrigida do webhook
 */

echo "🧪 TESTE WEBHOOK CORRIGIDO\n";
echo "==========================\n\n";

require_once __DIR__ . '/config.php';

// 1. VERIFICAR ARQUIVO CORRIGIDO
echo "1️⃣ VERIFICANDO ARQUIVO CORRIGIDO\n";
echo "===============================\n";

$webhook_file = 'webhook_sem_redirect/webhook_corrigido.php';
if (file_exists($webhook_file)) {
    echo "✅ Arquivo webhook corrigido encontrado: $webhook_file\n";
    $file_size = filesize($webhook_file);
    echo "   - Tamanho: " . number_format($file_size) . " bytes\n";
} else {
    echo "❌ Arquivo webhook corrigido não encontrado: $webhook_file\n";
    exit(1);
}

echo "\n";

// 2. VERIFICAR SINTAXE PHP
echo "2️⃣ VERIFICANDO SINTAXE PHP\n";
echo "==========================\n";

$syntax_check = shell_exec("php -l $webhook_file 2>&1");
if (strpos($syntax_check, 'No syntax errors') !== false) {
    echo "✅ Sintaxe PHP OK\n";
} else {
    echo "❌ Erro de sintaxe PHP:\n";
    echo $syntax_check;
}

echo "\n";

// 3. TESTAR WEBHOOK CORRIGIDO
echo "3️⃣ TESTANDO WEBHOOK CORRIGIDO\n";
echo "=============================\n";

// Simular dados de entrada
$dados_teste = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699@c.us",
        "to" => "554797146908@c.us",
        "text" => "TESTE WEBHOOK CORRIGIDO - " . date('Y-m-d H:i:s'),
        "type" => "text",
        "session" => "default",
        "timestamp" => time()
    ]
];

echo "📤 Dados de teste:\n";
echo "   - De: {$dados_teste['data']['from']}\n";
echo "   - Para: {$dados_teste['data']['to']}\n";
echo "   - Mensagem: {$dados_teste['data']['text']}\n";
echo "   - Tipo: {$dados_teste['data']['type']}\n";

echo "\n🔄 Testando webhook corrigido...\n";

// Fazer requisição para o webhook corrigido
$ch = curl_init("https://app.pixel12digital.com.br/webhook_sem_redirect/webhook_corrigido.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "User-Agent: WhatsApp/2.0"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do webhook corrigido:\n";
echo "   HTTP Code: $http_code\n";
echo "   Response: $response\n";

if ($curl_error) {
    echo "   ❌ Erro cURL: $curl_error\n";
}

echo "\n";

// 4. VERIFICAR SE MENSAGEM FOI SALVA
echo "4️⃣ VERIFICANDO SE MENSAGEM FOI SALVA\n";
echo "====================================\n";

sleep(2);

// Conectar ao banco
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_errno) {
        throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    
    // Verificar se a mensagem foi salva
    $mensagem_teste = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' AND mensagem LIKE '%TESTE WEBHOOK CORRIGIDO%' ORDER BY id DESC LIMIT 1");
    
    if ($mensagem_teste && $mensagem_teste->num_rows > 0) {
        $msg = $mensagem_teste->fetch_assoc();
        echo "✅ Mensagem de teste salva com sucesso!\n";
        echo "   - ID: {$msg['id']}\n";
        echo "   - Canal: {$msg['canal_id']}\n";
        echo "   - Cliente: {$msg['cliente_id']}\n";
        echo "   - Número: {$msg['numero_whatsapp']}\n";
        echo "   - Mensagem: {$msg['mensagem']}\n";
        echo "   - Data: {$msg['data_hora']}\n";
        echo "   - Direção: {$msg['direcao']}\n";
        echo "   - Status: {$msg['status']}\n";
    } else {
        echo "❌ Mensagem de teste não foi salva\n";
        
        // Verificar últimas mensagens
        $ultimas = $mysqli->query("SELECT id, mensagem, data_hora FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' ORDER BY id DESC LIMIT 3");
        if ($ultimas && $ultimas->num_rows > 0) {
            echo "📋 Últimas mensagens do usuário:\n";
            while ($ultima = $ultimas->fetch_assoc()) {
                echo "   - ID {$ultima['id']}: {$ultima['mensagem']} ({$ultima['data_hora']})\n";
            }
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Erro de conexão com banco: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. VERIFICAR LOGS
echo "5️⃣ VERIFICANDO LOGS\n";
echo "===================\n";

$log_file = 'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    echo "✅ Arquivo de log encontrado: $log_file\n";
    $log_size = filesize($log_file);
    echo "   - Tamanho: " . number_format($log_size) . " bytes\n";
    
    // Ler últimas linhas
    $log_content = file($log_file);
    $ultimas_linhas = array_slice($log_content, -5);
    echo "📋 Últimas 5 linhas do log:\n";
    foreach ($ultimas_linhas as $linha) {
        echo "   " . trim($linha) . "\n";
    }
} else {
    echo "⚠️ Arquivo de log não encontrado: $log_file\n";
}

echo "\n";

// 6. RESUMO
echo "6️⃣ RESUMO\n";
echo "=========\n";

$problemas = [];

if ($http_code !== 200) {
    $problemas[] = "❌ Webhook retornou erro HTTP $http_code";
}

if ($curl_error) {
    $problemas[] = "❌ Erro cURL: $curl_error";
}

if (!$mensagem_teste || $mensagem_teste->num_rows === 0) {
    $problemas[] = "❌ Mensagem não foi salva no banco de dados";
}

if (empty($problemas)) {
    echo "✅ Todos os testes passaram! Webhook corrigido funcionando.\n";
} else {
    echo "⚠️ Problemas identificados:\n";
    foreach ($problemas as $problema) {
        echo "   $problema\n";
    }
}

echo "\n✅ Teste webhook corrigido concluído!\n";
?> 