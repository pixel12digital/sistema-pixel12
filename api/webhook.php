<?php
/**
 * Webhook para receber mensagens do WPPConnect
 * Detecta automaticamente ambiente (local/produção)
 */

require_once __DIR__ . '/../config.php';
require_once '../painel/db.php';

// Detectar ambiente
$ambiente = $is_local ? 'LOCAL' : 'PRODUÇÃO';
$webhook_url = $is_local ? 'http://localhost:8080/loja-virtual-revenda/api/webhook.php' : 'https://seudominio.com/api/webhook.php';

// Log da requisição com informações do ambiente
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Salvar log com identificação do ambiente
$log_file = '../logs/webhook_' . date('Y-m-d') . '.log';
$log_data = date('Y-m-d H:i:s') . " [{$ambiente}] - " . $input . "\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Log de debug se estiver em desenvolvimento
if (DEBUG_MODE) {
    error_log("[WEBHOOK {$ambiente}] Requisição recebida - Host: " . $_SERVER['SERVER_NAME']);
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
        error_log("[WEBHOOK {$ambiente}] Processando mensagem de: $numero");
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
            error_log("[WEBHOOK {$ambiente}] Cliente encontrado: {$cliente['nome']} (ID: $cliente_id)");
        }
    }
    
    // Cadastro automático de clientes não cadastrados
    if (!$cliente_id) {
        if (DEBUG_MODE) {
            error_log("[WEBHOOK {$ambiente}] Cliente não encontrado, criando cadastro automático...");
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
                error_log("[WEBHOOK {$ambiente}] ✅ Cliente criado automaticamente - ID: $cliente_id, Número: $numero_para_salvar");
            }
        } else {
            error_log("[WEBHOOK {$ambiente}] ❌ Erro ao criar cliente: " . $mysqli->error);
        }
    }

    // Buscar canal WhatsApp padrão ou criar um
    $canal_id = 1; // Canal padrão
    $canal_result = $mysqli->query("SELECT id FROM canais_comunicacao WHERE tipo = 'whatsapp' LIMIT 1");
    if ($canal_result && $canal_result->num_rows > 0) {
        $canal = $canal_result->fetch_assoc();
        $canal_id = $canal['id'];
    } else {
        // Criar canal WhatsApp padrão se não existir
        $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
                        VALUES ('whatsapp', 'default', 'WhatsApp Padrão', 'conectado', NOW())");
        $canal_id = $mysqli->insert_id;
    }
    
    // Salvar mensagem recebida
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
    
    if ($mysqli->query($sql)) {
        $mensagem_id = $mysqli->insert_id;
        if (DEBUG_MODE) {
            error_log("[WEBHOOK {$ambiente}] ✅ Mensagem salva - ID: $mensagem_id, Cliente: $cliente_id");
        }
        require_once '../painel/cache_invalidator.php';
        if ($cliente_id) invalidate_message_cache($cliente_id);
    } else {
        error_log("[WEBHOOK {$ambiente}] ❌ Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Resposta automática melhorada
    if ($texto) {
        if ($cliente_id) {
            // Cliente encontrado - usar IA para processar mensagem
            $payload_ia = [
                'from' => $numero,
                'message' => $texto,
                'type' => $tipo
            ];
            
            // Chamar endpoint da IA
            $ch_ia = curl_init(($is_local ? 'http://localhost:8080/loja-virtual-revenda' : '') . '/painel/api/processar_mensagem_ia.php');
            curl_setopt($ch_ia, CURLOPT_POST, true);
            curl_setopt($ch_ia, CURLOPT_POSTFIELDS, json_encode($payload_ia));
            curl_setopt($ch_ia, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch_ia, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ia, CURLOPT_TIMEOUT, 15);
            
            $resposta_ia = curl_exec($ch_ia);
            $http_code_ia = curl_getinfo($ch_ia, CURLINFO_HTTP_CODE);
            curl_close($ch_ia);
            
            $resposta = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato."; // Fallback padrão
            
            if ($resposta_ia && $http_code_ia === 200) {
                $resultado_ia = json_decode($resposta_ia, true);
                if ($resultado_ia && $resultado_ia['success'] && isset($resultado_ia['resposta'])) {
                    $resposta = $resultado_ia['resposta'];
                    
                    if (DEBUG_MODE) {
                        error_log("[WEBHOOK {$ambiente}] ✅ Resposta IA: {$resultado_ia['metodo']} - {$resultado_ia['tipo']}");
                    }
                } else {
                    if (DEBUG_MODE) {
                        error_log("[WEBHOOK {$ambiente}] ❌ Erro na resposta IA: " . $resposta_ia);
                    }
                }
            } else {
                if (DEBUG_MODE) {
                    error_log("[WEBHOOK {$ambiente}] ❌ Falha na comunicação com IA: HTTP $http_code_ia");
                }
            }
        } else {
            $resposta = "Olá! Bem-vindo! Sua mensagem foi recebida. Em breve entraremos em contato.";
        }
        
        // Usar URL do WhatsApp configurada no config.php
        $api_url = WHATSAPP_ROBOT_URL . "/send/text";
        $data_envio = [
            "number" => $numero,
            "message" => $resposta
        ];
        
        if (DEBUG_MODE) {
            error_log("[WEBHOOK {$ambiente}] Enviando resposta via: $api_url");
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
                    error_log("[WEBHOOK {$ambiente}] ✅ Resposta automática enviada com sucesso");
                }
                
                // Salvar resposta enviada
                $resposta_escaped = $mysqli->real_escape_string($resposta);
                $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                                VALUES ($canal_id, " . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\")";
                $mysqli->query($sql_resposta);
            } else {
                error_log("[WEBHOOK {$ambiente}] ❌ Erro ao enviar resposta automática: " . $api_response);
            }
        } else {
            error_log("[WEBHOOK {$ambiente}] ❌ Erro HTTP ao enviar resposta: $http_code");
        }
    }
}

// Responder OK com informações do ambiente
$response = [
    'status' => 'ok',
    'ambiente' => $ambiente,
    'timestamp' => date('Y-m-d H:i:s'),
    'webhook_url' => $webhook_url
];

http_response_code(200);
echo json_encode($response);
?> 