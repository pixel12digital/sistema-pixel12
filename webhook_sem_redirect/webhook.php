<?php
/**
 * Webhook Sem Redirecionamento
 * Endpoint webhook em diretório separado para evitar redirecionamentos
 */

// Desabilitar redirecionamentos e cache
ini_set('max_execution_time', 30);
ini_set('memory_limit', '256M');

// Cabeçalhos específicos para evitar redirecionamentos
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Responder imediatamente para requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'method' => 'options']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once '../painel/db.php';

// Detectar ambiente
$ambiente = $is_local ? 'LOCAL' : 'PRODUÇÃO';

// Log da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Salvar log
$log_file = '../logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
$log_data = date('Y-m-d H:i:s') . " [{$ambiente}] - " . $input . "\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Log de debug
if (DEBUG_MODE) {
    error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Requisição recebida");
    error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Dados: " . $input);
}

// Verificar se é uma mensagem recebida
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'];
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    if (DEBUG_MODE) {
        error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Processando mensagem de: $numero");
    }
    
    // Buscar cliente pelo número
    $numero_limpo = preg_replace('/\D/', '', $numero);
    $sql = "SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
    $result = $mysqli->query($sql);
    
    $cliente_id = null;
    if ($result && $result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        $cliente_id = $cliente['id'];
        if (DEBUG_MODE) {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Cliente encontrado: {$cliente['nome']} (ID: $cliente_id)");
        }
    }
    
    // Cadastro automático de clientes não cadastrados
    if (!$cliente_id) {
        if (DEBUG_MODE) {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Cliente não encontrado, criando cadastro automático...");
        }
        
        // Formatar número para salvar
        $numero_para_salvar = $numero;
        if (strpos($numero, "55") === 0) {
            $numero_para_salvar = substr($numero, 2);
        }
        
        // Criar cliente automaticamente
        $nome_cliente = "Cliente WhatsApp (" . $numero_para_salvar . ")";
        $data_criacao = date("Y-m-d H:i:s");
        
        $sql_criar = "INSERT INTO clientes (nome, celular, data_criacao, data_atualizacao) 
                      VALUES (\"" . $mysqli->real_escape_string($nome_cliente) . "\", 
                              \"" . $mysqli->real_escape_string($numero_para_salvar) . "\", 
                              \"$data_criacao\", \"$data_criacao\")";
        
        if ($mysqli->query($sql_criar)) {
            $cliente_id = $mysqli->insert_id;
            if (DEBUG_MODE) {
                error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ✅ Cliente criado automaticamente - ID: $cliente_id");
            }
        } else {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ❌ Erro ao criar cliente: " . $mysqli->error);
        }
    }

    // Buscar canal WhatsApp padrão
    $canal_id = 1; // Canal padrão
    $canal_result = $mysqli->query("SELECT id FROM canais_comunicacao WHERE tipo = 'whatsapp' LIMIT 1");
    if ($canal_result && $canal_result->num_rows > 0) {
        $canal = $canal_result->fetch_assoc();
        $canal_id = $canal['id'];
    }
    
    // Salvar mensagem recebida
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
    
    if ($mysqli->query($sql)) {
        $mensagem_id = $mysqli->insert_id;
        if (DEBUG_MODE) {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ✅ Mensagem salva - ID: $mensagem_id");
        }
        
        // Invalidar cache se disponível
        if (file_exists('../painel/cache_invalidator.php')) {
            require_once '../painel/cache_invalidator.php';
            if ($cliente_id && function_exists('invalidate_message_cache')) {
                invalidate_message_cache($cliente_id);
            }
        }
    } else {
        error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ❌ Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Resposta automática simples
    if ($texto) {
        $resposta = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato.";
        
        // Usar URL do WhatsApp configurada
        $api_url = WHATSAPP_ROBOT_URL . "/send/text";
        $data_envio = [
            "number" => $numero,
            "message" => $resposta
        ];
        
        if (DEBUG_MODE) {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Enviando resposta via: $api_url");
        }
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, WHATSAPP_TIMEOUT);
        
        $api_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $api_result = json_decode($api_response, true);
            if ($api_result && isset($api_result["success"]) && $api_result["success"]) {
                if (DEBUG_MODE) {
                    error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ✅ Resposta automática enviada");
                }
                
                // Salvar resposta enviada
                $resposta_escaped = $mysqli->real_escape_string($resposta);
                $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                                VALUES ($canal_id, " . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\")";
                $mysqli->query($sql_resposta);
            } else {
                error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ❌ Erro ao enviar resposta: " . $api_response);
            }
        } else {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ❌ Erro HTTP ao enviar resposta: $http_code");
        }
    }
}

// Responder OK
$response = [
    'status' => 'ok',
    'ambiente' => $ambiente,
    'timestamp' => date('Y-m-d H:i:s'),
    'webhook_type' => 'sem_redirect',
    'message' => 'Webhook sem redirecionamento processado com sucesso'
];

http_response_code(200);
echo json_encode($response);
?> 