<?php
require_once 'painel/config.php';
require_once 'painel/db.php';

echo "=== VERIFICANDO CANAIS EXISTENTES ===\n\n";

// Verificar canal específico
$identificador = '554797146908';
$res = $mysqli->query("SELECT * FROM canais_comunicacao WHERE identificador = '$identificador'");

if ($res && $res->num_rows > 0) {
    echo "Canal encontrado:\n";
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Nenhum canal encontrado com identificador: $identificador\n";
}

// Verificar todos os canais
echo "\n=== TODOS OS CANAIS ===\n";
$res = $mysqli->query("SELECT * FROM canais_comunicacao ORDER BY id");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']} | Tipo: {$row['tipo']} | Identificador: {$row['identificador']} | Status: {$row['status']}\n";
    }
} else {
    echo "Nenhum canal cadastrado.\n";
}

$mysqli->close();
?> 