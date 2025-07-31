<?php
echo "🔍 VERIFICANDO ACESSO PELO DOMÍNIO\n";
echo "==================================\n\n";

// URLs para testar
$urls = [
    'localhost' => 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp_basico.php',
    'dominio' => 'https://app.pixel12digital.com.br/loja-virtual-revenda/api/webhook_whatsapp_basico.php'
];

echo "🌐 TESTANDO ACESSO AOS WEBHOOKS:\n\n";

foreach ($urls as $nome => $url) {
    echo "📡 TESTANDO $nome:\n";
    echo "   URL: $url\n";
    
    // Testar acesso básico
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Acessível (HTTP 200)\n";
        echo "   📄 Resposta: " . substr($response, 0, 100) . "...\n";
    } else {
        echo "   ❌ Não acessível (HTTP $http_code)\n";
        if ($error) {
            echo "   🔧 Erro: $error\n";
        }
    }
    
    // Testar com payload
    $payload_teste = [
        'event' => 'onmessage',
        'data' => [
            'from' => '554796164699',
            'text' => 'teste ' . $nome,
            'type' => 'text'
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_teste));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Payload processado com sucesso!\n";
        echo "   📄 Resposta: " . substr($response, 0, 150) . "...\n";
    } else {
        echo "   ❌ Erro ao processar payload (HTTP $http_code)\n";
    }
    
    echo "\n";
}

// Verificar qual é melhor para webhook
echo "🔧 ANÁLISE PARA WEBHOOK:\n";
echo "   📍 Localhost (8080):\n";
echo "      ✅ Vantagens: Sempre disponível quando XAMPP rodando\n";
echo "      ❌ Desvantagens: Precisa manter XAMPP ligado\n";
echo "      🔗 URL: http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp_basico.php\n\n";

echo "   🌐 Domínio (app.pixel12digital):\n";
echo "      ✅ Vantagens: Sempre acessível, não depende do XAMPP\n";
echo "      ✅ Vantagens: Mais confiável para produção\n";
echo "      ❌ Desvantagens: Pode ter problemas de SSL/certificado\n";
echo "      🔗 URL: https://app.pixel12digital.com.br/loja-virtual-revenda/api/webhook_whatsapp_basico.php\n\n";

echo "📋 RECOMENDAÇÃO:\n";
echo "   Se o domínio estiver funcionando, use o domínio para maior confiabilidade.\n";
echo "   Se houver problemas com SSL, use localhost como backup.\n";

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 