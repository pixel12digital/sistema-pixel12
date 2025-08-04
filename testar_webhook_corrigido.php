<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🧪 TESTANDO WEBHOOK CORRIGIDO\n";
echo "============================\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// 1. TESTE CANAL ANA
echo "🤖 1. TESTE CANAL ANA:\n";
echo "======================\n";

$teste_ana = json_encode([
    'from' => '554796164699@c.us',     // SEU número (remetente)
    'to' => '554797146908@c.us',       // Canal Ana (destinatário)
    'body' => '🧪 TESTE CANAL ANA CORRIGIDO - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

$ch_ana = curl_init($webhook_url);
curl_setopt($ch_ana, CURLOPT_POST, true);
curl_setopt($ch_ana, CURLOPT_POSTFIELDS, $teste_ana);
curl_setopt($ch_ana, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch_ana, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_ana, CURLOPT_TIMEOUT, 15);
curl_setopt($ch_ana, CURLOPT_SSL_VERIFYPEER, false);

$response_ana = curl_exec($ch_ana);
$code_ana = curl_getinfo($ch_ana, CURLINFO_HTTP_CODE);
curl_close($ch_ana);

echo "Status: HTTP $code_ana\n";
if ($code_ana === 200) {
    echo "✅ Webhook respondeu\n";
    $data_ana = json_decode($response_ana, true);
    if ($data_ana && isset($data_ana['success']) && $data_ana['success']) {
        echo "✅ Ana processou mensagem\n";
    }
} else {
    echo "❌ Webhook falhou\n";
}

// 2. VERIFICAR SE FOI SALVA NO CANAL ANA
sleep(3);
echo "\n🔍 VERIFICANDO BANCO DE DADOS:\n";
echo "==============================\n";

$msg_ana = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '554797146908' 
    AND mensagem LIKE '%TESTE CANAL ANA CORRIGIDO%'
    ORDER BY id DESC LIMIT 1
")->fetch_assoc();

if ($msg_ana) {
    echo "✅ SUCESSO! Mensagem salva no Canal Ana\n";
    echo "   ID: {$msg_ana['id']}\n";
    echo "   Canal: {$msg_ana['numero_whatsapp']}\n";
    echo "   Mensagem: {$msg_ana['mensagem']}\n";
} else {
    echo "❌ FALHA! Mensagem NÃO foi salva no Canal Ana\n";
    
    // Verificar se foi salva no seu número (erro antigo)
    $msg_seu = $mysqli->query("
        SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp = '554796164699' 
        AND mensagem LIKE '%TESTE CANAL ANA CORRIGIDO%'
        ORDER BY id DESC LIMIT 1
    ")->fetch_assoc();
    
    if ($msg_seu) {
        echo "⚠️ Mensagem foi salva no SEU número - PROBLEMA AINDA EXISTE!\n";
    } else {
        echo "❌ Mensagem não foi salva em lugar nenhum!\n";
    }
}

// 3. TESTE CANAL HUMANO (se webhook 3001 for corrigido)
echo "\n👥 2. TESTE CANAL HUMANO:\n";
echo "========================\n";

$teste_humano = json_encode([
    'from' => '554796164699@c.us',     // SEU número (remetente)
    'to' => '554797309525@c.us',       // Canal Humano (destinatário)
    'body' => '🧪 TESTE CANAL HUMANO CORRIGIDO - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

$ch_humano = curl_init($webhook_url);
curl_setopt($ch_humano, CURLOPT_POST, true);
curl_setopt($ch_humano, CURLOPT_POSTFIELDS, $teste_humano);
curl_setopt($ch_humano, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch_humano, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_humano, CURLOPT_TIMEOUT, 15);
curl_setopt($ch_humano, CURLOPT_SSL_VERIFYPEER, false);

$response_humano = curl_exec($ch_humano);
$code_humano = curl_getinfo($ch_humano, CURLINFO_HTTP_CODE);
curl_close($ch_humano);

echo "Status: HTTP $code_humano\n";
if ($code_humano === 200) {
    echo "✅ Webhook respondeu\n";
} else {
    echo "❌ Webhook falhou\n";
}

// Verificar se foi salva no canal humano
sleep(2);
$msg_humano = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '554797309525' 
    AND mensagem LIKE '%TESTE CANAL HUMANO CORRIGIDO%'
    ORDER BY id DESC LIMIT 1
")->fetch_assoc();

if ($msg_humano) {
    echo "✅ SUCESSO! Mensagem salva no Canal Humano\n";
    echo "   ID: {$msg_humano['id']}\n";
    echo "   Canal: {$msg_humano['numero_whatsapp']}\n";
} else {
    echo "❌ Mensagem NÃO foi salva no Canal Humano\n";
}

// 4. RESUMO FINAL
echo "\n🎯 RESUMO DO TESTE:\n";
echo "==================\n";

$hoje_ana = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '554797146908' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];
$hoje_humano = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp = '554797309525' AND DATE(data_hora) = CURDATE()")->fetch_assoc()['total'];

echo "Mensagens hoje:\n";
echo "🤖 Canal Ana: $hoje_ana\n";
echo "👥 Canal Humano: $hoje_humano\n\n";

if ($msg_ana) {
    echo "🎉 WEBHOOK CORRIGIDO COM SUCESSO!\n";
    echo "✅ Agora as mensagens são salvas no canal correto\n";
    echo "\n📱 TESTE REAL:\n";
    echo "1. Envie mensagem do seu WhatsApp (554796164699) para +55 47 97146908\n";
    echo "2. A mensagem aparecerá no Canal Ana\n";
    echo "3. Ana deve responder automaticamente\n";
} else {
    echo "❌ WEBHOOK AINDA PRECISA DE AJUSTES\n";
    echo "Verifique os logs de erro para mais detalhes\n";
}

?> 