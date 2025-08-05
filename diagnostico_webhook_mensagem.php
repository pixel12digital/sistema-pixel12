<?php
/**
 * 🔍 DIAGNÓSTICO WEBHOOK MENSAGEM
 * 
 * Script para diagnosticar problemas no webhook e salvamento de mensagens
 */

echo "🔍 DIAGNÓSTICO WEBHOOK MENSAGEM\n";
echo "===============================\n\n";

require_once __DIR__ . '/config.php';

// Função para conectar ao banco
function conectarBanco() {
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_errno) {
            throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
        }
        
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        echo "❌ Erro de conexão com banco: " . $e->getMessage() . "\n";
        return null;
    }
}

$mysqli = conectarBanco();
if (!$mysqli) {
    exit(1);
}

// 1. VERIFICAR ESTRUTURA DA TABELA
echo "1️⃣ VERIFICANDO ESTRUTURA DA TABELA\n";
echo "===================================\n";

$estrutura = $mysqli->query("DESCRIBE mensagens_comunicacao");
if ($estrutura) {
    echo "✅ Estrutura da tabela mensagens_comunicacao:\n";
    while ($coluna = $estrutura->fetch_assoc()) {
        echo "   - {$coluna['Field']}: {$coluna['Type']} " . ($coluna['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}

echo "\n";

// 2. VERIFICAR PERMISSÕES
echo "2️⃣ VERIFICANDO PERMISSÕES\n";
echo "==========================\n";

// Testar inserção simples
$teste_sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
              VALUES (36, 4296, '554796164699@c.us', 'TESTE DIAGNÓSTICO', 'texto', NOW(), 'recebido', 'recebido')";

if ($mysqli->query($teste_sql)) {
    $teste_id = $mysqli->insert_id;
    echo "✅ Inserção de teste realizada com sucesso - ID: $teste_id\n";
    
    // Remover teste
    $mysqli->query("DELETE FROM mensagens_comunicacao WHERE id = $teste_id");
    echo "✅ Registro de teste removido\n";
} else {
    echo "❌ Erro na inserção de teste: " . $mysqli->error . "\n";
}

echo "\n";

// 3. VERIFICAR WEBHOOK
echo "3️⃣ VERIFICANDO WEBHOOK\n";
echo "======================\n";

// Testar webhook diretamente
$dados_teste = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699@c.us",
        "to" => "554797146908@c.us",
        "text" => "TESTE DIAGNÓSTICO WEBHOOK",
        "type" => "text",
        "session" => "default",
        "timestamp" => time()
    ]
];

echo "🔄 Testando webhook...\n";

$ch = curl_init("https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php");
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

echo "📥 Resposta do webhook:\n";
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

$mensagem_teste = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' AND mensagem LIKE '%TESTE DIAGNÓSTICO WEBHOOK%' ORDER BY id DESC LIMIT 1");

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
    $ultimas_linhas = array_slice($log_content, -10);
    echo "📋 Últimas 10 linhas do log:\n";
    foreach ($ultimas_linhas as $linha) {
        echo "   " . trim($linha) . "\n";
    }
} else {
    echo "⚠️ Arquivo de log não encontrado: $log_file\n";
    
    // Verificar se o diretório existe
    $log_dir = 'logs';
    if (is_dir($log_dir)) {
        echo "✅ Diretório logs existe\n";
        $files = scandir($log_dir);
        echo "📁 Arquivos no diretório logs:\n";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "   - $file\n";
            }
        }
    } else {
        echo "❌ Diretório logs não existe\n";
    }
}

echo "\n";

// 6. VERIFICAR CONFIGURAÇÕES
echo "6️⃣ VERIFICANDO CONFIGURAÇÕES\n";
echo "============================\n";

echo "🔧 Configurações atuais:\n";
echo "   - DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . "\n";
echo "   - DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . "\n";
echo "   - DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : 'NÃO DEFINIDO') . "\n";
echo "   - WHATSAPP_ROBOT_URL: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'NÃO DEFINIDO') . "\n";

echo "\n";

// 7. VERIFICAR CANAIS
echo "7️⃣ VERIFICANDO CANAIS\n";
echo "=====================\n";

$canais = $mysqli->query("SELECT id, nome_exibicao, porta, status FROM canais_comunicacao WHERE porta IN (3000, 3001) ORDER BY porta");
if ($canais && $canais->num_rows > 0) {
    echo "✅ Canais encontrados:\n";
    while ($canal = $canais->fetch_assoc()) {
        echo "   - ID {$canal['id']}: {$canal['nome_exibicao']} (Porta: {$canal['porta']}, Status: {$canal['status']})\n";
    }
} else {
    echo "❌ Nenhum canal encontrado\n";
}

echo "\n";

// 8. RESUMO E SUGESTÕES
echo "8️⃣ RESUMO E SUGESTÕES\n";
echo "=====================\n";

$problemas = [];

if ($http_code !== 200) {
    $problemas[] = "❌ Webhook retornou erro HTTP $http_code";
}

if (!$mensagem_teste || $mensagem_teste->num_rows === 0) {
    $problemas[] = "❌ Mensagem não foi salva no banco de dados";
}

if (!file_exists($log_file)) {
    $problemas[] = "⚠️ Arquivo de log não encontrado";
}

if (empty($problemas)) {
    echo "✅ Todos os testes passaram! Sistema funcionando corretamente.\n";
} else {
    echo "⚠️ Problemas identificados:\n";
    foreach ($problemas as $problema) {
        echo "   $problema\n";
    }
    
    echo "\n🔧 SUGESTÕES DE CORREÇÃO:\n";
    
    if ($http_code !== 200) {
        echo "   - Verificar configuração do webhook\n";
        echo "   - Verificar logs de erro do servidor\n";
    }
    
    if (!$mensagem_teste || $mensagem_teste->num_rows === 0) {
        echo "   - Verificar permissões de escrita no banco\n";
        echo "   - Verificar estrutura da tabela mensagens_comunicacao\n";
        echo "   - Verificar se há triggers ou constraints bloqueando\n";
    }
    
    if (!file_exists($log_file)) {
        echo "   - Criar diretório logs se não existir\n";
        echo "   - Verificar permissões de escrita no diretório\n";
    }
}

echo "\n✅ Diagnóstico concluído!\n";

$mysqli->close();
?> 