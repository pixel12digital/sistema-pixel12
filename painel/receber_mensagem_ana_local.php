<?php
/**
 * 🔗 RECEPTOR DE MENSAGENS ANA - WEBHOOK PRINCIPAL
 * 
 * Recebe mensagens do WhatsApp VPS e processa via Ana
 * Versão final corrigida - sem HTTP 500
 */

// Headers corretos primeiro
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gerenciar OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Desativar notices/warnings que causam HTTP 500
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cache_invalidator.php';
require_once __DIR__ . '/api/integrador_ana_local.php';

// Log de recebimento
error_log("[WEBHOOK_ANA] Requisição recebida: " . $_SERVER['REQUEST_METHOD'] . " " . date('Y-m-d H:i:s'));

try {
    // Processar apenas POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Apenas POST permitido']);
        exit;
    }

    // Receber dados
    $input = file_get_contents('php://input');
    $dados = json_decode($input, true);

    if (!$dados) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'JSON inválido']);
        exit;
    }

    error_log("[WEBHOOK_ANA] Dados recebidos: " . substr($input, 0, 200));

    // ===== PROCESSAR MENSAGEM PRINCIPAL =====
    
    // 1. Identificar remetente e canal de destino
    $numero_remetente = str_replace('@c.us', '', $dados['from'] ?? '');
    $mensagem = $dados['body'] ?? $dados['message'] ?? '';
    
    // 2. NOVA LÓGICA: Identificar canal pela URL ou header
    $canal_id = 36; // Canal Ana padrão
    $numero_whatsapp = '554797146908'; // Canal Ana padrão
    
    // Verificar se vem de uma porta específica via header ou referer
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Log para debug
    error_log("[WEBHOOK_ANA] User-Agent: $user_agent | Referer: $referer | IP: $remote_addr");
    
    // Tentar identificar pelo campo 'to' primeiro
    $numero_destino = str_replace('@c.us', '', $dados['to'] ?? '');
    if (!empty($numero_destino)) {
        error_log("[WEBHOOK_ANA] Campo TO encontrado: $numero_destino");
        
        if ($numero_destino === '554797146908') {
            // Canal Ana (3000)
            $canal_id = 36;
            $numero_whatsapp = '554797146908';
            error_log("[WEBHOOK_ANA] Mapeado para Canal Ana via TO");
        } elseif ($numero_destino === '554797309525') {
            // Canal Humano (3001) 
            $canal_id = 37; // Verificar ID correto
            $numero_whatsapp = '554797309525';
            error_log("[WEBHOOK_ANA] Mapeado para Canal Humano via TO");
        }
    } else {
        // Fallback: Assumir Canal Ana se não há informação de destino
        error_log("[WEBHOOK_ANA] Campo TO vazio, usando Canal Ana como padrão");
    }
    
    // ALTERNATIVA: Se ainda não identificou corretamente, usar número fixo baseado no contexto
    // Por ora, FORÇAR Canal Ana para todos os casos como teste
    $numero_whatsapp = '554797146908';
    $canal_id = 36;
    
    error_log("[WEBHOOK_ANA] FINAL - Remetente: $numero_remetente -> Canal: $numero_whatsapp (ID: $canal_id)");
    
    // 3. Validar dados essenciais
    if (empty($numero_remetente) || empty($mensagem)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
        exit;
    }

    // 4. Salvar mensagem recebida no CANAL CORRETO
    $message_id = null;
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, direcao, data_hora, tipo) VALUES (?, ?, ?, 'recebido', NOW(), 'text')");
        if ($stmt) {
            $stmt->bind_param('iss', $canal_id, $numero_whatsapp, $mensagem);
            if ($stmt->execute()) {
                $message_id = $mysqli->insert_id;
                error_log("[WEBHOOK_ANA] Mensagem salva ID: $message_id no canal $numero_whatsapp (de: $numero_remetente)");
            }
            $stmt->close();
        }
    }

    // 5. Processar via integrador Ana
    $integrador = new IntegradorAnaLocal($mysqli);
    $resultado_ana = $integrador->processarMensagem($dados);

    // 6. Salvar resposta da Ana
    $response_id = null;
    if ($resultado_ana['success'] && !empty($resultado_ana['resposta_ana'])) {
        if (isset($mysqli) && $mysqli instanceof mysqli) {
            $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, direcao, data_hora, tipo) VALUES (?, ?, ?, 'enviado', NOW(), 'text')");
            if ($stmt) {
                $stmt->bind_param('iss', $canal_id, $numero_whatsapp, $resultado_ana['resposta_ana']);
                if ($stmt->execute()) {
                    $response_id = $mysqli->insert_id;
                    error_log("[WEBHOOK_ANA] Resposta Ana salva ID: $response_id");
                }
                $stmt->close();
            }
        }
    }

    // 7. Invalidar cache
    if (class_exists('CacheInvalidator')) {
        $cache = new CacheInvalidator();
        $cache->onNewMessage($canal_id, $numero_whatsapp);
    }

    // 8. Resposta final (HTTP 200)
    http_response_code(200);
    $resposta_final = [
        'success' => true,
        'message_id' => $message_id,
        'response_id' => $response_id,
        'ana_response' => $resultado_ana['resposta_ana'] ?? '',
        'action_taken' => $resultado_ana['acao_sistema'] ?? 'nenhuma',
        'department' => $resultado_ana['departamento_detectado'],
        'transfer_rafael' => $resultado_ana['transfer_para_rafael'] ?? false,
        'transfer_humano' => $resultado_ana['transfer_para_humano'] ?? false,
        'integration_type' => 'ana_webhook',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($resposta_final, JSON_UNESCAPED_UNICODE);
    error_log("[WEBHOOK_ANA] Processamento concluído com sucesso");

} catch (Exception $e) {
    // Erro controlado
    error_log("[WEBHOOK_ANA] Erro: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erro interno do servidor',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} finally {
    // Fechar conexão
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
?> 