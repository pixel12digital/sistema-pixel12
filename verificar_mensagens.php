<?php
require_once 'config.php';
require_once 'painel/db.php';

$result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us'");
$total = $result->fetch_assoc()['total'];
echo "Total de mensagens do usuário 554796164699@c.us: $total\n";

$ultimas = $mysqli->query("SELECT id, mensagem, data_hora FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' ORDER BY id DESC LIMIT 5");
echo "\nÚltimas 5 mensagens:\n";
while ($msg = $ultimas->fetch_assoc()) {
    echo "- ID {$msg['id']}: {$msg['mensagem']} ({$msg['data_hora']})\n";
}
?> 