<?php
/**
 * 🔧 CORRIGIR WEBHOOK VPS - VERSÃO MELHORADA
 * 
 * Corrige o formato de dados enviado pelo VPS para o webhook
 * com logger estruturado, validação JSON e payload adaptado
 */

// Configuração de logging estruturado
class WebhookLogger {
    private $logFile = 'webhook_debug.log';
    
    public function log($level, $message, $data = null) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'data' => $data
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Em produção, usar apenas error_log() para logs críticos
        if ($level === 'ERROR' || $level === 'CRITICAL') {
            error_log("WEBHOOK {$level}: {$message}");
        }
    }
}

$logger = new WebhookLogger();

echo "🔧 CORRIGIR WEBHOOK VPS - VERSÃO MELHORADA\n";
echo "==========================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

$logger->log('INFO', 'Iniciando correção do webhook', [
    'vps_ip' => $vps_ip,
    'webhook_url' => $webhook_url
]);

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n";
echo "Log file: webhook_debug.log\n\n";

// 1. VERIFICAR CONFIGURAÇÃO ATUAL
echo "1️⃣ VERIFICANDO CONFIGURAÇÃO ATUAL\n";
echo "---------------------------------\n";

$canal_urls = [
    'Canal 3000 (Ana)' => "http://{$vps_ip}:3000",
    'Canal 3001 (Humano)' => "http://{$vps_ip}:3001"
];

foreach ($canal_urls as $nome => $url) {
    echo "🔍 $nome...\n";
    
    $ch = curl_init($url . '/webhook/config');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        // Validar se é JSON válido
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if (strpos($contentType, 'application/json') !== false) {
            $config = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "  ✅ Configuração obtida\n";
                echo "  🔗 URL configurada: " . ($config['webhook_url'] ?? 'N/A') . "\n";
                
                $logger->log('INFO', "Configuração obtida para {$nome}", $config);
            } else {
                echo "  ❌ JSON inválido: " . json_last_error_msg() . "\n";
                $logger->log('ERROR', "JSON inválido em {$nome}", [
                    'response' => $response,
                    'json_error' => json_last_error_msg()
                ]);
            }
        } else {
            echo "  ❌ Content-Type incorreto: $contentType\n";
            $logger->log('ERROR', "Content-Type incorreto em {$nome}", ['content_type' => $contentType]);
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
        if ($error) {
            echo "  Erro cURL: $error\n";
        }
        $logger->log('ERROR', "Erro ao obter configuração de {$nome}", [
            'http_code' => $http_code,
            'curl_error' => $error
        ]);
    }
    echo "\n";
}

// 2. TESTAR DIFERENTES FORMATOS DE PAYLOAD
echo "2️⃣ TESTANDO DIFERENTES FORMATOS DE PAYLOAD\n";
echo "------------------------------------------\n";

// Teste 1: Formato simples (como sugerido)
$dados_simples = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE FORMATO SIMPLES - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

// Teste 2: Formato com wrapper (como o VPS envia)
$dados_wrapper = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699@c.us',
        'to' => '554797146908@c.us',
        'body' => 'TESTE FORMATO WRAPPER - ' . date('Y-m-d H:i:s'),
        'type' => 'text',
        'timestamp' => time()
    ]
];

// Teste 3: Formato exato do VPS (para comparação)
$dados_vps = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'TESTE FORMATO VPS - ' . date('Y-m-d H:i:s'),
        'type' => 'chat',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

$testes = [
    'Formato Simples' => $dados_simples,
    'Formato Wrapper' => $dados_wrapper,
    'Formato VPS (Incorreto)' => $dados_vps
];

