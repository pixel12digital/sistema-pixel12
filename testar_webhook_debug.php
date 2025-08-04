<?php
echo "🔍 TESTANDO WEBHOOK DEBUG SIMPLES\n";
echo "=================================\n\n";

$webhook_debug_url = 'https://app.pixel12digital.com.br/webhook_debug_simples.php';

// Teste 
$teste = json_encode([
    'from' => '554796164699@c.us',
    'body' => '🔍 TESTE DEBUG WEBHOOK - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

echo "📤 ENVIANDO PARA WEBHOOK DEBUG:\n";
echo "===============================\n";

$ch = curl_init($webhook_debug_url);
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
echo "Resposta: $response\n\n";

if ($code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ WEBHOOK DEBUG FUNCIONOU!\n";
        echo "Message ID: {$data['message_id']}\n";
        echo "Canal: {$data['canal_correto']}\n";
        
        // Verificar no banco
        require_once 'config.php';
        require_once 'painel/db.php';
        
        $msg_verificar = $mysqli->query("
            SELECT * FROM mensagens_comunicacao 
            WHERE id = {$data['message_id']}
        ")->fetch_assoc();
        
        if ($msg_verificar) {
            echo "\n✅ CONFIRMADO NO BANCO:\n";
            echo "ID: {$msg_verificar['id']}\n";
            echo "Canal: {$msg_verificar['numero_whatsapp']}\n";
            echo "Mensagem: {$msg_verificar['mensagem']}\n";
            echo "Data: {$msg_verificar['data_hora']}\n";
            
            if ($msg_verificar['numero_whatsapp'] === '554797146908') {
                echo "\n🎉 SUCESSO TOTAL! WEBHOOK SALVA NO CANAL CORRETO!\n";
                echo "🔧 O problema estava na lógica do webhook original!\n";
            } else {
                echo "\n❌ Ainda salva no canal errado: {$msg_verificar['numero_whatsapp']}\n";
            }
        }
    } else {
        echo "❌ Webhook retornou erro\n";
    }
} else {
    echo "❌ Webhook falhou com HTTP $code\n";
}

// Verificar log debug
if (file_exists('webhook_debug.log')) {
    echo "\n📋 ÚLTIMAS LINHAS DO LOG DEBUG:\n";
    echo "===============================\n";
    $log_lines = file('webhook_debug.log');
    foreach (array_slice($log_lines, -10) as $line) {
        echo $line;
    }
}

?> 