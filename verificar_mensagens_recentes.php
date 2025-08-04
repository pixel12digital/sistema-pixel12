<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "📱 VERIFICANDO MENSAGENS RECENTES\n";
echo "=================================\n\n";

// Últimas 20 mensagens dos últimos 60 minutos
$msgs = $mysqli->query("
    SELECT id, numero_whatsapp, direcao, SUBSTRING(mensagem, 1, 80) as msg, data_hora 
    FROM mensagens_comunicacao 
    WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
    ORDER BY data_hora DESC 
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

if (!empty($msgs)) {
    echo "✅ MENSAGENS DOS ÚLTIMOS 60 MINUTOS:\n";
    echo "====================================\n";
    foreach($msgs as $m) {
        $tipo = $m['direcao'] === 'recebido' ? '📩' : '📤';
        echo "$tipo ID {$m['id']} | {$m['numero_whatsapp']} | {$m['data_hora']}\n";
        echo "   {$m['msg']}...\n\n";
    }
} else {
    echo "❌ NENHUMA MENSAGEM ENCONTRADA\n";
}

echo "📊 TOTAL MENSAGENS HOJE: ";
$total = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc();
echo $total['total'] . "\n\n";

echo "🔍 ÚLTIMAS 5 MENSAGENS (QUALQUER HORA):\n";
echo "=======================================\n";
$ultimas = $mysqli->query("
    SELECT id, numero_whatsapp, direcao, SUBSTRING(mensagem, 1, 60) as msg, data_hora 
    FROM mensagens_comunicacao 
    ORDER BY id DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

foreach($ultimas as $m) {
    $tipo = $m['direcao'] === 'recebido' ? '📩' : '📤';
    echo "$tipo ID {$m['id']} | {$m['numero_whatsapp']} | {$m['data_hora']}\n";
    echo "   {$m['msg']}...\n\n";
}

?> 