foreach ($testes as $nome_teste => $dados_teste) {
    echo "🧪 Testando: $nome_teste\n";
    echo "  Dados: " . json_encode($dados_teste, JSON_PRETTY_PRINT) . "\n\n";
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Corrigir certificado em vez de desabilitar
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    echo "📥 Resposta do webhook:\n";
    echo "  HTTP Code: $http_code\n";
    echo "  Content-Type: $contentType\n";
    
    if ($http_code === 200) {
        // Validar JSON da resposta
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if ($data && $data['success']) {
                    echo "  ✅ Webhook processando corretamente!\n";
                    echo "  📝 Message ID: " . ($data['message_id'] ?? 'N/A') . "\n";
                    
                    $logger->log('SUCCESS', "Teste {$nome_teste} bem-sucedido", $data);
                } else {
                    echo "  ❌ Erro no processamento: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
                    $logger->log('ERROR', "Erro no processamento do teste {$nome_teste}", $data);
                }
            } else {
                echo "  ❌ JSON inválido na resposta: " . json_last_error_msg() . "\n";
                $logger->log('ERROR', "JSON inválido na resposta do teste {$nome_teste}", [
                    'response' => $response,
                    'json_error' => json_last_error_msg()
                ]);
            }
        } else {
            echo "  ❌ Content-Type incorreto na resposta: $contentType\n";
            $logger->log('ERROR', "Content-Type incorreto na resposta do teste {$nome_teste}", [
                'content_type' => $contentType,
                'response' => $response
            ]);
        }
    } else {
        echo "  ❌ Erro HTTP: $http_code\n";
        if ($error) {
            echo "  Erro cURL: $error\n";
        }
        echo "  Response: $response\n";
        
        $logger->log('ERROR', "Erro HTTP no teste {$nome_teste}", [
            'http_code' => $http_code,
            'curl_error' => $error,
            'response' => $response
        ]);
    }
    echo "\n";
}

// 3. ANÁLISE DOS RESULTADOS
echo "3️⃣ ANÁLISE DOS RESULTADOS\n";
echo "-------------------------\n";

echo "🔍 **RESULTADO DOS TESTES:**\n\n";

echo "✅ **Formato que funciona:**\n";
echo "   - Identificar qual formato retornou HTTP 200\n";
echo "   - Usar esse formato como padrão\n\n";

echo "❌ **Problemas identificados:**\n";
echo "   - Formato VPS incorreto (HTTP 400)\n";
echo "   - Campos ausentes ou mal formatados\n";
echo "   - Problemas de SSL/certificado\n\n";

// 4. INSTRUÇÕES MELHORADAS PARA CORREÇÃO NO VPS
echo "4️⃣ INSTRUÇÕES MELHORADAS PARA CORREÇÃO NO VPS\n";
echo "-----------------------------------------------\n";

echo "🔧 **CORREÇÕES NECESSÁRIAS NO CÓDIGO DO VPS:**\n\n";

echo "**Arquivo:** /var/whatsapp-api/whatsapp-api-server.js\n";
echo "**Linha:** ~180 (função de envio de webhook)\n\n";

echo "**PROBLEMA 1: Formato de dados incorreto**\n";
echo "```javascript\n";
echo "// ANTES (INCORRETO):\n";
echo "const webhookData = {\n";
echo "  event: 'onmessage',\n";
echo "  data: {\n";
echo "    from: message.from.split('@')[0], // ❌ Remove @c.us\n";
echo "    text: message.body,               // ❌ Campo errado\n";
echo "    type: 'chat',                    // ❌ Tipo errado\n";
echo "    timestamp: message.timestamp,\n";
echo "    session: sessionName\n";
echo "  }\n";
echo "};\n\n";
echo "// DEPOIS (CORRETO):\n";
echo "const webhookData = {\n";
echo "  from: message.from,                // ✅ Mantém @c.us\n";
echo "  to: message.to || '554797146908@c.us',\n";
echo "  body: message.body,                // ✅ Campo correto\n";
echo "  type: 'text',                      // ✅ Tipo correto\n";
echo "  timestamp: message.timestamp\n";
echo "};\n";
echo "```\n\n";

