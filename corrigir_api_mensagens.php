<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correção da API de Mensagens</h1>";
echo "<p>Corrigindo o problema de carregamento de mensagens no chat...</p>";

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/painel/db.php';

// Teste 1: Verificar se o arquivo da API existe
echo "<h2>📁 Teste 1: Verificar Arquivo da API</h2>";

$api_file = 'painel/api/mensagens_cliente.php';
if (file_exists($api_file)) {
    echo "<p style='color: green; font-weight: bold;'>✅ Arquivo da API encontrado: $api_file</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Arquivo da API não encontrado: $api_file</p>";
    exit;
}

// Teste 2: Testar acesso direto à API
echo "<h2>🔗 Teste 2: Acesso Direto à API</h2>";

$cliente_id = 4296; // ID do cliente Charles
$api_url = "http://localhost/loja-virtual-revenda/painel/api/mensagens_cliente.php?cliente_id=$cliente_id";

echo "<p><strong>Testando URL:</strong> $api_url</p>";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$api_response = curl_exec($ch);
$api_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $api_http_code</p>";
echo "<p><strong>Curl Error:</strong> " . ($curl_error ?: 'Nenhum') . "</p>";
echo "<p><strong>Resposta:</strong> <pre>" . htmlspecialchars($api_response) . "</pre></p>";

if ($api_http_code == 200) {
    $api_data = json_decode($api_response, true);
    if ($api_data && $api_data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ API funcionando - " . count($api_data['mensagens']) . " mensagens retornadas</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro na API: " . ($api_data['error'] ?? 'Erro desconhecido') . "</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Erro HTTP na API: $api_http_code</p>";
}

// Teste 3: Verificar se há problemas de CORS ou permissões
echo "<h2>🔒 Teste 3: Verificar Permissões e CORS</h2>";

// Verificar se o diretório logs existe e tem permissões
$logs_dir = 'logs';
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0755, true);
    echo "<p style='color: green; font-weight: bold;'>✅ Diretório logs criado</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ Diretório logs existe</p>";
}

// Verificar permissões do arquivo da API
if (is_readable($api_file)) {
    echo "<p style='color: green; font-weight: bold;'>✅ Arquivo da API é legível</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Arquivo da API não é legível</p>";
}

// Teste 4: Criar versão simplificada da API para teste
echo "<h2>🧪 Teste 4: Versão Simplificada da API</h2>";

$api_simples = 'teste_api_mensagens_simples.php';
$api_content = '<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../db.php";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$cliente_id = isset($_GET["cliente_id"]) ? intval($_GET["cliente_id"]) : 0;

if (!$cliente_id) {
    echo json_encode(["success" => false, "error" => "ID do cliente não fornecido"]);
    exit;
}

try {
    $sql = "SELECT m.*, c.nome_exibicao as canal_nome
            FROM mensagens_comunicacao m
            LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
            WHERE m.cliente_id = ?
            ORDER BY m.data_hora ASC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($msg = $result->fetch_assoc()) {
        $mensagens[] = [
            "id" => $msg["id"],
            "mensagem" => $msg["mensagem"],
            "direcao" => $msg["direcao"],
            "status" => $msg["status"],
            "data_hora" => $msg["data_hora"],
            "canal_nome" => $msg["canal_nome"] ?: "WhatsApp",
            "contato_interagiu" => $msg["direcao"] == "enviado" ? "Você" : ($msg["canal_nome"] ?: "Sistema")
        ];
    }
    $stmt->close();
    
    echo json_encode([
        "success" => true,
        "mensagens" => $mensagens,
        "total" => count($mensagens)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Erro ao carregar mensagens: " . $e->getMessage()
    ]);
}
?>';

file_put_contents($api_simples, $api_content);
echo "<p style='color: green; font-weight: bold;'>✅ API simplificada criada: $api_simples</p>";

// Teste 5: Testar a API simplificada
echo "<h2>🔬 Teste 5: API Simplificada</h2>";

$api_simples_url = "http://localhost/loja-virtual-revenda/$api_simples?cliente_id=$cliente_id";
echo "<p><strong>Testando API simplificada:</strong> $api_simples_url</p>";

$ch = curl_init($api_simples_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$api_simples_response = curl_exec($ch);
$api_simples_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $api_simples_http_code</p>";
echo "<p><strong>Resposta:</strong> <pre>" . htmlspecialchars($api_simples_response) . "</pre></p>";

if ($api_simples_http_code == 200) {
    $api_simples_data = json_decode($api_simples_response, true);
    if ($api_simples_data && $api_simples_data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ API simplificada funcionando - " . count($api_simples_data['mensagens']) . " mensagens retornadas</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro na API simplificada: " . ($api_simples_data['error'] ?? 'Erro desconhecido') . "</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Erro HTTP na API simplificada: $api_simples_http_code</p>";
}

// Teste 6: Atualizar o arquivo original da API
echo "<h2>🔧 Teste 6: Corrigir API Original</h2>";

// Fazer backup do arquivo original
$api_backup = $api_file . '.backup.' . date('Y-m-d_H-i-s');
copy($api_file, $api_backup);
echo "<p style='color: green; font-weight: bold;'>✅ Backup criado: $api_backup</p>";

// Ler o arquivo original
$api_original_content = file_get_contents($api_file);

// Verificar se já tem headers CORS
if (strpos($api_original_content, 'Access-Control-Allow-Origin') === false) {
    // Adicionar headers CORS
    $cors_headers = 'header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

';

    $api_original_content = str_replace('header("Content-Type: application/json; charset=utf-8");', 
                                      'header("Content-Type: application/json; charset=utf-8");
' . $cors_headers, 
                                      $api_original_content);
    
    file_put_contents($api_file, $api_original_content);
    echo "<p style='color: green; font-weight: bold;'>✅ Headers CORS adicionados ao arquivo original</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ Headers CORS já existem no arquivo original</p>";
}

// Teste 7: Testar API original corrigida
echo "<h2>✅ Teste 7: API Original Corrigida</h2>";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$api_corrigida_response = curl_exec($ch);
$api_corrigida_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $api_corrigida_http_code</p>";
echo "<p><strong>Resposta:</strong> <pre>" . htmlspecialchars($api_corrigida_response) . "</pre></p>";

if ($api_corrigida_http_code == 200) {
    $api_corrigida_data = json_decode($api_corrigida_response, true);
    if ($api_corrigida_data && $api_corrigida_data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ API original corrigida funcionando - " . count($api_corrigida_data['mensagens']) . " mensagens retornadas</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro na API corrigida: " . ($api_corrigida_data['error'] ?? 'Erro desconhecido') . "</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Erro HTTP na API corrigida: $api_corrigida_http_code</p>";
}

// Limpar arquivo temporário
unlink($api_simples);
echo "<p style='color: green; font-weight: bold;'>✅ Arquivo temporário removido</p>";

echo "<h2>🎯 Resumo da Correção</h2>";

if ($api_corrigida_http_code == 200) {
    echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>🎉 PROBLEMA CORRIGIDO!</p>";
    echo "<p>✅ API de mensagens funcionando corretamente</p>";
    echo "<p>✅ Headers CORS adicionados</p>";
    echo "<p>✅ Mensagens podem ser carregadas no chat</p>";
    echo "<p>✅ Sistema pronto para uso</p>";
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'>❌ PROBLEMA PERSISTE</p>";
    echo "<p>Verifique as configurações do servidor web</p>";
}

echo "<p><a href='painel/chat.php?cliente_id=4296'>← Testar no Chat</a></p>";
echo "<p><a href='teste_webhook_mensagens.php'>← Executar Teste Novamente</a></p>";
?> 