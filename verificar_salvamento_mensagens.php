<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICAÇÃO COMPLETA - SALVAMENTO DE MENSAGENS\n";
echo "=================================================\n\n";

// 1. Verificar configuração do banco
echo "📊 CONFIGURAÇÃO DO BANCO:\n";
echo "   Host: " . DB_HOST . "\n";
echo "   Usuário: " . DB_USER . "\n";
echo "   Banco: " . DB_NAME . "\n";
echo "   Conectado: " . ($mysqli->ping() ? '✅ Sim' : '❌ Não') . "\n\n";

// 2. Verificar estrutura da tabela mensagens_comunicacao
echo "📋 ESTRUTURA DA TABELA mensagens_comunicacao:\n";
$estrutura = $mysqli->query("DESCRIBE mensagens_comunicacao");
if ($estrutura) {
    while ($coluna = $estrutura->fetch_assoc()) {
        echo "   {$coluna['Field']} - {$coluna['Type']} - {$coluna['Null']} - {$coluna['Key']}\n";
    }
} else {
    echo "   ❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}

// 3. Verificar total de mensagens
echo "\n📨 TOTAL DE MENSAGENS:\n";
$total = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao")->fetch_assoc()['total'];
echo "   Total: $total mensagens\n";

if ($total > 0) {
    echo "\n📊 ÚLTIMAS 5 MENSAGENS:\n";
    $ultimas = $mysqli->query("SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5");
    while ($msg = $ultimas->fetch_assoc()) {
        echo "   ID: {$msg['id']} | Canal: {$msg['canal_id']} | Data: {$msg['data_hora']}\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
}

// 4. Verificar canais configurados
echo "\n📱 CANAIS CONFIGURADOS:\n";
$canais = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao WHERE status <> 'excluido'");
while ($canal = $canais->fetch_assoc()) {
    $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
    echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']}, Porta: {$canal['porta']})\n";
    echo "      Status: {$canal['status']} | Identificador: {$canal['identificador']}\n";
}

// 5. Testar salvamento de mensagem
echo "\n🧪 TESTANDO SALVAMENTO DE MENSAGEM:\n";

// Simular dados de webhook
$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us', // Canal Comercial
    'body' => 'Teste salvamento - ' . date('H:i:s'),
    'timestamp' => time()
];

echo "   Dados de teste:\n";
echo "      From: {$dados_teste['from']}\n";
echo "      To: {$dados_teste['to']}\n";
echo "      Body: {$dados_teste['body']}\n";

// Processar como o receber_mensagem.php faria
$from = $mysqli->real_escape_string($dados_teste['from']);
$body = $mysqli->real_escape_string($dados_teste['body']);
$to = $mysqli->real_escape_string($dados_teste['to']);

// Buscar canal pelo identificador de destino
$canal = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE identificador = '$to' LIMIT 1")->fetch_assoc();

if ($canal) {
    $canal_id = intval($canal['id']);
    $canal_nome = $canal['nome_exibicao'];
    echo "   ✅ Canal encontrado: $canal_nome (ID: $canal_id)\n";
} else {
    echo "   ❌ Canal não encontrado para '$to'\n";
    echo "   🔍 Verificando canais disponíveis:\n";
    $canais_disponiveis = $mysqli->query("SELECT identificador, nome_exibicao FROM canais_comunicacao");
    while ($c = $canais_disponiveis->fetch_assoc()) {
        echo "      {$c['identificador']} -> {$c['nome_exibicao']}\n";
    }
    exit(1);
}

// Simular salvamento
$numero_remetente = str_replace('@c.us', '', $dados_teste['from']);
$data_hora = date('Y-m-d H:i:s');

$sql = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao) 
        VALUES ($canal_id, '$numero_remetente', '$body', 'texto', '$data_hora', 'recebido')";

echo "   SQL: $sql\n";

$insert = $mysqli->query($sql);

if ($insert) {
    $mensagem_id = $mysqli->insert_id;
    echo "   ✅ Mensagem salva com sucesso! ID: $mensagem_id\n";
    
    // Verificar se foi realmente salva
    $mensagem_salva = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE id = $mensagem_id")->fetch_assoc();
    if ($mensagem_salva) {
        echo "   📊 Mensagem verificada no banco:\n";
        echo "      ID: {$mensagem_salva['id']}\n";
        echo "      Canal ID: {$mensagem_salva['canal_id']}\n";
        echo "      Mensagem: {$mensagem_salva['mensagem']}\n";
        echo "      Data/Hora: {$mensagem_salva['data_hora']}\n";
    }
} else {
    echo "   ❌ Erro ao salvar mensagem: " . $mysqli->error . "\n";
}

// 6. Verificar logs de erro
echo "\n📝 VERIFICANDO LOGS DE ERRO:\n";
$log_file = __DIR__ . '/logs/error.log';
if (file_exists($log_file)) {
    echo "   Log encontrado: $log_file\n";
    $ultimas_linhas = shell_exec("tail -n 20 \"$log_file\" 2>/dev/null");
    if ($ultimas_linhas) {
        echo "   Últimas linhas do log:\n";
        echo $ultimas_linhas;
    }
} else {
    echo "   ❌ Arquivo de log não encontrado\n";
}

// 7. Testar webhook diretamente
echo "\n🌐 TESTANDO WEBHOOK DIRETAMENTE:\n";
$webhook_url = 'http://localhost:8080/loja-virtual-revenda/painel/receber_mensagem.php';
$dados_webhook = json_encode([
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste webhook direto - ' . date('H:i:s'),
    'timestamp' => time()
]);

echo "   URL: $webhook_url\n";
echo "   Dados: $dados_webhook\n";

// Simular chamada cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dados_webhook);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
if ($error) {
    echo "   ❌ Erro cURL: $error\n";
} else {
    echo "   ✅ Resposta: $response\n";
}

// 8. Verificar se a mensagem foi salva após o teste
echo "\n📊 VERIFICAÇÃO FINAL:\n";
$total_final = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao")->fetch_assoc()['total'];
echo "   Total de mensagens: $total_final\n";

if ($total_final > $total) {
    echo "   ✅ Mensagens foram salvas! (+" . ($total_final - $total) . ")\n";
} else {
    echo "   ❌ Nenhuma mensagem adicional foi salva\n";
}

echo "\n🎯 DIAGNÓSTICO COMPLETO!\n";
echo "Se as mensagens não estão sendo salvas, verifique:\n";
echo "1. ✅ Configuração do banco de dados\n";
echo "2. ✅ Estrutura da tabela mensagens_comunicacao\n";
echo "3. ✅ Configuração dos canais\n";
echo "4. ✅ Permissões de escrita no banco\n";
echo "5. ✅ Logs de erro do PHP\n";
echo "6. ✅ Acesso ao webhook\n";
?> 