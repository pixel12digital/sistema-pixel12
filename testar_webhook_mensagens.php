<?php
/**
 * 🔍 DIAGNÓSTICO COMPLETO - MENSAGENS WHATSAPP CANAIS 3000 E 3001
 * 
 * Este script verifica por que as mensagens não estão chegando e não são salvas
 */

echo "🔍 DIAGNÓSTICO COMPLETO - MENSAGENS WHATSAPP\n";
echo "==========================================\n\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

// 1. VERIFICAR ESTRUTURA DO BANCO
echo "1️⃣ VERIFICANDO ESTRUTURA DO BANCO\n";
echo "==================================\n";

// Verificar tabela mensagens_comunicacao
$columns = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao");
echo "📋 Colunas da tabela mensagens_comunicacao:\n";
while ($col = $columns->fetch_assoc()) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}
echo "\n";

// Verificar se existe a coluna telefone_origem
$check_telefone_origem = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'telefone_origem'");
if ($check_telefone_origem && $check_telefone_origem->num_rows > 0) {
    echo "✅ Coluna 'telefone_origem': EXISTE\n";
} else {
    echo "❌ Coluna 'telefone_origem': NÃO EXISTE\n";
    echo "💡 Adicionando coluna telefone_origem...\n";
    $mysqli->query("ALTER TABLE mensagens_comunicacao ADD COLUMN telefone_origem VARCHAR(20) AFTER numero_whatsapp");
    echo "✅ Coluna telefone_origem adicionada\n";
}
echo "\n";

// 2. VERIFICAR CANAIS CONFIGURADOS
echo "2️⃣ VERIFICANDO CANAIS CONFIGURADOS\n";
echo "==================================\n";

$canais = $mysqli->query("SELECT id, nome_exibicao, porta, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY porta");
while ($canal = $canais->fetch_assoc()) {
    echo "📱 Canal {$canal['id']}: {$canal['nome_exibicao']}\n";
    echo "   Porta: {$canal['porta']}\n";
    echo "   Identificador: {$canal['identificador']}\n\n";
}

// 3. VERIFICAR MENSAGENS RECENTES
echo "3️⃣ VERIFICANDO MENSAGENS RECENTES\n";
echo "=================================\n";

$hoje = $mysqli->query("SELECT 
    canal_id, 
    COUNT(*) as total,
    MAX(data_hora) as ultima_mensagem,
    COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
    COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas
FROM mensagens_comunicacao 
WHERE canal_id IN (36, 37) AND DATE(data_hora) = CURDATE()
GROUP BY canal_id");

if ($hoje && $hoje->num_rows > 0) {
    while ($msg = $hoje->fetch_assoc()) {
        echo "📊 Canal {$msg['canal_id']} (hoje):\n";
        echo "   Total: {$msg['total']} mensagens\n";
        echo "   Recebidas: {$msg['recebidas']}\n";
        echo "   Enviadas: {$msg['enviadas']}\n";
        echo "   Última: {$msg['ultima_mensagem']}\n\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada hoje nos canais 36 e 37\n\n";
}

// 4. TESTAR CONECTIVIDADE VPS
echo "4️⃣ TESTANDO CONECTIVIDADE VPS\n";
echo "=============================\n";

$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

foreach ($portas as $porta) {
    echo "🔄 Testando porta $porta...\n";
    
    // Testar status
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Status: Online (HTTP $http_code)\n";
        $status = json_decode($response, true);
        if ($status) {
            echo "  📊 Ready: " . ($status['ready'] ? 'Sim' : 'Não') . "\n";
            if (isset($status['clients_status'])) {
                echo "  👥 Sessões ativas: " . count($status['clients_status']) . "\n";
            }
        }
    } else {
        echo "  ❌ Status: Offline (HTTP $http_code)\n";
        if ($error) {
            echo "  🚫 Erro: $error\n";
        }
    }
    
    // Testar webhook config
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $webhook_response = curl_exec($ch);
    $webhook_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($webhook_code === 200) {
        $webhook_config = json_decode($webhook_response, true);
        if ($webhook_config && isset($webhook_config['webhook_url'])) {
            echo "  🔗 Webhook: {$webhook_config['webhook_url']}\n";
        } else {
            echo "  ⚠️ Webhook: Não configurado\n";
        }
    } else {
        echo "  ❌ Webhook: Endpoint não disponível (HTTP $webhook_code)\n";
    }
    echo "\n";
}

// 5. TESTAR WEBHOOK ENDPOINTS
echo "5️⃣ TESTANDO WEBHOOK ENDPOINTS\n";
echo "==============================\n";

$webhook_endpoints = [
    'painel/receber_mensagem.php',
    'painel/receber_mensagem_ana.php',
    'painel/receber_mensagem_ana_local.php',
    'webhook_sem_redirect/webhook.php'
];

foreach ($webhook_endpoints as $endpoint) {
    echo "🔄 Testando $endpoint...\n";
    
    $test_data = [
        'from' => '554796164699@c.us',
        'to' => '554797146908@c.us',
        'body' => 'TESTE DIAGNÓSTICO - ' . date('H:i:s'),
        'type' => 'text',
        'timestamp' => time()
    ];
    
    $ch = curl_init("https://app.pixel12digital.com.br/$endpoint");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ HTTP $http_code: Online\n";
        $result = json_decode($response, true);
        if ($result) {
            echo "  📝 Resposta: " . json_encode($result) . "\n";
        } else {
            echo "  📝 Resposta: " . substr($response, 0, 100) . "\n";
        }
    } else {
        echo "  ❌ HTTP $http_code: Erro\n";
        if ($error) {
            echo "  🚫 Erro: $error\n";
        }
    }
    echo "\n";
}

