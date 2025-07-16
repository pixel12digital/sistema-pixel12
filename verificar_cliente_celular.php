<?php
require_once 'painel/config.php';
require_once 'painel/db.php';

echo "=== CLIENTES COM CELULAR ===\n\n";

$clientes = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE celular IS NOT NULL AND celular != '' LIMIT 10");

if ($clientes->num_rows > 0) {
    while ($cliente = $clientes->fetch_assoc()) {
        echo "ID: " . $cliente['id'] . " | Nome: " . $cliente['nome'] . " | Celular: " . $cliente['celular'] . "\n";
    }
} else {
    echo "Nenhum cliente com celular encontrado!\n";
}

echo "\n=== MENSAGENS EXISTENTES ===\n";

$mensagens = $mysqli->query("SELECT m.id, m.cliente_id, c.nome, m.mensagem, m.direcao, m.data_hora FROM mensagens_comunicacao m LEFT JOIN clientes c ON m.cliente_id = c.id ORDER BY m.data_hora DESC LIMIT 5");

if ($mensagens->num_rows > 0) {
    while ($msg = $mensagens->fetch_assoc()) {
        echo "ID: " . $msg['id'] . " | Cliente: " . $msg['nome'] . " | Direção: " . $msg['direcao'] . " | Data: " . $msg['data_hora'] . "\n";
        echo "Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n\n";
    }
} else {
    echo "Nenhuma mensagem encontrada!\n";
}
?> 