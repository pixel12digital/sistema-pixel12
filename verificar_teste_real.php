<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO MENSAGEM DO TESTE REAL\n";
echo "=====================================\n\n";

// Verificar mensagem específica
$result = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE mensagem LIKE '%TESTE WEBHOOK REAL DETALHADO%' ORDER BY id DESC LIMIT 1");

if ($result && $result->num_rows > 0) {
    $msg = $result->fetch_assoc();
    echo "✅ Mensagem encontrada!\n";
    echo "   - ID: {$msg['id']}\n";
    echo "   - Canal: {$msg['canal_id']}\n";
    echo "   - Cliente: {$msg['cliente_id']}\n";
    echo "   - Número: {$msg['numero_whatsapp']}\n";
    echo "   - Mensagem: {$msg['mensagem']}\n";
    echo "   - Data: {$msg['data_hora']}\n";
    echo "   - Direção: {$msg['direcao']}\n";
    echo "   - Status: {$msg['status']}\n";
} else {
    echo "❌ Mensagem não encontrada\n";
}

echo "\n📊 RESUMO:\n";
$total = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us'")->fetch_assoc()['total'];
echo "Total de mensagens do usuário: $total\n";

$hoje = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];
echo "Mensagens hoje: $hoje\n";
?> 