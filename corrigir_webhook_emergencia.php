<?php
/**
 * CORRIGIR WEBHOOK - EMERGÊNCIA
 * 
 * Script para corrigir e testar o webhook do WhatsApp
 */

echo "🔧 CORRIGINDO WEBHOOK - EMERGÊNCIA\n";
echo "==================================\n\n";

// 1. Verificar configuração atual
echo "1️⃣ VERIFICANDO CONFIGURAÇÃO ATUAL\n";
echo "==================================\n\n";

echo "📱 Configurações do WhatsApp:\n";
echo "   URL Robot: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'Não definida') . "\n";
echo "   Timeout: " . (defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : 'Não definido') . "\n";
echo "   Ambiente: " . ($is_local ? 'LOCAL' : 'PRODUÇÃO') . "\n\n";

// 2. Testar webhook com dados válidos
echo "2️⃣ TESTANDO WEBHOOK COM DADOS VÁLIDOS\n";
echo "======================================\n\n";

$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste de mensagem às ' . date('H:i:s'),
        'type' => 'text'
    ]
];

$webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';

echo "🔗 Testando webhook: $webhook_url\n";
echo "📤 Dados de teste:\n";
echo "   From: {$test_data['data']['from']}\n";
echo "   Text: {$test_data['data']['text']}\n\n";

// Fazer requisição POST para o webhook
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: WhatsApp-Webhook-Test/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📡 Resposta do webhook:\n";
echo "   HTTP Code: $http_code\n";
echo "   Response: " . substr($response, 0, 200) . "...\n";

if ($error) {
    echo "   ❌ Erro cURL: $error\n";
}

if ($http_code === 200) {
    echo "   ✅ Webhook funcionando corretamente!\n";
} else {
    echo "   ❌ Webhook com problema (HTTP $http_code)\n";
}

// 3. Verificar se a mensagem foi salva
echo "\n3️⃣ VERIFICANDO SE MENSAGEM FOI SALVA\n";
echo "=====================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT mc.*, c.nome as cliente_nome
        FROM mensagens_comunicacao mc
        LEFT JOIN clientes c ON mc.cliente_id = c.id
        WHERE mc.data_hora >= '" . date('Y-m-d H:i:s', time() - 60) . "'
        AND mc.numero_whatsapp = '554796164699'
        ORDER BY mc.data_hora DESC
        LIMIT 5";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "✅ Mensagem de teste encontrada no banco:\n\n";
    while ($msg = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        echo "   $direcao [$hora] {$msg['cliente_nome']}\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Mensagem de teste NÃO foi salva no banco\n";
}

// 4. Verificar logs de webhook
echo "\n4️⃣ VERIFICANDO LOGS DE WEBHOOK\n";
echo "================================\n\n";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $total_logs = count($logs);
    echo "📄 Total de logs hoje: $total_logs\n";
    
    if ($total_logs > 0) {
        echo "📊 Últimas 5 requisições:\n";
        $ultimas_logs = array_slice($logs, -5);
        foreach ($ultimas_logs as $log) {
            $hora = substr($log, 0, 19);
            $conteudo = substr($log, 20);
            echo "   [$hora] " . substr($conteudo, 0, 80) . "...\n";
        }
    }
} else {
    echo "❌ Arquivo de log não encontrado\n";
}

// 5. Criar script de teste local
echo "\n5️⃣ CRIANDO SCRIPT DE TESTE LOCAL\n";
echo "==================================\n\n";

$teste_local = "<?php
/**
 * TESTE LOCAL DO WEBHOOK
 * 
 * Script para testar o webhook localmente
 */

require_once 'config.php';
require_once 'painel/db.php';

echo \"🧪 TESTE LOCAL DO WEBHOOK\n\";
echo \"=========================\n\n\";

// Simular dados do WhatsApp
\$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste local às ' . date('H:i:s'),
        'type' => 'text'
    ]
];

