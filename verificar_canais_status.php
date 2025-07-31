<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "=== Status dos Canais WhatsApp ===\n\n";

$sql = "SELECT id, nome_exibicao, identificador, status, porta FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Nome: {$row['nome_exibicao']} | Status: {$row['status']} | Porta: {$row['porta']}\n";
    }
} else {
    echo "Erro na consulta: " . $mysqli->error . "\n";
}

echo "\n=== Teste da API ===\n";
$api_content = file_get_contents('painel/api/listar_canais_whatsapp.php');
echo "API retorna: " . $api_content . "\n";
?> 