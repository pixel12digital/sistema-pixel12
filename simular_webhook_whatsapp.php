<?php
/**
 * SIMULADOR DE WEBHOOK WHATSAPP
 * 
 * Este script simula o recebimento de mensagens do WhatsApp
 * e as envia para o webhook do sistema
 */

echo "=== SIMULADOR DE WEBHOOK WHATSAPP ===\n\n";

// Função para enviar webhook
function enviarWebhook($numero, $mensagem, $tipo = 'text') {
    $webhook_data = [
        'event' => 'onmessage',
        'data' => [
            'from' => $numero,
            'text' => $mensagem,
            'type' => $tipo,
            'timestamp' => time()
        ]
    ];
    
    $webhook_url = 'http://localhost:8080/loja-virtual-revenda/api/webhook.php';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($webhook_data),
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($webhook_url, false, $context);
    return $response;
}

// Simular mensagem do número 4796164699
echo "1. Simulando mensagem do número 4796164699...\n";
$numero_teste = '4796164699';
$mensagem_teste = 'Olá! Esta é uma mensagem de teste do número ' . $numero_teste . ' - ' . date('Y-m-d H:i:s');

$resultado = enviarWebhook($numero_teste, $mensagem_teste);
echo "   Resposta: $resultado\n";

// Verificar se foi salvo no banco
echo "\n2. Verificando se foi salvo no banco...\n";
require_once 'painel/config.php';
require_once 'painel/db.php';

$sql = "SELECT id, cliente_id, mensagem, direcao, status, data_hora 
        FROM mensagens_comunicacao 
        WHERE mensagem LIKE '%$numero_teste%' 
        ORDER BY id DESC LIMIT 1";

$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "✅ Mensagem salva com sucesso:\n";
    echo "   ID: {$row['id']}\n";
    echo "   Cliente: {$row['cliente_id']}\n";
    echo "   Direção: {$row['direcao']}\n";
    echo "   Status: {$row['status']}\n";
    echo "   Data: {$row['data_hora']}\n";
} else {
    echo "❌ Mensagem não foi salva no banco\n";
}

$mysqli->close();

echo "\n=== SIMULAÇÃO CONCLUÍDA ===\n";
echo "\n📋 INSTRUÇÕES:\n";
echo "1. Este script simula o que aconteceria quando uma mensagem é recebida\n";
echo "2. Para receber mensagens reais, o servidor WhatsApp precisa ser atualizado\n";
echo "3. Enquanto isso, você pode usar este script para testar o sistema\n";
echo "\n🔧 PARA TESTAR COM OUTRO NÚMERO:\n";
echo "Modifique as variáveis \$numero_teste e \$mensagem_teste no script\n";
?> 