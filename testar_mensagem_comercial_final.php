<?php
/**
 * TESTAR MENSAGEM COMERCIAL - VERIFICAÇÃO FINAL
 * 
 * Este script testa o envio de mensagem para o canal comercial
 * e verifica se aparece corretamente como "COMERCIAL" no chat
 */

echo "🧪 TESTE FINAL - MENSAGEM COMERCIAL\n";
echo "===================================\n\n";

// 1. Enviar mensagem para o canal comercial via VPS
echo "📨 ENVIANDO MENSAGEM PARA CANAL COMERCIAL:\n";
$vps_ip = "212.85.11.238";
$test_url = "http://$vps_ip:3001/send/text";

$dados_teste = [
    'sessionName' => 'default',
    'number' => '554797146908@c.us', // Número da Alessandra
    'message' => 'Teste final canal comercial - ' . date('H:i:s') . ' - Deve aparecer como COMERCIAL'
];

echo "  📋 Dados de envio:\n";
echo "    Para: {$dados_teste['number']}\n";
echo "    Mensagem: {$dados_teste['message']}\n";
echo "    URL: $test_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Mensagem enviada com sucesso!\n";
    echo "  📋 Resposta: $response\n";
} else {
    echo "  ❌ Erro ao enviar mensagem\n";
    echo "  📋 Resposta: $response\n";
}

// 2. Aguardar um pouco para a mensagem ser processada
echo "\n⏳ Aguardando processamento da mensagem...\n";
sleep(3);

// 3. Verificar onde a mensagem foi salva
echo "\n🔍 VERIFICANDO ONDE A MENSAGEM FOI SALVA:\n";

// Verificar banco principal
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT id, canal_id, mensagem, data_hora FROM mensagens_comunicacao WHERE mensagem LIKE '%Teste final canal comercial%' ORDER BY data_hora DESC LIMIT 1";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $msg = $result->fetch_assoc();
    echo "  ✅ Mensagem encontrada no banco principal:\n";
    echo "    ID: {$msg['id']}\n";
    echo "    Canal ID: {$msg['canal_id']}\n";
    echo "    Data/Hora: {$msg['data_hora']}\n";
    echo "    Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    
    // Verificar nome do canal
    $sql_canal = "SELECT nome_exibicao FROM canais_comunicacao WHERE id = {$msg['canal_id']}";
    $result_canal = $mysqli->query($sql_canal);
    if ($result_canal && $result_canal->num_rows > 0) {
        $canal = $result_canal->fetch_assoc();
        echo "    Canal: {$canal['nome_exibicao']}\n";
        
        if ($canal['nome_exibicao'] === 'Comercial - Pixel') {
            echo "    ✅ Canal correto - deve aparecer como COMERCIAL no chat!\n";
        } else {
            echo "    ❌ Canal incorreto - ainda pode aparecer como FINANCEIRO\n";
        }
    }
} else {
    echo "  ⚠️ Mensagem não encontrada no banco principal\n";
}

// Verificar banco comercial
echo "\n🔍 VERIFICANDO BANCO COMERCIAL:\n";
require_once 'canais/comercial/canal_config.php';

$mysqli_comercial = conectarBancoCanal();
if ($mysqli_comercial) {
    $sql = "SELECT id, canal_id, mensagem, data_hora FROM mensagens_comunicacao WHERE mensagem LIKE '%Teste final canal comercial%' ORDER BY data_hora DESC LIMIT 1";
    $result = $mysqli_comercial->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $msg = $result->fetch_assoc();
        echo "  ✅ Mensagem encontrada no banco comercial:\n";
        echo "    ID: {$msg['id']}\n";
        echo "    Canal ID: {$msg['canal_id']}\n";
        echo "    Data/Hora: {$msg['data_hora']}\n";
        echo "    Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    } else {
        echo "  ⚠️ Mensagem não encontrada no banco comercial\n";
        
        // Verificar mensagens pendentes
        $sql_pendentes = "SELECT id, canal_id, mensagem, data_hora FROM mensagens_pendentes WHERE mensagem LIKE '%Teste final canal comercial%' ORDER BY data_hora DESC LIMIT 1";
        $result_pendentes = $mysqli_comercial->query($sql_pendentes);
        
        if ($result_pendentes && $result_pendentes->num_rows > 0) {
            $msg = $result_pendentes->fetch_assoc();
            echo "  ✅ Mensagem encontrada na tabela mensagens_pendentes:\n";
            echo "    ID: {$msg['id']}\n";
            echo "    Canal ID: {$msg['canal_id']}\n";
            echo "    Data/Hora: {$msg['data_hora']}\n";
            echo "    Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    }
    
    $mysqli_comercial->close();
} else {
    echo "  ❌ Não foi possível conectar ao banco comercial\n";
}

echo "\n🎯 RESULTADO DO TESTE:\n";
echo "📋 Se a mensagem foi salva no banco principal com canal_id = 37:\n";
echo "  ✅ Deve aparecer como 'COMERCIAL' no chat\n";
echo "  ✅ O nome do canal foi corrigido para 'Comercial - Pixel'\n";
echo "\n📋 Se a mensagem foi salva no banco comercial:\n";
echo "  ✅ Pode não aparecer no chat atual (sistema carrega do banco principal)\n";
echo "  ✅ Precisa implementar carregamento do banco comercial\n";

echo "\n🌐 PRÓXIMOS PASSOS:\n";
echo "1. Acesse o painel: https://app.pixel12digital.com.br/painel/\n";
echo "2. Abra o chat da Alessandra\n";
echo "3. Verifique se a mensagem aparece como 'COMERCIAL' em vez de 'FINANCEIRO'\n";
echo "4. Se ainda aparecer como 'FINANCEIRO', recarregue a página\n";

echo "\n📞 SUPORTE:\n";
echo "• Se ainda houver problemas, execute: php corrigir_identificacao_canal_comercial.php\n";
echo "• Verifique logs: tail -f logs/webhook_whatsapp_*.log\n";
?> 