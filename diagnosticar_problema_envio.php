<?php
/**
 * 🔍 DIAGNOSTICAR PROBLEMA DE ENVIO ANA
 * 
 * Verifica por que Ana não está enviando mensagens para WhatsApp
 */

echo "=== 🔍 DIAGNOSTICAR PROBLEMA DE ENVIO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

require_once 'config.php';

// ===== 1. VERIFICAR ÚLTIMAS MENSAGENS =====
echo "1. 📋 VERIFICANDO ÚLTIMAS MENSAGENS NO BANCO:\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Buscar mensagens recentes do teste
    $result = $mysqli->query("
        SELECT id, canal_id, numero_whatsapp, mensagem, direcao, data_hora, status, motivo_erro
        FROM mensagens_comunicacao 
        WHERE numero_whatsapp IN ('554796164699', '5547999999999', '554797146908') 
        ORDER BY data_hora DESC 
        LIMIT 10
    ");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "   📊 ID: {$row['id']} | Direção: {$row['direcao']} | Status: '{$row['status']}'\n";
            echo "      Número: {$row['numero_whatsapp']}\n";
            echo "      Mensagem: " . substr($row['mensagem'], 0, 100) . "...\n";
            echo "      Data: {$row['data_hora']}\n";
            if (!empty($row['motivo_erro'])) {
                echo "      ❌ Erro: {$row['motivo_erro']}\n";
            }
            echo "      ---\n";
        }
    } else {
        echo "   ❌ Nenhuma mensagem encontrada\n";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "   ❌ Erro no banco: " . $e->getMessage() . "\n";
}

echo "\n";

// ===== 2. VERIFICAR WEBHOOK ATUAL =====
echo "2. 🔍 VERIFICANDO CONTEÚDO DO WEBHOOK:\n";

$webhook_file = 'painel/receber_mensagem_ana_local.php';
if (file_exists($webhook_file)) {
    echo "   ✅ Webhook existe: $webhook_file\n";
    
    $webhook_content = file_get_contents($webhook_file);
    
    // Verificar se tem a função de envio incluída
    if (strpos($webhook_content, 'funcao_envio_whatsapp.php') !== false) {
        echo "   ✅ Include da função de envio encontrado\n";
    } else {
        echo "   ❌ Include da função de envio NÃO encontrado\n";
    }
    
    // Verificar se tem o código de envio
    if (strpos($webhook_content, 'enviarMensagemWhatsApp') !== false) {
        echo "   ✅ Código de envio encontrado no webhook\n";
    } else {
        echo "   ❌ Código de envio NÃO encontrado no webhook\n";
    }
    
    // Verificar se tem logs de erro
    if (strpos($webhook_content, 'error_log("[WEBHOOK_ANA]') !== false) {
        echo "   ✅ Logs de debug encontrados\n";
    } else {
        echo "   ❌ Logs de debug NÃO encontrados\n";
    }
    
} else {
    echo "   ❌ Webhook não encontrado!\n";
}

echo "\n";

// ===== 3. VERIFICAR LOGS DE ERRO =====
echo "3. 📋 VERIFICANDO LOGS DE ERRO:\n";

$log_files = [
    'painel/debug_ajax_whatsapp.log',
    'logs/webhook.log',
    'webhook.log',
    'error.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "   ✅ Log encontrado: $log_file\n";
        $lines = file($log_file);
        if ($lines) {
            $recent_lines = array_slice($lines, -10);
            foreach ($recent_lines as $line) {
                if (strpos($line, 'WEBHOOK_ANA') !== false || strpos($line, 'Ana') !== false) {
                    echo "      📄 " . trim($line) . "\n";
                }
            }
        }
        echo "\n";
    }
}

// Verificar log do PHP (pode estar em diferentes locais)
$php_logs = [
    '/var/log/php_errors.log',
    '/var/log/apache2/error.log',
    '../logs/php_errors.log',
    'php_errors.log'
];

foreach ($php_logs as $php_log) {
    if (file_exists($php_log)) {
        echo "   ✅ Log PHP encontrado: $php_log\n";
        $recent_php = shell_exec("tail -20 $php_log | grep -i 'webhook\|ana'");
        if ($recent_php) {
            echo "      📄 Logs recentes:\n";
            echo "      " . str_replace("\n", "\n      ", trim($recent_php)) . "\n";
        }
        break;
    }
}

