<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO MENSAGENS DE TESTE\n";
echo "=================================\n\n";

$msgs = $mysqli->query("
    SELECT id, direcao, SUBSTRING(mensagem, 1, 100) as msg, data_hora 
    FROM mensagens_comunicacao 
    WHERE id IN (668, 669) OR mensagem LIKE '%TESTE AUTOMÁTICO%'
    ORDER BY id DESC
")->fetch_all(MYSQLI_ASSOC);

if (!empty($msgs)) {
    echo "✅ MENSAGENS ENCONTRADAS:\n";
    foreach($msgs as $m) {
        echo "ID {$m['id']} | {$m['direcao']} | {$m['data_hora']}\n";
        echo "  {$m['msg']}...\n\n";
    }
} else {
    echo "❌ Mensagens não encontradas\n";
}

echo "📊 Total de mensagens hoje: ";
$total = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc();
echo $total['total'] . "\n";
?> 