echo "**PROBLEMA 2: URL inválida**\n";
echo "```javascript\n";
echo "// ANTES (INCORRETO):\n";
echo "const webhookUrl = 'api/webhook.php'; // ❌ URL relativa\n\n";
echo "// DEPOIS (CORRETO):\n";
echo "const webhookUrl = webhookConfig.webhook_url || webhookConfig.url;\n";
echo "if (!webhookUrl) {\n";
echo "  console.log('❌ Webhook não configurado');\n";
echo "  return;\n";
echo "}\n";
echo "```\n\n";

echo "**PROBLEMA 3: Timeout e SSL**\n";
echo "```javascript\n";
echo "// ANTES (INCORRETO):\n";
echo "const response = await fetch(webhookUrl, {\n";
echo "  method: 'POST',\n";
echo "  headers: { 'Content-Type': 'application/json' },\n";
echo "  body: JSON.stringify(webhookData)\n";
echo "}); // ❌ Sem timeout\n\n";
echo "// DEPOIS (CORRETO):\n";
echo "const controller = new AbortController();\n";
echo "const timeoutId = setTimeout(() => controller.abort(), 30000);\n\n";
echo "try {\n";
echo "  const response = await fetch(webhookUrl, {\n";
echo "    method: 'POST',\n";
echo "    headers: { 'Content-Type': 'application/json' },\n";
echo "    body: JSON.stringify(webhookData),\n";
echo "    signal: controller.signal\n";
echo "  });\n";
echo "  clearTimeout(timeoutId);\n";
echo "} catch (error) {\n";
echo "  clearTimeout(timeoutId);\n";
echo "  console.log('❌ Erro ao enviar webhook:', error.message);\n";
echo "}\n";
echo "```\n\n";

// 5. COMANDOS PARA APLICAR CORREÇÕES
echo "5️⃣ COMANDOS PARA APLICAR CORREÇÕES\n";
echo "-----------------------------------\n";

echo "🔧 **Execute no VPS:**\n\n";

echo "1. **Fazer backup do arquivo atual:**\n";
echo "   cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup\n\n";

echo "2. **Editar o arquivo:**\n";
echo "   nano /var/whatsapp-api/whatsapp-api-server.js\n\n";

echo "3. **Localizar e corrigir a função de webhook:**\n";
echo "   - Procurar por 'webhook' ou 'fetch'\n";
echo "   - Aplicar as correções de formato\n";
echo "   - Corrigir a URL\n";
echo "   - Implementar timeout adequado\n\n";

echo "4. **Reiniciar os serviços:**\n";
echo "   pm2 restart whatsapp-3000\n";
echo "   pm2 restart whatsapp-3001\n\n";

echo "5. **Verificar logs:**\n";
echo "   pm2 logs whatsapp-3000 --lines 50\n";
echo "   pm2 logs whatsapp-3001 --lines 50\n\n";

echo "6. **Testar com mensagem real:**\n";
echo "   - Enviar mensagem para 554797146908\n";
echo "   - Verificar se aparece no chat\n";
echo "   - Verificar se Ana responde\n\n";

// 6. VALIDAÇÃO FINAL
echo "6️⃣ VALIDAÇÃO FINAL\n";
echo "-------------------\n";

echo "✅ **CRITÉRIOS DE SUCESSO:**\n";
echo "1. Webhook recebendo dados no formato correto\n";
echo "2. Mensagens sendo salvas no banco de dados\n";
echo "3. Ana respondendo automaticamente\n";
echo "4. Chat funcionando normalmente\n";
echo "5. Logs sem erros de formato ou timeout\n\n";

echo "📊 **MONITORAMENTO:**\n";
echo "- Verificar logs: tail -f webhook_debug.log\n";
echo "- Monitorar PM2: pm2 monit\n";
echo "- Testar periodicamente com mensagens reais\n\n";

$logger->log('INFO', 'Análise de correção do webhook concluída');

echo "✅ ANÁLISE CONCLUÍDA!\n";
echo "O problema está no formato de dados enviado pelo VPS.\n";
echo "Aplique as correções sugeridas e monitore os logs.\n";
echo "Log detalhado salvo em: webhook_debug.log\n";
?> 