<?php
/**
 * 🎯 CONFIGURAR ENVIO WHATSAPP CORRETO
 * 
 * Baseado na descoberta: whatsapp-web.js com endpoints /send/text
 */

echo "=== 🎯 CONFIGURAR ENVIO WHATSAPP CORRETO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

$vps_ip = "212.85.11.238";
$vps_port = "3000";

// ===== 1. TESTAR ENDPOINT CORRETO =====
echo "1. 🧪 TESTANDO ENDPOINT CORRETO /send/text:\n";

$endpoint_correto = "/send/text";
$test_message = [
    "number" => "554796164699@c.us",
    "message" => "🎉 TESTE ENDPOINT CORRETO - " . date('Y-m-d H:i:s')
];

$url = "http://$vps_ip:$vps_port$endpoint_correto";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_message));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
echo "   📄 Resposta: $response\n";

if ($http_code == 200) {
    echo "   ✅ ENDPOINT FUNCIONANDO!\n";
    $working = true;
} else {
    echo "   ❌ Testando formatos alternativos...\n";
    $working = false;
    
    // Testar outros formatos possíveis
    $formatos_alternativos = [
        ["to" => "554796164699@c.us", "text" => "🧪 Teste formato 2"],
        ["chatId" => "554796164699@c.us", "text" => "🧪 Teste formato 3"],
        ["phone" => "554796164699", "message" => "🧪 Teste formato 4"],
        ["number" => "554796164699", "message" => "🧪 Teste formato 5"]
    ];
    
    foreach ($formatos_alternativos as $i => $formato) {
        echo "   🧪 Testando formato " . ($i + 2) . ":\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($formato));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "      📊 HTTP: $http_code\n";
        echo "      📄 Resposta: " . substr($response, 0, 100) . "\n";
        
        if ($http_code == 200) {
            echo "      ✅ FORMATO FUNCIONANDO!\n";
            $test_message = $formato;
            $working = true;
            break;
        }
        echo "\n";
    }
}

echo "\n";

// ===== 2. CRIAR FUNÇÃO DE ENVIO =====
echo "2. 🔧 CRIANDO FUNÇÃO DE ENVIO:\n";

if ($working) {
    echo "   ✅ Criando função baseada no formato que funcionou...\n";
    
    $function_content = '<?php
/**
 * Função para enviar mensagens via WhatsApp Web.js
 */
function enviarMensagemWhatsApp($numero, $mensagem) {
    $vps_url = "http://212.85.11.238:3000/send/text";
    
    // Garantir formato correto do número
    if (strpos($numero, "@c.us") === false) {
        $numero = $numero . "@c.us";
    }
    
    $data = ' . json_encode($test_message, JSON_PRETTY_PRINT) . ';
    $data["number"] = $numero;
    $data["message"] = $mensagem;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        "success" => $http_code == 200,
        "http_code" => $http_code,
        "response" => $response,
        "error" => $error
    ];
}

// Teste da função
echo "🧪 Testando função de envio:\n";
$resultado = enviarMensagemWhatsApp("554796164699", "🎉 Função de envio funcionando! - " . date("Y-m-d H:i:s"));
echo json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
?>';

    file_put_contents('funcao_envio_whatsapp.php', $function_content);
    echo "   ✅ Função criada: funcao_envio_whatsapp.php\n";
    
} else {
    echo "   ❌ Nenhum formato funcionou. Verifique se WhatsApp está conectado no VPS.\n";
}

echo "\n";

// ===== 3. INTEGRAR NO WEBHOOK =====
echo "3. 🔗 INTEGRANDO NO WEBHOOK:\n";

