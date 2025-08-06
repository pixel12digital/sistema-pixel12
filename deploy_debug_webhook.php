<?php
/**
 * 🚀 DEPLOY DEBUG WEBHOOK
 * 
 * Este script faz o deploy do arquivo debug_webhook_real.php
 */

echo "🚀 DEPLOY DEBUG WEBHOOK\n";
echo "======================\n\n";

// ===== 1. VERIFICAR SE ARQUIVO LOCAL EXISTE =====
echo "1️⃣ VERIFICANDO ARQUIVO LOCAL:\n";
echo "=============================\n";

$local_file = 'debug_webhook_real.php';

if (!file_exists($local_file)) {
    echo "❌ Arquivo local não encontrado: $local_file\n";
    exit;
}

echo "✅ Arquivo local encontrado\n";
echo "📄 Tamanho: " . filesize($local_file) . " bytes\n";

// ===== 2. TESTAR ACESSIBILIDADE ATUAL =====
echo "\n2️⃣ TESTANDO ACESSIBILIDADE ATUAL:\n";
echo "==================================\n";

$test_url = "https://app.pixel12digital.com.br/debug_webhook_real.php";

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($ch);
$test_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($test_http_code === 200) {
    echo "✅ Arquivo já está acessível!\n";
    echo "📡 HTTP Code: $test_http_code\n";
    
    $json_data = json_decode($test_response, true);
    if ($json_data && isset($json_data['debug'])) {
        echo "✅ Modo debug ativo!\n";
    }
} else {
    echo "❌ Arquivo não está acessível (HTTP: $test_http_code)\n";
    echo "📄 Resposta: " . substr($test_response, 0, 200) . "...\n";
}

echo "\n";

// ===== 3. VERIFICAR SE HÁ PROBLEMAS DE DEPLOY =====
echo "3️⃣ VERIFICANDO PROBLEMAS DE DEPLOY:\n";
echo "====================================\n";

echo "🔍 Possíveis problemas:\n";
echo "1. Arquivo não foi enviado para o servidor\n";
echo "2. Arquivo está em localização incorreta\n";
echo "3. Permissões incorretas\n";
echo "4. Cache do servidor\n";

echo "\n🔧 SOLUÇÕES:\n";
echo "1. Fazer git pull no servidor\n";
echo "2. Verificar se arquivo existe no servidor\n";
echo "3. Configurar permissões corretas\n";
echo "4. Limpar cache do servidor\n";

echo "\n";

// ===== 4. INSTRUÇÕES PARA DEPLOY MANUAL =====
echo "4️⃣ INSTRUÇÕES PARA DEPLOY MANUAL:\n";
echo "==================================\n";

echo "📋 Para fazer o deploy manualmente:\n";
echo "\n1. Acesse o servidor via SSH:\n";
echo "   ssh u342734079@us-phx-web1607.hostinger.com\n";
echo "\n2. Navegue para o diretório:\n";
echo "   cd /home/u342734079/public_html/app\n";
echo "\n3. Faça pull do repositório:\n";
echo "   git pull origin master\n";
echo "\n4. Verifique se o arquivo existe:\n";
echo "   ls -la debug_webhook_real.php\n";
echo "\n5. Configure permissões:\n";
echo "   chmod 644 debug_webhook_real.php\n";
echo "\n6. Teste acessibilidade:\n";
echo "   curl https://app.pixel12digital.com.br/debug_webhook_real.php\n";

echo "\n🎯 CONCLUSÃO:\n";
echo "=============\n";

if ($test_http_code === 200) {
    echo "✅ DEBUG WEBHOOK ESTÁ FUNCIONANDO!\n";
    echo "🎉 Agora você pode:\n";
    echo "1. Enviar uma mensagem real para o WhatsApp\n";
    echo "2. Acessar: $test_url\n";
    echo "3. Verificar os dados que chegam\n";
    echo "4. Analisar os logs para identificar problemas\n";
} else {
    echo "⚠️ DEBUG WEBHOOK AINDA NÃO ESTÁ ACESSÍVEL!\n";
    echo "🔧 Execute as instruções de deploy manual acima\n";
    echo "🔧 Ou aguarde alguns minutos para o cache atualizar\n";
}
?> 