<?php
/**
 * VERIFICADOR DE MENSAGENS NÃO RECEBIDAS
 * 
 * Script para diagnosticar e corrigir problemas de recebimento de mensagens
 */

echo "🔍 VERIFICANDO MENSAGENS NÃO RECEBIDAS\n";
echo "======================================\n\n";

// 1. Verificar logs de webhook
echo "1️⃣ VERIFICANDO LOGS DE WEBHOOK\n";
echo "==============================\n\n";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $ultimas_linhas = array_slice($logs, -10);
    
    echo "📄 Últimas 10 linhas do log de webhook:\n";
    foreach ($ultimas_linhas as $linha) {
        echo "   " . trim($linha) . "\n";
    }
    
    // Verificar se há mensagens recentes
    $mensagens_recentes = 0;
    foreach ($logs as $linha) {
        if (strpos($linha, '"event":"onmessage"') !== false) {
            $mensagens_recentes++;
        }
    }
    
    echo "\n📊 Total de mensagens recebidas hoje: $mensagens_recentes\n";
} else {
    echo "❌ Arquivo de log não encontrado: $log_file\n";
}

// 2. Verificar tabela de mensagens
echo "\n2️⃣ VERIFICANDO TABELA DE MENSAGENS\n";
echo "===================================\n\n";

try {
    require_once 'painel/db_emergency.php';
    
    // Verificar mensagens de hoje
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "📊 Mensagens salvas hoje: " . $row['total'] . "\n";
    }
    
    // Verificar mensagens não lidas
    $sql = "SELECT COUNT(*) as nao_lidas FROM mensagens_comunicacao WHERE status = 'recebido' AND direcao = 'recebido'";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "📬 Mensagens não lidas: " . $row['nao_lidas'] . "\n";
    }
    
    // Verificar mensagens pendentes
    $sql = "SELECT COUNT(*) as pendentes FROM mensagens_pendentes WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "⏳ Mensagens pendentes: " . $row['pendentes'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar banco: " . $e->getMessage() . "\n";
}

// 3. Verificar configuração do webhook
echo "\n3️⃣ VERIFICANDO CONFIGURAÇÃO DO WEBHOOK\n";
echo "=======================================\n\n";

$webhook_url = "https://pixel12digital.com.br/app/api/webhook_whatsapp.php";
echo "🔗 URL do webhook: $webhook_url\n";

// Testar se o webhook está acessível
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📡 Status do webhook: HTTP $http_code\n";

if ($error) {
    echo "❌ Erro de conectividade: $error\n";
} elseif ($http_code === 200) {
    echo "✅ Webhook está respondendo\n";
} else {
    echo "⚠️ Webhook com problema (HTTP $http_code)\n";
}

// 4. Verificar arquivo de recebimento
echo "\n4️⃣ VERIFICANDO ARQUIVO DE RECEBIMENTO\n";
echo "=====================================\n\n";

$receber_file = 'painel/receber_mensagem.php';
if (file_exists($receber_file)) {
    echo "✅ Arquivo de recebimento existe: $receber_file\n";
    
    // Verificar se o arquivo está funcionando
    $test_data = [
        'from' => '4796164699',
        'body' => 'Teste de mensagem',
        'timestamp' => time()
    ];
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "🧪 Teste de recebimento: HTTP $http_code\n";
    echo "📝 Resposta: " . substr($response, 0, 100) . "...\n";
    
} else {
    echo "❌ Arquivo de recebimento não encontrado\n";
}

// 5. Verificar cache do sistema
echo "\n5️⃣ VERIFICANDO CACHE DO SISTEMA\n";
echo "===============================\n\n";

$cache_dir = 'painel/cache/';
if (is_dir($cache_dir)) {
    $cache_files = glob($cache_dir . '*.cache');
    echo "📁 Arquivos de cache: " . count($cache_files) . "\n";
    
    // Verificar tamanho total do cache
    $total_size = 0;
    foreach ($cache_files as $file) {
        $total_size += filesize($file);
    }
    
    echo "💾 Tamanho total do cache: " . round($total_size / 1024, 2) . " KB\n";
    
    // Limpar cache se necessário
    if ($total_size > 10240) { // Mais de 10MB
        echo "🧹 Cache muito grande, limpando...\n";
        foreach ($cache_files as $file) {
            unlink($file);
        }
        echo "✅ Cache limpo\n";
    }
} else {
    echo "❌ Diretório de cache não encontrado\n";
}

// 6. Verificar configuração do WhatsApp
echo "\n6️⃣ VERIFICANDO CONFIGURAÇÃO DO WHATSAPP\n";
echo "========================================\n\n";

$whatsapp_config = [
    'robot_url' => defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000',
    'timeout' => defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : 15,
    'webhook_url' => $webhook_url
];

echo "🤖 Configurações do WhatsApp:\n";
echo "   Robot URL: " . $whatsapp_config['robot_url'] . "\n";
echo "   Timeout: " . $whatsapp_config['timeout'] . "s\n";
echo "   Webhook URL: " . $whatsapp_config['webhook_url'] . "\n";

