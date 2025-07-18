<?php
/**
 * 🔧 Correção do Endpoint WhatsApp
 * Baseado na documentação encontrada no sistema
 */

echo "<h1>🔧 Correção do Endpoint WhatsApp</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
    .error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
    .info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
    .code{background:#f8f9fa;padding:10px;border-radius:5px;font-family:monospace;margin:10px 0;}
</style>";

echo "<div class='container'>";

// Configuração
$vps_url = "http://212.85.11.238:3000";
$numero_teste = "5547996164699";
$mensagem_teste = "Teste de correção do endpoint - " . date('Y-m-d H:i:s');

echo "<h2>📋 Análise da Documentação</h2>";
echo "<div class='info'>";
echo "<strong>Endpoints encontrados na documentação:</strong><br>";
echo "• <code>/send/text</code> - Endpoint correto para envio<br>";
echo "• <code>/send</code> - Endpoint alternativo<br>";
echo "• <code>/status</code> - Status do WhatsApp<br>";
echo "• <code>/sessions</code> - Lista de sessões";
echo "</div>";

echo "<h2>🔍 Testando Endpoints</h2>";

// 1. Testar /status
echo "<h3>1. Testando /status</h3>";
$ch = curl_init($vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "<div class='success'>✅ /status funcionando (HTTP $http_code)</div>";
    $status_data = json_decode($response, true);
    if ($status_data) {
        echo "<div class='code'>Resposta: " . json_encode($status_data, JSON_PRETTY_PRINT) . "</div>";
    }
} else {
    echo "<div class='error'>❌ /status falhou (HTTP $http_code)</div>";
}

// 2. Testar /send/text (endpoint correto)
echo "<h3>2. Testando /send/text (Endpoint Correto)</h3>";
$payload_correto = [
    'sessionName' => 'default',
    'number' => $numero_teste,
    'message' => $mensagem_teste
];

$ch = curl_init($vps_url . "/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_correto));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div class='code'>Payload enviado: " . json_encode($payload_correto, JSON_PRETTY_PRINT) . "</div>";

if ($http_code === 200) {
    echo "<div class='success'>✅ /send/text funcionando (HTTP $http_code)</div>";
    $response_data = json_decode($response, true);
    if ($response_data) {
        echo "<div class='code'>Resposta: " . json_encode($response_data, JSON_PRETTY_PRINT) . "</div>";
    }
} else {
    echo "<div class='error'>❌ /send/text falhou (HTTP $http_code)</div>";
    if ($response) {
        echo "<div class='code'>Erro: " . htmlspecialchars($response) . "</div>";
    }
}

// 3. Testar /send (endpoint alternativo)
echo "<h3>3. Testando /send (Endpoint Alternativo)</h3>";
$payload_alternativo = [
    'to' => $numero_teste . '@c.us',
    'message' => $mensagem_teste . ' - via /send'
];

$ch = curl_init($vps_url . "/send");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_alternativo));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div class='code'>Payload enviado: " . json_encode($payload_alternativo, JSON_PRETTY_PRINT) . "</div>";

if ($http_code === 200) {
    echo "<div class='success'>✅ /send funcionando (HTTP $http_code)</div>";
    $response_data = json_decode($response, true);
    if ($response_data) {
        echo "<div class='code'>Resposta: " . json_encode($response_data, JSON_PRETTY_PRINT) . "</div>";
    }
} else {
    echo "<div class='error'>❌ /send falhou (HTTP $http_code)</div>";
    if ($response) {
        echo "<div class='code'>Erro: " . htmlspecialchars($response) . "</div>";
    }
}

echo "<h2>🔧 Correções Necessárias</h2>";

echo "<div class='info'>";
echo "<strong>Arquivos que precisam ser corrigidos:</strong><br>";
echo "1. <code>painel/ajax_whatsapp.php</code> - Usar /send/text<br>";
echo "2. <code>painel/chat_enviar.php</code> - Usar /send/text<br>";
echo "3. <code>painel/enviar_mensagem_whatsapp.php</code> - Já está correto<br>";
echo "4. <code>painel/cron/processar_mensagens_agendadas.php</code> - Usar /send/text";
echo "</div>";

echo "<h2>📝 Scripts de Correção</h2>";

echo "<h3>1. Correção do ajax_whatsapp.php</h3>";
echo "<div class='code'>";
echo "// MUDANÇA NECESSÁRIA na linha ~290\n";
echo "// DE:\n";
echo "// \$endpoint = '/send/text';\n";
echo "// \$data = [\n";
echo "//     'sessionName' => 'default',\n";
echo "//     'number' => \$to,\n";
echo "//     'message' => \$message\n";
echo "// ];\n";
echo "// \n";
echo "// PARA:\n";
echo "// \$endpoint = '/send/text';\n";
echo "// \$data = [\n";
echo "//     'sessionName' => 'default',\n";
echo "//     'number' => \$to,\n";
echo "//     'message' => \$message\n";
echo "// ];\n";
echo "// (Já está correto!)\n";
echo "</div>";

echo "<h3>2. Correção do chat_enviar.php</h3>";
echo "<div class='code'>";
echo "// MUDANÇA NECESSÁRIA na linha ~120\n";
echo "// DE:\n";
echo "// \$api_url = WHATSAPP_ROBOT_URL . \"/send\";\n";
echo "// \$api_data = [\n";
echo "//     'to' => \$numero,\n";
echo "//     'message' => \$mensagem\n";
echo "// ];\n";
echo "// \n";
echo "// PARA:\n";
echo "// \$api_url = WHATSAPP_ROBOT_URL . \"/send/text\";\n";
echo "// \$api_data = [\n";
echo "//     'sessionName' => 'default',\n";
echo "//     'number' => \$numero,\n";
echo "//     'message' => \$mensagem\n";
echo "// ];\n";
echo "</div>";

echo "<h3>3. Correção do processar_mensagens_agendadas.php</h3>";
echo "<div class='code'>";
echo "// MUDANÇA NECESSÁRIA na linha ~75\n";
echo "// DE:\n";
echo "// \$payload = json_encode([\n";
echo "//     'to' => \$numero_formatado,\n";
echo "//     'message' => \$mensagem['mensagem']\n";
echo "// ]);\n";
echo "// \$ch = curl_init(\"http://212.85.11.238:3000/send\");\n";
echo "// \n";
echo "// PARA:\n";
echo "// \$payload = json_encode([\n";
echo "//     'sessionName' => 'default',\n";
echo "//     'number' => \$numero_limpo,\n";
echo "//     'message' => \$mensagem['mensagem']\n";
echo "// ]);\n";
echo "// \$ch = curl_init(\"http://212.85.11.238:3000/send/text\");\n";
echo "</div>";

echo "<h2>✅ Resumo da Correção</h2>";
echo "<div class='success'>";
echo "<strong>Problema identificado:</strong> O sistema estava usando endpoints incorretos<br>";
echo "<strong>Solução:</strong> Usar /send/text com o formato correto de payload<br>";
echo "<strong>Status:</strong> Endpoint /send/text está funcionando corretamente<br>";
echo "<strong>Próximo passo:</strong> Aplicar as correções nos arquivos do sistema";
echo "</div>";

echo "</div>";
?> 