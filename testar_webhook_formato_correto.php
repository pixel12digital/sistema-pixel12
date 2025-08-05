<?php
/**
 * 🧪 TESTE WEBHOOK COM FORMATO CORRETO
 * 
 * Testa o webhook com o formato exato que ele espera
 */

echo "🧪 TESTE WEBHOOK COM FORMATO CORRETO\n";
echo "====================================\n\n";

// 1. TESTAR COM FORMATO CORRETO
echo "1️⃣ TESTANDO COM FORMATO CORRETO\n";
echo "================================\n";

$numero_remetente = '554796164699';
$numero_destino = '554797146908';
$mensagem = 'Teste formato correto - ' . date('Y-m-d H:i:s');

// FORMATO CORRETO que o webhook espera
$webhook_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => $numero_remetente,
        'to' => $numero_destino,
        'text' => $mensagem,
        'type' => 'text',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

echo "📱 Dados da simulação:\n";
echo "   De: $numero_remetente\n";
echo "   Para: $numero_destino (Canal 3000)\n";
echo "   Mensagem: $mensagem\n";
echo "   Formato: event + data\n\n";

echo "📤 Enviando para webhook...\n";

$ch = curl_init('https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook processado com sucesso (HTTP $http_code)\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "📝 Resposta: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "❌ Erro no webhook (HTTP $http_code)\n";
    if ($error) {
        echo "🚫 Erro cURL: $error\n";
    }
    echo "📝 Resposta: $response\n";
}
echo "\n";

// 2. VERIFICAR SE FOI SALVA NO BANCO
echo "2️⃣ VERIFICANDO SE FOI SALVA NO BANCO\n";
echo "====================================\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

$check_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                            WHERE numero_whatsapp = '$numero_remetente' 
                            AND mensagem = '$mensagem' 
                            AND canal_id = 36 
                            AND data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                            ORDER BY data_hora DESC LIMIT 1");

if ($check_msg && $check_msg->num_rows > 0) {
    $msg = $check_msg->fetch_assoc();
    echo "✅ Mensagem encontrada no banco:\n";
    echo "   ID: {$msg['id']}\n";
    echo "   Canal: {$msg['canal_id']} (3000)\n";
    echo "   Número: {$msg['numero_whatsapp']}\n";
    echo "   Mensagem: {$msg['mensagem']}\n";
    echo "   Data/Hora: {$msg['data_hora']}\n";
    echo "   Status: {$msg['status']}\n";
    echo "   Direção: {$msg['direcao']}\n";
    
    $mensagem_id = $msg['id'];
} else {
    echo "❌ Mensagem NÃO encontrada no banco\n";
    echo "💡 Verificando mensagens recentes do canal 3000...\n";
    
    $recent = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                              WHERE canal_id = 36 
                              AND data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                              ORDER BY data_hora DESC LIMIT 3");
    
    if ($recent && $recent->num_rows > 0) {
        echo "📋 Últimas mensagens do canal 3000:\n";
        while ($row = $recent->fetch_assoc()) {
            echo "   - ID: {$row['id']} | {$row['numero_whatsapp']} | {$row['mensagem']} | {$row['data_hora']}\n";
        }
    } else {
        echo "⚠️ Nenhuma mensagem recente encontrada no canal 3000\n";
    }
    
    $mensagem_id = null;
}
echo "\n";

// 3. VERIFICAR LOGS DO WEBHOOK
echo "3️⃣ VERIFICANDO LOGS DO WEBHOOK\n";
echo "==============================\n";

$log_file = 'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    echo "📋 Log do webhook (últimas 5 linhas):\n";
    $lines = file($log_file);
    $recent_lines = array_slice($lines, -5);
    foreach ($recent_lines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "⚠️ Arquivo de log não encontrado: $log_file\n";
    
    // Verificar se existe o diretório logs
    if (!is_dir('logs')) {
        echo "📁 Criando diretório logs...\n";
        mkdir('logs', 0755, true);
    }
}
echo "\n";

// 4. TESTAR INSERÇÃO DIRETA PARA COMPARAR
echo "4️⃣ TESTANDO INSERÇÃO DIRETA PARA COMPARAR\n";
echo "=========================================\n";

$mensagem_teste = 'Teste inserção direta - ' . date('Y-m-d H:i:s');
$texto_escaped = $mysqli->real_escape_string($mensagem_teste);

$sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
        VALUES (36, 4296, '$numero_remetente', '$texto_escaped', 'texto', NOW(), 'recebido', 'recebido')";

if ($mysqli->query($sql)) {
    $mensagem_id_direta = $mysqli->insert_id;
    echo "✅ Inserção direta funcionando - ID: $mensagem_id_direta\n";
} else {
    echo "❌ Erro na inserção direta: " . $mysqli->error . "\n";
}
echo "\n";

// 5. RESUMO FINAL
echo "5️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status dos testes:\n";
echo "   ✅ Webhook: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n";
echo "   ✅ Banco: " . ($mensagem_id ? "Salva (ID: $mensagem_id)" : "Não salva") . "\n";
echo "   ✅ Inserção direta: " . (isset($mensagem_id_direta) ? "Funcionando (ID: $mensagem_id_direta)" : "Falhou") . "\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if ($mensagem_id) {
    echo "1. ✅ Webhook funcionando corretamente!\n";
    echo "2. 🧪 Teste real: Envie 'oi' para 554797146908 via WhatsApp\n";
    echo "3. 🔗 Verificar no chat: https://app.pixel12digital.com.br/painel/chat.php\n";
    echo "4. 🤖 Ana deve responder automaticamente\n";
} else {
    echo "1. ❌ Webhook não está salvando - verificar logs\n";
    echo "2. 🔧 Verificar se há erros no processamento\n";
    echo "3. 🧪 Testar novamente\n";
}

echo "\n✅ TESTE CONCLUÍDO!\n";
?> 