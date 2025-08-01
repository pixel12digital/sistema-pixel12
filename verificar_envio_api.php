<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação do Envio via API</h1>";
echo "<p>Verificando por que as mensagens não estão sendo enviadas via API...</p>";

require_once __DIR__ . '/config.php';

// Teste 1: Verificar logs detalhados do chat_enviar.php
echo "<h2>📋 Teste 1: Logs Detalhados do Envio</h2>";

$debug_log_path = __DIR__ . '/painel/debug_chat_enviar.log';
if (file_exists($debug_log_path)) {
    echo "<p><strong>Log de Debug:</strong></p>";
    $logs = file_get_contents($debug_log_path);
    $linhas = explode("\n", $logs);
    $ultimas_linhas = array_slice($linhas, -30); // Últimas 30 linhas
    
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    foreach ($ultimas_linhas as $linha) {
        if (trim($linha)) {
            echo htmlspecialchars($linha) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: orange;'>⚠️ Arquivo de log não encontrado: $debug_log_path</p>";
}

// Teste 2: Verificar configuração do VPS
echo "<h2>🖥️ Teste 2: Configuração do VPS</h2>";

$vps_url = "http://212.85.11.238:3001";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Status do VPS:</strong> HTTP $http_code</p>";
if ($response) {
    $vps_status = json_decode($response, true);
    if ($vps_status) {
        echo "<p><strong>Resposta completa:</strong></p>";
        echo "<pre>" . json_encode($vps_status, JSON_PRETTY_PRINT) . "</pre>";
        
        if (isset($vps_status['clients_status']['comercial'])) {
            $comercial = $vps_status['clients_status']['comercial'];
            echo "<p><strong>Sessão Comercial:</strong> {$comercial['status']} - {$comercial['message']}</p>";
        }
    }
}

// Teste 3: Testar envio direto via API
echo "<h2>📤 Teste 3: Envio Direto via API</h2>";

$api_data = [
    'sessionName' => 'comercial',
    'number' => '554796164699',
    'message' => 'Teste direto via API - ' . date('Y-m-d H:i:s')
];

echo "<p><strong>Dados da API:</strong></p>";
echo "<ul>";
echo "<li>URL: $vps_url/send/text</li>";
echo "<li>Session: {$api_data['sessionName']}</li>";
echo "<li>Number: {$api_data['number']}</li>";
echo "<li>Message: {$api_data['message']}</li>";
echo "</ul>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capturar verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Resposta HTTP:</strong> $http_code</p>";
if ($curl_error) {
    echo "<p style='color: red;'><strong>Erro cURL:</strong> $curl_error</p>";
}

// Mostrar verbose output
rewind($verbose);
$verbose_log = stream_get_contents($verbose);
echo "<p><strong>Log Verbose:</strong></p>";
echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;'>";
echo htmlspecialchars($verbose_log);
echo "</pre>";

if ($response) {
    echo "<p><strong>Resposta da API:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    $api_result = json_decode($response, true);
    if ($api_result) {
        if (isset($api_result['success']) && $api_result['success']) {
            echo "<p style='color: green; font-weight: bold;'>✅ API funcionando corretamente!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Erro na API</p>";
            echo "<p><strong>Erro:</strong> " . ($api_result['error'] ?? 'Erro desconhecido') . "</p>";
        }
    }
}

// Teste 4: Verificar configuração do chat_enviar.php
echo "<h2>🔧 Teste 4: Configuração do chat_enviar.php</h2>";

$chat_enviar_content = file_get_contents(__DIR__ . '/painel/chat_enviar.php');
if ($chat_enviar_content) {
    // Procurar pela configuração da API
    if (preg_match('/WHATSAPP_ROBOT_URL.*?=.*?[\'"]([^\'"]+)[\'"]/', $chat_enviar_content, $matches)) {
        echo "<p><strong>URL do Robô configurada:</strong> " . $matches[1] . "</p>";
    }
    
    // Procurar pela lógica de envio
    if (preg_match('/api_url.*?=.*?[\'"]([^\'"]+)[\'"]/', $chat_enviar_content, $matches)) {
        echo "<p><strong>URL da API:</strong> " . $matches[1] . "</p>";
    }
    
    // Verificar se está usando a porta correta
    if (strpos($chat_enviar_content, ':3001') !== false) {
        echo "<p style='color: green;'>✅ Usando porta 3001</p>";
    } else {
        echo "<p style='color: red;'>❌ Não está usando porta 3001</p>";
    }
}

// Teste 5: Verificar mensagens recentes no banco
echo "<h2>💾 Teste 5: Mensagens Recentes no Banco</h2>";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>❌ Erro na conexão com o banco</p>";
    } else {
        $sql = "SELECT m.*, c.nome_exibicao as canal_nome 
                FROM mensagens_comunicacao m 
                LEFT JOIN canais_comunicacao c ON m.canal_id = c.id 
                WHERE m.cliente_id = 4296 
                AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                ORDER BY m.id DESC 
                LIMIT 10";
        
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green; font-weight: bold;'>✅ Mensagens recentes encontradas:</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Canal</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data</th><th>WhatsApp ID</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['canal_nome']} (ID: {$row['canal_id']})</td>";
                echo "<td>" . htmlspecialchars(substr($row['mensagem'], 0, 50)) . "...</td>";
                echo "<td>{$row['direcao']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "<td>{$row['data_hora']}</td>";
                echo "<td>{$row['whatsapp_message_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma mensagem recente encontrada</p>";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

// Teste 6: Verificar se há problemas de conectividade
echo "<h2>🌐 Teste 6: Conectividade</h2>";

$test_urls = [
    "$vps_url/status",
    "$vps_url/sessions",
    "$vps_url/session/comercial/status"
];

foreach ($test_urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>$url:</strong> HTTP $http_code</p>";
    if ($response) {
        echo "<p><strong>Resposta:</strong> " . htmlspecialchars(substr($response, 0, 200)) . "...</p>";
    }
}

echo "<h2>🎯 Diagnóstico Final</h2>";

echo "<h3>🔍 Possíveis Problemas:</h3>";
echo "<ol>";
echo "<li><strong>URL da API incorreta:</strong> O chat_enviar.php pode estar usando URL errada</li>";
echo "<li><strong>Porta incorreta:</strong> Pode estar usando porta 3000 em vez de 3001</li>";
echo "<li><strong>Sessão incorreta:</strong> Pode estar usando 'default' em vez de 'comercial'</li>";
echo "<li><strong>Timeout da API:</strong> A API pode estar demorando muito para responder</li>";
echo "<li><strong>Erro na API:</strong> A API pode estar retornando erro</li>";
echo "</ol>";

echo "<h3>🔧 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Verificar se a URL da API está correta no chat_enviar.php</li>";
echo "<li>Verificar se está usando a porta 3001</li>";
echo "<li>Verificar se está usando a sessão 'comercial'</li>";
echo "<li>Aumentar timeout da API se necessário</li>";
echo "</ol>";

echo "<p><a href='corrigir_canal_id.php'>← Correção de Canal</a></p>";
?> 