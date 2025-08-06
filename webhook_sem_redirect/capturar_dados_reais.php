<?php
/**
 * Capturar Dados Reais
 * Captura exatamente os dados que estão sendo enviados pelo WhatsApp real
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

// Detectar ambiente
$ambiente = $is_local ? 'LOCAL' : 'PRODUÇÃO';

// Log da requisição completa
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Salvar log detalhado
$log_file = '../logs/captura_dados_reais_' . date('Y-m-d') . '.log';
$log_data = "=== CAPTURA DADOS REAIS ===\n";
$log_data .= "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
$log_data .= "Ambiente: $ambiente\n";
$log_data .= "Método: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log_data .= "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A') . "\n";
$log_data .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "\n";
$log_data .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "\n";
$log_data .= "Raw Input: $input\n";
$log_data .= "JSON Decoded: " . print_r($data, true) . "\n";
$log_data .= "POST Data: " . print_r($_POST, true) . "\n";
$log_data .= "GET Data: " . print_r($_GET, true) . "\n";
$log_data .= "Headers: " . print_r(getallheaders(), true) . "\n";
$log_data .= "=== FIM CAPTURA ===\n\n";

file_put_contents($log_file, $log_data, FILE_APPEND);

// Verificar se é uma mensagem recebida
$is_message = false;
$message_info = [];

if (isset($data['event']) && $data['event'] === 'onmessage') {
    $is_message = true;
    $message_info['event'] = 'onmessage';
    $message_info['data'] = $data['data'] ?? [];
} elseif (isset($data['type']) && $data['type'] === 'message') {
    $is_message = true;
    $message_info['event'] = 'message';
    $message_info['data'] = $data;
} elseif (isset($data['message'])) {
    $is_message = true;
    $message_info['event'] = 'direct_message';
    $message_info['data'] = $data;
} elseif (!empty($input)) {
    // Tentar detectar outros formatos
    $message_info['event'] = 'unknown';
    $message_info['data'] = $data;
    $message_info['raw'] = $input;
}

// Log da detecção de mensagem
$log_detection = "=== DETECÇÃO DE MENSAGEM ===\n";
$log_detection .= "Is Message: " . ($is_message ? 'SIM' : 'NÃO') . "\n";
$log_detection .= "Message Info: " . print_r($message_info, true) . "\n";
$log_detection .= "=== FIM DETECÇÃO ===\n\n";
file_put_contents($log_file, $log_detection, FILE_APPEND);

// Processar mensagem se for válida
if ($is_message && isset($message_info['data'])) {
    $message = $message_info['data'];
    
    // Extrair informações
    $numero = $message['from'] ?? '';
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    // Log das informações extraídas
    $log_extracted = "=== INFORMAÇÕES EXTRAÍDAS ===\n";
    $log_extracted .= "Numero: $numero\n";
    $log_extracted .= "Texto: $texto\n";
    $log_extracted .= "Tipo: $tipo\n";
    $log_extracted .= "Data/Hora: $data_hora\n";
    $log_extracted .= "=== FIM INFORMAÇÕES ===\n\n";
    file_put_contents($log_file, $log_extracted, FILE_APPEND);
    
    // Conectar ao banco
    require_once '../painel/db.php';
    
    if (isset($mysqli)) {
        // Buscar cliente pelo número
        $numero_limpo = preg_replace('/\D/', '', $numero);
        $sql = "SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
        $result = $mysqli->query($sql);
        
        $cliente_id = null;
        if ($result && $result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
            $cliente_id = $cliente['id'];
        }
        
        // Cadastro automático de clientes não cadastrados
        if (!$cliente_id) {
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
            }
        }
        
        // Salvar mensagem recebida
        $texto_escaped = $mysqli->real_escape_string($texto);
        $tipo_escaped = $mysqli->real_escape_string($tipo);
        $canal_id = 36; // Padrão: Financeiro (3000)
        
        $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '" . $mysqli->real_escape_string($numero) . "', '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
        
        if ($mysqli->query($sql)) {
            $mensagem_id = $mysqli->insert_id;
            
            // Log do sucesso
            $log_success = "=== SUCESSO ===\n";
            $log_success .= "Mensagem salva com sucesso!\n";
            $log_success .= "ID da mensagem: $mensagem_id\n";
            $log_success .= "ID do cliente: $cliente_id\n";
            $log_success .= "=== FIM SUCESSO ===\n\n";
            file_put_contents($log_file, $log_success, FILE_APPEND);
            
            // Resposta automática
            $resposta = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato.";
            
            // Salvar resposta
            $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                             VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '" . $mysqli->real_escape_string($resposta) . "', 'texto', '$data_hora', 'enviado', 'entregue')";
            $mysqli->query($sql_resposta);
            
            // Enviar resposta via WhatsApp
            $api_url = WHATSAPP_ROBOT_URL . "/send/text";
            $data_envio = [
                "number" => $numero,
                "message" => $resposta
            ];
            
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response_whats = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Log da resposta
            $log_response = "=== RESPOSTA ===\n";
            $log_response .= "Resposta enviada: $resposta\n";
            $log_response .= "HTTP Code: $http_code\n";
            $log_response .= "cURL Error: " . ($curl_error ?: 'Nenhum') . "\n";
            $log_response .= "=== FIM RESPOSTA ===\n\n";
            file_put_contents($log_file, $log_response, FILE_APPEND);
        } else {
            // Log do erro
            $log_error = "=== ERRO ===\n";
            $log_error .= "Erro ao salvar mensagem: " . $mysqli->error . "\n";
            $log_error .= "SQL: $sql\n";
            $log_error .= "=== FIM ERRO ===\n\n";
            file_put_contents($log_file, $log_error, FILE_APPEND);
        }
    }
}

// Resposta de sucesso
$response = [
    'status' => 'success',
    'ambiente' => $ambiente,
    'timestamp' => date('Y-m-d H:i:s'),
    'webhook_type' => 'captura_dados_reais',
    'is_message' => $is_message,
    'message_info' => $message_info,
    'raw_input' => $input,
    'json_data' => $data
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT);
?> 