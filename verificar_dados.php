<?php
require_once 'painel/config.php';
require_once 'painel/db.php';

echo "=== VERIFICAÇÃO DE DADOS ===\n\n";

// Verificar clientes
$clientes = $mysqli->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc();
echo "Total de clientes: " . $clientes['total'] . "\n";

if ($clientes['total'] > 0) {
    $primeiro_cliente = $mysqli->query("SELECT id, nome, celular FROM clientes LIMIT 1")->fetch_assoc();
    echo "Primeiro cliente: ID=" . $primeiro_cliente['id'] . ", Nome=" . $primeiro_cliente['nome'] . ", Celular=" . $primeiro_cliente['celular'] . "\n";
}

// Verificar canais
$canais = $mysqli->query("SELECT COUNT(*) as total FROM canais_comunicacao")->fetch_assoc();
echo "Total de canais: " . $canais['total'] . "\n";

if ($canais['total'] > 0) {
    $canais_lista = $mysqli->query("SELECT id, nome_exibicao, tipo, status FROM canais_comunicacao");
    while ($canal = $canais_lista->fetch_assoc()) {
        echo "Canal: ID=" . $canal['id'] . ", Nome=" . $canal['nome_exibicao'] . ", Tipo=" . $canal['tipo'] . ", Status=" . $canal['status'] . "\n";
    }
}

// Verificar mensagens
$mensagens = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao")->fetch_assoc();
echo "Total de mensagens: " . $mensagens['total'] . "\n";

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
?> 