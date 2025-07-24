<?php
require_once __DIR__ . '/config.php';

echo "🔧 CORRIGINDO PROBLEMA DO WEBHOOK - DETECÇÃO AUTOMÁTICA DE PORTA\n\n";

// Testar diferentes portas e protocolos possíveis do XAMPP
$testes = [
    ['url' => 'http://localhost/loja-virtual-revenda/', 'porta' => 80, 'protocolo' => 'http'],
    ['url' => 'http://localhost:8080/loja-virtual-revenda/', 'porta' => 8080, 'protocolo' => 'http'],
    ['url' => 'https://localhost/loja-virtual-revenda/', 'porta' => 443, 'protocolo' => 'https'],
    ['url' => 'https://localhost:443/loja-virtual-revenda/', 'porta' => 443, 'protocolo' => 'https'],
    ['url' => 'https://localhost:8443/loja-virtual-revenda/', 'porta' => 8443, 'protocolo' => 'https'],
    ['url' => 'http://127.0.0.1/loja-virtual-revenda/', 'porta' => 80, 'protocolo' => 'http'],
    ['url' => 'http://127.0.0.1:8080/loja-virtual-revenda/', 'porta' => 8080, 'protocolo' => 'http'],
];

$configuracao_funcionando = null;

echo "🔍 Testando portas e protocolos do XAMPP...\n";

foreach ($testes as $teste) {
    echo "   Testando {$teste['protocolo']}://localhost";
    if ($teste['porta'] != 80 && $teste['porta'] != 443) echo ":{$teste['porta']}";
    echo " ... ";
    
    $ch = curl_init($teste['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para HTTPS sem certificado
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 || $http_code === 301 || $http_code === 302) {
        echo "✅ FUNCIONANDO!\n";
        $configuracao_funcionando = $teste;
        break;
    } else {
        echo "❌ (HTTP $http_code)\n";
    }
}

if (!$configuracao_funcionando) {
    echo "\n❌ PROBLEMA: XAMPP não está respondendo em nenhuma configuração!\n";
    echo "🔧 POSSÍVEIS SOLUÇÕES:\n";
    echo "   1. Verifique se o Apache realmente iniciou\n";
    echo "   2. Teste acessar manualmente: http://localhost/\n";
    echo "   3. Verifique se não há outro software usando as portas\n";
    echo "   4. Reinicie o Apache no XAMPP\n\n";
    
    // Tentar um teste simples da raiz
    echo "🧪 Testando acesso à raiz do XAMPP...\n";
    $ch = curl_init('http://localhost/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   http://localhost/ retornou: HTTP $http_code\n";
    if ($http_code === 200) {
        echo "   ✅ XAMPP está funcionando na raiz!\n";
        echo "   💡 O problema pode ser com a pasta do projeto\n";
        echo "   🔧 Verifique se a pasta 'loja-virtual-revenda' existe em htdocs\n";
    }
    
    exit;
}

$porta = $configuracao_funcionando['porta'];
$protocolo = $configuracao_funcionando['protocolo'];
$base_url = $protocolo . '://localhost';
if (($protocolo === 'http' && $porta != 80) || ($protocolo === 'https' && $porta != 443)) {
    $base_url .= ':' . $porta;
}

echo "\n✅ XAMPP encontrado funcionando!\n";
echo "🌐 Protocolo: $protocolo\n";
echo "🔌 Porta: $porta\n";
echo "🌍 URL base: $base_url\n\n";

// Configurar webhook com a configuração correta
$webhook_url_correta = "$base_url/loja-virtual-revenda/api/webhook_whatsapp.php";
echo "🔗 Configurando webhook para: $webhook_url_correta\n";

$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url_correta]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook configurado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "❌ Erro ao configurar webhook (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}

echo "\n";

// Testar o webhook configurado
echo "🧪 Testando webhook configurado...\n";
$ch = curl_init($webhook_url_correta);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 || $http_code === 400) {
    echo "✅ Webhook está respondendo!\n";
    echo "📝 Resposta HTTP: $http_code\n";
} else {
    echo "❌ Webhook ainda não está funcionando (HTTP $http_code)\n";
}

echo "\n";

// Teste final enviando uma mensagem de teste via VPS
echo "🚀 Enviando mensagem de teste via VPS...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '5547997146908@c.us',
        'text' => 'TESTE WEBHOOK CORRIGIDO ' . date('H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init('http://212.85.11.238:3000/webhook/test');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Teste de webhook enviado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro no teste de webhook (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}

echo "\n=== 🎯 RESUMO ===\n";
if ($configuracao_funcionando) {
    echo "✅ PROBLEMA CORRIGIDO!\n\n";
    echo "📋 Configuração atual:\n";
    echo "   🌐 XAMPP rodando em: $protocolo://localhost";
    if (($protocolo === 'http' && $porta != 80) || ($protocolo === 'https' && $porta != 443)) {
        echo ":$porta";
    }
    echo "\n";
    echo "   🔗 Webhook configurado para: $webhook_url_correta\n";
    echo "   📱 Número para teste: 554797146908\n\n";
    
    echo "🧪 PRÓXIMOS PASSOS:\n";
    echo "   1. Envie uma mensagem WhatsApp para: 554797146908\n";
    echo "   2. Execute: php monitorar_mensagens.php\n";
    echo "   3. As mensagens devem aparecer no chat agora!\n\n";
} else {
    echo "❌ Não foi possível configurar o webhook!\n\n";
}

echo "🎉 Script concluído!\n";
?> 