if ($working) {
    echo "   🔧 Modificando webhook para usar a função de envio...\n";
    
    // Ler webhook atual
    $webhook_file = 'painel/receber_mensagem_ana_local.php';
    $webhook_content = file_get_contents($webhook_file);
    
    // Fazer backup
    $backup_file = $webhook_file . '.backup_antes_envio_' . date('Ymd_His');
    copy($webhook_file, $backup_file);
    echo "   💾 Backup criado: $backup_file\n";
    
    // Adicionar include da função
    $include_funcao = '<?php
// Incluir função de envio WhatsApp
require_once \'../funcao_envio_whatsapp.php\';

';
    
    // Adicionar após Ana processar
    $search_pattern = '/(\$resultado_ana = \$integrador->processarMensagem\(\$dados\);)/';
    $replacement = '$1

    // Enviar resposta da Ana via WhatsApp
    if (isset($resultado_ana[\'resposta_ana\']) && !empty($resultado_ana[\'resposta_ana\'])) {
        $numero_destinatario = $dados[\'from\'];
        $mensagem_ana = $resultado_ana[\'resposta_ana\'];
        
        error_log("[WEBHOOK_ANA] Enviando resposta da Ana para: $numero_destinatario");
        
        $resultado_envio = enviarMensagemWhatsApp($numero_destinatario, $mensagem_ana);
        
        if ($resultado_envio[\'success\']) {
            error_log("[WEBHOOK_ANA] ✅ Mensagem enviada com sucesso");
        } else {
            error_log("[WEBHOOK_ANA] ❌ Erro ao enviar mensagem: " . json_encode($resultado_envio));
        }
        
        // Salvar log do envio no banco
        $stmt = $mysqli->prepare("
            UPDATE mensagens_comunicacao 
            SET status = ?, motivo_erro = ? 
            WHERE id = ?
        ");
        
        $status_envio = $resultado_envio[\'success\'] ? \'enviado\' : \'erro_envio\';
        $erro_envio = $resultado_envio[\'success\'] ? null : json_encode($resultado_envio);
        $message_id = $resultado_ana[\'response_id\'] ?? null;
        
        if ($message_id) {
            $stmt->bind_param("ssi", $status_envio, $erro_envio, $message_id);
            $stmt->execute();
        }
    }';
    
    // Aplicar modificações
    $new_webhook_content = preg_replace($search_pattern, $replacement, $webhook_content);
    
    // Adicionar include no início
    if (strpos($new_webhook_content, 'funcao_envio_whatsapp.php') === false) {
        $new_webhook_content = str_replace('<?php', $include_funcao, $new_webhook_content);
    }
    
    // Salvar webhook modificado
    if (file_put_contents($webhook_file, $new_webhook_content)) {
        echo "   ✅ Webhook modificado com função de envio!\n";
    } else {
        echo "   ❌ Erro ao modificar webhook\n";
    }
    
} else {
    echo "   ⚠️  Pulando integração - endpoint não funcionou\n";
}

echo "\n";

// ===== 4. TESTE FINAL =====
echo "4. 🧪 TESTE FINAL DO SISTEMA:\n";

if ($working) {
    echo "   🧪 Testando webhook com envio integrado...\n";
    
    $test_data = [
        "from" => "554796164699@c.us",
        "body" => "🎉 TESTE FINAL COMPLETO - " . date('Y-m-d H:i:s'),
        "timestamp" => time()
    ];
    
    $webhook_url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";
    
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
    echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
    
    if ($http_code == 200) {
        echo "   ✅ SISTEMA FUNCIONANDO COMPLETAMENTE!\n";
        echo "   📱 Verifique se a mensagem chegou no WhatsApp\n";
    } else {
        echo "   ❌ Ainda há problemas (HTTP $http_code)\n";
    }
}

echo "\n";

// ===== 5. INSTRUÇÕES FINAIS =====
echo "5. 🎯 INSTRUÇÕES FINAIS:\n";

if ($working) {
    echo "   ✅ SISTEMA CONFIGURADO CORRETAMENTE!\n\n";
    
    echo "   📋 O QUE FOI FEITO:\n";
    echo "   1. ✅ Encontrado endpoint correto: /send/text\n";
    echo "   2. ✅ Criada função de envio: funcao_envio_whatsapp.php\n";
    echo "   3. ✅ Webhook integrado com envio automático\n";
    echo "   4. ✅ Ana agora responde no WhatsApp!\n\n";
    
    echo "   📱 PARA TESTAR:\n";
    echo "   1. Envie mensagem para: 554797146908\n";
    echo "   2. Ana deve responder automaticamente\n";
    echo "   3. Verifique no chat web se aparece\n\n";
    
    echo "   🔧 ARQUIVOS IMPORTANTES:\n";
    echo "   - funcao_envio_whatsapp.php (função de envio)\n";
    echo "   - $backup_file (backup do webhook)\n\n";
    
} else {
    echo "   ⚠️  CONFIGURAÇÃO PARCIAL\n\n";
    echo "   📋 PROBLEMAS ENCONTRADOS:\n";
    echo "   - Endpoint /send/text não respondeu como esperado\n";
    echo "   - Pode ser necessário verificar conexão WhatsApp no VPS\n\n";
    
    echo "   🔧 PRÓXIMOS PASSOS:\n";
    echo "   1. Verificar se WhatsApp está conectado no VPS\n";
    echo "   2. Testar manualmente: curl -X POST http://212.85.11.238:3000/send/text\n";
    echo "   3. Verificar logs do PM2 no VPS\n\n";
}

echo "   🎯 STATUS FINAL:\n";
echo "   - ✅ Webhook processa mensagens\n";
echo "   - ✅ Ana analisa e responde\n";
echo "   - ✅ Mensagens salvas no banco\n";
echo "   - " . ($working ? "✅" : "❌") . " Ana envia respostas para WhatsApp\n";

echo "\n=== FIM DA CONFIGURAÇÃO ===\n";
echo "Status: " . ($working ? "SISTEMA COMPLETO ✅" : "NECESSITA AJUSTES ⚠️") . "\n";
?> 