echo "\n";

// ===== 4. TESTAR FUNÇÃO DE ENVIO DIRETAMENTE =====
echo "4. 🧪 TESTANDO FUNÇÃO DE ENVIO DIRETAMENTE:\n";

if (file_exists('funcao_envio_whatsapp.php')) {
    echo "   ✅ Arquivo funcao_envio_whatsapp.php existe\n";
    
    // Incluir e testar a função
    require_once 'funcao_envio_whatsapp.php';
    
    echo "   🧪 Testando função enviarMensagemWhatsApp...\n";
    
    $resultado_teste = enviarMensagemWhatsApp("554796164699@c.us", "🧪 Teste direto função - " . date('H:i:s'));
    
    echo "   📊 Resultado do teste:\n";
    echo "      Success: " . ($resultado_teste['success'] ? 'SIM' : 'NÃO') . "\n";
    echo "      HTTP Code: " . $resultado_teste['http_code'] . "\n";
    echo "      Response: " . $resultado_teste['response'] . "\n";
    if (!empty($resultado_teste['error'])) {
        echo "      ❌ Erro: " . $resultado_teste['error'] . "\n";
    }
    
} else {
    echo "   ❌ Arquivo funcao_envio_whatsapp.php NÃO existe\n";
}

echo "\n";

// ===== 5. TESTAR WEBHOOK DIRETAMENTE =====
echo "5. 🧪 TESTANDO WEBHOOK COM MENSAGEM SIMPLES:\n";

$webhook_url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";
$test_data = [
    "from" => "554796164699@c.us",
    "body" => "teste",
    "timestamp" => time()
];

echo "   🧪 Enviando mensagem simples para webhook...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
echo "   📄 Resposta: $response\n";

if ($http_code == 500) {
    echo "   ⚠️  HTTP 500 - Verificando se a mensagem foi processada mesmo assim...\n";
    
    // Aguardar um momento e verificar se foi salvo
    sleep(2);
    
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $check_result = $mysqli->query("
            SELECT id, mensagem, direcao 
            FROM mensagens_comunicacao 
            WHERE mensagem LIKE '%teste%' 
            ORDER BY data_hora DESC 
            LIMIT 2
        ");
        
        if ($check_result && $check_result->num_rows > 0) {
            echo "   ✅ Mensagens encontradas no banco:\n";
            while ($row = $check_result->fetch_assoc()) {
                echo "      ID {$row['id']}: {$row['direcao']} - " . substr($row['mensagem'], 0, 50) . "...\n";
            }
        } else {
            echo "   ❌ Nenhuma mensagem nova encontrada\n";
        }
        
        $mysqli->close();
    } catch (Exception $e) {
        echo "   ❌ Erro ao verificar banco: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ===== 6. DIAGNÓSTICO E SOLUÇÕES =====
echo "6. 🎯 DIAGNÓSTICO E PRÓXIMAS AÇÕES:\n";

echo "   📋 PROBLEMAS IDENTIFICADOS:\n";

// Verificar se é problema de integração
if (!file_exists('funcao_envio_whatsapp.php')) {
    echo "   ❌ Função de envio não existe - CRÍTICO\n";
    echo "   🔧 Solução: Recriar funcao_envio_whatsapp.php\n\n";
} else {
    echo "   ✅ Função de envio existe\n";
}

// Verificar webhook
if (!file_exists($webhook_file)) {
    echo "   ❌ Webhook não existe - CRÍTICO\n";
} else {
    $webhook_content = file_get_contents($webhook_file);
    if (strpos($webhook_content, 'enviarMensagemWhatsApp') === false) {
        echo "   ❌ Webhook não tem integração com envio - CRÍTICO\n";
        echo "   🔧 Solução: Reintegrar função de envio no webhook\n\n";
    } else {
        echo "   ✅ Webhook tem código de envio\n";
    }
}

echo "   🚀 PRÓXIMAS AÇÕES RECOMENDADAS:\n";
echo "   1. Verificar se funcao_envio_whatsapp.php está correto\n";
echo "   2. Verificar se webhook está integrando a função\n";
echo "   3. Verificar logs de erro em tempo real\n";
echo "   4. Testar com mensagem real no WhatsApp\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?> 