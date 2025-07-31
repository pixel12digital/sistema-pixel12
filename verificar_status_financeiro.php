<?php
require_once "config.php";
require_once "painel/db.php";

echo "🔍 VERIFICANDO STATUS DO CANAL FINANCEIRO\n";
echo "========================================\n\n";

// 1. Verificar status direto na VPS
echo "📡 CONSULTANDO VPS (PORTA 3000):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ VPS respondeu (HTTP 200)\n";
    echo "   📊 Dados da resposta:\n";
    echo "      - Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "      - Message: " . ($data['message'] ?? 'N/A') . "\n";
    echo "      - Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
    
    if (isset($data['clients_status']['default'])) {
        $client = $data['clients_status']['default'];
        echo "      - Client Status: " . ($client['status'] ?? 'N/A') . "\n";
        echo "      - Client Number: " . ($client['number'] ?? 'N/A') . "\n";
        echo "      - Client Message: " . ($client['message'] ?? 'N/A') . "\n";
    }
} else {
    echo "   ❌ VPS não respondeu (HTTP $http_code)\n";
}

echo "\n";

// 2. Verificar status via ajax_whatsapp.php
echo "🔧 TESTANDO AJAX_WHATSAPP.PHP (PORTA 3000):\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/loja-virtual-revenda/painel/ajax_whatsapp.php?action=status&porta=3000");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Ajax respondeu (HTTP 200)\n";
    echo "   📊 Dados do Ajax:\n";
    echo "      - Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
    echo "      - Number: " . ($data['number'] ?? 'N/A') . "\n";
    echo "      - Message: " . ($data['message'] ?? 'N/A') . "\n";
    
    if (isset($data['debug'])) {
        echo "      - VPS Status: " . ($data['debug']['vps_status'] ?? 'N/A') . "\n";
        echo "      - VPS Status Parsed: " . ($data['debug']['vps_status_parsed'] ?? 'N/A') . "\n";
    }
} else {
    echo "   ❌ Ajax não respondeu (HTTP $http_code)\n";
}

echo "\n";

// 3. Verificar banco de dados
echo "📋 VERIFICANDO BANCO DE DADOS:\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3000");
if ($row = $result->fetch_assoc()) {
    echo "   Canal: " . $row['nome_exibicao'] . "\n";
    echo "   Porta: " . $row['porta'] . "\n";
    echo "   Status: " . $row['status'] . "\n";
    echo "   Identificador: " . ($row['identificador'] ?: 'VAZIO') . "\n";
    echo "   Data Conexão: " . ($row['data_conexao'] ?: 'NULL') . "\n";
} else {
    echo "   ❌ Canal não encontrado no banco\n";
}

echo "\n";

// 4. Testar envio de mensagem
echo "📤 TESTANDO ENVIO DE MENSAGEM:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/loja-virtual-revenda/painel/ajax_whatsapp.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'action' => 'send',
    'porta' => '3000',
    'to' => '554797146908',
    'message' => 'Teste de mensagem - ' . date('H:i:s')
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Envio respondeu (HTTP 200)\n";
    echo "   📊 Resultado do envio:\n";
    echo "      - Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "      - Message: " . ($data['message'] ?? 'N/A') . "\n";
    if (isset($data['error'])) {
        echo "      - Error: " . $data['error'] . "\n";
    }
} else {
    echo "   ❌ Envio não respondeu (HTTP $http_code)\n";
}

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 