<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO ESTRUTURA CANAIS\n";
echo "==============================\n\n";

// 1. Estrutura da tabela
echo "📊 Estrutura da tabela canais_comunicacao:\n";
$result = $mysqli->query("DESCRIBE canais_comunicacao");
while($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} ({$row['Type']})\n";
}

echo "\n📱 Dados dos canais:\n";
$canais = $mysqli->query("SELECT * FROM canais_comunicacao ORDER BY porta")->fetch_all(MYSQLI_ASSOC);
foreach ($canais as $canal) {
    echo "Canal ID {$canal['id']}:\n";
    foreach($canal as $campo => $valor) {
        echo "  $campo: $valor\n";
    }
    echo "\n";
}

echo "📱 Últimas mensagens:\n";
$mensagens = $mysqli->query("SELECT canal_id, COUNT(*) as total, MAX(data_hora) as ultima FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE() GROUP BY canal_id ORDER BY ultima DESC")->fetch_all(MYSQLI_ASSOC);
foreach ($mensagens as $msg) {
    echo "Canal {$msg['canal_id']}: {$msg['total']} mensagens (última: {$msg['ultima']})\n";
}
?> 