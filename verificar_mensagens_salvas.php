<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO MENSAGENS SALVAS\n";
echo "================================\n\n";

// Verificar mensagens de hoje
$result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()");
$row = $result->fetch_assoc();
echo "📅 Mensagens hoje: " . $row['total'] . "\n";

// Verificar mensagens dos últimos 7 dias
$result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$row = $result->fetch_assoc();
echo "📅 Mensagens últimos 7 dias: " . $row['total'] . "\n";

// Verificar últimas mensagens
$result = $mysqli->query("SELECT id, cliente_id, mensagem, data_hora, numero_whatsapp FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5");
echo "\n📝 Últimas 5 mensagens:\n";
while ($row = $result->fetch_assoc()) {
    echo "- ID: {$row['id']} | Cliente: {$row['cliente_id']} | Número: {$row['numero_whatsapp']} | Data: {$row['data_hora']}\n";
    echo "  Mensagem: " . substr($row['mensagem'], 0, 50) . "...\n\n";
}

// Verificar se há mensagens com numero_whatsapp
$result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp IS NOT NULL AND numero_whatsapp != ''");
$row = $result->fetch_assoc();
echo "📱 Mensagens com número WhatsApp: " . $row['total'] . "\n";

// Verificar mensagens sem número WhatsApp
$result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp IS NULL OR numero_whatsapp = ''");
$row = $result->fetch_assoc();
echo "❌ Mensagens sem número WhatsApp: " . $row['total'] . "\n";

echo "\n✅ Verificação concluída!\n";
?> 