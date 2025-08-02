<?php
/**
 * 🔍 DIAGNÓSTICO COMPLETO - POR QUE ANA NÃO ESTÁ ATENDENDO?
 * 
 * Script detalhado para identificar problemas no sistema Ana
 */

echo "🔍 DIAGNÓSTICO COMPLETO DO SISTEMA ANA\n";
echo "=====================================\n\n";

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';
$ana_api = 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php';

// ====== TESTE 1: VPS WHATSAPP ======
echo "🔧 TESTE 1: VPS WhatsApp\n";
echo "========================\n";

// Verificar status do VPS
$vps_check = curl_init("http://$vps_ip:3000/status");
curl_setopt($vps_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($vps_check, CURLOPT_TIMEOUT, 10);

$vps_response = curl_exec($vps_check);
$vps_code = curl_getinfo($vps_check, CURLINFO_HTTP_CODE);
curl_close($vps_check);

echo "VPS Status: HTTP $vps_code\n";
if ($vps_code === 200) {
    echo "✅ VPS WhatsApp Online\n";
    $vps_data = json_decode($vps_response, true);
    if (isset($vps_data['sessions'])) {
        echo "📱 Sessões ativas: " . count($vps_data['sessions']) . "\n";
    }
} else {
    echo "❌ VPS WhatsApp Offline/Problema\n";
}

// Verificar webhook configurado no VPS
echo "\n🔗 Verificando webhook no VPS...\n";
$webhook_check = curl_init("http://$vps_ip:3000/webhook/status");
curl_setopt($webhook_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_check, CURLOPT_TIMEOUT, 10);

$webhook_vps_response = curl_exec($webhook_check);
$webhook_vps_code = curl_getinfo($webhook_check, CURLINFO_HTTP_CODE);
curl_close($webhook_check);

echo "Webhook VPS: HTTP $webhook_vps_code\n";
if ($webhook_vps_code === 200) {
    echo "✅ Endpoint webhook responde\n";
    echo "Configuração: " . substr($webhook_vps_response, 0, 100) . "\n";
} else {
    echo "⚠️ Endpoint webhook não responde (normal se não implementado)\n";
}

echo "\n";

// ====== TESTE 2: API ANA EXTERNA ======
echo "🤖 TESTE 2: API Ana Externa\n";
echo "===========================\n";

$test_message = "Olá, preciso de ajuda";

$ana_payload = json_encode([
    'question' => $test_message,
    'agent_id' => '3'
]);

$ana_test = curl_init($ana_api);
curl_setopt($ana_test, CURLOPT_POST, true);
curl_setopt($ana_test, CURLOPT_POSTFIELDS, $ana_payload);
curl_setopt($ana_test, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($ana_payload)
]);
curl_setopt($ana_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ana_test, CURLOPT_TIMEOUT, 30);
curl_setopt($ana_test, CURLOPT_SSL_VERIFYPEER, false);

echo "Testando Ana com: '$test_message'\n";
echo "URL: $ana_api\n";
echo "Agent ID: 3\n\n";

$ana_response = curl_exec($ana_test);
$ana_code = curl_getinfo($ana_test, CURLINFO_HTTP_CODE);
$ana_time = curl_getinfo($ana_test, CURLINFO_TOTAL_TIME);
curl_close($ana_test);

echo "Status: HTTP $ana_code\n";
echo "Tempo: {$ana_time}s\n";

if ($ana_code === 200 && $ana_response) {
    $ana_data = json_decode($ana_response, true);
    if (isset($ana_data['success']) && $ana_data['success'] && !empty($ana_data['response'])) {
        echo "✅ ANA FUNCIONANDO PERFEITAMENTE!\n";
        echo "Resposta Ana: " . substr($ana_data['response'], 0, 100) . "...\n";
        
        // Verificar se resposta tem frases de transferência
        $response_lower = strtolower($ana_data['response']);
        if (strpos($response_lower, 'ativar_transferencia') !== false) {
            echo "🎯 Ana usa frases de transferência!\n";
        } else {
            echo "ℹ️ Ana não usou frases específicas de transferência\n";
        }
    } else {
        echo "⚠️ Ana responde mas formato inválido\n";
        echo "Raw: " . substr($ana_response, 0, 200) . "\n";
    }
} else {
    echo "❌ ANA NÃO FUNCIONANDO\n";
    echo "Erro: " . curl_error($ana_test) . "\n";
    echo "Raw: " . substr($ana_response, 0, 200) . "\n";
}

echo "\n";

// ====== TESTE 3: NOSSO WEBHOOK ======
echo "🔗 TESTE 3: Nosso Webhook\n";
echo "=========================\n";

echo "URL: $webhook_url\n\n";

// Teste GET básico
$webhook_get = curl_init($webhook_url);
curl_setopt($webhook_get, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_get, CURLOPT_TIMEOUT, 10);
curl_setopt($webhook_get, CURLOPT_SSL_VERIFYPEER, false);

$get_response = curl_exec($webhook_get);
$get_code = curl_getinfo($webhook_get, CURLINFO_HTTP_CODE);
curl_close($webhook_get);

echo "GET Test: HTTP $get_code\n";
if ($get_code === 200) {
    echo "✅ Webhook acessível\n";
} else {
    echo "❌ Webhook inacessível\n";
}

// Teste POST com dados reais
echo "\nTeste POST com dados simulados...\n";

$test_data = json_encode([
    'from' => '5547999999999',
    'body' => 'Quero criar um site para minha empresa'
]);

