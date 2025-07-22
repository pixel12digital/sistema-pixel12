<?php
echo "🚀 INICIANDO SERVIDOR LOCAL PARA RESOLVER PROBLEMA DO WEBHOOK\n\n";

echo "🔍 Verificando se o XAMPP está funcionando...\n";

// Testar XAMPP primeiro
$xampp_funcionando = false;
$portas_xampp = [80, 8080, 443];

foreach ($portas_xampp as $porta) {
    $url = ($porta === 443) ? "https://localhost:$porta/" : "http://localhost:$porta/";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 || $http_code === 301 || $http_code === 302) {
        echo "   ✅ XAMPP funcionando na porta $porta!\n";
        $xampp_funcionando = true;
        $porta_xampp = $porta;
        break;
    }
}

if ($xampp_funcionando) {
    echo "   🎉 XAMPP está funcionando! Usando XAMPP...\n\n";
    
    // Configurar webhook para XAMPP
    $base_url = ($porta_xampp === 443) ? "https://localhost" : "http://localhost";
    if ($porta_xampp != 80 && $porta_xampp != 443) {
        $base_url .= ":$porta_xampp";
    }
    $webhook_url = "$base_url/loja-virtual-revenda/api/webhook_whatsapp.php";
    
} else {
    echo "   ❌ XAMPP não está funcionando corretamente\n";
    echo "   🔧 Usando servidor PHP alternativo...\n\n";
    
    // Usar PHP built-in server como alternativa
    $porta_php = 8000;
    $webhook_url = "http://localhost:$porta_php/api/webhook_whatsapp.php";
    
    echo "📝 INSTRUÇÕES PARA SERVIDOR PHP:\n";
    echo "   1. Abra um novo terminal/prompt\n";
    echo "   2. Navegue até: C:\\xampp\\htdocs\\loja-virtual-revenda\n";
    echo "   3. Execute: php -S localhost:$porta_php\n";
    echo "   4. Deixe esse terminal aberto\n";
    echo "   5. Volte aqui e pressione Enter para continuar\n\n";
    
    echo "💡 Alternativa rápida: Execute este comando em outro terminal:\n";
    echo "   cd C:\\xampp\\htdocs\\loja-virtual-revenda && php -S localhost:$porta_php\n\n";
    
    // Aguardar o usuário
    echo "⌛ Aguardando servidor PHP iniciar... Pressione Enter quando pronto: ";
    fgets(STDIN);
    
    // Testar se o servidor PHP está funcionando
    echo "🧪 Testando servidor PHP...\n";
    $ch = curl_init("http://localhost:$porta_php/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 || $http_code === 404) {
        echo "   ✅ Servidor PHP funcionando!\n\n";
    } else {
        echo "   ❌ Servidor PHP não está respondendo\n";
        echo "   🔧 Verifique se executou o comando corretamente\n\n";
        exit;
    }
}

echo "🔗 Configurando webhook para: $webhook_url\n";

// Configurar webhook no VPS
$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
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

// Testar webhook
echo "🧪 Testando webhook...\n";
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 || $http_code === 400) {
    echo "✅ Webhook respondendo!\n";
} else {
    echo "❌ Webhook não está respondendo (HTTP $http_code)\n";
}

echo "\n";

// Enviar teste via VPS
echo "🚀 Enviando teste via VPS...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '5547997146908@c.us',
        'text' => 'TESTE SERVIDOR LOCAL ' . date('H:i:s'),
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
    echo "✅ Teste enviado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro no teste (HTTP $http_code)\n";
}

echo "\n=== 🎯 CONFIGURAÇÃO FINAL ===\n";
echo "✅ Webhook configurado para: $webhook_url\n";
echo "📱 Número para teste: 554797146908\n\n";

if (!$xampp_funcionando) {
    echo "⚠️ IMPORTANTE: Mantenha o servidor PHP rodando!\n";
    echo "   Comando: cd C:\\xampp\\htdocs\\loja-virtual-revenda && php -S localhost:$porta_php\n\n";
}

echo "🧪 TESTE AGORA:\n";
echo "   1. Envie uma mensagem WhatsApp para: 554797146908\n";
echo "   2. Execute: php monitorar_mensagens.php\n";
echo "   3. As mensagens devem aparecer!\n\n";

echo "🎉 Configuração concluída!\n";
?> 