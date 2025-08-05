<?php
/**
 * 🧪 TESTE DIRETO DO WEBHOOK
 * Simula mensagem real para testar se Ana responde via WhatsApp
 */

echo "🧪 TESTE DIRETO DO WEBHOOK - ANA\n";
echo "=================================\n\n";

// Configuração
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';
$numero_teste = '554796164699';
$mensagem_teste = '🧪 Teste webhook direto - ' . date('H:i:s');

echo "📱 Simulando mensagem para Ana:\n";
echo "   De: $numero_teste\n";
echo "   Mensagem: $mensagem_teste\n";
echo "   Webhook: $webhook_url\n\n";

// Payload da mensagem (formato real do WhatsApp)
$payload = [
    'event' => 'onmessage',
    'data' => [
        'from' => $numero_teste,
        'text' => $mensagem_teste,
        'type' => 'text',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

echo "📤 Enviando para webhook...\n";

// Enviar para webhook
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: WhatsApp-API/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Resposta HTTP: $http_code\n";
echo "📡 Resposta: $response\n";

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
} else {
    if ($http_code == 200) {
        $response_data = json_decode($response, true);
        if (isset($response_data['success']) && $response_data['success']) {
            echo "✅ WEBHOOK PROCESSADO COM SUCESSO!\n";
            echo "✅ Fonte: " . ($response_data['source'] ?? 'N/A') . "\n";
            if (isset($response_data['ana_response'])) {
                echo "✅ Ana respondeu: " . substr($response_data['ana_response'], 0, 100) . "...\n";
            }
        } else {
            echo "❌ Erro no webhook: " . ($response_data['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "❌ Erro HTTP: $http_code\n";
    }
}

echo "\n🔍 VERIFICANDO SE A MENSAGEM FOI SALVA...\n";
echo "==========================================\n";

// Aguardar um pouco para o processamento
sleep(2);

// Verificar no banco
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp = '$numero_teste' 
        AND DATE(data_hora) = CURDATE()
        ORDER BY data_hora DESC 
        LIMIT 3";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "📋 MENSAGENS RECENTES:\n";
    while ($row = $result->fetch_assoc()) {
        $direcao = $row['direcao'] == 'recebido' ? '📥' : '📤';
        $status = $row['status'];
        $hora = date('H:i:s', strtotime($row['data_hora']));
        $msg = substr($row['mensagem'], 0, 60) . (strlen($row['mensagem']) > 60 ? '...' : '');
        
        echo "   $direcao [$hora] $status: $msg\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada\n";
}

echo "\n🎯 RESULTADO DO TESTE:\n";
echo "======================\n";
if ($http_code == 200) {
    echo "✅ WEBHOOK FUNCIONANDO - Verifique se Ana enviou resposta\n";
    echo "📱 Verifique o WhatsApp se recebeu a resposta da Ana\n";
} else {
    echo "❌ WEBHOOK COM PROBLEMA - Verifique configurações\n";
}
?> 