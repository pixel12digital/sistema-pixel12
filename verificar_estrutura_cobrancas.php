<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO ESTRUTURA DA TABELA COBRANÇAS\n";
echo "============================================\n\n";

// Verificar estrutura da tabela
$result = $mysqli->query('DESCRIBE cobrancas');
if ($result) {
    echo "📋 Estrutura da tabela cobrancas:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['Field']} | {$row['Type']} | Null: {$row['Null']} | Default: " . ($row['Default'] ?? 'NULL') . "\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura da tabela: " . $mysqli->error . "\n";
}

echo "\n📊 VERIFICANDO DADOS DE EXEMPLO:\n";
$exemplo = $mysqli->query("SELECT * FROM cobrancas LIMIT 1");
if ($exemplo && $exemplo->num_rows > 0) {
    $row = $exemplo->fetch_assoc();
    echo "   Dados de exemplo:\n";
    foreach ($row as $campo => $valor) {
        echo "      $campo: $valor\n";
    }
} else {
    echo "   Nenhum registro encontrado na tabela\n";
}

echo "\n📊 VERIFICANDO COBRANÇAS DO CLIENTE DUPLICADO (ID 4295):\n";
$cobrancas = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = 4295");
if ($cobrancas && $cobrancas->num_rows > 0) {
    echo "   Cobranças encontradas:\n";
    while ($cobranca = $cobrancas->fetch_assoc()) {
        echo "   📋 Cobrança ID: {$cobranca['id']}\n";
        foreach ($cobranca as $campo => $valor) {
            echo "      $campo: $valor\n";
        }
        echo "\n";
    }
} else {
    echo "   Nenhuma cobrança encontrada para o cliente ID 4295\n";
}

$mysqli->close();
?> 