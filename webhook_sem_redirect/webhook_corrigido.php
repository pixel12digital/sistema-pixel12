<?php
/**
 * Webhook Sem Redirecionamento - VERSÃO CORRIGIDA
 * Endpoint webhook em diretório separado para evitar redirecionamentos
 * 
 * CORREÇÕES APLICADAS:
 * - Melhor tratamento de erros
 * - Garantia de salvamento de mensagens
 * - Logs mais detalhados
 * - Validação de dados
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

// Incluir configurações
require_once __DIR__ . '/../config.php';

// Função para conectar ao banco de forma segura
function conectarBanco() {
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_errno) {
            throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
        }
        
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        error_log("[WEBHOOK CORRIGIDO] Erro de conexão com banco: " . $e->getMessage());
        return null;
    }
}

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
    error_log("[WEBHOOK CORRIGIDO {$ambiente}] Requisição recebida");
    error_log("[WEBHOOK CORRIGIDO {$ambiente}] Dados: " . $input);
}

// Verificar se é uma mensagem recebida
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'] ?? '';
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    // Debug - log das variáveis
    if (DEBUG_MODE) {
        error_log("[WEBHOOK CORRIGIDO {$ambiente}] Debug - Numero: $numero, Texto: $texto, Tipo: $tipo");
    }
    
    // Conectar ao banco
    $mysqli = conectarBanco();
    if (!$mysqli) {
        error_log("[WEBHOOK CORRIGIDO {$ambiente}] ❌ Erro ao conectar ao banco");
        http_response_code(500);
        echo json_encode(['error' => 'Erro de conexão com banco']);
        exit;
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
            error_log("[WEBHOOK CORRIGIDO {$ambiente}] Cliente encontrado: {$cliente['nome']} (ID: $cliente_id)");
        }
    }
    
    // Cadastro automático de clientes não cadastrados
    if (!$cliente_id) {
        if (DEBUG_MODE) {
            error_log("[WEBHOOK CORRIGIDO {$ambiente}] Cliente não encontrado, criando cadastro automático...");
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
                error_log("[WEBHOOK CORRIGIDO {$ambiente}] ✅ Cliente criado automaticamente - ID: $cliente_id");
            }
        } else {
            error_log("[WEBHOOK CORRIGIDO {$ambiente}] ❌ Erro ao criar cliente: " . $mysqli->error);
        }
    }

    // Identificar canal baseado na sessão
    $canal_id = 36; // Padrão: Financeiro (3000)
    $canal_nome = 'Financeiro';
    
    // Verificar se há informação da sessão para identificar o canal
    $session_name = $message['session'] ?? 'default';
    
    if ($session_name === 'comercial') {
        // Canal Comercial (3001)
        $canal_id = 37;
        $canal_nome = 'Comercial - Pixel';
    } else {
        // Canal Financeiro (3000) - padrão
        $canal_id = 36;
        $canal_nome = 'Financeiro';
    }
    
    if (DEBUG_MODE) {
        error_log("[WEBHOOK CORRIGIDO {$ambiente}] Canal identificado: $canal_nome (ID: $canal_id) - Sessão: $session_name");
    }
    
    // Salvar mensagem recebida
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    $numero_escaped = $mysqli->real_escape_string($numero);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$numero_escaped', '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
    
    // Debug - log da SQL
    if (DEBUG_MODE) {
        error_log("[WEBHOOK CORRIGIDO {$ambiente}] SQL: $sql");
    }
    
    $mensagem_salva = false;
    $mensagem_id = null;
    
    if ($mysqli->query($sql)) {
        $mensagem_id = $mysqli->insert_id;
        $mensagem_salva = true;
        if (DEBUG_MODE) {
            error_log("[WEBHOOK CORRIGIDO {$ambiente}] ✅ Mensagem salva - ID: $mensagem_id");
        }
        
        // Invalidar cache se disponível
        if (file_exists('../painel/cache_invalidator.php')) {
            require_once '../painel/cache_invalidator.php';
            if ($cliente_id && function_exists('invalidate_message_cache')) {
                invalidate_message_cache($cliente_id);
            }
        }
    } else {
        error_log("[WEBHOOK CORRIGIDO {$ambiente}] ❌ Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Resposta automática simples
    if ($texto && $mensagem_salva) {
        // 🚀 INTEGRAÇÃO COM ANA - Canal 3000
        $canal_ana = $mysqli->query("SELECT porta FROM canais_comunicacao WHERE id = $canal_id")->fetch_assoc();
        
        if ($canal_ana && intval($canal_ana['porta']) === 3000) {
            // CANAL 3000 - USAR ANA
            error_log("[WEBHOOK_CORRIGIDO_ANA] Canal 3000 detectado - Chamando Ana");
            
            try {
                $api_url = 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php';
                $payload = [
                    'question' => $texto,
                    'agent_id' => '3' // ID da Ana
                ];
                
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response_ana = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);
                curl_close($ch);
                
                if (!$curl_error && $http_code === 200) {
                    $data_ana = json_decode($response_ana, true);
                    
                    if ($data_ana && isset($data_ana['response'])) {
                        $resposta_ana = $data_ana['response'];
                        error_log("[WEBHOOK_CORRIGIDO_ANA] ✅ Ana API respondeu com sucesso");
                        
                        // Salvar resposta da Ana no banco
                        $sql_resposta = "INSERT INTO mensagens_comunicacao 
                                         (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                                         VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '" . $mysqli->real_escape_string($resposta_ana) . "', 'texto', '$data_hora', 'enviado', 'entregue')";
                        
                        if ($mysqli->query($sql_resposta)) {
                            $resposta_id = $mysqli->insert_id;
                            error_log("[WEBHOOK_CORRIGIDO_ANA] ✅ Resposta Ana salva - ID: $resposta_id");
                        } else {
                            error_log("[WEBHOOK_CORRIGIDO_ANA] ❌ Erro ao salvar resposta Ana: " . $mysqli->error);
                        }
                        
                        // Ana processou via API, resposta enviada
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Processado via Ana API',
                            'source' => 'webhook_corrigido_ana_api',
                            'ana_response' => $resposta_ana,
                            'mensagem_id' => $mensagem_id
                        ]);
                        exit;
                        
                    } else {
                        error_log("[WEBHOOK_CORRIGIDO_ANA] ❌ Resposta inválida da API Ana");
                    }
                } else {
                    error_log("[WEBHOOK_CORRIGIDO_ANA] ❌ Erro na API Ana - HTTP: $http_code, cURL: $curl_error");
                }
                
            } catch (Exception $e) {
                error_log("[WEBHOOK_CORRIGIDO_ANA] ❌ Erro ao chamar API Ana: " . $e->getMessage());
            }
        }
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Responder OK
$response = [
    'status' => 'ok',
    'ambiente' => $ambiente,
    'timestamp' => date('Y-m-d H:i:s'),
    'webhook_type' => 'corrigido',
    'message' => 'Webhook corrigido processado com sucesso',
    'mensagem_salva' => $mensagem_salva ?? false,
    'mensagem_id' => $mensagem_id ?? null
];

http_response_code(200);
echo json_encode($response);
?> 