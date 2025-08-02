<?php
echo "🔍 TESTANDO URLs DA ANA\n";
echo "======================\n\n";

$urls_ana = [
    'URL 1 (atual)' => 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php',
    'URL 2 (sem ai-agents)' => 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php',
    'URL 3 (root)' => 'https://agentes.pixel12digital.com.br/agent_chat.php',
    'URL 4 (chat)' => 'https://agentes.pixel12digital.com.br/chat/agent_chat.php',
    'URL 5 (v1)' => 'https://agentes.pixel12digital.com.br/v1/chat/agent_chat.php'
];

$test_message = "Olá Ana, teste de conectividade";

foreach ($urls_ana as $nome => $url) {
    echo "🧪 Testando $nome:\n";
    echo "URL: $url\n";
    
    $payload = json_encode([
        'question' => $test_message,
        'agent_id' => '3'
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "Status: HTTP $http_code\n";
    
    if ($curl_error) {
        echo "❌ Erro cURL: $curl_error\n";
    } elseif ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            echo "✅ FUNCIONANDO! Ana respondeu:\n";
            echo "   " . substr($data['response'], 0, 100) . "...\n";
            echo "🎉 URL CORRETA ENCONTRADA: $url\n";
        } else {
            echo "⚠️ Responde mas formato inválido\n";
            echo "   Resposta: " . substr($response, 0, 100) . "\n";
        }
    } else {
        echo "❌ Falhou - Resposta: " . substr($response, 0, 100) . "\n";
    }
    
    echo "\n";
}

// Teste adicional - verificar se o domínio está online
echo "🌐 Testando domínio base:\n";
$base_check = curl_init('https://agentes.pixel12digital.com.br/');
curl_setopt($base_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($base_check, CURLOPT_TIMEOUT, 10);
curl_setopt($base_check, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($base_check, CURLOPT_NOBODY, true);

$base_response = curl_exec($base_check);
$base_code = curl_getinfo($base_check, CURLINFO_HTTP_CODE);
curl_close($base_check);

echo "Domínio agentes.pixel12digital.com.br: HTTP $base_code\n";

if ($base_code === 200) {
    echo "✅ Domínio online\n";
} else {
    echo "❌ Domínio offline ou com problemas\n";
}

echo "\n📋 CONCLUSÃO:\n";
echo "=============\n";
echo "Se nenhuma URL funcionou, usar fallback temporário\n";
echo "Se uma URL funcionou, atualizar integrador_ana_local.php\n";
?> 