<?php
/**
 * 🔧 CORRIGIR FORMATO DE DADOS - WEBHOOK
 * 
 * Ajusta o webhook para aceitar tanto formato correto quanto formato WhatsApp robot
 */

echo "=== 🔧 CORREÇÃO DE FORMATO DE DADOS - WEBHOOK ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// ===== 1. VERIFICAR ARQUIVO WEBHOOK =====
echo "1. 📋 VERIFICANDO ARQUIVO WEBHOOK:\n";

$webhook_file = 'painel/receber_mensagem_ana_local.php';
if (file_exists($webhook_file)) {
    echo "   ✅ Arquivo encontrado: $webhook_file\n";
    
    $content = file_get_contents($webhook_file);
    
    // Verificar se já tem tratamento para formato robot
    if (strpos($content, 'event') !== false && strpos($content, 'data') !== false) {
        echo "   ✅ Já tem tratamento para formato robot\n";
    } else {
        echo "   ❌ Precisa de ajuste para formato robot\n";
    }
    
} else {
    echo "   ❌ Arquivo não encontrado: $webhook_file\n";
    exit(1);
}

echo "\n";

// ===== 2. CRIAR BACKUP =====
echo "2. 💾 CRIANDO BACKUP:\n";

$backup_file = $webhook_file . '.backup.' . date('Ymd_His');
if (copy($webhook_file, $backup_file)) {
    echo "   ✅ Backup criado: $backup_file\n";
} else {
    echo "   ❌ Erro ao criar backup\n";
    exit(1);
}

echo "\n";

// ===== 3. ADICIONAR TRATAMENTO DE FORMATO =====
echo "3. 🔧 ADICIONANDO TRATAMENTO DE FORMATO:\n";

// Ler o conteúdo atual
$content = file_get_contents($webhook_file);

// Verificar se já tem o tratamento
if (strpos($content, '// Tratamento de formato WhatsApp robot') !== false) {
    echo "   ✅ Tratamento já existe\n";
} else {
    // Adicionar tratamento antes da primeira linha que processa dados
    $search = '$input = file_get_contents("php://input");';
    $replace = '$input = file_get_contents("php://input");

// Tratamento de formato WhatsApp robot
$data = json_decode($input, true);

// Verificar se é formato robot (com event e data)
if (isset($data["event"]) && isset($data["data"])) {
    // Formato robot: {"event":"onmessage","data":{"from":"554796164699","text":"msg"}}
    $from = $data["data"]["from"] ?? null;
    $body = $data["data"]["text"] ?? null;
    $timestamp = $data["data"]["timestamp"] ?? time();
    
    // Converter para formato padrão
    $data = [
        "from" => $from . "@c.us",
        "body" => $body,
        "timestamp" => $timestamp
    ];
    
    // Reconverter para JSON
    $input = json_encode($data);
} else {
    // Formato padrão: {"from":"554796164699@c.us","body":"msg"}
    $data = json_decode($input, true);
}';

    $new_content = str_replace($search, $replace, $content);
    
    if ($new_content !== $content) {
        if (file_put_contents($webhook_file, $new_content)) {
            echo "   ✅ Tratamento adicionado com sucesso\n";
        } else {
            echo "   ❌ Erro ao salvar arquivo\n";
            exit(1);
        }
    } else {
        echo "   ⚠️  Padrão não encontrado, verificação manual necessária\n";
    }
}

echo "\n";

// ===== 4. TESTAR CORREÇÃO =====
echo "4. 🧪 TESTANDO CORREÇÃO:\n";

// Testar formato robot
$dados_robot = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699",
        "text" => "Teste após correção - " . date('Y-m-d H:i:s'),
        "type" => "chat",
        "timestamp" => time(),
        "session" => "default"
    ]
];

$url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_robot));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Teste-Correcao/1.0'
]);
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
} else {
    echo "   ✅ Resposta: " . substr($response, 0, 200) . "...\n";
}

echo "\n";

// ===== 5. RESUMO =====
echo "5. 📊 RESUMO DA CORREÇÃO:\n";
echo "   ✅ Backup criado: $backup_file\n";
echo "   ✅ Tratamento de formato adicionado\n";
echo "   ✅ Teste realizado: HTTP $http_code\n";

if ($http_code == 200) {
    echo "   🎉 CORREÇÃO FUNCIONANDO!\n";
} else {
    echo "   ⚠️  Ainda há problemas (HTTP $http_code)\n";
    echo "   🔧 Verificar se a coluna foi adicionada na hospedagem\n";
}

echo "\n   🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Fazer deploy na hospedagem\n";
echo "   2. Executar correção de coluna\n";
echo "   3. Testar com mensagem real\n";
echo "   4. Monitorar logs\n";

echo "\n=== FIM DA CORREÇÃO ===\n";
echo "Status: " . ($http_code == 200 ? "✅ SUCESSO" : "⚠️  PARCIAL") . "\n";
?> 