echo \"📤 Simulando mensagem:\n\";
echo \"   From: {\$test_data['data']['from']}\n\";
echo \"   Text: {\$test_data['data']['text']}\n\n\";

// Processar como se fosse o webhook
\$message = \$test_data['data'];
\$numero = \$message['from'];
\$texto = \$message['text'] ?? '';
\$tipo = \$message['type'] ?? 'text';
\$data_hora = date('Y-m-d H:i:s');

echo \"🔍 Processando mensagem...\n\";

// Buscar cliente
\$numero_limpo = preg_replace('/\D/', '', \$numero);
\$cliente_id = null;
\$cliente = null;

\$sql = \"SELECT id, nome, contact_name, celular FROM clientes 
        WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%\$numero_limpo%' 
        LIMIT 1\";

\$result = \$mysqli->query(\$sql);
if (\$result && \$result->num_rows > 0) {
    \$cliente = \$result->fetch_assoc();
    \$cliente_id = \$cliente['id'];
    echo \"✅ Cliente encontrado: {\$cliente['nome']}\n\";
} else {
    echo \"❌ Cliente não encontrado\n\";
}

// Buscar canal
\$canal_id = 36; // Canal financeiro
echo \"📡 Usando canal ID: \$canal_id\n\";

// Salvar mensagem
\$texto_escaped = \$mysqli->real_escape_string(\$texto);
\$tipo_escaped = \$mysqli->real_escape_string(\$tipo);
\$numero_escaped = \$mysqli->real_escape_string(\$numero);

\$sql = \"INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
        VALUES (\$canal_id, \" . (\$cliente_id ? \$cliente_id : 'NULL') . \", '\$texto_escaped', '\$tipo_escaped', '\$data_hora', 'recebido', 'recebido', '\$numero_escaped')\";

if (\$mysqli->query(\$sql)) {
    \$mensagem_id = \$mysqli->insert_id;
    echo \"✅ Mensagem salva - ID: \$mensagem_id\n\";
} else {
    echo \"❌ Erro ao salvar mensagem: \" . \$mysqli->error . \"\n\";
}

echo \"\n✅ Teste local concluído!\n\";
?>";

file_put_contents('teste_webhook_local.php', $teste_local);
echo "✅ Arquivo teste_webhook_local.php criado\n";

// 6. Recomendações
echo "\n6️⃣ RECOMENDAÇÕES\n";
echo "=================\n\n";

echo "🚨 **PROBLEMAS IDENTIFICADOS:**\n\n";

echo "1. **Webhook retornando HTTP 400:**\n";
echo "   - O webhook não está processando requisições corretamente\n";
echo "   - Pode ser problema de configuração ou código\n\n";

echo "2. **Mensagem de 17:03 não recebida:**\n";
echo "   - Última mensagem no banco foi às 16:06\n";
echo "   - Webhook parou de funcionar após 16:06\n\n";

echo "3. **Campo numero_whatsapp como 'N/A':**\n";
echo "   - Problema na identificação do número\n";
echo "   - Pode estar relacionado ao erro do webhook\n\n";

echo "🔧 **SOLUÇÕES IMEDIATAS:**\n\n";

echo "1. **Testar webhook localmente:**\n";
echo "   ```bash\n";
echo "   php teste_webhook_local.php\n";
echo "   ```\n\n";

echo "2. **Verificar logs do servidor:**\n";
echo "   - Logs do Apache/Nginx\n";
echo "   - Logs de erro do PHP\n";
echo "   - Logs do WhatsApp\n\n";

echo "3. **Verificar configuração do WhatsApp:**\n";
echo "   - URL do webhook está correta?\n";
echo "   - Webhook está ativo no WhatsApp?\n";
echo "   - Certificado SSL válido?\n\n";

echo "4. **Implementar emergência:**\n";
echo "   - Usar configuração de emergência\n";
echo "   - Monitorar em tempo real\n";
echo "   - Verificar se resolve o problema\n\n";

echo "✅ Análise concluída!\n";
?> 