// Testar conectividade com o servidor WhatsApp
$ch = curl_init($whatsapp_config['robot_url'] . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Status do servidor WhatsApp: HTTP $http_code\n";

// 7. Soluções recomendadas
echo "\n7️⃣ SOLUÇÕES RECOMENDADAS\n";
echo "========================\n\n";

echo "🔧 Se as mensagens não estão sendo recebidas:\n\n";

echo "1. **Verificar limite de conexões:**\n";
echo "   - O banco pode ter atingido o limite de 500 conexões/hora\n";
echo "   - Aguarde 1 hora ou contate o provedor\n\n";

echo "2. **Verificar webhook:**\n";
echo "   - Confirme se o webhook está configurado no WhatsApp\n";
echo "   - Teste a URL: $webhook_url\n\n";

echo "3. **Verificar logs:**\n";
echo "   - Monitore o arquivo: $log_file\n";
echo "   - Procure por erros ou mensagens perdidas\n\n";

echo "4. **Limpar cache:**\n";
echo "   - Execute: php painel/cache_invalidator.php\n";
echo "   - Ou delete manualmente os arquivos .cache\n\n";

echo "5. **Reiniciar serviços:**\n";
echo "   - Reinicie o servidor web\n";
echo "   - Verifique se o servidor WhatsApp está rodando\n\n";

echo "6. **Testar manualmente:**\n";
echo "   - Envie uma mensagem de teste\n";
echo "   - Verifique se aparece no log\n";
echo "   - Confirme se é salva no banco\n\n";

// 8. Criar script de correção automática
echo "\n8️⃣ CRIANDO SCRIPT DE CORREÇÃO\n";
echo "============================\n\n";

$correcao_content = '<?php
/**
 * CORREÇÃO AUTOMÁTICA DE MENSAGENS
 * 
 * Script para corrigir problemas de recebimento
 */

echo "🔧 CORRIGINDO PROBLEMAS DE MENSAGENS\n";
echo "====================================\n\n";

// 1. Limpar cache
echo "1. Limpando cache...\n";
$cache_dir = "painel/cache/";
if (is_dir($cache_dir)) {
    $files = glob($cache_dir . "*.cache");
    foreach ($files as $file) {
        unlink($file);
    }
    echo "   ✅ Cache limpo\n";
}

// 2. Verificar mensagens pendentes
echo "2. Verificando mensagens pendentes...\n";
try {
    require_once "painel/db_emergency.php";
    
    $sql = "SELECT * FROM mensagens_pendentes WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "   📬 Encontradas " . $result->num_rows . " mensagens pendentes\n";
        
        while ($row = $result->fetch_assoc()) {
            // Tentar processar mensagem pendente
            $numero = $row["numero"];
            $mensagem = $row["mensagem"];
            
            // Buscar cliente pelo número
            $numero_limpo = preg_replace("/\D/", "", $numero);
            $sql_cliente = "SELECT id FROM clientes WHERE celular LIKE \'%$numero_limpo%\' LIMIT 1";
            $result_cliente = $mysqli->query($sql_cliente);
            
            if ($result_cliente && $result_cliente->num_rows > 0) {
                $cliente = $result_cliente->fetch_assoc();
                $cliente_id = $cliente["id"];
                
                // Mover para tabela principal
                $sql_move = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                            VALUES (" . $row["canal_id"] . ", $cliente_id, \'" . $mysqli->real_escape_string($mensagem) . "\', \'texto\', \'" . $row["data_hora"] . "\', \'recebido\', \'recebido\')";
                
                if ($mysqli->query($sql_move)) {
                    // Remover da tabela pendente
                    $mysqli->query("DELETE FROM mensagens_pendentes WHERE id = " . $row["id"]);
                    echo "   ✅ Mensagem processada: $numero\n";
                }
            }
        }
    } else {
        echo "   ✅ Nenhuma mensagem pendente\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

// 3. Verificar integridade das mensagens
echo "3. Verificando integridade...\n";
try {
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   📊 Total de mensagens hoje: " . $row["total"] . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n🎉 CORREÇÃO CONCLUÍDA!\n";
echo "======================\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "   1. Teste o chat novamente\n";
echo "   2. Envie uma mensagem de teste\n";
echo "   3. Verifique se aparece no painel\n";
echo "   4. Se ainda não funcionar, aguarde 1 hora\n";
?>';

file_put_contents('corrigir_mensagens.php', $correcao_content);
echo "✅ Script de correção criado: corrigir_mensagens.php\n";

echo "\n🎯 RESUMO DO DIAGNÓSTICO\n";
echo "========================\n\n";

echo "📊 Status atual:\n";
echo "   - Logs de webhook: " . (file_exists($log_file) ? "✅" : "❌") . "\n";
echo "   - Webhook acessível: " . ($http_code === 200 ? "✅" : "❌") . "\n";
echo "   - Servidor WhatsApp: " . ($http_code === 200 ? "✅" : "❌") . "\n";
echo "   - Banco de dados: " . (isset($mysqli) ? "✅" : "❌") . "\n\n";

echo "🔧 Para corrigir o problema:\n";
echo "   Execute: php corrigir_mensagens.php\n\n";

echo "📞 Se o problema persistir:\n";
echo "   - Verifique se o WhatsApp está enviando mensagens\n";
echo "   - Confirme se o webhook está configurado\n";
echo "   - Aguarde 1 hora para resetar limite de conexões\n";
?> 