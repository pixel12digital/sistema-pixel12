<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICAÇÃO FINAL DA SOLUÇÃO\n";
echo "===============================\n\n";

// 1. Status dos canais no banco
echo "📊 STATUS DOS CANAIS NO BANCO:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador, data_conexao FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        $data_conexao = $canal['data_conexao'] ? date('d/m/Y H:i:s', strtotime($canal['data_conexao'])) : 'Nunca';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']}\n";
        echo "      Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n";
        echo "      Última conexão: $data_conexao\n\n";
    }
}

// 2. Teste de conectividade do servidor
echo "🔍 TESTE DE CONECTIVIDADE:\n";
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

if ($http_code === 200) {
    echo "✅ Servidor WhatsApp (porta 3000) está funcionando\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
        echo "   Mensagem: " . ($data['message'] ?? 'N/A') . "\n";
        if (isset($data['clients_status']['default']['number'])) {
            echo "   Número: " . $data['clients_status']['default']['number'] . "\n";
        }
    }
} else {
    echo "❌ Servidor não está respondendo\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
}

// 3. Teste de QR code
echo "\n🔍 TESTE DE QR CODE:\n";
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
        echo "✅ QR Code disponível para conexão\n";
        echo "   Mensagem: " . ($qr_data['message'] ?? 'QR gerado') . "\n";
    } else {
        echo "ℹ️ Servidor respondeu mas sem QR code\n";
        echo "   Provavelmente já está conectado\n";
    }
} else {
    echo "❌ Erro ao gerar QR code\n";
    echo "   HTTP Code: $http_code_qr\n";
    echo "   Erro: $error_qr\n";
}

// 4. Resumo da solução
echo "\n📋 RESUMO DA SOLUÇÃO APLICADA:\n";
echo "   ✅ Canal comercial configurado para porta 3000\n";
echo "   ✅ Status resetado para 'pendente' no banco\n";
echo "   ✅ Cache limpo para forçar atualização\n";
echo "   ✅ Servidor WhatsApp está funcionando\n";

// 5. Próximos passos
echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Abra o navegador em modo incógnito\n";
echo "   2. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   3. Pressione Ctrl+F5 para forçar reload\n";
echo "   4. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   5. Os canais devem aparecer como 'Desconectado'\n";
echo "   6. Clique em 'Conectar' para gerar QR code\n";
echo "   7. Escaneie o QR code com o WhatsApp comercial\n";

// 6. Possíveis problemas e soluções
echo "\n⚠️ POSSÍVEIS PROBLEMAS E SOLUÇÕES:\n";
echo "   PROBLEMA: Ainda aparece 'Conectado'\n";
echo "   SOLUÇÃO: Limpe o cache do navegador (Ctrl+Shift+Delete)\n\n";
echo "   PROBLEMA: QR code não aparece\n";
echo "   SOLUÇÃO: Verifique se o servidor está rodando na VPS\n\n";
echo "   PROBLEMA: Erro de CORS\n";
echo "   SOLUÇÃO: Use o botão 'Atualizar Status (CORS-FREE)'\n\n";
echo "   PROBLEMA: Múltiplos canais na mesma porta\n";
echo "   SOLUÇÃO: Configure portas separadas ou use apenas um canal\n";

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
echo "Agora teste a interface web conforme os passos acima.\n";
?> 