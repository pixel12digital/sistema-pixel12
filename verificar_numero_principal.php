<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO NÚMERO PRINCIPAL CANAL 3000\n";
echo "==========================================\n\n";

$numero_principal = '554796164699';

echo "📱 NÚMERO: +55 47 96164699\n";
echo "============================\n\n";

// Últimas mensagens deste número
$mensagens = $mysqli->query("
    SELECT id, direcao, SUBSTRING(mensagem, 1, 100) as msg, data_hora
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '$numero_principal'
    ORDER BY data_hora DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

if (!empty($mensagens)) {
    echo "✅ ÚLTIMAS 10 MENSAGENS DESTE NÚMERO:\n";
    echo "=====================================\n";
    foreach ($mensagens as $msg) {
        $tipo = $msg['direcao'] === 'recebido' ? '📩 RECEBIDA' : '📤 ENVIADA (Ana)';
        echo "$tipo | ID {$msg['id']} | {$msg['data_hora']}\n";
        echo "   {$msg['msg']}...\n\n";
    }
    
    $total = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$numero_principal'")->fetch_assoc()['total'];
    echo "📊 TOTAL: $total mensagens neste número\n\n";
    
    $hoje = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$numero_principal' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];
    echo "📊 HOJE: $hoje mensagens\n\n";
    
    echo "🎯 ESTE PARECE SER O NÚMERO CORRETO DO CANAL 3000!\n";
    echo "===================================================\n\n";
    
    echo "📱 PARA TESTAR:\n";
    echo "===============\n";
    echo "1. Envie mensagem no WhatsApp para: +55 47 96164699\n";
    echo "2. Digite: 'Teste Ana'\n";
    echo "3. Ana deve responder em segundos\n\n";
    
} else {
    echo "❌ Nenhuma mensagem encontrada para este número\n";
}

// Verificar outros números similares
echo "🔍 OUTROS NÚMEROS SIMILARES:\n";
echo "============================\n";

$similares = $mysqli->query("
    SELECT numero_whatsapp, COUNT(*) as total, MAX(data_hora) as ultima
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp LIKE '%96164699%'
    GROUP BY numero_whatsapp
    ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);

foreach ($similares as $sim) {
    echo "📞 {$sim['numero_whatsapp']}: {$sim['total']} mensagens (última: {$sim['ultima']})\n";
}

?> 