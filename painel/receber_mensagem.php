<?php
/**
 * 🔗 RECEPTOR DE MENSAGENS COM INTEGRAÇÃO ANA LOCAL
 * 
 * Recebe mensagens do WhatsApp Canal 3000 e processa via Ana LOCAL
 * Muito mais eficiente - sem chamadas HTTP externas
 * COMPATÍVEL COM SISTEMA EXISTENTE
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

// COMPATIBILIDADE: Determinar canal igual ao sistema anterior
$canal_id = 36; // Canal Pixel12Digital (Ana)
$canal_nome = "Pixel12Digital";
$canal_porta = 3000;

// Buscar canal pelo destino se disponível (compatibilidade)
if (isset($data['to'])) {
    $to = $mysqli->real_escape_string($data['to']);
    error_log("[RECEBIMENTO_ANA_LOCAL] Número de destino (to): " . $to);
    
    $canal = $mysqli->query("SELECT id, nome_exibicao, porta FROM canais_comunicacao WHERE identificador = '$to' LIMIT 1")->fetch_assoc();
    
    if ($canal) {
        $canal_id = intval($canal['id']);
        $canal_nome = $canal['nome_exibicao'];
        $canal_porta = intval($canal['porta']);
    }
}

error_log("[RECEBIMENTO_ANA_LOCAL] Processando via Ana LOCAL - Canal: $canal_nome (ID: $canal_id)");

try {
    // 1. VERIFICAR SE É CANAL 3000 (ANA) OU OUTRO
    if ($canal_porta === 3000) {
        // CANAL 3000 - PROCESSAR COM ANA
        error_log("[RECEBIMENTO_ANA_LOCAL] Canal 3000 detectado - Processando com Ana");
        
        // 1.1. SALVAR MENSAGEM RECEBIDA (compatível com sistema anterior)
        $data_hora = date('Y-m-d H:i:s', $timestamp);
        $sql_mensagem = "INSERT INTO mensagens_comunicacao 
                         (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                         VALUES (?, ?, ?, 'texto', ?, 'recebido', 'nao_lido')";
        
        $stmt = $mysqli->prepare($sql_mensagem);
        $stmt->bind_param('isss', $canal_id, $from, $body, $data_hora);
        $stmt->execute();
        $mensagem_id = $mysqli->insert_id;
        $stmt->close();
        
        error_log("[RECEBIMENTO_ANA_LOCAL] Mensagem salva - ID: $mensagem_id");
        
        // 1.2. PROCESSAR VIA INTEGRADOR ANA (HTTP)
        require_once __DIR__ . '/api/integrador_ana.php';
        
        $integrador = new IntegradorAna($mysqli);
        $resultado_ana = $integrador->processarMensagem($data);
        
        if ($resultado_ana['success']) {
            error_log("[RECEBIMENTO_ANA_LOCAL] Ana local processou com sucesso - Ação: " . $resultado_ana['acao_sistema']);
            
            // 1.3. SALVAR RESPOSTA DA ANA
            $sql_resposta = "INSERT INTO mensagens_comunicacao 
                             (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                             VALUES (?, ?, ?, 'texto', NOW(), 'enviado', 'entregue')";
            
            $stmt = $mysqli->prepare($sql_resposta);
            $resposta_ana = $resultado_ana['resposta_ana'];
            $stmt->bind_param('iss', $canal_id, $from, $resposta_ana);
            $stmt->execute();
            $resposta_id = $mysqli->insert_id;
            $stmt->close();
            
            // 1.4. LOG DETALHADO PARA MONITORAMENTO
            $sql_log = "INSERT INTO logs_integracao_ana 
                        (numero_cliente, mensagem_enviada, resposta_ana, acao_sistema, departamento_detectado, status_api) 
                        VALUES (?, ?, ?, ?, ?, 'success_local')";
            
            $stmt = $mysqli->prepare($sql_log);
            $stmt->bind_param('sssss', 
                $from, 
                $body, 
                $resposta_ana, 
                $resultado_ana['acao_sistema'], 
                $resultado_ana['departamento_detectado']
            );
            $stmt->execute();
            $stmt->close();
            
            // 1.5. INVALIDAR CACHE DE MENSAGENS (compatibilidade)
            $invalidator = new CacheInvalidator();
            $invalidator->onNewMessage($canal_id);
            
            // 1.6. RESPOSTA PARA WHATSAPP (compatível com formato anterior)
            $resposta_sistema = [
                'success' => true,
                'mensagem_id' => $mensagem_id,
                'response_id' => $resposta_id,
                'canal' => $canal_nome,
                'ana_response' => $resposta_ana,
                'action_taken' => $resultado_ana['acao_sistema'],
                'department' => $resultado_ana['departamento_detectado'],
                'transfer_rafael' => $resultado_ana['transfer_para_rafael'],
                'transfer_humano' => $resultado_ana['transfer_para_humano'],
                'integration_type' => 'ana_local',
                'performance' => 'optimized'
            ];
            
            // 1.7. AÇÕES ESPECIAIS BASEADAS NA RESPOSTA DA ANA
            if ($resultado_ana['transfer_para_rafael']) {
                error_log("[RECEBIMENTO_ANA_LOCAL] Transferência para Rafael detectada");
                $resposta_sistema['next_action'] = 'transfer_to_rafael';
                $resposta_sistema['rafael_info'] = 'Cliente será atendido por Rafael - Especialista em Sites/Ecommerce';
            }
            
            if ($resultado_ana['transfer_para_humano']) {
                error_log("[RECEBIMENTO_ANA_LOCAL] Transferência para humano detectada - Depto: " . $resultado_ana['departamento_detectado']);
                $resposta_sistema['next_action'] = 'transfer_to_human';
                $resposta_sistema['human_channel'] = '47 97309525';
                $resposta_sistema['department'] = $resultado_ana['departamento_detectado'];
            }
            
            echo json_encode($resposta_sistema);
            
        } else {
            error_log("[RECEBIMENTO_ANA_LOCAL] ERRO no processamento da Ana local");
            
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
                'mensagem_id' => $mensagem_id,
                'response_id' => $fallback_id,
                'canal' => $canal_nome,
                'ana_response' => $resposta_fallback,
                'action_taken' => 'fallback_emergency',
                'integration_type' => 'ana_local_fallback',
                'note' => 'Ana local indisponível - Resposta de emergência enviada'
            ]);
        }
        
    } else {
        // OUTROS CANAIS - PROCESSAR COM LÓGICA ANTERIOR (COMPATIBILIDADE TOTAL)
        error_log("[RECEBIMENTO_ANA_LOCAL] Canal $canal_porta detectado - Processando com lógica anterior");
        
        // Incluir e executar lógica do sistema anterior para outros canais
        $backup_file = __DIR__ . '/receber_mensagem_backup.php';
        if (file_exists($backup_file)) {
            // Redirecionar para lógica anterior
            include $backup_file;
            return;
        } else {
            // Lógica básica de fallback
            $data_hora = date('Y-m-d H:i:s', $timestamp);
            $sql_mensagem = "INSERT INTO mensagens_comunicacao 
                             (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                             VALUES (?, ?, ?, 'texto', ?, 'recebido', 'recebido')";
            
            $stmt = $mysqli->prepare($sql_mensagem);
            $stmt->bind_param('isss', $canal_id, $from, $body, $data_hora);
            $stmt->execute();
            $mensagem_id = $mysqli->insert_id;
            $stmt->close();
            
            echo json_encode([
                'success' => true, 
                'mensagem_id' => $mensagem_id, 
                'canal' => $canal_nome,
                'integration_type' => 'fallback_basico'
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log("[RECEBIMENTO_ANA_LOCAL] ERRO CRÍTICO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'message' => 'Mensagem recebida mas não processada',
        'integration_type' => 'ana_local_error',
        'debug' => $e->getMessage()
    ]);
}

$mysqli->close();
?> 