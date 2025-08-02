<?php
require_once 'painel/db.php';

echo "🔍 ESTRUTURA DA TABELA mensagens_comunicacao:\n";
echo "============================================\n\n";

$result = $mysqli->query('DESCRIBE mensagens_comunicacao');

if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "❌ Erro: " . $mysqli->error . "\n";
}

echo "\n🔍 VERIFICANDO SE EXISTE telefone_origem:\n";
$check = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'telefone_origem'");
if ($check && $check->num_rows > 0) {
    echo "✅ Campo telefone_origem existe\n";
} else {
    echo "❌ Campo telefone_origem NÃO existe\n";
}

echo "\n🔍 VERIFICANDO SE EXISTE numero_whatsapp:\n";
$check2 = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'numero_whatsapp'");
if ($check2 && $check2->num_rows > 0) {
    echo "✅ Campo numero_whatsapp existe\n";
} else {
    echo "❌ Campo numero_whatsapp NÃO existe\n";
}

$mysqli->close();
?> 