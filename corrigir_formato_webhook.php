<?php
/**
 * 🔧 CORREÇÃO DO FORMATO WEBHOOK - MENSAGENS WHATSAPP
 * 
 * O webhook espera um formato específico com 'event' e 'data'
 * Vamos corrigir o formato enviado
 */

echo "🔧 CORREÇÃO DO FORMATO WEBHOOK\n";
echo "==============================\n\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

// 1. SIMULAR MENSAGEM COM FORMATO CORRETO
echo "1️⃣ SIMULANDO MENSAGEM COM FORMATO CORRETO\n";
echo "==========================================\n";

$numero_remetente = '554796164699';
$numero_destino = '554797146908';
$mensagem = 'oi';
$timestamp = time();

// FORMATO CORRETO que o webhook espera
$webhook_data_correto = [
    'event' => 'onmessage',
    'data' => [
        'from' => $numero_remetente,
        'to' => $numero_destino,
        'text' => $mensagem,
        'type' => 'text',
        'timestamp' => $timestamp,
        'session' => 'default'
    ]
];

echo "📱 Dados da simulação (formato correto):\n";
echo "   De: $numero_remetente\n";
echo "   Para: $numero_destino (Canal 3000)\n";
echo "   Mensagem: $mensagem\n";
echo "   Timestamp: " . date('Y-m-d H:i:s', $timestamp) . "\n";
echo "   Formato: event + data\n\n";

// 2. ENVIAR PARA WEBHOOK COM FORMATO CORRETO
echo "2️⃣ ENVIANDO PARA WEBHOOK COM FORMATO CORRETO\n";
echo "=============================================\n";

$ch = curl_init('https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_data_correto));
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

// 3. VERIFICAR SE FOI SALVA NO BANCO
echo "3️⃣ VERIFICANDO SE FOI SALVA NO BANCO\n";
echo "====================================\n";

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

// 4. VERIFICAR SE ANA RESPONDEU
echo "4️⃣ VERIFICANDO SE ANA RESPONDEU\n";
echo "================================\n";

if ($mensagem_id) {
    // Verificar se há resposta da Ana (mensagem enviada após a recebida)
    $resposta_ana = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                                   WHERE canal_id = 36 
                                   AND direcao = 'enviado' 
                                   AND data_hora > (SELECT data_hora FROM mensagens_comunicacao WHERE id = $mensagem_id)
                                   AND data_hora <= DATE_ADD((SELECT data_hora FROM mensagens_comunicacao WHERE id = $mensagem_id), INTERVAL 2 MINUTE)
                                   ORDER BY data_hora ASC LIMIT 1");
    
    if ($resposta_ana && $resposta_ana->num_rows > 0) {
        $ana_msg = $resposta_ana->fetch_assoc();
        echo "✅ Ana respondeu:\n";
        echo "   ID: {$ana_msg['id']}\n";
        echo "   Mensagem: {$ana_msg['mensagem']}\n";
        echo "   Data/Hora: {$ana_msg['data_hora']}\n";
        echo "   Status: {$ana_msg['status']}\n";
    } else {
        echo "❌ Ana NÃO respondeu\n";
        echo "💡 Verificando se há integração Ana configurada...\n";
        
        // Verificar se existe integração Ana
        $ana_check = $mysqli->query("SELECT * FROM logs_integracao_ana 
                                    WHERE numero_cliente = '$numero_remetente' 
                                    AND data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                                    ORDER BY data_hora DESC LIMIT 1");
        
        if ($ana_check && $ana_check->num_rows > 0) {
            $ana_log = $ana_check->fetch_assoc();
            echo "📋 Log da Ana encontrado:\n";
            echo "   Mensagem: {$ana_log['mensagem_enviada']}\n";
            echo "   Resposta: {$ana_log['resposta_ana']}\n";
            echo "   Ação: {$ana_log['acao_sistema']}\n";
        } else {
            echo "⚠️ Nenhum log da Ana encontrado\n";
        }
    }
} else {
    echo "❌ Não é possível verificar resposta da Ana - mensagem não foi salva\n";
}
echo "\n";

// 5. TESTAR FORMATO ALTERNATIVO
echo "5️⃣ TESTANDO FORMATO ALTERNATIVO\n";
echo "================================\n";

// Testar também com formato alternativo que pode estar sendo usado
$webhook_data_alternativo = [
    'from' => $numero_remetente . '@c.us',
    'to' => $numero_destino . '@c.us',
    'body' => $mensagem,
    'type' => 'text',
    'timestamp' => $timestamp,
    'session' => 'default'
];

echo "🔄 Testando formato alternativo...\n";

$ch = curl_init('https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_data_alternativo));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response_alt = curl_exec($ch);
$http_code_alt = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_alt === 200) {
    echo "✅ Formato alternativo também funcionou (HTTP $http_code_alt)\n";
} else {
    echo "❌ Formato alternativo falhou (HTTP $http_code_alt)\n";
}
echo "\n";

// 6. VERIFICAR LOGS DO WEBHOOK
echo "6️⃣ VERIFICANDO LOGS DO WEBHOOK\n";
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
}
echo "\n";

// 7. RESUMO FINAL
echo "7️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status da correção:\n";
echo "   ✅ Formato correto: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n";
echo "   ✅ Banco: " . ($mensagem_id ? "Salva (ID: $mensagem_id)" : "Não salva") . "\n";
echo "   ✅ Ana: " . (isset($ana_msg) ? "Respondeu" : "Não respondeu") . "\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if ($mensagem_id) {
    echo "1. ✅ Mensagem salva no banco - ID: $mensagem_id\n";
    echo "2. 🔗 Verificar no chat: https://app.pixel12digital.com.br/painel/chat.php\n";
    echo "3. 🤖 Ana " . (isset($ana_msg) ? "respondeu automaticamente" : "precisa ser testada") . "\n";
    echo "4. 🧪 Teste real: Envie 'oi' para 554797146908 via WhatsApp\n";
} else {
    echo "1. ❌ Mensagem não foi salva - verificar logs\n";
    echo "2. 🔧 Verificar se o webhook está processando corretamente\n";
    echo "3. 🧪 Testar novamente com formato correto\n";
}

echo "\n✅ CORREÇÃO CONCLUÍDA!\n";
?> 