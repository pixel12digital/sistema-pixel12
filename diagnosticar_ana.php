<?php
/**
 * 🔍 DIAGNÓSTICO E CORREÇÃO DA ANA
 * 
 * Este script diagnostica e corrige problemas com a Ana não responder mensagens
 */

echo "🔍 DIAGNÓSTICO E CORREÇÃO DA ANA\n";
echo "================================\n\n";

// ===== 1. VERIFICAR CONFIGURAÇÃO ATUAL =====
echo "1️⃣ VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
echo "==================================\n";

// Verificar se o webhook está configurado corretamente
$webhook_url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php";
echo "✅ Webhook URL: $webhook_url\n";

// Verificar se a Ana está acessível
$ana_url = "https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php";
echo "✅ Ana URL: $ana_url\n";

// ===== 2. TESTAR CONECTIVIDADE COM ANA =====
echo "\n2️⃣ TESTANDO CONECTIVIDADE COM ANA:\n";
echo "===================================\n";

$test_message = "Olá, teste de conectividade";
$ana_payload = [
    'question' => $test_message,
    'agent_id' => '3'
];

$ch = curl_init($ana_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ana_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$ana_response = curl_exec($ch);
$ana_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ana_error = curl_error($ch);
curl_close($ch);

if ($ana_error) {
    echo "❌ Erro cURL Ana: $ana_error\n";
} elseif ($ana_http_code === 200) {
    $ana_data = json_decode($ana_response, true);
    if ($ana_data && isset($ana_data['response'])) {
        echo "✅ Ana respondendo corretamente\n";
        echo "📄 Resposta: " . substr($ana_data['response'], 0, 100) . "...\n";
    } else {
        echo "❌ Ana não retornou resposta válida\n";
        echo "📄 Resposta bruta: " . substr($ana_response, 0, 200) . "...\n";
    }
} else {
    echo "❌ Ana não acessível - HTTP: $ana_http_code\n";
    echo "📄 Resposta: " . substr($ana_response, 0, 200) . "...\n";
}

// ===== 3. VERIFICAR WEBHOOK =====
echo "\n3️⃣ VERIFICANDO WEBHOOK:\n";
echo "========================\n";

$webhook_test = [
    'from' => '554796164699@c.us',
    'body' => 'Teste de conectividade Ana',
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699@c.us',
        'text' => 'Teste de conectividade Ana',
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_test));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$webhook_response = curl_exec($ch);
$webhook_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$webhook_error = curl_error($ch);
curl_close($ch);

if ($webhook_error) {
    echo "❌ Erro cURL Webhook: $webhook_error\n";
} elseif ($webhook_http_code === 200) {
    $webhook_data = json_decode($webhook_response, true);
    if ($webhook_data) {
        echo "✅ Webhook funcionando\n";
        echo "📄 Resposta: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Webhook não retornou JSON válido\n";
        echo "📄 Resposta bruta: " . substr($webhook_response, 0, 200) . "...\n";
    }
} else {
    echo "❌ Webhook não acessível - HTTP: $webhook_http_code\n";
    echo "📄 Resposta: " . substr($webhook_response, 0, 200) . "...\n";
}

// ===== 4. VERIFICAR ARQUIVOS DE PROCESSAMENTO =====
echo "\n4️⃣ VERIFICANDO ARQUIVOS DE PROCESSAMENTO:\n";
echo "==========================================\n";

$arquivos_ana = [
    'painel/api/integrador_ana.php',
    'painel/receber_mensagem_ana.php',
    'painel/receber_mensagem_ana_simples.php',
    'webhook_sem_redirect/webhook.php'
];

foreach ($arquivos_ana as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo - EXISTE\n";
    } else {
        echo "❌ $arquivo - NÃO EXISTE\n";
    }
}

// ===== 5. VERIFICAR LOGS =====
echo "\n5️⃣ VERIFICANDO LOGS RECENTES:\n";
echo "=============================\n";

$log_files = [
    'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log',
    'painel/debug_ajax_whatsapp.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        $lines = explode("\n", $log_content);
        $recent_lines = array_slice($lines, -5);
        
        echo "📄 $log_file (últimas 5 linhas):\n";
        foreach ($recent_lines as $line) {
            if (!empty(trim($line))) {
                echo "   " . trim($line) . "\n";
            }
        }
        echo "\n";
    } else {
        echo "⚠️ $log_file - NÃO EXISTE\n";
    }
}

// ===== 6. DIAGNÓSTICO DO PROBLEMA =====
echo "\n6️⃣ DIAGNÓSTICO DO PROBLEMA:\n";
echo "============================\n";

$problemas = [];

// Verificar se Ana está respondendo
if ($ana_http_code !== 200 || $ana_error) {
    $problemas[] = "Ana não está acessível (HTTP: $ana_http_code, Erro: $ana_error)";
}

// Verificar se webhook está funcionando
if ($webhook_http_code !== 200 || $webhook_error) {
    $problemas[] = "Webhook não está funcionando (HTTP: $webhook_http_code, Erro: $webhook_error)";
}

