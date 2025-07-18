<?php
require_once 'painel/config.php';
require_once 'painel/db.php';

echo "=== ESTRUTURA DA TABELA CLIENTES ===\n\n";

// Buscar estrutura da tabela
$result = $mysqli->query("DESCRIBE clientes");

if ($result) {
    echo "Campos da tabela clientes:\n";
    echo str_repeat("-", 50) . "\n";
    echo sprintf("%-20s %-15s %-10s %-10s\n", "Campo", "Tipo", "Null", "Chave");
    echo str_repeat("-", 50) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-20s %-15s %-10s %-10s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key']
        );
    }
    
    echo "\n=== CAMPOS DISPONÍVEIS PARA EDIÇÃO ===\n";
    echo "Total de campos: " . $result->num_rows . "\n\n";
    
    // Listar todos os campos
    $result->data_seek(0);
    $campos = [];
    while ($row = $result->fetch_assoc()) {
        $campos[] = $row['Field'];
    }
    
    echo "Lista completa de campos:\n";
    foreach ($campos as $i => $campo) {
        echo ($i + 1) . ". " . $campo . "\n";
    }
    
} else {
    echo "Erro ao buscar estrutura da tabela: " . $mysqli->error . "\n";
}

echo "\n=== FIM ===\n";
?> 