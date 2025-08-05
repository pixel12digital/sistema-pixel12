<?php
/**
 * 🧪 TESTE WEBHOOK LOCAL
 * 
 * Script para testar o webhook localmente e identificar problemas
 */

echo "🧪 TESTE WEBHOOK LOCAL\n";
echo "=====================\n\n";

require_once __DIR__ . '/config.php';

// 1. VERIFICAR SE O ARQUIVO WEBHOOK EXISTE
echo "1️⃣ VERIFICANDO ARQUIVO WEBHOOK\n";
echo "===============================\n";

$webhook_file = 'webhook_sem_redirect/webhook.php';
if (file_exists($webhook_file)) {
    echo "✅ Arquivo webhook encontrado: $webhook_file\n";
    $file_size = filesize($webhook_file);
    echo "   - Tamanho: " . number_format($file_size) . " bytes\n";
} else {
    echo "❌ Arquivo webhook não encontrado: $webhook_file\n";
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

// 3. TESTAR WEBHOOK LOCALMENTE
echo "3️⃣ TESTANDO WEBHOOK LOCALMENTE\n";
echo "==============================\n";

// Simular dados de entrada
$dados_teste = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699@c.us",
        "to" => "554797146908@c.us",
        "text" => "TESTE LOCAL WEBHOOK",
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

echo "\n🔄 Testando webhook...\n";

// Fazer requisição local
$ch = curl_init("http://localhost/loja-virtual-revenda/webhook_sem_redirect/webhook.php");
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

echo "📥 Resposta do webhook local:\n";
echo "   HTTP Code: $http_code\n";
echo "   Response: $response\n";

if ($curl_error) {
    echo "   ❌ Erro cURL: $curl_error\n";
}

echo "\n";

// 4. VERIFICAR LOGS LOCAIS
echo "4️⃣ VERIFICANDO LOGS LOCAIS\n";
echo "==========================\n";

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
    
    // Tentar criar o arquivo de log
    $log_dir = 'logs';
    if (!is_dir($log_dir)) {
        if (mkdir($log_dir, 0755, true)) {
            echo "✅ Diretório logs criado\n";
        } else {
            echo "❌ Erro ao criar diretório logs\n";
        }
    }
    
    // Tentar criar arquivo de log
    $log_content = date('Y-m-d H:i:s') . " [LOCAL] - " . json_encode($dados_teste) . "\n";
    if (file_put_contents($log_file, $log_content, FILE_APPEND)) {
        echo "✅ Arquivo de log criado: $log_file\n";
    } else {
        echo "❌ Erro ao criar arquivo de log\n";
    }
}

echo "\n";

// 5. VERIFICAR CONFIGURAÇÕES
echo "5️⃣ VERIFICANDO CONFIGURAÇÕES\n";
echo "============================\n";

echo "🔧 Configurações atuais:\n";
echo "   - Ambiente: " . ($is_local ? 'LOCAL' : 'PRODUÇÃO') . "\n";
echo "   - DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . "\n";
echo "   - DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . "\n";
echo "   - DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : 'NÃO DEFINIDO') . "\n";

echo "\n";

// 6. TESTAR CONEXÃO COM BANCO
echo "6️⃣ TESTANDO CONEXÃO COM BANCO\n";
echo "=============================\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_errno) {
        throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
    }
    
    echo "✅ Conexão com banco OK\n";
    echo "   - Host: " . DB_HOST . "\n";
    echo "   - Database: " . DB_NAME . "\n";
    echo "   - Versão MySQL: " . $mysqli->server_info . "\n";
    
    // Testar query simples
    $teste_query = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao");
    if ($teste_query) {
        $total = $teste_query->fetch_assoc()['total'];
        echo "   - Total de mensagens: $total\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Erro de conexão com banco: " . $e->getMessage() . "\n";
}

echo "\n";

// 7. RESUMO
echo "7️⃣ RESUMO\n";
echo "=========\n";

$problemas = [];

if ($http_code !== 200) {
    $problemas[] = "❌ Webhook retornou erro HTTP $http_code";
}

if ($curl_error) {
    $problemas[] = "❌ Erro cURL: $curl_error";
}

if (!file_exists($log_file)) {
    $problemas[] = "⚠️ Arquivo de log não encontrado";
}

if (empty($problemas)) {
    echo "✅ Todos os testes passaram! Webhook funcionando localmente.\n";
} else {
    echo "⚠️ Problemas identificados:\n";
    foreach ($problemas as $problema) {
        echo "   $problema\n";
    }
}

echo "\n✅ Teste local concluído!\n";
?> 