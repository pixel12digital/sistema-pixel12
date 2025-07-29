<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO ESTRUTURA REAL DA TABELA CLIENTES\n";
echo "===============================================\n\n";

// Verificar estrutura da tabela
$result = $mysqli->query('DESCRIBE clientes');
if ($result) {
    echo "📋 Estrutura da tabela clientes:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['Field']} | {$row['Type']} | Null: {$row['Null']} | Default: " . ($row['Default'] ?? 'NULL') . "\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura da tabela: " . $mysqli->error . "\n";
}

echo "\n📊 VERIFICANDO DADOS DE EXEMPLO:\n";
$exemplo = $mysqli->query("SELECT * FROM clientes LIMIT 1");
if ($exemplo && $exemplo->num_rows > 0) {
    $row = $exemplo->fetch_assoc();
    echo "   Dados de exemplo:\n";
    foreach ($row as $campo => $valor) {
        echo "      $campo: $valor\n";
    }
} else {
    echo "   Nenhum registro encontrado na tabela\n";
}

$mysqli->close();
?> 