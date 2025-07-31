<?php
echo "🔧 CORRIGINDO CANAL FINANCEIRO NO DOMÍNIO\n";
echo "=========================================\n\n";

// URL do webhook financeiro no domínio
$webhook_url_financeiro = "https://app.pixel12digital.com.br/api/webhook_whatsapp.php";

echo "🔗 Configurando webhook financeiro para: $webhook_url_financeiro\n\n";

// 1. Verificar se o webhook financeiro existe no domínio
echo "📁 VERIFICANDO WEBHOOK FINANCEIRO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url_financeiro);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook financeiro existe no domínio!\n";
} else {
    echo "   ❌ Webhook financeiro não encontrado (HTTP $http_code)\n";
    echo "   🔧 Tentando caminhos alternativos...\n";
    
    // Tentar caminhos alternativos
    $caminhos_alternativos = [
        'https://app.pixel12digital.com.br/loja-virtual-revenda/api/webhook_whatsapp.php',
        'https://app.pixel12digital.com.br/painel/api/webhook_whatsapp.php',
        'https://app.pixel12digital.com.br/webhook_whatsapp.php'
    ];
    
    foreach ($caminhos_alternativos as $caminho) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $caminho);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "   ✅ Encontrado em: $caminho\n";
            $webhook_url_financeiro = $caminho;
            break;
        }
    }
}

echo "\n";

// 2. Configurar webhook financeiro na VPS (porta 3000)
echo "📡 CONFIGURANDO CANAL FINANCEIRO NA VPS (PORTA 3000):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url_financeiro]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Canal financeiro configurado com sucesso!\n";
} else {
    echo "   ❌ Erro ao configurar canal financeiro (HTTP $http_code)\n";
    echo "   📄 Resposta: $response\n";
}

echo "\n";

// 3. Verificar se há canal comercial configurado (porta 3001)
echo "📡 VERIFICANDO CANAL COMERCIAL (PORTA 3001):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Canal comercial configurado\n";
    echo "   🔗 URL: " . ($data['webhook_url'] ?? 'não definida') . "\n";
} else {
    echo "   ⚠️ Canal comercial não configurado ou não existe (HTTP $http_code)\n";
}

echo "\n";

// 4. Testar recebimento de mensagem no canal financeiro
echo "🧪 TESTANDO RECEBIMENTO NO CANAL FINANCEIRO:\n";
$payload_teste = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'teste canal financeiro',
        'type' => 'text'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url_financeiro);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_teste));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Mensagem processada com sucesso!\n";
    echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n";
} else {
    echo "   ❌ Erro ao processar mensagem (HTTP $http_code)\n";
    echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
}

echo "\n";

// 5. Verificar configuração atual do canal financeiro
echo "📋 CONFIGURAÇÃO ATUAL DO CANAL FINANCEIRO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Canal financeiro configurado\n";
    echo "   🔗 URL: " . ($data['webhook_url'] ?? 'não definida') . "\n";
} else {
    echo "   ❌ Erro ao consultar configuração (HTTP $http_code)\n";
}

echo "\n";

// 6. Instruções finais
echo "📱 INSTRUÇÕES DE TESTE:\n";
echo "   1. Acesse: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "   2. Envie uma mensagem para o WhatsApp: 47 96164699\n";
echo "   3. Verifique se a mensagem aparece no chat centralizado\n";
echo "   4. Confirme se o sistema responde automaticamente\n";

echo "\n🎉 CORREÇÃO CONCLUÍDA!\n";
echo "O canal financeiro deve estar funcionando novamente no domínio!\n";
?> 