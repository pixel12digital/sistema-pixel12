<?php
require_once "config.php";
require_once "painel/db.php";

echo "🔧 DESCONECTANDO CANAL COMERCIAL\n";
echo "================================\n\n";

// 1. Atualizar status no banco para 'pendente'
$update = $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente', data_conexao = NULL WHERE nome_exibicao LIKE '%Comercial%'");
if ($update) {
    echo "✅ Status atualizado para 'pendente' no banco\n";
} else {
    echo "❌ Erro ao atualizar banco: " . $mysqli->error . "\n";
}

// 2. Desconectar via API da VPS
$vps_ip = "212.85.11.238";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/session/default/disconnect");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Desconectando via API (porta 3001)...\n";
echo "   HTTP Code: $http_code\n";
echo "   Resposta: $response\n\n";

// 3. Verificar status atual
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 STATUS ATUAL DA API:\n";
echo "   HTTP Code: $http_code\n";
echo "   Resposta: $response\n\n";

// 4. Verificar banco de dados
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE nome_exibicao LIKE '%Comercial%'");
if ($row = $result->fetch_assoc()) {
    echo "📋 STATUS NO BANCO:\n";
    echo "   ID: " . $row['id'] . "\n";
    echo "   Nome: " . $row['nome_exibicao'] . "\n";
    echo "   Status: " . $row['status'] . "\n";
    echo "   Porta: " . $row['porta'] . "\n";
    echo "   Identificador: " . ($row['identificador'] ?: 'VAZIO') . "\n";
}

echo "\n✅ DESCONEXÃO CONCLUÍDA!\n";
echo "Agora você pode reconectar o canal comercial.\n";
?> 