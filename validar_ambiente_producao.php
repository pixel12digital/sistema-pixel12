<?php
/**
 * 🔍 VALIDADOR DE AMBIENTE DE PRODUÇÃO - CHAT MULTICANAL
 * 
 * Valida todas as configurações necessárias para o sistema funcionar em produção
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "=== 🔍 VALIDAÇÃO DO AMBIENTE DE PRODUÇÃO ===\n\n";

// ===== 1. VALIDAR CONFIGURAÇÕES DE BANCO =====
echo "1. 📊 CONFIGURAÇÕES DE BANCO DE DADOS:\n";
echo "   DB_HOST: " . (defined('DB_HOST') ? DB_HOST : '❌ NÃO DEFINIDO') . "\n";
echo "   DB_NAME: " . (defined('DB_NAME') ? DB_NAME : '❌ NÃO DEFINIDO') . "\n";
echo "   DB_USER: " . (defined('DB_USER') ? DB_USER : '❌ NÃO DEFINIDO') . "\n";
echo "   DB_PASS: " . (defined('DB_PASS') ? '✅ DEFINIDO' : '❌ NÃO DEFINIDO') . "\n";

// Testar conexão com banco
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        echo "   ❌ ERRO DE CONEXÃO: " . $mysqli->connect_error . "\n";
    } else {
        echo "   ✅ CONEXÃO COM BANCO: OK\n";
        
        // Verificar tabelas essenciais
        $tabelas_essenciais = [
            'mensagens_comunicacao',
            'canais_comunicacao', 
            'clientes',
            'logs_integracao_ana',
            'transferencias_rafael',
            'transferencias_humano'
        ];
        
        echo "   📋 VERIFICANDO TABELAS:\n";
        foreach ($tabelas_essenciais as $tabela) {
            $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
            if ($result && $result->num_rows > 0) {
                echo "      ✅ $tabela: OK\n";
            } else {
                echo "      ❌ $tabela: NÃO ENCONTRADA\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n";

// ===== 2. VALIDAR CONFIGURAÇÕES DO WHATSAPP =====
echo "2. 📱 CONFIGURAÇÕES DO WHATSAPP:\n";
echo "   WHATSAPP_ROBOT_URL: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : '❌ NÃO DEFINIDO') . "\n";
echo "   WHATSAPP_TIMEOUT: " . (defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : '❌ NÃO DEFINIDO') . "\n";

// Verificar se VPS está online
$vps_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';
echo "   🔍 TESTANDO VPS ($vps_url): ";

$ch = curl_init($vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response && $http_code === 200) {
    echo "✅ ONLINE\n";
} else {
    echo "❌ OFFLINE (HTTP $http_code)\n";
}

echo "\n";

// ===== 3. VALIDAR CONFIGURAÇÕES DOS CANAIS =====
echo "3. 🔄 CONFIGURAÇÕES DOS CANAIS:\n";

// Verificar canais no banco
if (isset($mysqli) && !$mysqli->connect_error) {
    $canais = $mysqli->query("SELECT id, nome_exibicao, identificador, porta, status FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id");
    
    if ($canais && $canais->num_rows > 0) {
        echo "   📋 CANAIS CONFIGURADOS:\n";
        while ($canal = $canais->fetch_assoc()) {
            $status_icon = $canal['status'] === 'conectado' ? '✅' : '❌';
            echo "      $status_icon ID {$canal['id']}: {$canal['nome_exibicao']} ({$canal['identificador']}) - Porta {$canal['porta']}\n";
        }
    } else {
        echo "   ❌ NENHUM CANAL CONFIGURADO\n";
    }
}

// Verificar números específicos
echo "   📞 NÚMEROS ESPERADOS:\n";
echo "      Canal Ana (3000): 554797146908@c.us\n";
echo "      Canal Rafael (3001): 554797309525@c.us\n";

echo "\n";

// ===== 4. VALIDAR WEBHOOK URL =====
echo "4. 🌐 CONFIGURAÇÕES DO WEBHOOK:\n";
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem.php';
echo "   WEBHOOK_URL: $webhook_url\n";

// Testar webhook
echo "   🔍 TESTANDO WEBHOOK: ";
$test_payload = json_encode([
    'from' => '554796164699@c.us',
    'body' => 'Teste de validação - ' . date('Y-m-d H:i:s'),
    'timestamp' => time()
]);

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if (!$curl_error && $http_code === 200) {
    echo "✅ FUNCIONANDO (HTTP $http_code)\n";
    $response_data = json_decode($response, true);
    if ($response_data && isset($response_data['success'])) {
        echo "      Resposta: " . ($response_data['success'] ? 'SUCCESS' : 'ERROR') . "\n";
    }
} else {
    echo "❌ ERRO (HTTP $http_code): $curl_error\n";
}

echo "\n";

// ===== 5. VALIDAR CONFIGURAÇÕES DA ANA =====
echo "5. 🤖 CONFIGURAÇÕES DA ANA:\n";
echo "   AGENT_ANA_ID: 3\n";
echo "   ANA_API_URL: https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php\n";

// Testar API da Ana
echo "   🔍 TESTANDO API DA ANA: ";
$ana_payload = json_encode([
    'question' => 'Teste de conexão',
    'agent_id' => '3'
]);

$ch = curl_init('https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $ana_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ana_response = curl_exec($ch);
$ana_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ana_curl_error = curl_error($ch);
curl_close($ch);

if (!$ana_curl_error && $ana_http_code === 200) {
    echo "✅ ONLINE (HTTP $ana_http_code)\n";
    $ana_data = json_decode($ana_response, true);
    if ($ana_data && isset($ana_data['response'])) {
        echo "      Resposta: " . substr($ana_data['response'], 0, 50) . "...\n";
    }
} else {
    echo "❌ OFFLINE (HTTP $ana_http_code): $ana_curl_error\n";
}

echo "\n";

// ===== 6. VERIFICAR PROCESSOS PM2 =====
echo "6. ⚙️ PROCESSOS PM2 (SIMULAÇÃO):\n";
echo "   📋 COMANDOS PARA EXECUTAR NO VPS:\n";
echo "      ssh root@212.85.11.238\n";
echo "      pm2 status\n";
echo "      pm2 logs whatsapp-3000 --lines 10\n";
echo "      pm2 logs whatsapp-3001 --lines 10\n";

echo "\n";

// ===== 7. RESUMO FINAL =====
echo "7. 📊 RESUMO DA VALIDAÇÃO:\n";

$problemas = [];
$sucessos = [];

// Verificar banco
if (isset($mysqli) && !$mysqli->connect_error) {
    $sucessos[] = "Banco de dados conectado";
} else {
    $problemas[] = "Problema na conexão com banco";
}

// Verificar VPS
if ($response && $http_code === 200) {
    $sucessos[] = "VPS WhatsApp online";
} else {
    $problemas[] = "VPS WhatsApp offline";
}

// Verificar webhook
if (!$curl_error && $http_code === 200) {
    $sucessos[] = "Webhook funcionando";
} else {
    $problemas[] = "Webhook com problema";
}

// Verificar Ana
if (!$ana_curl_error && $ana_http_code === 200) {
    $sucessos[] = "API Ana online";
} else {
    $problemas[] = "API Ana offline";
}

echo "   ✅ SUCESSOS (" . count($sucessos) . "):\n";
foreach ($sucessos as $sucesso) {
    echo "      • $sucesso\n";
}

if (!empty($problemas)) {
    echo "   ❌ PROBLEMAS (" . count($problemas) . "):\n";
    foreach ($problemas as $problema) {
        echo "      • $problema\n";
    }
}

echo "\n";

// ===== 8. PRÓXIMOS PASSOS =====
echo "8. 🚀 PRÓXIMOS PASSOS:\n";

if (empty($problemas)) {
    echo "   ✅ AMBIENTE VALIDADO COM SUCESSO!\n";
    echo "   📱 Envie uma mensagem para 554797146908 para testar o fluxo completo\n";
    echo "   📊 Monitore os logs em painel/debug_ajax_whatsapp.log\n";
} else {
    echo "   ⚠️  CORRIGIR PROBLEMAS ANTES DE PROSSEGUIR:\n";
    foreach ($problemas as $problema) {
        echo "      • $problema\n";
    }
}

echo "\n=== FIM DA VALIDAÇÃO ===\n";

// Fechar conexão
if (isset($mysqli)) {
    $mysqli->close();
}
?> 