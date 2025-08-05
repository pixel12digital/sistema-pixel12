<?php
/**
 * 🧪 SIMULAÇÃO COMPLETA - MENSAGEM WHATSAPP CANAL 3000
 * 
 * Este script simula exatamente o caminho que uma mensagem real percorre:
 * 1. WhatsApp → VPS → Webhook → Banco → Chat → Ana → Resposta
 */

echo "🧪 SIMULAÇÃO COMPLETA - MENSAGEM WHATSAPP CANAL 3000\n";
echo "==================================================\n\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

// 1. SIMULAR MENSAGEM RECEBIDA DO WHATSAPP
echo "1️⃣ SIMULANDO MENSAGEM RECEBIDA DO WHATSAPP\n";
echo "==========================================\n";

$numero_remetente = '554796164699'; // Número que está enviando
$numero_destino = '554797146908';   // Canal 3000 (Ana)
$mensagem = 'oi';                   // Mensagem de teste
$timestamp = time();

echo "📱 Dados da simulação:\n";
echo "   De: $numero_remetente\n";
echo "   Para: $numero_destino (Canal 3000)\n";
echo "   Mensagem: $mensagem\n";
echo "   Timestamp: " . date('Y-m-d H:i:s', $timestamp) . "\n\n";

// 2. SIMULAR WEBHOOK DA VPS
echo "2️⃣ SIMULANDO WEBHOOK DA VPS\n";
echo "============================\n";

$webhook_data = [
    'from' => $numero_remetente . '@c.us',
    'to' => $numero_destino . '@c.us',
    'body' => $mensagem,
    'type' => 'text',
    'timestamp' => $timestamp,
    'session' => 'default'
];

echo "📤 Enviando para webhook: https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php\n";
echo "📝 Dados: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n\n";

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

// 4. VERIFICAR SE APARECE NO CHAT
echo "4️⃣ VERIFICANDO SE APARECE NO CHAT\n";
echo "==================================\n";

if ($mensagem_id) {
    // Verificar se existe cliente associado
    $cliente_check = $mysqli->query("SELECT cliente_id FROM mensagens_comunicacao WHERE id = $mensagem_id")->fetch_assoc();
    
    if ($cliente_check && $cliente_check['cliente_id']) {
        $cliente_id = $cliente_check['cliente_id'];
        echo "✅ Cliente encontrado: ID $cliente_id\n";
        
        // Verificar se aparece na consulta do chat
        $chat_check = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao 
                                     WHERE cliente_id = $cliente_id 
                                     AND canal_id = 36 
                                     ORDER BY data_hora DESC");
        
        if ($chat_check && $row = $chat_check->fetch_assoc()) {
            echo "📊 Total de mensagens do cliente no chat: {$row['total']}\n";
            
            if ($row['total'] > 0) {
                echo "✅ Mensagem deve aparecer no chat\n";
                echo "🔗 URL do chat: https://app.pixel12digital.com.br/painel/chat.php?cliente_id=$cliente_id\n";
            } else {
                echo "❌ Cliente não tem mensagens no chat\n";
            }
        }
    } else {
        echo "⚠️ Mensagem sem cliente associado\n";
        echo "💡 Verificando se existe cliente para o número $numero_remetente...\n";
        
        $cliente_existe = $mysqli->query("SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_remetente%' LIMIT 1");
        if ($cliente_existe && $cliente_existe->num_rows > 0) {
            $cliente = $cliente_existe->fetch_assoc();
            echo "✅ Cliente encontrado: {$cliente['nome']} (ID: {$cliente['id']})\n";
        } else {
            echo "❌ Cliente não encontrado para o número $numero_remetente\n";
        }
    }
} else {
    echo "❌ Não é possível verificar chat - mensagem não foi salva\n";
}
echo "\n";

// 5. VERIFICAR SE ANA RESPONDEU
echo "5️⃣ VERIFICANDO SE ANA RESPONDEU\n";
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

// 6. TESTAR INTEGRAÇÃO ANA MANUALMENTE
echo "6️⃣ TESTANDO INTEGRAÇÃO ANA MANUALMENTE\n";
echo "======================================\n";

echo "🤖 Testando integração Ana diretamente...\n";

// Simular chamada da Ana
$ana_data = [
    'question' => $mensagem,
    'agent_id' => '3',
    'session_id' => $numero_remetente
];

$ch = curl_init('https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ana_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$ana_response = curl_exec($ch);
$ana_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($ana_http_code === 200) {
    $ana_result = json_decode($ana_response, true);
    if ($ana_result && isset($ana_result['response'])) {
        echo "✅ Ana respondeu via API:\n";
        echo "   Resposta: {$ana_result['response']}\n";
        
        // Salvar resposta da Ana no banco
        $resposta_texto = $mysqli->real_escape_string($ana_result['response']);
        $sql_resposta = "INSERT INTO mensagens_comunicacao 
                        (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                        VALUES (36, '$numero_remetente', '$resposta_texto', 'texto', NOW(), 'enviado', 'entregue')";
        
        if ($mysqli->query($sql_resposta)) {
            echo "✅ Resposta da Ana salva no banco (ID: " . $mysqli->insert_id . ")\n";
        } else {
            echo "❌ Erro ao salvar resposta da Ana: " . $mysqli->error . "\n";
        }
    } else {
        echo "⚠️ Resposta inesperada da Ana: " . substr($ana_response, 0, 200) . "\n";
    }
} else {
    echo "❌ Erro ao chamar Ana (HTTP $ana_http_code)\n";
    echo "📝 Resposta: $ana_response\n";
}
echo "\n";

// 7. RESUMO FINAL
echo "7️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status da simulação:\n";
echo "   ✅ Webhook: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n";
echo "   ✅ Banco: " . ($mensagem_id ? "Salva (ID: $mensagem_id)" : "Não salva") . "\n";
echo "   ✅ Chat: " . (isset($cliente_id) ? "Disponível (Cliente: $cliente_id)" : "Indisponível") . "\n";
echo "   ✅ Ana: " . (isset($ana_result['response']) ? "Respondeu" : "Não respondeu") . "\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if ($mensagem_id) {
    echo "1. ✅ Mensagem salva no banco - ID: $mensagem_id\n";
    echo "2. 🔗 Verificar no chat: https://app.pixel12digital.com.br/painel/chat.php?cliente_id=" . ($cliente_id ?? 'N/A') . "\n";
    echo "3. 🤖 Ana " . (isset($ana_result['response']) ? "respondeu automaticamente" : "precisa ser testada") . "\n";
} else {
    echo "1. ❌ Mensagem não foi salva - verificar webhook\n";
    echo "2. 🔧 Executar: php corrigir_webhook_mensagens.php\n";
    echo "3. 🧪 Testar novamente\n";
}

echo "\n✅ SIMULAÇÃO CONCLUÍDA!\n";
?> 