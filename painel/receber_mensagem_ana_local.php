<?php
/**
 * 🔗 RECEPTOR DE MENSAGENS COM INTEGRAÇÃO ANA LOCAL
 * 
 * Recebe mensagens do WhatsApp Canal 3000 e processa via Ana localmente
 * NOVO: Verifica bloqueios antes de processar
 */

require_once __DIR__ . '/../config.php';
require_once 'db.php';
require_once 'cache_invalidator.php';

header('Content-Type: application/json');

// LOG: Capturar dados recebidos
$input = file_get_contents('php://input');
error_log("[RECEBIMENTO_ANA_LOCAL] Dados recebidos: " . $input);

// Tentar diferentes métodos de obter os dados
$data = null;

// 1. Tentar JSON do corpo da requisição
if (!empty($input)) {
    $data = json_decode($input, true);
    error_log("[RECEBIMENTO_ANA_LOCAL] JSON decodificado: " . print_r($data, true));
}

// 2. Se JSON falhou, tentar $_POST
if (!$data && !empty($_POST)) {
    $data = $_POST;
    error_log("[RECEBIMENTO_ANA_LOCAL] Usando dados POST: " . print_r($data, true));
}

// 3. Se ainda não tem dados, tentar $_GET  
if (!$data && !empty($_GET)) {
    $data = $_GET;
    error_log("[RECEBIMENTO_ANA_LOCAL] Usando dados GET: " . print_r($data, true));
}

// Normalizar campos - diferentes APIs usam nomes diferentes
$from = null;
$body = null;

if ($data) {
    // Tentar diferentes campos para 'from'
    if (isset($data['from'])) $from = $data['from'];
    elseif (isset($data['number'])) $from = $data['number'];
    elseif (isset($data['phone'])) $from = $data['phone'];
    elseif (isset($data['sender'])) $from = $data['sender'];
    elseif (isset($data['chatId'])) $from = $data['chatId'];
    
    // Tentar diferentes campos para 'body'
    if (isset($data['body'])) $body = $data['body'];
    elseif (isset($data['message'])) $body = $data['message'];
    elseif (isset($data['text'])) $body = $data['text'];
    elseif (isset($data['content'])) $body = $data['content'];
    elseif (isset($data['msg'])) $body = $data['msg'];
}

error_log("[RECEBIMENTO_ANA_LOCAL] From extraído: " . ($from ?? 'NULL'));
error_log("[RECEBIMENTO_ANA_LOCAL] Body extraído: " . ($body ?? 'NULL'));

// Validar se temos os dados mínimos necessários
if (empty($from) || empty($body)) {
    $error_debug = [
        'error' => 'Dados incompletos',
        'input_raw' => $input,
        'data_parsed' => $data,
        'from_found' => $from,
        'body_found' => $body,
        'post_data' => $_POST,
        'get_data' => $_GET,
        'headers' => getallheaders(),
        'possible_fields' => array_keys($data ?? [])
    ];
    
    error_log("[RECEBIMENTO_ANA_LOCAL] ERRO DETALHADO: " . json_encode($error_debug));
    echo json_encode($error_debug);
    exit;
}

$from = $mysqli->real_escape_string($from);
$body = $mysqli->real_escape_string($body);
$timestamp = isset($data['timestamp']) ? intval($data['timestamp']) : time();

// NOVO: Verificar se Ana está bloqueada para este cliente
$bloqueio = $mysqli->query("
    SELECT * FROM bloqueios_ana 
    WHERE numero_cliente = '$from' 
    AND ativo = 1 
    AND (data_desbloqueio IS NULL OR data_desbloqueio > NOW())
    LIMIT 1
")->fetch_assoc();

if ($bloqueio) {
    error_log("[RECEBIMENTO_ANA_LOCAL] ⛔ Ana bloqueada para cliente: $from (Motivo: {$bloqueio['motivo']})");
    
    // Cliente está bloqueado - enviar mensagem de redirecionamento
    $mensagem_bloqueio = "🤝 Olá! Você está sendo atendido por nossa equipe humana.\n\n";
    $mensagem_bloqueio .= "📞 Para urgências: 47 97309525\n";
    $mensagem_bloqueio .= "⏰ Horário: Segunda a Sexta, 8h às 18h\n\n";
    $mensagem_bloqueio .= "Obrigado pela preferência! 🚀";
    
    // Salvar mensagem de redirecionamento
    $canal_id = 36; // Canal Ana
    $sql_mensagem = "INSERT INTO mensagens_comunicacao 
                     (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status, observacoes) 
                     VALUES (?, ?, ?, 'texto', NOW(), 'enviado', 'entregue', 'Redirecionamento - Ana bloqueada')";
    
    $stmt = $mysqli->prepare($sql_mensagem);
    $stmt->bind_param('iss', $canal_id, $from, $mensagem_bloqueio);
    $stmt->execute();
    $resposta_id = $mysqli->insert_id;
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message_id' => 0,
        'response_id' => $resposta_id,
        'ana_response' => $mensagem_bloqueio,
        'action_taken' => 'ana_bloqueada',
        'blocked_reason' => $bloqueio['motivo'],
        'redirect_message' => true
    ]);
    
    $mysqli->close();
    exit;
}

// Determinar canal (3000 = Ana, 3001 = Humanos)
$canal_id = 36; // Canal Pixel12Digital (Ana)
$canal_nome = "Pixel12Digital";
$canal_porta = 3000;

error_log("[RECEBIMENTO_ANA_LOCAL] ✅ Ana liberada para cliente: $from - Processando via Ana Local");

