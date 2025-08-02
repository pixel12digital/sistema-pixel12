<?php
echo "🔍 VERIFICANDO ERROS NO WEBHOOK\n";
echo "===============================\n\n";

// Verificar se o arquivo existe e tem permissões
$webhook_file = 'painel/receber_mensagem_ana_local.php';

echo "📁 Verificando arquivo webhook...\n";
if (file_exists($webhook_file)) {
    echo "✅ Arquivo existe: $webhook_file\n";
    echo "📏 Tamanho: " . filesize($webhook_file) . " bytes\n";
    echo "🔐 Permissões: " . substr(sprintf('%o', fileperms($webhook_file)), -4) . "\n";
} else {
    echo "❌ Arquivo não encontrado: $webhook_file\n";
}
echo "\n";

// Verificar dependências
echo "📦 Verificando dependências...\n";
$dependencies = [
    'config.php',
    'painel/db.php',
    'painel/cache_invalidator.php',
    'painel/api/integrador_ana_local.php'
];

foreach ($dependencies as $dep) {
    if (file_exists($dep)) {
        echo "✅ $dep\n";
    } else {
        echo "❌ $dep - FALTANDO!\n";
    }
}
echo "\n";

// Testar sintaxe PHP
echo "🔍 Testando sintaxe PHP...\n";
$syntax_check = shell_exec("php -l $webhook_file 2>&1");
if (strpos($syntax_check, 'No syntax errors') !== false) {
    echo "✅ Sintaxe PHP OK\n";
} else {
    echo "❌ Erro de sintaxe:\n";
    echo $syntax_check . "\n";
}
echo "\n";

// Verificar banco de dados
echo "🗄️ Testando conexão com banco...\n";
try {
    require_once 'painel/db.php';
    if (isset($mysqli) && $mysqli->ping()) {
        echo "✅ Conexão com banco OK\n";
    } else {
        echo "❌ Problema na conexão com banco\n";
    }
} catch (Exception $e) {
    echo "❌ Erro no banco: " . $e->getMessage() . "\n";
}
echo "\n";

// Testar Ana AI
echo "🤖 Testando Ana AI...\n";
$ana_test = curl_init('https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php');
curl_setopt($ana_test, CURLOPT_POST, true);
curl_setopt($ana_test, CURLOPT_POSTFIELDS, json_encode([
    'question' => 'teste',
    'agent_id' => '3'
]));
curl_setopt($ana_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ana_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ana_test, CURLOPT_TIMEOUT, 10);
curl_setopt($ana_test, CURLOPT_SSL_VERIFYPEER, false);

$ana_response = curl_exec($ana_test);
$ana_code = curl_getinfo($ana_test, CURLINFO_HTTP_CODE);
curl_close($ana_test);

if ($ana_code === 200) {
    echo "✅ Ana AI responde (HTTP $ana_code)\n";
    $ana_data = json_decode($ana_response, true);
    if (isset($ana_data['success']) && $ana_data['success']) {
        echo "✅ Ana funcionando corretamente\n";
    } else {
        echo "⚠️ Ana responde mas com problema\n";
    }
} else {
    echo "❌ Ana AI offline (HTTP $ana_code)\n";
}
echo "\n";

// Criar versão simplificada para teste
echo "🔧 Criando versão de teste simplificada...\n";

$webhook_test_content = '<?php
header("Content-Type: application/json");

try {
    // Log de entrada
    error_log("[WEBHOOK_TEST] Iniciando...");
    
    $input = file_get_contents("php://input");
    error_log("[WEBHOOK_TEST] Input: " . $input);
    
    // Dados mínimos
    $data = json_decode($input, true);
    if (!$data && !empty($_GET)) {
        $data = $_GET;
    }
    
    $from = $data["from"] ?? $data["number"] ?? "teste";
    $body = $data["body"] ?? $data["message"] ?? "teste";
    
    error_log("[WEBHOOK_TEST] From: $from, Body: $body");
    
    // Resposta básica
    $response = [
        "success" => true,
        "message_id" => 999,
        "response_id" => 999,
        "ana_response" => "Olá! Webhook funcionando em modo teste. Mensagem: $body",
        "action_taken" => "teste",
        "debug_mode" => true,
        "timestamp" => date("Y-m-d H:i:s")
    ];
    
    error_log("[WEBHOOK_TEST] Resposta: " . json_encode($response));
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[WEBHOOK_TEST] ERRO: " . $e->getMessage());
    
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "debug_mode" => true
    ]);
}
?>';

file_put_contents('painel/webhook_teste.php', $webhook_test_content);
echo "✅ Arquivo de teste criado: painel/webhook_teste.php\n\n";

// Testar versão simplificada
echo "🧪 Testando webhook simplificado...\n";
$test_url = 'https://app.pixel12digital.com.br/painel/webhook_teste.php';

$simple_test = curl_init($test_url);
curl_setopt($simple_test, CURLOPT_POST, true);
curl_setopt($simple_test, CURLOPT_POSTFIELDS, json_encode([
    'from' => '5547999999999',
    'body' => 'Teste webhook simplificado'
]));
curl_setopt($simple_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($simple_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($simple_test, CURLOPT_TIMEOUT, 10);
curl_setopt($simple_test, CURLOPT_SSL_VERIFYPEER, false);

$simple_response = curl_exec($simple_test);
$simple_code = curl_getinfo($simple_test, CURLINFO_HTTP_CODE);
curl_close($simple_test);

echo "Teste simples: HTTP $simple_code\n";
echo "Resposta: " . substr($simple_response, 0, 200) . "\n\n";

echo "🎯 CONCLUSÃO:\n";
echo "=============\n";
if ($simple_code === 200) {
    echo "✅ Webhook simplificado funciona\n";
    echo "💡 Problema está no código complexo do webhook principal\n";
    echo "🔧 Precisamos corrigir erros específicos\n\n";
    
    echo "📋 PRÓXIMOS PASSOS:\n";
    echo "1. Temporariamente usar webhook teste\n";
    echo "2. Corrigir erros no webhook principal\n";
    echo "3. Voltar para webhook principal\n";
} else {
    echo "❌ Até webhook simples falha\n";
    echo "💡 Problema no servidor/configuração\n";
}
?> 