$webhook_post = curl_init($webhook_url);
curl_setopt($webhook_post, CURLOPT_POST, true);
curl_setopt($webhook_post, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($webhook_post, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($webhook_post, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_post, CURLOPT_TIMEOUT, 30);
curl_setopt($webhook_post, CURLOPT_SSL_VERIFYPEER, false);

$post_response = curl_exec($webhook_post);
$post_code = curl_getinfo($webhook_post, CURLINFO_HTTP_CODE);
$post_time = curl_getinfo($webhook_post, CURLINFO_TOTAL_TIME);
curl_close($webhook_post);

echo "POST Status: HTTP $post_code\n";
echo "Tempo: {$post_time}s\n";

if ($post_code === 200) {
    $webhook_data = json_decode($post_response, true);
    if (isset($webhook_data['success']) && $webhook_data['success']) {
        echo "✅ WEBHOOK FUNCIONANDO!\n";
        echo "Ana respondeu: " . substr($webhook_data['ana_response'] ?? 'N/A', 0, 100) . "...\n";
        echo "Ação detectada: " . ($webhook_data['action_taken'] ?? 'nenhuma') . "\n";
        
        if (isset($webhook_data['transfer_rafael']) && $webhook_data['transfer_rafael']) {
            echo "🎯 Transferência para Rafael detectada!\n";
        }
    } else {
        echo "⚠️ Webhook responde mas com erro\n";
        echo "Erro: " . ($webhook_data['error'] ?? 'Desconhecido') . "\n";
    }
} else {
    echo "❌ WEBHOOK COM PROBLEMA\n";
    echo "Erro cURL: " . curl_error($webhook_post) . "\n";
}

echo "Raw Response: " . substr($post_response, 0, 300) . "\n";

echo "\n";

// ====== TESTE 4: BANCO DE DADOS ======
echo "🗄️ TESTE 4: Banco de Dados\n";
echo "==========================\n";

try {
    require_once 'config.php';
    require_once 'painel/db.php';
    
    echo "✅ Conexão com banco estabelecida\n";
    
    // Verificar tabelas necessárias
    $tabelas = [
        'mensagens_comunicacao',
        'logs_integracao_ana',
        'transferencias_rafael',
        'transferencias_humano',
        'bloqueios_ana'
    ];
    
    echo "\nVerificando tabelas:\n";
    foreach ($tabelas as $tabela) {
        $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
        if ($result && $result->num_rows > 0) {
            echo "✅ $tabela exists\n";
        } else {
            echo "❌ $tabela MISSING\n";
        }
    }
    
    // Verificar canal Ana (ID 36)
    $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE id = 36")->fetch_assoc();
    if ($canal) {
        echo "\n✅ Canal Ana (ID 36) configurado: {$canal['nome']}\n";
        echo "Porta: {$canal['porta']}\n";
        echo "Status: {$canal['status']}\n";
    } else {
        echo "\n❌ Canal Ana (ID 36) não encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro no banco: " . $e->getMessage() . "\n";
}

echo "\n";

// ====== ANÁLISE E RECOMENDAÇÕES ======
echo "📊 ANÁLISE E DIAGNÓSTICO\n";
echo "========================\n";

$problemas = [];
$solucoes = [];

if ($vps_code !== 200) {
    $problemas[] = "VPS WhatsApp offline";
    $solucoes[] = "Verificar PM2 no VPS: pm2 status";
}

if ($ana_code !== 200) {
    $problemas[] = "API Ana externa não responde";
    $solucoes[] = "Verificar se https://agentes.pixel12digital.com.br está online";
}

if ($post_code !== 200) {
    $problemas[] = "Webhook não processa requisições";
    $solucoes[] = "Verificar configuração do servidor web";
}

if ($webhook_vps_code !== 200) {
    $problemas[] = "Webhook não configurado no VPS";
    $solucoes[] = "Executar: php configurar_webhook_vps.php";
}

if (empty($problemas)) {
    echo "🎉 SISTEMA APARENTA ESTAR FUNCIONANDO!\n\n";
    echo "🔍 POSSÍVEIS CAUSAS DA ANA NÃO ATENDER:\n";
    echo "1. Webhook não configurado corretamente no WhatsApp VPS\n";
    echo "2. Mensagens não estão chegando ao webhook\n";
    echo "3. Ana está respondendo mas não pelo WhatsApp\n\n";
    
    echo "🚀 AÇÕES IMEDIATAS:\n";
    echo "1. Configure webhook: php configurar_webhook_vps.php\n";
    echo "2. Teste real via WhatsApp enviando: 'Olá'\n";
    echo "3. Monitore logs: tail -f /var/log/apache2/error.log\n";
    echo "4. Verificar PM2: ssh root@$vps_ip 'pm2 logs whatsapp-3000'\n";
    
} else {
    echo "⚠️ PROBLEMAS IDENTIFICADOS:\n";
    foreach ($problemas as $i => $problema) {
        echo ($i + 1) . ". $problema\n";
    }
    
    echo "\n🔧 SOLUÇÕES:\n";
    foreach ($solucoes as $i => $solucao) {
        echo ($i + 1) . ". $solucao\n";
    }
}

echo "\n";
echo "📞 TESTE MANUAL RECOMENDADO:\n";
echo "===========================\n";
echo "1. Execute: php configurar_webhook_vps.php\n";
echo "2. Envie WhatsApp: 'Preciso de um site'\n";
echo "3. Monitore dashboard: https://app.pixel12digital.com.br/painel/gestao_transferencias.php\n";
echo "4. Verifique logs do servidor para '[RECEBIMENTO_ANA_LOCAL]'\n";

?> 