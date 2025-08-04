<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "ESTRUTURA DA TABELA canais_comunicacao:\n";
echo "=======================================\n";

$result = $mysqli->query('DESCRIBE canais_comunicacao');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

echo "\nDADOS DOS CANAIS:\n";
echo "==================\n";

$canais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status = 'conectado' LIMIT 3");
if ($canais && $canais->num_rows > 0) {
    while ($canal = $canais->fetch_assoc()) {
        echo "Canal: " . json_encode($canal) . "\n";
        echo "---\n";
    }
} else {
    echo "Nenhum canal conectado encontrado.\n";
}
?> 