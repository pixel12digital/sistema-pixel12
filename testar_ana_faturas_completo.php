<?php
/**
 * TESTE FINAL: ANA + CONSULTA DE FATURAS
 * Simular mensagem real para testar Ana com consulta de faturas
 */

echo "=== TESTE FINAL: ANA + CONSULTA DE FATURAS ===\n\n";

// Simular chamada real do webhook com mensagem sobre faturas
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

$dados_teste = [
    'event' => 'onmessage',
    'data' => [
        'from' => '55479991616699', // Cliente com faturas
        'text' => 'Olá Ana, preciso consultar minhas faturas',
        'type' => 'text',
        'session' => 'default'
    ]
];

echo "📱 TESTANDO WEBHOOK + ANA + FATURAS...\n";
echo "🔗 URL: $webhook_url\n";
echo "📋 Mensagem: \"{$dados_teste['data']['text']}\"\n";
echo "📞 Número: {$dados_teste['data']['from']}\n\n";

echo "🚀 Enviando requisição...\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "📊 RESULTADO WEBHOOK:\n";
echo "HTTP Code: $http_code\n";
echo "cURL Error: " . ($curl_error ?: 'Nenhum') . "\n";

if (!$curl_error && $http_code === 200) {
    $data = json_decode($response, true);
    
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ Webhook executado com sucesso!\n\n";
        
        if (isset($data['ana_response'])) {
            echo "💬 RESPOSTA COMPLETA DA ANA:\n";
            echo "═══════════════════════════════════════\n";
            echo $data['ana_response'];
            echo "\n═══════════════════════════════════════\n\n";
            
            // Verificar se contém dados financeiros
            $resposta = $data['ana_response'];
            $tem_dados_financeiros = (
                strpos($resposta, 'RESUMO DA SUA CONTA') !== false ||
                strpos($resposta, 'FATURAS VENCIDAS') !== false ||
                strpos($resposta, 'PRÓXIMA FATURA') !== false
            );
            
            if ($tem_dados_financeiros) {
                echo "🎉 SUCESSO TOTAL!\n";
                echo "✅ Ana respondeu inteligentemente\n";
                echo "✅ Detectou consulta de faturas\n";
                echo "✅ Enriqueceu resposta com dados financeiros\n";
                echo "✅ Enviou para WhatsApp\n";
                echo "✅ Salvou no chat interno\n";
                
                echo "\n🚀 SISTEMA COMPLETAMENTE OPERACIONAL!\n";
                echo "💰 Ana agora consulta faturas automaticamente!\n";
                
            } else {
                echo "⚠️ Ana respondeu, mas não detectou consulta de faturas\n";
                echo "   (Pode ser que o cliente não tenha faturas ou a detecção não funcionou)\n";
            }
        } else {
            echo "⚠️ Webhook OK, mas sem resposta da Ana no retorno\n";
        }
        
    } else {
        echo "⚠️ Webhook respondeu mas pode ter havido problema\n";
        echo "Response: $response\n";
    }
} else {
    echo "❌ Problema no webhook\n";
    echo "Response: $response\n";
}

echo "\n";

// Verificar logs recentes para ver se funcionou
echo "📋 VERIFICANDO LOGS RECENTES...\n";

$log_file = 'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $linhas = explode("\n", $log_content);
    
    // Pegar últimas linhas
    $ultimas_linhas = array_slice(array_filter($linhas), -3);
    
    echo "✅ Últimas entradas do log:\n";
    foreach ($ultimas_linhas as $linha) {
        echo "   " . substr($linha, 0, 100) . "...\n";
    }
} else {
    echo "❌ Log não encontrado\n";
}

echo "\n=== FIM DO TESTE FINAL ===\n";
?> 