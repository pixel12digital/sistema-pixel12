<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🧪 TESTANDO WEBHOOK FORÇADO PARA CANAL ANA\n";
echo "==========================================\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// Teste simples
$teste = json_encode([
    'from' => '554796164699@c.us',
    'body' => '🧪 TESTE FORÇADO CANAL ANA - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

echo "📤 ENVIANDO PARA WEBHOOK:\n";
echo "=========================\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $teste);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $code\n";
if ($code === 200) {
    echo "✅ Webhook processou\n";
} else {
    echo "❌ Webhook falhou\n";
}

// Verificar onde foi salvo
sleep(3);
echo "\n🔍 VERIFICANDO ONDE FOI SALVO:\n";
echo "==============================\n";

// Verificar no Canal Ana
$msg_ana = $mysqli->query("
    SELECT * FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '554797146908' 
    AND mensagem LIKE '%TESTE FORÇADO CANAL ANA%'
    ORDER BY id DESC LIMIT 1
")->fetch_assoc();

if ($msg_ana) {
    echo "✅ SUCESSO! Mensagem salva no Canal Ana\n";
    echo "   ID: {$msg_ana['id']}\n";
    echo "   Canal: {$msg_ana['numero_whatsapp']}\n";
    echo "   Data: {$msg_ana['data_hora']}\n";
    echo "   Mensagem: {$msg_ana['mensagem']}\n";
} else {
    echo "❌ NÃO encontrada no Canal Ana\n";
    
    // Verificar se foi salva no seu número (problema antigo)
    $msg_seu = $mysqli->query("
        SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp = '554796164699' 
        AND mensagem LIKE '%TESTE FORÇADO CANAL ANA%'
        ORDER BY id DESC LIMIT 1
    ")->fetch_assoc();
    
    if ($msg_seu) {
        echo "⚠️ PROBLEMA: Ainda salvou no SEU número\n";
        echo "   ID: {$msg_seu['id']}\n";
        echo "   Número: {$msg_seu['numero_whatsapp']}\n";
    } else {
        echo "❌ NÃO encontrada em lugar nenhum!\n";
    }
}

echo "\n🎯 RESULTADO:\n";
echo "=============\n";
if ($msg_ana) {
    echo "🎉 WEBHOOK CORRIGIDO! Agora salva no canal correto!\n";
    echo "📱 Teste real: Envie mensagem para +55 47 97146908\n";
} else {
    echo "❌ Ainda há problemas na lógica do webhook\n";
}

?> 