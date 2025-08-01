<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO STATUS FALSO DO CANAL COMERCIAL\n";
echo "=============================================\n\n";

// Verificar status atual no banco
$sql_atual = "SELECT id, nome_exibicao, identificador, porta, status, sessao FROM canais_comunicacao WHERE id = 37";
$result = $mysqli->query($sql_atual);

if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "📱 STATUS ATUAL NO BANCO:\n";
    echo "=========================\n";
    echo "ID: {$canal['id']}\n";
    echo "Nome: {$canal['nome_exibicao']}\n";
    echo "Identificador: {$canal['identificador']}\n";
    echo "Porta: {$canal['porta']}\n";
    echo "Status: {$canal['status']}\n";
    echo "Sessão: " . ($canal['sessao'] ?: 'NULL') . "\n\n";
}

// Verificar status real no VPS
echo "📋 VERIFICANDO STATUS REAL NO VPS:\n";
echo "==================================\n";
$vps_url_3001 = "http://212.85.11.238:3001";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Resposta: $response\n\n";

// Decodificar resposta
$sessions_data = json_decode($response, true);
$real_status = 'desconectado';

if ($sessions_data && isset($sessions_data['sessions'])) {
    foreach ($sessions_data['sessions'] as $session) {
        if ($session['name'] === 'comercial') {
            $real_status = $session['status']['status'];
            break;
        }
    }
}

echo "📊 STATUS REAL DA SESSÃO COMERCIAL: $real_status\n\n";

// Corrigir status no banco baseado no status real
$new_status = ($real_status === 'connected') ? 'conectado' : 'desconectado';

echo "🔧 CORRIGINDO STATUS NO BANCO:\n";
echo "==============================\n";
echo "Status atual no banco: {$canal['status']}\n";
echo "Status real no VPS: $real_status\n";
echo "Novo status a ser definido: $new_status\n\n";

$sql_update = "UPDATE canais_comunicacao SET status = ? WHERE id = 37";
$stmt = $mysqli->prepare($sql_update);
$stmt->bind_param('s', $new_status);

if ($stmt->execute()) {
    echo "✅ Status corrigido para: $new_status\n\n";
} else {
    echo "❌ Erro ao corrigir status: " . $stmt->error . "\n\n";
}
$stmt->close();

// Verificar configuração final
$sql_final = "SELECT id, nome_exibicao, identificador, porta, status, sessao FROM canais_comunicacao WHERE id = 37";
$result_final = $mysqli->query($sql_final);

if ($result_final && $result_final->num_rows > 0) {
    $canal_final = $result_final->fetch_assoc();
    echo "📱 CONFIGURAÇÃO FINAL:\n";
    echo "======================\n";
    echo "ID: {$canal_final['id']}\n";
    echo "Nome: {$canal_final['nome_exibicao']}\n";
    echo "Identificador: {$canal_final['identificador']}\n";
    echo "Porta: {$canal_final['porta']}\n";
    echo "Status: {$canal_final['status']}\n";
    echo "Sessão: " . ($canal_final['sessao'] ?: 'NULL') . "\n\n";
}

echo "💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
if ($real_status === 'qr_ready') {
    echo "1. Acesse: http://212.85.11.238:3001/qr?session=comercial\n";
    echo "2. Escaneie o QR Code com o WhatsApp 4797309525\n";
    echo "3. Aguarde a conexão\n";
    echo "4. Execute este script novamente para verificar\n";
} elseif ($real_status === 'connected') {
    echo "1. ✅ Sessão comercial está conectada!\n";
    echo "2. Teste o envio de mensagem\n";
    echo "3. Acesse o painel e atualize o status\n";
} else {
    echo "1. ❌ Sessão comercial não está conectada\n";
    echo "2. Verifique se o servidor 3001 está rodando\n";
    echo "3. Tente criar a sessão novamente\n";
}
?> 