// 6. VERIFICAR LOGS DE ERRO
echo "6️⃣ VERIFICANDO LOGS DE ERRO\n";
echo "===========================\n";

$log_files = [
    'painel/debug_ajax_whatsapp.log',
    'painel/debug_webhook.log',
    'error.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "📋 $log_file (últimas 5 linhas):\n";
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -5);
        foreach ($recent_lines as $line) {
            echo "  " . trim($line) . "\n";
        }
        echo "\n";
    } else {
        echo "⚠️ $log_file: Arquivo não encontrado\n\n";
    }
}

// 7. SIMULAR RECEBIMENTO DE MENSAGEM
echo "7️⃣ SIMULANDO RECEBIMENTO DE MENSAGEM\n";
echo "====================================\n";

echo "🧪 Inserindo mensagem de teste diretamente no banco...\n";

$test_insert = "INSERT INTO mensagens_comunicacao 
                (canal_id, numero_whatsapp, telefone_origem, mensagem, tipo, data_hora, direcao, status) 
                VALUES 
                (36, '554796164699', '554796164699', 'TESTE DIAGNÓSTICO - " . date('Y-m-d H:i:s') . "', 'texto', NOW(), 'recebido', 'nao_lido')";

if ($mysqli->query($test_insert)) {
    $test_id = $mysqli->insert_id;
    echo "✅ Mensagem de teste inserida com ID: $test_id\n";
    
    // Verificar se aparece na consulta
    $verify = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE id = $test_id")->fetch_assoc();
    if ($verify) {
        echo "✅ Mensagem encontrada na consulta:\n";
        echo "   Canal: {$verify['canal_id']}\n";
        echo "   Número: {$verify['numero_whatsapp']}\n";
        echo "   Mensagem: {$verify['mensagem']}\n";
        echo "   Status: {$verify['status']}\n";
    }
} else {
    echo "❌ Erro ao inserir mensagem de teste: " . $mysqli->error . "\n";
}

echo "\n";

// 8. RECOMENDAÇÕES
echo "8️⃣ RECOMENDAÇÕES\n";
echo "================\n";

echo "🎯 Para resolver o problema:\n\n";

echo "1. Verificar se o webhook está configurado corretamente:\n";
echo "   curl -X POST http://212.85.11.238:3000/webhook/config \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"url\":\"https://app.pixel12digital.com.br/painel/receber_mensagem.php\"}'\n\n";

echo "2. Verificar se o webhook está configurado para o canal 3001:\n";
echo "   curl -X POST http://212.85.11.238:3001/webhook/config \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"url\":\"https://app.pixel12digital.com.br/painel/receber_mensagem.php\"}'\n\n";

echo "3. Testar envio de mensagem:\n";
echo "   Envie uma mensagem do WhatsApp para os números dos canais\n\n";

echo "4. Verificar logs em tempo real:\n";
echo "   tail -f painel/debug_webhook.log\n\n";

echo "🔧 DIAGNÓSTICO CONCLUÍDO!\n";
?> 