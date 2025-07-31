<?php
require_once "config.php";
require_once "painel/db.php";

echo "🧪 TESTANDO CANAIS INDIVIDUALMENTE\n";
echo "==================================\n\n";

// Testar canal financeiro (porta 3000)
echo "📱 TESTANDO CANAL FINANCEIRO (PORTA 3000):\n";
echo "----------------------------------------\n";

$ch_financeiro = curl_init();
curl_setopt($ch_financeiro, CURLOPT_URL, "http://212.85.11.238:3000/status");
curl_setopt($ch_financeiro, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_financeiro, CURLOPT_TIMEOUT, 10);
curl_setopt($ch_financeiro, CURLOPT_CONNECTTIMEOUT, 5);

$response_financeiro = curl_exec($ch_financeiro);
$http_code_financeiro = curl_getinfo($ch_financeiro, CURLINFO_HTTP_CODE);
$error_financeiro = curl_error($ch_financeiro);
curl_close($ch_financeiro);

echo "   URL: http://212.85.11.238:3000/status\n";
echo "   HTTP Code: $http_code_financeiro\n";
echo "   Erro: " . ($error_financeiro ?: 'Nenhum') . "\n";
echo "   Resposta: " . substr($response_financeiro, 0, 200) . "...\n\n";

// Testar canal comercial (porta 3001)
echo "📱 TESTANDO CANAL COMERCIAL (PORTA 3001):\n";
echo "----------------------------------------\n";

$ch_comercial = curl_init();
curl_setopt($ch_comercial, CURLOPT_URL, "http://212.85.11.238:3001/status");
curl_setopt($ch_comercial, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_comercial, CURLOPT_TIMEOUT, 10);
curl_setopt($ch_comercial, CURLOPT_CONNECTTIMEOUT, 5);

$response_comercial = curl_exec($ch_comercial);
$http_code_comercial = curl_getinfo($ch_comercial, CURLINFO_HTTP_CODE);
$error_comercial = curl_error($ch_comercial);
curl_close($ch_comercial);

echo "   URL: http://212.85.11.238:3001/status\n";
echo "   HTTP Code: $http_code_comercial\n";
echo "   Erro: " . ($error_comercial ?: 'Nenhum') . "\n";
echo "   Resposta: " . substr($response_comercial, 0, 200) . "...\n\n";

// Verificar banco de dados
echo "📋 VERIFICANDO BANCO DE DADOS:\n";
echo "-----------------------------\n";

$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   Canal: " . $row['nome_exibicao'] . "\n";
        echo "   Porta: " . $row['porta'] . "\n";
        echo "   Status: " . $row['status'] . "\n";
        echo "   Identificador: " . ($row['identificador'] ?: 'VAZIO') . "\n";
        echo "   ---\n";
    }
} else {
    echo "   ❌ Erro ao consultar banco: " . $mysqli->error . "\n";
}

echo "\n✅ TESTE CONCLUÍDO!\n";
?> 