try {
    // 1. SALVAR MENSAGEM RECEBIDA
    $sql_mensagem = "INSERT INTO mensagens_comunicacao 
                     (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                     VALUES (?, ?, ?, 'texto', NOW(), 'recebido', 'nao_lido')";
    
    $stmt = $mysqli->prepare($sql_mensagem);
    $stmt->bind_param('iss', $canal_id, $from, $body);
    $stmt->execute();
    $mensagem_id = $mysqli->insert_id;
    $stmt->close();
    
    error_log("[RECEBIMENTO_ANA_LOCAL] Mensagem salva - ID: $mensagem_id");
    
    // 2. PROCESSAR VIA INTEGRADOR ANA LOCAL
    require_once __DIR__ . '/api/integrador_ana_local.php';
    
    $integrador = new IntegradorAnaLocal($mysqli);
    $resultado_ana = $integrador->processarMensagem($data);
    
    if ($resultado_ana['success']) {
        error_log("[RECEBIMENTO_ANA_LOCAL] Ana processou com sucesso - Ação: " . $resultado_ana['acao_sistema']);
        
        // 3. SALVAR RESPOSTA DA ANA
        $sql_resposta = "INSERT INTO mensagens_comunicacao 
                         (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                         VALUES (?, ?, ?, 'texto', NOW(), 'enviado', 'entregue')";
        
        $stmt = $mysqli->prepare($sql_resposta);
        $resposta_ana = $resultado_ana['resposta_ana'];
        $stmt->bind_param('iss', $canal_id, $from, $resposta_ana);
        $stmt->execute();
        $resposta_id = $mysqli->insert_id;
        $stmt->close();
        
        // 4. LOG DETALHADO PARA MONITORAMENTO
        $sql_log = "INSERT INTO logs_integracao_ana 
                    (numero_cliente, mensagem_enviada, resposta_ana, acao_sistema, departamento_detectado, status_api, transferencia_executada) 
                    VALUES (?, ?, ?, ?, ?, 'success', ?)";
        
        $transferencia_executada = ($resultado_ana['transfer_para_rafael'] || $resultado_ana['transfer_para_humano']) ? 1 : 0;
        
        $stmt = $mysqli->prepare($sql_log);
        $stmt->bind_param('sssssi', 
            $from, 
            $body, 
            $resposta_ana, 
            $resultado_ana['acao_sistema'], 
            $resultado_ana['departamento_detectado'],
            $transferencia_executada
        );
        $stmt->execute();
        $stmt->close();
        
        // 5. INVALIDAR CACHE DE MENSAGENS
        $invalidator = new CacheInvalidator();
        $invalidator->onNewMessage($canal_id);
        
        // 6. RESPOSTA PARA WHATSAPP
        $resposta_sistema = [
            'success' => true,
            'message_id' => $mensagem_id,
            'response_id' => $resposta_id,
            'ana_response' => $resposta_ana,
            'action_taken' => $resultado_ana['acao_sistema'],
            'department' => $resultado_ana['departamento_detectado'],
            'transfer_rafael' => $resultado_ana['transfer_para_rafael'],
            'transfer_humano' => $resultado_ana['transfer_para_humano'],
            'integration_type' => 'local',
            'performance' => 'optimized'
        ];
        
        // 7. AÇÕES ESPECIAIS BASEADAS NA RESPOSTA DA ANA
        if ($resultado_ana['transfer_para_rafael']) {
            error_log("[RECEBIMENTO_ANA_LOCAL] Transferência para Rafael detectada");
            $resposta_sistema['next_action'] = 'transfer_to_rafael';
            $resposta_sistema['rafael_info'] = 'Cliente será atendido por Rafael - Especialista em Sites/Ecommerce';
            $resposta_sistema['rafael_context'] = [
                'interesse' => 'sites_ecommerce',
                'mensagem_original' => $body,
                'cliente' => $from,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        if ($resultado_ana['transfer_para_humano']) {
            error_log("[RECEBIMENTO_ANA_LOCAL] Transferência para humano detectada - Depto: " . $resultado_ana['departamento_detectado']);
            $resposta_sistema['next_action'] = 'transfer_to_human';
            $resposta_sistema['human_channel'] = '47 97309525';
            $resposta_sistema['department'] = $resultado_ana['departamento_detectado'];
            $resposta_sistema['human_context'] = [
                'departamento' => $resultado_ana['departamento_detectado'],
                'mensagem_original' => $body,
                'cliente' => $from,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        echo json_encode($resposta_sistema);
        
    } else {
        error_log("[RECEBIMENTO_ANA_LOCAL] ERRO no processamento da Ana");
        
        // Fallback para resposta básica
        $resposta_fallback = "Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊";
        
        $sql_fallback = "INSERT INTO mensagens_comunicacao 
                         (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                         VALUES (?, ?, ?, 'texto', NOW(), 'enviado', 'entregue')";
        
        $stmt = $mysqli->prepare($sql_fallback);
        $stmt->bind_param('iss', $canal_id, $from, $resposta_fallback);
        $stmt->execute();
        $fallback_id = $mysqli->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message_id' => $mensagem_id,
            'response_id' => $fallback_id,
            'ana_response' => $resposta_fallback,
            'action_taken' => 'fallback_emergency',
            'note' => 'Ana indisponível - Resposta de emergência enviada'
        ]);
    }
    
} catch (Exception $e) {
    error_log("[RECEBIMENTO_ANA_LOCAL] ERRO CRÍTICO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'message' => 'Mensagem recebida mas não processada',
        'debug' => $e->getMessage()
    ]);
}

$mysqli->close();
?> 