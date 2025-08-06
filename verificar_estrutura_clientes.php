<?php
/**
 * 🔍 VERIFICAR ESTRUTURA DA TABELA CLIENTES
 */

echo "🔍 VERIFICANDO ESTRUTURA DA TABELA CLIENTES\n";
echo "==========================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$sql_estrutura = "DESCRIBE clientes";
$result_estrutura = $mysqli->query($sql_estrutura);

if ($result_estrutura) {
    echo "✅ Estrutura da tabela clientes:\n";
    while ($row = $result_estrutura->fetch_assoc()) {
        $null = $row['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
        $default = $row['Default'] ? "DEFAULT '{$row['Default']}'" : '';
        echo "   - {$row['Field']} ({$row['Type']}) - $null $default\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}
?> 