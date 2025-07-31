<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 Corrigindo canal comercial...\n\n";

// 1. Verificar status atual
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001");
if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "📱 Canal atual:\n";
    echo "   ID: " . $canal['id'] . "\n";
    echo "   Nome: " . $canal['nome_exibicao'] . "\n";
    echo "   Status: " . $canal['status'] . "\n";
    echo "   Identificador: " . $canal['identificador'] . "\n";
}

// 2. Corrigir status para pendente
echo "\n🔧 Corrigindo status...\n";
$update = $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente', data_conexao = NULL WHERE porta = 3001");
if ($update) {
    echo "✅ Status corrigido para 'pendente'\n";
} else {
    echo "❌ Erro ao corrigir status: " . $mysqli->error . "\n";
}

// 3. Testar conectividade da porta 3001
echo "\n🔍 Testando conectividade da porta 3001...\n";
$vps_ip = '212.85.11.238';
$porta = 3001;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:{$porta}/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Porta 3001 está respondendo!\n";
    echo "   Resposta: " . substr($response, 0, 100) . "...\n";
} else {
    echo "❌ Porta 3001 não está respondendo\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
    
    echo "\n🚨 PROBLEMA IDENTIFICADO:\n";
    echo "   O servidor WhatsApp na porta 3001 não está rodando.\n";
    echo "   Isso explica por que não aparece QR code para conectar.\n";
    
    echo "\n🔧 SOLUÇÕES POSSÍVEIS:\n";
    echo "   1. Configurar servidor WhatsApp na porta 3001\n";
    echo "   2. Usar a porta 3000 para ambos os canais\n";
    echo "   3. Configurar uma nova porta disponível\n";
}

// 4. Verificar se porta 3000 está funcionando
echo "\n🔍 Verificando porta 3000 (canal financeiro)...\n";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, "http://{$vps_ip}:3000/status");
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 3);

$response2 = curl_exec($ch2);
$http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);
curl_close($ch2);

if ($http_code2 === 200) {
    echo "✅ Porta 3000 está funcionando!\n";
    $data = json_decode($response2, true);
    if ($data && isset($data['ready'])) {
        echo "   WhatsApp conectado: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "❌ Porta 3000 também não está respondendo\n";
}

// 5. Sugerir ações
echo "\n📋 AÇÕES RECOMENDADAS:\n";
echo "   1. Acessar VPS: ssh root@212.85.11.238\n";
echo "   2. Verificar processos: ps aux | grep node\n";
echo "   3. Verificar portas: netstat -tulpn | grep :300\n";
echo "   4. Iniciar servidor na porta 3001 se necessário\n";
echo "   5. Ou configurar para usar porta 3000 para ambos\n";

echo "\n✅ Correção concluída!\n";
echo "\n💡 DICA: Após corrigir o servidor, acesse:\n";
echo "   http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   E clique em 'Atualizar Status (CORS-FREE)'\n";
?> 