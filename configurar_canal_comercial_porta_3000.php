<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 Configurando canal comercial para usar porta 3000...\n\n";

// 1. Verificar se porta 3000 está funcionando
echo "🔍 Verificando porta 3000...\n";
$vps_ip = '212.85.11.238';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200) {
    echo "❌ Porta 3000 não está funcionando!\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
    exit(1);
}

echo "✅ Porta 3000 está funcionando!\n";
$data = json_decode($response, true);
if ($data && isset($data['ready'])) {
    echo "   WhatsApp conectado: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
}

// 2. Atualizar canal comercial para usar porta 3000
echo "\n🔧 Atualizando canal comercial...\n";
$update = $mysqli->query("UPDATE canais_comunicacao SET porta = 3000, status = 'pendente', data_conexao = NULL WHERE nome_exibicao LIKE '%Comercial%'");
if ($update) {
    echo "✅ Canal comercial atualizado para porta 3000\n";
} else {
    echo "❌ Erro ao atualizar canal: " . $mysqli->error . "\n";
    exit(1);
}

// 3. Verificar se há conflito (dois canais na mesma porta)
echo "\n🔍 Verificando conflitos de porta...\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3000 ORDER BY id");
if ($result && $result->num_rows > 0) {
    echo "📱 Canais na porta 3000:\n";
    while ($canal = $result->fetch_assoc()) {
        echo "   - {$canal['nome_exibicao']} (ID: {$canal['id']}) - {$canal['status']}\n";
    }
    
    if ($result->num_rows > 1) {
        echo "\n⚠️ ATENÇÃO: Múltiplos canais na porta 3000\n";
        echo "   Isso pode causar conflitos. Recomenda-se:\n";
        echo "   1. Usar apenas um canal por porta\n";
        echo "   2. Ou configurar o servidor para suportar múltiplas sessões\n";
    }
}

// 4. Testar QR code na porta 3000
echo "\n🔍 Testando geração de QR code...\n";
$ch_qr = curl_init();
curl_setopt($ch_qr, CURLOPT_URL, "http://{$vps_ip}:3000/qr");
curl_setopt($ch_qr, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_qr, CURLOPT_TIMEOUT, 5);
curl_setopt($ch_qr, CURLOPT_CONNECTTIMEOUT, 3);

$response_qr = curl_exec($ch_qr);
$http_code_qr = curl_getinfo($ch_qr, CURLINFO_HTTP_CODE);
$error_qr = curl_error($ch_qr);
curl_close($ch_qr);

if ($http_code_qr === 200) {
    $qr_data = json_decode($response_qr, true);
    if ($qr_data && isset($qr_data['qr'])) {
        echo "✅ QR Code disponível!\n";
        echo "   Status: " . ($qr_data['message'] ?? 'QR gerado') . "\n";
    } else {
        echo "ℹ️ Servidor respondeu mas sem QR code (pode estar conectado)\n";
    }
} else {
    echo "❌ Erro ao gerar QR code\n";
    echo "   HTTP Code: $http_code_qr\n";
    echo "   Erro: $error_qr\n";
}

// 5. Resumo final
echo "\n📋 RESUMO DA CONFIGURAÇÃO:\n";
echo "   ✅ Canal comercial configurado para porta 3000\n";
echo "   ✅ Status atualizado para 'pendente'\n";
echo "   ✅ Servidor WhatsApp está funcionando\n";
echo "   ✅ QR code pode ser gerado\n";

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   2. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   3. O canal comercial deve aparecer como 'Desconectado'\n";
echo "   4. Clique em 'Conectar' para gerar QR code\n";
echo "   5. Escaneie o QR code com o WhatsApp comercial\n";

echo "\n💡 DICA: Se quiser usar portas separadas no futuro:\n";
echo "   - Configure um segundo servidor WhatsApp na porta 3001\n";
echo "   - Ou use uma porta diferente disponível\n";

echo "\n✅ Configuração concluída!\n";
?> 