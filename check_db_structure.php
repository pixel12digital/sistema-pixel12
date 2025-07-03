<?php
require_once 'config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        die("Erro de conexão: " . $mysqli->connect_error);
    }
    
    echo "=== ESTRUTURA ATUAL DO BANCO DE DADOS ===\n\n";
    
    // Listar todas as tabelas
    $result = $mysqli->query("SHOW TABLES");
    echo "TABELAS EXISTENTES:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    echo "\n";
    
    // Verificar estrutura das tabelas principais
    $tables = ['clientes', 'cobrancas', 'faturas', 'assinaturas'];
    
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "ESTRUTURA DA TABELA '$table':\n";
            $result = $mysqli->query("DESCRIBE $table");
            while ($row = $result->fetch_assoc()) {
                echo "- {$row['Field']}: {$row['Type']} " . 
                     ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . 
                     ($row['Key'] ? " ({$row['Key']})" : "") . "\n";
            }
            echo "\n";
        } else {
            echo "TABELA '$table' NÃO EXISTE\n\n";
        }
    }
    
    // Verificar dados existentes
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $count = $mysqli->query("SELECT COUNT(*) as total FROM $table")->fetch_assoc()['total'];
            echo "TABELA '$table': $count registros\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?> 