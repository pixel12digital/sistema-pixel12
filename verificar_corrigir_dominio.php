<?php
echo "🔧 VERIFICANDO E CORRIGINDO DOMÍNIO\n";
echo "===================================\n\n";

$dominio_base = "https://app.pixel12digital.com.br";
$caminhos_teste = [
    '/loja-virtual-revenda/api/webhook_whatsapp_basico.php',
    '/api/webhook_whatsapp_basico.php',
    '/loja-virtual-revenda/webhook_whatsapp_basico.php',
    '/webhook_whatsapp_basico.php'
];

echo "🔍 TESTANDO DIFERENTES CAMINHOS:\n\n";

$caminho_funcionando = null;

foreach ($caminhos_teste as $caminho) {
    $url = $dominio_base . $caminho;
    echo "📡 Testando: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ FUNCIONANDO! (HTTP 200)\n";
        $caminho_funcionando = $caminho;
        break;
    } else {
        echo "   ❌ Não funciona (HTTP $http_code)\n";
    }
}

echo "\n";

if ($caminho_funcionando) {
    echo "🎉 CAMINHO FUNCIONANDO ENCONTRADO!\n";
    echo "   🔗 URL: $dominio_base$caminho_funcionando\n\n";
    
    // Testar com payload
    echo "🧪 TESTANDO COM PAYLOAD:\n";
    $payload_teste = [
        'event' => 'onmessage',
        'data' => [
            'from' => '554796164699',
            'text' => 'teste dominio',
            'type' => 'text'
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $dominio_base . $caminho_funcionando);
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
        echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n\n";
        
        // Configurar webhook para o domínio
        echo "📡 CONFIGURANDO WEBHOOK PARA DOMÍNIO:\n";
        $webhook_url_dominio = $dominio_base . $caminho_funcionando;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url_dominio]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "   ✅ Webhook configurado para domínio!\n";
            echo "   🔗 URL: $webhook_url_dominio\n\n";
            
            echo "🎉 SUCESSO! DOMÍNIO CONFIGURADO!\n";
            echo "   ✅ Não precisa mais manter XAMPP rodando\n";
            echo "   ✅ Webhook sempre acessível\n";
            echo "   ✅ Mais confiável para produção\n";
        } else {
            echo "   ❌ Erro ao configurar webhook (HTTP $http_code)\n";
        }
        
    } else {
        echo "   ❌ Erro ao processar payload (HTTP $http_code)\n";
    }
    
} else {
    echo "❌ NENHUM CAMINHO FUNCIONANDO ENCONTRADO\n\n";
    
    echo "🔧 POSSÍVEIS SOLUÇÕES:\n";
    echo "   1. Verificar se o arquivo foi enviado para o servidor\n";
    echo "   2. Verificar estrutura de pastas no servidor\n";
    echo "   3. Verificar permissões de arquivo\n";
    echo "   4. Manter usando localhost por enquanto\n\n";
    
    echo "📋 RECOMENDAÇÃO ATUAL:\n";
    echo "   Continue usando localhost que está funcionando perfeitamente.\n";
    echo "   URL atual: http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp_basico.php\n";
}

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 