<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO CANAIS CORRETOS\n";
echo "==============================\n\n";

$canal_ana = '554797146908';      // Canal 3000 - Ana
$canal_humano = '554797309525';   // Canal 3001 - Humanos  
$seu_numero = '554796164699';     // Seu WhatsApp

// 1. VERIFICAR CANAL ANA (3000)
echo "🤖 1. CANAL ANA (3000) - $canal_ana:\n";
echo "=====================================\n";

$msgs_ana = $mysqli->query("
    SELECT id, direcao, SUBSTRING(mensagem, 1, 80) as msg, data_hora
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '$canal_ana'
    ORDER BY data_hora DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

if (!empty($msgs_ana)) {
    foreach ($msgs_ana as $msg) {
        $tipo = $msg['direcao'] === 'recebido' ? '📩' : '📤';
        echo "$tipo ID {$msg['id']} | {$msg['data_hora']}\n";
        echo "   {$msg['msg']}...\n\n";
    }
    
    $total_ana = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$canal_ana'")->fetch_assoc()['total'];
    echo "📊 Total mensagens Canal Ana: $total_ana\n\n";
} else {
    echo "❌ Nenhuma mensagem no Canal Ana\n\n";
}

// 2. VERIFICAR CANAL HUMANO (3001)
echo "👥 2. CANAL HUMANO (3001) - $canal_humano:\n";
echo "=========================================\n";

$msgs_humano = $mysqli->query("
    SELECT id, direcao, SUBSTRING(mensagem, 1, 80) as msg, data_hora
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '$canal_humano'
    ORDER BY data_hora DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

if (!empty($msgs_humano)) {
    foreach ($msgs_humano as $msg) {
        $tipo = $msg['direcao'] === 'recebido' ? '📩' : '📤';
        echo "$tipo ID {$msg['id']} | {$msg['data_hora']}\n";
        echo "   {$msg['msg']}...\n\n";
    }
    
    $total_humano = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$canal_humano'")->fetch_assoc()['total'];
    echo "📊 Total mensagens Canal Humano: $total_humano\n\n";
} else {
    echo "❌ Nenhuma mensagem no Canal Humano\n\n";
}

// 3. VERIFICAR SEU NÚMERO (ENVIANDO PARA OS CANAIS)
echo "📱 3. SEU WHATSAPP - $seu_numero:\n";
echo "=================================\n";

$suas_msgs = $mysqli->query("
    SELECT id, direcao, SUBSTRING(mensagem, 1, 80) as msg, data_hora
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '$seu_numero'
    ORDER BY data_hora DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

if (!empty($suas_msgs)) {
    foreach ($suas_msgs as $msg) {
        $tipo = $msg['direcao'] === 'recebido' ? '📩' : '📤';
        echo "$tipo ID {$msg['id']} | {$msg['data_hora']}\n";
        echo "   {$msg['msg']}...\n\n";
    }
    
    $total_seu = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$seu_numero'")->fetch_assoc()['total'];
    echo "📊 Total suas mensagens: $total_seu\n\n";
} else {
    echo "❌ Nenhuma mensagem do seu número\n\n";
}

// 4. RESUMO E ORIENTAÇÃO
echo "🎯 RESUMO:\n";
echo "==========\n";

$hoje_ana = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$canal_ana' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];
$hoje_humano = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$canal_humano' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];
$hoje_seu = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '$seu_numero' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];

echo "Mensagens hoje:\n";
echo "  🤖 Canal Ana (3000): $hoje_ana\n";
echo "  👥 Canal Humano (3001): $hoje_humano\n";
echo "  📱 Seu número: $hoje_seu\n\n";

echo "📱 PARA TESTAR:\n";
echo "===============\n";
echo "1. 🤖 TESTAR ANA: Envie mensagem do seu WhatsApp ($seu_numero) para +55 47 97146908\n";
echo "2. 👥 TESTAR HUMANO: Envie mensagem do seu WhatsApp ($seu_numero) para +55 47 97309525\n\n";

echo "💡 IMPORTANTE:\n";
echo "=============\n";
echo "- Você envia DE: $seu_numero (seu WhatsApp)\n";
echo "- PARA Ana: +55 47 97146908 (Canal 3000)\n";
echo "- PARA Humanos: +55 47 97309525 (Canal 3001)\n";

?> 