// Verificar se arquivos existem
foreach ($arquivos_ana as $arquivo) {
    if (!file_exists($arquivo)) {
        $problemas[] = "Arquivo $arquivo não existe";
    }
}

if (empty($problemas)) {
    echo "✅ Nenhum problema detectado na configuração\n";
    echo "🔍 O problema pode estar no processamento das mensagens\n";
} else {
    echo "❌ Problemas detectados:\n";
    foreach ($problemas as $problema) {
        echo "   - $problema\n";
    }
}

// ===== 7. SOLUÇÕES =====
echo "\n7️⃣ SOLUÇÕES RECOMENDADAS:\n";
echo "=========================\n";

if (!empty($problemas)) {
    echo "🔧 Aplicando correções automáticas...\n\n";
    
    // 1. Corrigir webhook se necessário
    if ($webhook_http_code !== 200) {
        echo "1️⃣ Corrigindo webhook...\n";
        
        // Verificar se o webhook está processando Ana corretamente
        $webhook_content = file_get_contents('webhook_sem_redirect/webhook.php');
        
        if (strpos($webhook_content, 'Ana') === false) {
            echo "   ⚠️ Webhook não tem processamento da Ana\n";
            echo "   🔧 Adicionando processamento da Ana...\n";
            
            // Adicionar processamento da Ana no webhook
            $ana_processing = '
        // ===== PROCESSAMENTO DA ANA =====
        if ($canal_id == 36 || strpos($canal_nome, "Pixel12") !== false) {
            try {
                // Chamar Ana
                $ana_payload = [
                    "question" => $texto,
                    "agent_id" => "3"
                ];
                
                $ch = curl_init("https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ana_payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $ana_response = curl_exec($ch);
                $ana_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($ana_http_code === 200) {
                    $ana_data = json_decode($ana_response, true);
                    if ($ana_data && isset($ana_data["response"])) {
                        $resposta_automatica = $ana_data["response"];
                        error_log("[WEBHOOK ANA] ✅ Ana respondeu: " . substr($resposta_automatica, 0, 50) . "...");
                    } else {
                        error_log("[WEBHOOK ANA] ❌ Resposta inválida da Ana");
                        $resposta_automatica = "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo? 😊";
                    }
                } else {
                    error_log("[WEBHOOK ANA] ❌ Ana não acessível - HTTP: $ana_http_code");
                    $resposta_automatica = "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo? 😊";
                }
            } catch (Exception $e) {
                error_log("[WEBHOOK ANA] ❌ Erro ao chamar Ana: " . $e->getMessage());
                $resposta_automatica = "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo? 😊";
            }
        } else {
            // Resposta padrão para outros canais
            $resposta_automatica = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato.";
        }';
            
            // Inserir o código no webhook
            $webhook_content = str_replace(
                '// Preparar resposta automática baseada na situação',
                $ana_processing,
                $webhook_content
            );
            
            file_put_contents('webhook_sem_redirect/webhook.php', $webhook_content);
            echo "   ✅ Processamento da Ana adicionado ao webhook\n";
        }
    }
    
    // 2. Verificar se Ana está configurada corretamente
    if ($ana_http_code !== 200) {
        echo "\n2️⃣ Verificando configuração da Ana...\n";
        echo "   ⚠️ Ana não está respondendo (HTTP: $ana_http_code)\n";
        echo "   🔧 Verificar se a URL da Ana está correta\n";
        echo "   🔧 Verificar se o agent_id está correto\n";
    }
    
    // 3. Criar arquivo de fallback se necessário
    if (!file_exists('painel/receber_mensagem_ana_simples.php')) {
        echo "\n3️⃣ Criando arquivo de fallback da Ana...\n";
        
        $fallback_content = '<?php
/**
 * 🔗 RECEPTOR DE MENSAGENS ANA - VERSÃO SIMPLIFICADA
 * 
 * Recebe mensagens do WhatsApp Canal 3000 e processa via Ana
 * Versão simplificada sem conflitos
 */

header("Content-Type: application/json");

// LOG: Capturar dados recebidos
$input = file_get_contents("php://input");
error_log("[RECEBIMENTO_ANA] Dados recebidos: " . $input);

$data = json_decode($input, true);

if (!isset($data["from"]) || !isset($data["body"])) {
    error_log("[RECEBIMENTO_ANA] ERRO: Dados incompletos");
    echo json_encode(["success" => false, "error" => "Dados incompletos"]);
    exit;
}

$from = $data["from"];
$body = $data["body"];
$timestamp = $data["timestamp"] ?? time();

try {
    // Conectar com banco
    require_once __DIR__ . "/../config.php";
    require_once "db.php";
    
    // Canal Ana (3000)
    $canal_id = 36;
    $canal_nome = "Pixel12Digital";
    
    error_log("[RECEBIMENTO_ANA] Processando via Ana - Canal: $canal_nome (ID: $canal_id)");
    
    // 1. SALVAR MENSAGEM RECEBIDA
    $data_hora = date("Y-m-d H:i:s", $timestamp);
    $sql_mensagem = "INSERT INTO mensagens_comunicacao 
                     (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                     VALUES (?, ?, ?, \"texto\", ?, \"recebido\", \"nao_lido\")";
    
    $stmt = $mysqli->prepare($sql_mensagem);
    $stmt->bind_param("isss", $canal_id, $from, $body, $data_hora);
    $stmt->execute();
    $mensagem_id = $mysqli->insert_id;
    $stmt->close();
    
    error_log("[RECEBIMENTO_ANA] Mensagem salva - ID: $mensagem_id");
    
    // 2. CHAMAR ANA VIA API EXTERNA
    $ana_payload = [
        "question" => $body,
        "agent_id" => "3"
    ];
    
    $ch = curl_init("https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ana_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $ana_response = curl_exec($ch);
    $ana_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ana_error = curl_error($ch);
    curl_close($ch);
    
    $resposta_ana = "";
    $sucesso_ana = false;
    
    if ($ana_error) {
        error_log("[RECEBIMENTO_ANA] Erro cURL Ana: $ana_error");
    } elseif ($ana_http_code === 200) {
        $ana_data = json_decode($ana_response, true);
        if ($ana_data && isset($ana_data["response"])) {
            $resposta_ana = $ana_data["response"];
            $sucesso_ana = true;
            error_log("[RECEBIMENTO_ANA] Ana respondeu com sucesso");
        }
    }
    
    // 3. FALLBACK SE ANA FALHAR
    if (!$sucesso_ana || empty($resposta_ana)) {
        $resposta_ana = "Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊";
        error_log("[RECEBIMENTO_ANA] Usando resposta de fallback");
    }
    
    // 4. SALVAR RESPOSTA DA ANA
    $sql_resposta = "INSERT INTO mensagens_comunicacao 
                     (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                     VALUES (?, ?, ?, \"texto\", NOW(), \"enviado\", \"entregue\")";
    
    $stmt = $mysqli->prepare($sql_resposta);
    $stmt->bind_param("iss", $canal_id, $from, $resposta_ana);
    $stmt->execute();
    $resposta_id = $mysqli->insert_id;
    $stmt->close();
    
    // 5. ENVIAR RESPOSTA PARA WHATSAPP
    $whatsapp_payload = [
        "sessionName" => "default",
        "number" => $from,
        "message" => $resposta_ana
    ];
    
    $ch = curl_init("http://212.85.11.238:3000/send/text");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($whatsapp_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $whatsapp_response = curl_exec($ch);
    $whatsapp_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($whatsapp_http_code === 200) {
        error_log("[RECEBIMENTO_ANA] Resposta enviada para WhatsApp com sucesso");
    } else {
        error_log("[RECEBIMENTO_ANA] Erro ao enviar para WhatsApp: HTTP $whatsapp_http_code");
    }
    
    // 6. RESPOSTA PARA O WEBHOOK
    echo json_encode([
        "success" => true,
        "message_id" => $mensagem_id,
        "response_id" => $resposta_id,
        "ana_response" => $resposta_ana,
        "ana_success" => $sucesso_ana,
        "whatsapp_sent" => ($whatsapp_http_code === 200)
    ]);
    
} catch (Exception $e) {
    error_log("[RECEBIMENTO_ANA] ERRO CRÍTICO: " . $e->getMessage());
    
    echo json_encode([
        "success" => false,
        "error" => "Erro interno do servidor",
        "message" => "Mensagem recebida mas não processada",
        "debug" => $e->getMessage()
    ]);
}
?>';
        
        file_put_contents('painel/receber_mensagem_ana_simples.php', $fallback_content);
        echo "   ✅ Arquivo de fallback da Ana criado\n";
    }
    
} else {
    echo "✅ Sistema funcionando corretamente\n";
    echo "🔍 Verificar logs para problemas específicos\n";
}

// ===== 8. TESTE FINAL =====
echo "\n8️⃣ TESTE FINAL:\n";
echo "================\n";

echo "🧪 Testando envio de mensagem para Ana...\n";

$test_final = [
    'from' => '554796164699@c.us',
    'body' => 'Teste final - Ana está funcionando?'
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_final));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($ch);
$test_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($test_http_code === 200) {
    $test_data = json_decode($test_response, true);
    if ($test_data && isset($test_data['ana_response'])) {
        echo "✅ TESTE FINAL: Ana está respondendo!\n";
        echo "📄 Resposta: " . substr($test_data['ana_response'], 0, 100) . "...\n";
    } else {
        echo "⚠️ TESTE FINAL: Ana não retornou resposta esperada\n";
        echo "📄 Resposta: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "❌ TESTE FINAL: Webhook não respondeu (HTTP: $test_http_code)\n";
}

echo "\n🎯 DIAGNÓSTICO CONCLUÍDO!\n";
echo "========================\n";
echo "Se a Ana ainda não estiver respondendo, verifique:\n";
echo "1. Logs do sistema para erros específicos\n";
echo "2. Configuração da URL da Ana\n";
echo "3. Configuração do agent_id\n";
echo "4. Conectividade com a API da Ana\n";
?> 