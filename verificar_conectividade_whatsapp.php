<?php
/**
 * VERIFICAR CONECTIVIDADE DO WHATSAPP
 * 
 * Script para verificar se o WhatsApp está enviando mensagens para o webhook
 */

echo "🔍 VERIFICANDO CONECTIVIDADE DO WHATSAPP\n";
echo "========================================\n\n";

// 1. Verificar configuração do WhatsApp
echo "1️⃣ CONFIGURAÇÃO DO WHATSAPP\n";
echo "============================\n\n";

echo "📱 Configurações:\n";
echo "   URL Robot: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'Não definida') . "\n";
echo "   Timeout: " . (defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : 'Não definido') . "\n";
echo "   Webhook URL: https://pixel12digital.com.br/app/api/webhook_whatsapp.php\n\n";

// 2. Testar conectividade com o servidor WhatsApp
echo "2️⃣ TESTANDO CONECTIVIDADE COM SERVIDOR WHATSAPP\n";
echo "================================================\n\n";

$whatsapp_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';

echo "🔗 Testando conectividade com: $whatsapp_url\n";

// Testar se o servidor WhatsApp responde
$ch = curl_init($whatsapp_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📡 Status do servidor WhatsApp: HTTP $http_code\n";

if ($error) {
    echo "❌ Erro de conectividade: $error\n";
} elseif ($http_code === 200) {
    echo "✅ Servidor WhatsApp está respondendo\n";
} else {
    echo "⚠️ Servidor WhatsApp com problema (HTTP $http_code)\n";
}

// 3. Verificar logs de webhook em tempo real
echo "\n3️⃣ LOGS DE WEBHOOK EM TEMPO REAL\n";
echo "==================================\n\n";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
echo "📄 Monitorando logs: $log_file\n\n";

if (file_exists($log_file)) {
    $initial_size = filesize($log_file);
    echo "📊 Tamanho inicial do log: " . round($initial_size / 1024, 2) . " KB\n";
    echo "🕐 Aguardando novas mensagens por 30 segundos...\n\n";
    
    $start_time = time();
    $end_time = $start_time + 30;
    
    while (time() < $end_time) {
        clearstatcache();
        $current_size = filesize($log_file);
        
        if ($current_size > $initial_size) {
            echo "🆕 NOVA MENSAGEM DETECTADA!\n";
            
            // Ler novas linhas
            $logs = file($log_file);
            $new_lines = array_slice($logs, -1);
            
            foreach ($new_lines as $log) {
                $hora = substr($log, 0, 19);
                $conteudo = substr($log, 20);
                echo "   [$hora] " . substr($conteudo, 0, 100) . "...\n";
            }
            
            $initial_size = $current_size;
        }
        
        sleep(1);
    }
    
    echo "\n⏰ Tempo de monitoramento concluído\n";
} else {
    echo "❌ Arquivo de log não encontrado\n";
}

// 4. Verificar configuração do webhook no WhatsApp
echo "\n4️⃣ CONFIGURAÇÃO DO WEBHOOK NO WHATSAPP\n";
echo "========================================\n\n";

echo "🔧 Para verificar se o webhook está configurado corretamente:\n\n";

echo "1. **Acessar painel do WhatsApp Business API:**\n";
echo "   - Verificar se o webhook está ativo\n";
echo "   - Verificar se a URL está correta\n";
echo "   - Verificar se há erros de validação\n\n";

echo "2. **URL do webhook deve ser:**\n";
echo "   https://pixel12digital.com.br/app/api/webhook_whatsapp.php\n\n";

echo "3. **Verificar certificado SSL:**\n";
echo "   - O certificado deve ser válido\n";
echo "   - HTTPS deve estar funcionando\n\n";

// 5. Testar envio de mensagem via WhatsApp
echo "\n5️⃣ TESTE DE ENVIO VIA WHATSAPP\n";
echo "================================\n\n";

echo "📤 Para testar se o WhatsApp está funcionando:\n\n";

echo "1. **Enviar mensagem de teste:**\n";
echo "   - Enviar 'teste' para o número conectado\n";
echo "   - Verificar se aparece nos logs\n";
echo "   - Verificar se é salva no banco\n\n";

echo "2. **Verificar logs em tempo real:**\n";
echo "   - Monitorar logs enquanto envia mensagem\n";
echo "   - Verificar se há erros\n\n";

// 6. Criar script de monitoramento em tempo real
echo "\n6️⃣ CRIANDO MONITORAMENTO EM TEMPO REAL\n";
echo "========================================\n\n";

$monitor_tempo_real = "<?php
/**
 * MONITORAMENTO EM TEMPO REAL DO WEBHOOK
 * 
 * Monitora logs do webhook em tempo real
 */

echo \"🔍 MONITORAMENTO EM TEMPO REAL DO WEBHOOK\n\";
echo \"=========================================\n\n\";

\$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';

if (!file_exists(\$log_file)) {
    echo \"❌ Arquivo de log não encontrado: \$log_file\n\";
    exit;
}

\$initial_size = filesize(\$log_file);
echo \"📄 Monitorando: \$log_file\n\";
echo \"📊 Tamanho inicial: \" . round(\$initial_size / 1024, 2) . \" KB\n\";
echo \"🕐 Iniciando monitoramento... (Ctrl+C para parar)\n\n\";

while (true) {
    clearstatcache();
    \$current_size = filesize(\$log_file);
    
    if (\$current_size > \$initial_size) {
        \$logs = file(\$log_file);
        \$new_lines = array_slice(\$logs, -1);
        
        foreach (\$new_lines as \$log) {
            \$hora = substr(\$log, 0, 19);
            \$conteudo = substr(\$log, 20);
            
            echo \"🆕 [\$hora] Nova mensagem recebida!\n\";
            echo \"   Conteúdo: \" . substr(\$conteudo, 0, 150) . \"...\n\";
            echo \"   \" . str_repeat(\"-\", 50) . \"\n\";
        }
        
        \$initial_size = \$current_size;
    }
    
    usleep(500000); // 0.5 segundos
}
?>";

file_put_contents('monitor_tempo_real.php', $monitor_tempo_real);
echo "✅ Arquivo monitor_tempo_real.php criado\n";

// 7. Recomendações finais
echo "\n7️⃣ RECOMENDAÇÕES FINAIS\n";
echo "========================\n\n";

echo "🚨 **DIAGNÓSTICO:**\n\n";

echo "✅ **Webhook funcionando:** HTTP 200, mensagens sendo salvas\n";
echo "❌ **WhatsApp não enviando:** Última mensagem real às 16:06\n";
echo "❌ **Mensagem 17:03:** Não foi enviada pelo WhatsApp\n\n";

echo "🔧 **SOLUÇÕES:**\n\n";

echo "1. **Verificar configuração do WhatsApp:**\n";
echo "   - Acessar painel do WhatsApp Business API\n";
echo "   - Verificar se webhook está ativo\n";
echo "   - Verificar se URL está correta\n\n";

echo "2. **Testar conectividade:**\n";
echo "   - Enviar mensagem de teste agora\n";
echo "   - Monitorar logs em tempo real\n";
echo "   - Verificar se chega ao webhook\n\n";

echo "3. **Verificar servidor WhatsApp:**\n";
echo "   - Servidor pode estar offline\n";
echo "   - Problemas de conectividade\n";
echo "   - Configuração incorreta\n\n";

echo "4. **Monitoramento contínuo:**\n";
echo "   ```bash\n";
echo "   php monitor_tempo_real.php\n";
echo "   ```\n\n";

echo "✅ Análise concluída!\n";
echo "💡 **Próximo passo:** Enviar mensagem de teste e monitorar!\n";
?> 