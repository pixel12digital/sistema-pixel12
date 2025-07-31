<?php
/**
 * TESTAR WEBHOOK LOCAL
 * 
 * Este script testa o webhook do canal comercial localmente
 */

echo "🧪 TESTANDO WEBHOOK LOCAL\n";
echo "=========================\n\n";

// 1. Testar se o arquivo existe
echo "🔍 TESTE 1: VERIFICANDO ARQUIVO\n";
$webhook_file = __DIR__ . '/api/webhook_canal_37.php';
if (file_exists($webhook_file)) {
    echo "  ✅ Arquivo existe: $webhook_file\n";
    echo "  📏 Tamanho: " . filesize($webhook_file) . " bytes\n";
} else {
    echo "  ❌ Arquivo não encontrado: $webhook_file\n";
    exit;
}

// 2. Testar sintaxe PHP
echo "\n🔍 TESTE 2: VERIFICANDO SINTAXE PHP\n";
$output = shell_exec("php -l $webhook_file 2>&1");
if (strpos($output, 'No syntax errors') !== false) {
    echo "  ✅ Sintaxe PHP correta\n";
} else {
    echo "  ❌ Erro de sintaxe:\n";
    echo "     $output\n";
    exit;
}

// 3. Testar inclusão do arquivo
echo "\n🔍 TESTE 3: TESTANDO INCLUSÃO\n";
try {
    // Simular dados de entrada
    $dados_teste = [
        'from' => '554797146908@c.us',
        'to' => '4797309525@c.us',
        'body' => 'Teste webhook local - ' . date('H:i:s'),
        'timestamp' => time()
    ];
    
    // Capturar saída
    ob_start();
    
    // Simular POST data
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Incluir o arquivo
    include $webhook_file;
    
    $output = ob_get_clean();
    
    echo "  ✅ Arquivo executado com sucesso\n";
    echo "  📄 Saída: $output\n";
    
} catch (Exception $e) {
    echo "  ❌ Erro ao executar arquivo: " . $e->getMessage() . "\n";
}

// 4. Testar via HTTP local
echo "\n🔍 TESTE 4: TESTANDO VIA HTTP LOCAL\n";
$webhook_url = 'http://localhost:8080/loja-virtual-revenda/api/webhook_canal_37.php';

$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste HTTP local - ' . date('H:i:s'),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "  URL: $webhook_url\n";
echo "  HTTP Code: $http_code\n";
if ($error) {
    echo "  ❌ Erro cURL: $error\n";
} else {
    echo "  ✅ Resposta: $response\n";
}

echo "\n🎯 RESULTADO:\n";
if ($http_code === 200) {
    echo "✅ O webhook está funcionando localmente!\n";
    echo "📋 O problema pode ser:\n";
    echo "1. Configuração do servidor web no VPS\n";
    echo "2. Permissões de arquivo\n";
    echo "3. Configuração do Nginx/Apache\n";
} else {
    echo "❌ O webhook não está funcionando localmente\n";
    echo "📋 Verificar:\n";
    echo "1. Se o servidor web está rodando\n";
    echo "2. Se a porta 8080 está correta\n";
    echo "3. Se o arquivo está acessível\n";
}
?> 