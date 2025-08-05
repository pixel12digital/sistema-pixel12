<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO MENSAGEM DO WEBHOOK\n";
echo "==================================\n\n";

// Verificar se a mensagem foi salva
$result = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' AND mensagem LIKE '%TESTE WEBHOOK DIRETO%' ORDER BY id DESC LIMIT 1");

if ($result && $result->num_rows > 0) {
    $msg = $result->fetch_assoc();
    echo "✅ Mensagem salva pelo webhook!\n";
    echo "   - ID: {$msg['id']}\n";
    echo "   - Mensagem: {$msg['mensagem']}\n";
    echo "   - Data: {$msg['data_hora']}\n";
    echo "   - Canal: {$msg['canal_id']}\n";
    echo "   - Cliente: {$msg['cliente_id']}\n";
} else {
    echo "❌ Mensagem não foi salva pelo webhook\n";
    
    // Verificar últimas mensagens
    $ultimas = $mysqli->query("SELECT id, mensagem, data_hora FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' ORDER BY id DESC LIMIT 3");
    if ($ultimas && $ultimas->num_rows > 0) {
        echo "📋 Últimas mensagens do número:\n";
        while ($msg = $ultimas->fetch_assoc()) {
            echo "   - ID {$msg['id']}: {$msg['mensagem']} ({$msg['data_hora']})\n";
        }
    }
}

echo "\n🎯 VERIFICAÇÃO CONCLUÍDA!\n";
?> 