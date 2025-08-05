<?php
/**
 * 🧪 TESTAR WEBHOOK DIRETO
 * 
 * Este script testa o webhook diretamente para identificar por que
 * a mensagem não está sendo salva
 */

echo "🧪 TESTAR WEBHOOK DIRETO\n";
echo "========================\n\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

// 1. PREPARAR DADOS DE TESTE
echo "1️⃣ PREPARANDO DADOS DE TESTE\n";
echo "============================\n";

$dados_teste = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699@c.us",
        "to" => "554797146908@c.us",
        "text" => "TESTE WEBHOOK DIRETO - " . date('Y-m-d H:i:s'),
        "type" => "text",
        "session" => "default",
        "timestamp" => time()
    ]
];

echo "📤 Dados de teste:\n";
echo "   - Event: {$dados_teste['event']}\n";
echo "   - From: {$dados_teste['data']['from']}\n";
echo "   - Text: {$dados_teste['data']['text']}\n";
echo "   - Type: {$dados_teste['data']['type']}\n";

echo "\n";

// 2. SIMULAR PROCESSAMENTO DO WEBHOOK
echo "2️⃣ SIMULANDO PROCESSAMENTO DO WEBHOOK\n";
echo "=====================================\n";

// Extrair informações (mesmo código do webhook)
$message = $dados_teste['data'];
$numero = $message['from'] ?? '';
$texto = $message['text'] ?? '';
$tipo = $message['type'] ?? 'text';
$data_hora = date('Y-m-d H:i:s');

echo "🔍 Variáveis extraídas:\n";
echo "   - Numero: $numero\n";
echo "   - Texto: $texto\n";
echo "   - Tipo: $tipo\n";
echo "   - Data/Hora: $data_hora\n";

// Buscar cliente pelo número
$numero_limpo = preg_replace('/\D/', '', $numero);
echo "   - Número limpo: $numero_limpo\n";

$sql = "SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
echo "   - SQL cliente: $sql\n";

$result = $mysqli->query($sql);
$cliente_id = null;

if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    $cliente_id = $cliente['id'];
    echo "✅ Cliente encontrado: {$cliente['nome']} (ID: $cliente_id)\n";
} else {
    echo "⚠️ Cliente não encontrado\n";
}

// Identificar canal
$canal_id = 36; // Canal 3000 (Financeiro)
$canal_nome = 'Financeiro';

echo "   - Canal ID: $canal_id ($canal_nome)\n";

// 3. TESTAR INSERÇÃO (mesmo código do webhook)
echo "\n3️⃣ TESTANDO INSERÇÃO\n";
echo "====================\n";

$texto_escaped = $mysqli->real_escape_string($texto);
$tipo_escaped = $mysqli->real_escape_string($tipo);

$sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
        VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '" . $mysqli->real_escape_string($numero) . "', '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";

echo "🔍 SQL: $sql\n";

if ($mysqli->query($sql)) {
    $mensagem_id = $mysqli->insert_id;
    echo "✅ Mensagem salva com sucesso - ID: $mensagem_id\n";
    
    // Verificar se foi salva
    $check = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE id = $mensagem_id");
    if ($check && $check->num_rows > 0) {
        $msg = $check->fetch_assoc();
        echo "✅ Mensagem confirmada no banco:\n";
        echo "   ID: {$msg['id']}\n";
        echo "   Canal: {$msg['canal_id']}\n";
        echo "   Cliente: {$msg['cliente_id']}\n";
        echo "   Número: {$msg['numero_whatsapp']}\n";
        echo "   Mensagem: {$msg['mensagem']}\n";
        echo "   Data/Hora: {$msg['data_hora']}\n";
        echo "   Direção: {$msg['direcao']}\n";
        echo "   Status: {$msg['status']}\n";
    } else {
        echo "❌ Mensagem não encontrada após inserção\n";
    }
} else {
    echo "❌ Erro ao inserir mensagem: " . $mysqli->error . "\n";
}

echo "\n";

// 4. VERIFICAR SE HÁ PROBLEMAS NO WEBHOOK
echo "4️⃣ VERIFICANDO PROBLEMAS NO WEBHOOK\n";
echo "===================================\n";

// Verificar se o webhook está sendo chamado
$webhook_file = 'webhook_sem_redirect/webhook.php';
if (file_exists($webhook_file)) {
    echo "✅ Arquivo webhook existe: $webhook_file\n";
    
    // Verificar se há problemas de permissão
    if (is_readable($webhook_file)) {
        echo "✅ Arquivo webhook é legível\n";
    } else {
        echo "❌ Arquivo webhook não é legível\n";
    }
    
    // Verificar se há problemas de sintaxe
    $output = shell_exec("php -l $webhook_file 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ Sintaxe do webhook OK\n";
    } else {
        echo "❌ Erro de sintaxe no webhook:\n$output\n";
    }
} else {
    echo "❌ Arquivo webhook não encontrado\n";
}

// 5. TESTAR CHAMADA REAL DO WEBHOOK
echo "\n5️⃣ TESTANDO CHAMADA REAL DO WEBHOOK\n";
echo "====================================\n";

echo "🔄 Fazendo chamada real para o webhook...\n";

$ch = curl_init("https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "User-Agent: WhatsApp/2.0",
    "X-Forwarded-For: 212.85.11.238"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do webhook:\n";
echo "   HTTP Code: $http_code\n";
echo "   Response: $response\n";

if ($curl_error) {
    echo "   ❌ Erro cURL: $curl_error\n";
}

echo "\n";

// 6. VERIFICAR SE A MENSAGEM FOI SALVA PELO WEBHOOK
echo "6️⃣ VERIFICANDO SE MENSAGEM FOI SALVA PELO WEBHOOK\n";
echo "================================================\n";

// Aguardar um pouco
sleep(2);

// Verificar se a mensagem foi salva
$mensagem_webhook = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' AND mensagem LIKE '%TESTE WEBHOOK DIRETO%' ORDER BY id DESC LIMIT 1");

if ($mensagem_webhook && $mensagem_webhook->num_rows > 0) {
    $msg = $mensagem_webhook->fetch_assoc();
    echo "✅ Mensagem salva pelo webhook!\n";
    echo "   - ID: {$msg['id']}\n";
    echo "   - Mensagem: {$msg['mensagem']}\n";
    echo "   - Data: {$msg['data_hora']}\n";
} else {
    echo "❌ Mensagem não foi salva pelo webhook\n";
    
    // Verificar últimas mensagens
    $ultimas = $mysqli->query("SELECT id, mensagem, data_hora FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' ORDER BY id DESC LIMIT 3");
    if ($ultimas && $ultimas->num_rows > 0) {
        echo "📋 Últimas mensagens do número:\n";
        while ($msg = $ultimas->fetch_assoc()) {
            echo "   - ID {$msg['id']}: {$msg['mensagem']} ({$msg['data_hora']})\n";
        }
    }
}

echo "\n🎯 TESTE CONCLUÍDO!\n";
?> 