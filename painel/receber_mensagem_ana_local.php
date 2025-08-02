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

$data = json_decode($input, true);

if (!isset($data['from']) || !isset($data['body'])) {
    error_log("[RECEBIMENTO_ANA_LOCAL] ERRO: Dados incompletos - from ou body não encontrados");
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$from = $mysqli->real_escape_string($data['from']);
$body = $mysqli->real_escape_string($data['body']);
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
                     (canal_id, telefone_origem, mensagem, tipo, data_hora, direcao, status, observacoes) 
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
                     (canal_id, telefone_origem, mensagem, tipo, data_hora, direcao, status) 
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
                         (canal_id, telefone_origem, mensagem, tipo, data_hora, direcao, status) 
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
                         (canal_id, telefone_origem, mensagem, tipo, data_hora, direcao, status) 
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