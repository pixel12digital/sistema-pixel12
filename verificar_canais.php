<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VERIFICANDO CANAIS DISPONÍVEIS ===\n";

// Conectar ao banco remoto
$mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
if ($mysqli->connect_errno) {
    echo "❌ Erro ao conectar ao MySQL: " . $mysqli->connect_error . "\n";
    exit;
}
$mysqli->set_charset('utf8mb4');

// Buscar todos os canais
echo "Canais disponíveis:\n";
$res = $mysqli->query("SELECT id, nome_exibicao, status, porta FROM canais_comunicacao ORDER BY id");

if (!$res) {
    echo "❌ Erro ao buscar canais: " . $mysqli->error . "\n";
    exit;
}

if ($res->num_rows === 0) {
    echo "❌ Nenhum canal encontrado\n";
    exit;
}

while ($canal = $res->fetch_assoc()) {
    echo "ID: " . $canal['id'] . " | Nome: " . $canal['nome_exibicao'] . " | Status: " . $canal['status'] . " | Porta: " . $canal['porta'] . "\n";
}

$mysqli->close();
echo "\n=== FIM DA VERIFICAÇÃO ===\n";
?> 