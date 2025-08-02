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
        error_log("[WEBHOOK SEM REDIRECT {$ambiente}] Canal identificado: $canal_nome (ID: $canal_id) - Sessão: $session_name");
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
        // 🚀 INTEGRAÇÃO COM ANA - REDIRECIONAR PARA SISTEMA NOVO
        // Se for canal 3000 (Pixel12Digital), usar Ana ao invés de resposta automática
        
        $canal_ana = $mysqli->query("SELECT porta FROM canais_comunicacao WHERE id = $canal_id")->fetch_assoc();
        
        if ($canal_ana && intval($canal_ana['porta']) === 3000) {
            // CANAL 3000 - REDIRECIONAR PARA ANA
            error_log("[WEBHOOK_REDIRECT_ANA] Canal 3000 detectado - Redirecionando para Ana");
            
            // Chamar sistema Ana via API (funcionando)
            try {
                $api_url = 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php';
                
                $payload = [
                    'question' => $texto,
                    'agent_id' => '3' // ID da Ana
                ];
                
                error_log("[WEBHOOK_REDIRECT_ANA] Chamando API de agentes: $api_url");
                
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
                        error_log("[WEBHOOK_REDIRECT_ANA] ✅ Ana API respondeu com sucesso");
                        
                        // 🧠 ANÁLISE DA RESPOSTA ANA PARA DETECTAR AÇÕES
                        $analise = analisarRespostaAna($resposta_ana, $texto);
                        error_log("[WEBHOOK_REDIRECT_ANA] 🔍 Análise: " . json_encode($analise));
                        
                        // 🔍 ENRIQUECER RESPOSTA COM DADOS DO CLIENTE (se necessário)
                        if ($cliente_id && ($analise['consultar_faturas'] || strpos(strtolower($texto), 'fatura') !== false || strpos(strtolower($texto), 'pagamento') !== false)) {
                            error_log("[WEBHOOK_REDIRECT_ANA] 📊 Consultando faturas do cliente: $cliente_id");
                            $dados_faturas = consultarFaturasCliente($cliente_id, $mysqli);
                            
                            if ($dados_faturas['tem_faturas']) {
                                $resposta_ana = enriquecerRespostaComFaturas($resposta_ana, $dados_faturas);
                                error_log("[WEBHOOK_REDIRECT_ANA] 💰 Resposta enriquecida com dados de faturas");
                            }
                        }
                        
                        // 💾 SALVAR RESPOSTA DA ANA NO BANCO
                        $sql_resposta = "INSERT INTO mensagens_comunicacao 
                                         (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                                         VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '" . $mysqli->real_escape_string($resposta_ana) . "', 'texto', '$data_hora', 'enviado', 'entregue')";
                        
                        if ($mysqli->query($sql_resposta)) {
                            $resposta_id = $mysqli->insert_id;
                            error_log("[WEBHOOK_REDIRECT_ANA] ✅ Resposta Ana salva - ID: $resposta_id");
                            
                            // 🎯 EXECUTAR AÇÕES ESPECÍFICAS DO SISTEMA
                            if ($analise['acao'] !== 'nenhuma') {
                                executarAcaoSistema($analise, $numero, $texto, $cliente_id, $mysqli);
                            }
                            
                            // 📱 ENVIAR RESPOSTA DA ANA PARA O WHATSAPP
                            $api_url_whats = WHATSAPP_ROBOT_URL . "/send/text";
                            $data_envio = [
                                "number" => $numero,
                                "message" => $resposta_ana
                            ];
                            
                            error_log("[WEBHOOK_REDIRECT_ANA] 📤 Enviando resposta Ana via WhatsApp...");
                            
                            $ch_whats = curl_init($api_url_whats);
                            curl_setopt($ch_whats, CURLOPT_POST, true);
                            curl_setopt($ch_whats, CURLOPT_POSTFIELDS, json_encode($data_envio));
                            curl_setopt($ch_whats, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                            curl_setopt($ch_whats, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch_whats, CURLOPT_TIMEOUT, 10);
                            
                            $response_whats = curl_exec($ch_whats);
                            $curl_error_whats = curl_error($ch_whats);
                            $http_code_whats = curl_getinfo($ch_whats, CURLINFO_HTTP_CODE);
                            curl_close($ch_whats);
                            
                            if ($curl_error_whats) {
                                error_log("[WEBHOOK_REDIRECT_ANA] ❌ Erro ao enviar via WhatsApp: $curl_error_whats");
                            } else {
                                error_log("[WEBHOOK_REDIRECT_ANA] ✅ Resposta Ana enviada via WhatsApp - HTTP: $http_code_whats");
                            }
                        } else {
                            error_log("[WEBHOOK_REDIRECT_ANA] ❌ Erro ao salvar resposta Ana: " . $mysqli->error);
                        }
                        
                        // Ana processou via API com orquestração completa
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Processado via Ana API + Orquestração',
                            'source' => 'webhook_ana_orchestrated',
                            'ana_response' => $resposta_ana,
                            'analise' => $analise
                        ]);
                        exit;
                        
                    } else {
                        error_log("[WEBHOOK_REDIRECT_ANA] ❌ Resposta inválida da API Ana");
                    }
                } else {
                    error_log("[WEBHOOK_REDIRECT_ANA] ❌ Erro na API Ana - HTTP: $http_code, cURL: $curl_error");
                }
                
            } catch (Exception $e) {
                error_log("[WEBHOOK_REDIRECT_ANA] ❌ Erro ao chamar API Ana: " . $e->getMessage());
            }
        }
        
        // OUTROS CANAIS OU FALLBACK - USAR RESPOSTA AUTOMÁTICA ORIGINAL
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($curl_error) {
            error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ❌ Erro cURL: $curl_error");
        } else {
            if (DEBUG_MODE) {
                error_log("[WEBHOOK SEM REDIRECT {$ambiente}] ✅ Resposta enviada - HTTP: $http_code - Response: $response");
            }
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

// ===== FUNÇÕES DE ORQUESTRAÇÃO ANA =====

/**
 * Analisar resposta da Ana para detectar ações do sistema
 */
function analisarRespostaAna($resposta_ana, $mensagem_original) {
    $analise = [
        'acao' => 'nenhuma',
        'departamento' => null,
        'transfer_rafael' => false,
        'transfer_humano' => false,
        'transfer_canal_3001' => false,
        'consultar_faturas' => false
    ];
    
    $resposta_lower = strtolower($resposta_ana);
    $mensagem_lower = strtolower($mensagem_original);
    
    // Detectar consulta de faturas
    if (strpos($resposta_lower, 'fatura') !== false || 
        strpos($resposta_lower, 'pagamento') !== false ||
        strpos($mensagem_lower, 'fatura') !== false ||
        strpos($mensagem_lower, 'pagamento') !== false) {
        $analise['consultar_faturas'] = true;
    }
    
    // Detectar transferência para Rafael (sites/ecommerce)
    if (strpos($resposta_lower, 'rafael') !== false || 
        strpos($resposta_lower, 'transferir você para o rafael') !== false ||
        strpos($resposta_lower, 'desenvolvimento web') !== false ||
        strpos($resposta_lower, 'especialista em desenvolvimento web') !== false) {
        
        $analise['acao'] = 'transfer_rafael';
        $analise['transfer_rafael'] = true;
        $analise['departamento'] = 'SITES';
    }
    
    // Detectar transferência para canal 3001 (atendimento humano)
    elseif (strpos($resposta_lower, 'canal comercial') !== false ||
            strpos($resposta_lower, 'atendente humano') !== false ||
            strpos($resposta_lower, 'transferir para atendimento') !== false) {
        
        $analise['acao'] = 'transfer_canal_3001';
        $analise['transfer_canal_3001'] = true;
        $analise['transfer_humano'] = true;
        $analise['departamento'] = 'COM';
    }
    
    // Detectar transferência para humanos em geral
    elseif (strpos($resposta_lower, '47 97309525') !== false ||
            strpos($resposta_lower, 'equipe humana') !== false ||
            strpos($resposta_lower, 'atendimento humano') !== false) {
        
        $analise['acao'] = 'transfer_humano';
        $analise['transfer_humano'] = true;
        
        // Detectar departamento da transferência
        if (strpos($resposta_lower, 'financeira') !== false) $analise['departamento'] = 'FIN';
        elseif (strpos($resposta_lower, 'suporte') !== false) $analise['departamento'] = 'SUP';
        elseif (strpos($resposta_lower, 'comercial') !== false) $analise['departamento'] = 'COM';
        elseif (strpos($resposta_lower, 'administrativa') !== false) $analise['departamento'] = 'ADM';
    }
    
    // Detectar departamento sem transferência
    elseif (strpos($resposta_lower, 'financeira') !== false) {
        $analise['acao'] = 'departamento_identificado';
        $analise['departamento'] = 'FIN';
    }
    elseif (strpos($resposta_lower, 'suporte técnico') !== false) {
        $analise['acao'] = 'departamento_identificado';
        $analise['departamento'] = 'SUP';
    }
    elseif (strpos($resposta_lower, 'comercial') !== false) {
        $analise['acao'] = 'departamento_identificado';
        $analise['departamento'] = 'COM';
    }
    elseif (strpos($resposta_lower, 'administrativa') !== false) {
        $analise['acao'] = 'departamento_identificado';
        $analise['departamento'] = 'ADM';
    }
    
    return $analise;
}

/**
 * Consultar faturas do cliente
 */
function consultarFaturasCliente($cliente_id, $mysqli) {
    $dados = [
        'tem_faturas' => false,
        'faturas_vencidas' => [],
        'proxima_fatura' => null,
        'total_vencidas' => 0,
        'valor_total_vencido' => 0
    ];
    
    if (!$cliente_id) return $dados;
    
    try {
        // Buscar faturas vencidas
        $sql_vencidas = "SELECT 
                            id, valor, status,
                            DATE_FORMAT(vencimento, '%d/%m/%Y') as vencimento_formatado,
                            url_fatura,
                            DATEDIFF(CURDATE(), vencimento) as dias_vencido
                        FROM cobrancas 
                        WHERE cliente_id = $cliente_id 
                        AND status = 'OVERDUE'
                        ORDER BY vencimento ASC";
        
        $result_vencidas = $mysqli->query($sql_vencidas);
        
        if ($result_vencidas && $result_vencidas->num_rows > 0) {
            $dados['tem_faturas'] = true;
            while ($row = $result_vencidas->fetch_assoc()) {
                $dados['faturas_vencidas'][] = $row;
                $dados['valor_total_vencido'] += floatval($row['valor']);
            }
            $dados['total_vencidas'] = count($dados['faturas_vencidas']);
        }
        
        // Buscar próxima fatura a vencer
        $sql_proxima = "SELECT 
                            id, valor, status,
                            DATE_FORMAT(vencimento, '%d/%m/%Y') as vencimento_formatado,
                            url_fatura,
                            DATEDIFF(vencimento, CURDATE()) as dias_para_vencer
                        FROM cobrancas 
                        WHERE cliente_id = $cliente_id 
                        AND status = 'PENDING'
                        ORDER BY vencimento ASC 
                        LIMIT 1";
        
        $result_proxima = $mysqli->query($sql_proxima);
        
        if ($result_proxima && $result_proxima->num_rows > 0) {
            $dados['tem_faturas'] = true;
            $dados['proxima_fatura'] = $result_proxima->fetch_assoc();
        }
        
    } catch (Exception $e) {
        error_log("[WEBHOOK_FATURAS] Erro ao consultar faturas: " . $e->getMessage());
    }
    
    return $dados;
}

/**
 * Enriquecer resposta da Ana com dados de faturas
 */
function enriquecerRespostaComFaturas($resposta_ana, $dados_faturas) {
    $adicional = "\n\n📊 **Resumo da sua conta:**\n";
    
    if ($dados_faturas['total_vencidas'] > 0) {
        $adicional .= "⚠️ Você possui {$dados_faturas['total_vencidas']} fatura(s) vencida(s)\n";
        $adicional .= "💰 Total em atraso: R$ " . number_format($dados_faturas['valor_total_vencido'], 2, ',', '.') . "\n";
        
        if (count($dados_faturas['faturas_vencidas']) > 0) {
            $primeira_vencida = $dados_faturas['faturas_vencidas'][0];
            $adicional .= "📅 Vencimento mais antigo: {$primeira_vencida['vencimento_formatado']} ({$primeira_vencida['dias_vencido']} dias atrás)\n";
            
            if (!empty($primeira_vencida['url_fatura'])) {
                $adicional .= "🔗 Link para pagamento: {$primeira_vencida['url_fatura']}\n";
            }
        }
    }
    
    if ($dados_faturas['proxima_fatura']) {
        $proxima = $dados_faturas['proxima_fatura'];
        $adicional .= "📅 Próxima fatura: {$proxima['vencimento_formatado']} (R$ " . number_format($proxima['valor'], 2, ',', '.') . ")\n";
        
        if (!empty($proxima['url_fatura'])) {
            $adicional .= "🔗 Link: {$proxima['url_fatura']}\n";
        }
    }
    
    if (!$dados_faturas['tem_faturas']) {
        $adicional .= "✅ Sua conta está em dia! Nenhuma fatura pendente.\n";
    }
    
    return $resposta_ana . $adicional;
}

/**
 * Executar ações específicas do sistema
 */
function executarAcaoSistema($analise, $numero, $mensagem, $cliente_id, $mysqli) {
    switch ($analise['acao']) {
        case 'transfer_rafael':
            registrarTransferenciaRafael($numero, $mensagem, $cliente_id, $mysqli);
            break;
            
        case 'transfer_canal_3001':
            registrarTransferenciaCanal3001($numero, $mensagem, $cliente_id, $mysqli);
            break;
            
        case 'transfer_humano':
            registrarTransferenciaHumano($numero, $mensagem, $analise['departamento'], $cliente_id, $mysqli);
            break;
            
        case 'departamento_identificado':
            registrarAtendimentoDepartamento($numero, $mensagem, $analise['departamento'], $cliente_id, $mysqli);
            break;
    }
}

/**
 * Registrar transferência para Rafael
 */
function registrarTransferenciaRafael($numero, $mensagem, $cliente_id, $mysqli) {
    $sql = "INSERT INTO transferencias_rafael (numero_cliente, cliente_id, mensagem_original, data_transferencia, status) 
            VALUES (?, ?, ?, NOW(), 'pendente')";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('sis', $numero, $cliente_id, $mensagem);
        $stmt->execute();
        $stmt->close();
        error_log("[WEBHOOK_ORQUESTRAÇÃO] Transferência para Rafael registrada: $numero");
    }
}

/**
 * Registrar transferência para canal 3001 (comercial)
 */
function registrarTransferenciaCanal3001($numero, $mensagem, $cliente_id, $mysqli) {
    // Salvar notificação para canal 3001
    $sql = "INSERT INTO notificacoes_canal (canal_origem, canal_destino, numero_cliente, cliente_id, mensagem, tipo, data_criacao, status) 
            VALUES (3000, 3001, ?, ?, ?, 'transferencia', NOW(), 'pendente')";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('sis', $numero, $cliente_id, $mensagem);
        $stmt->execute();
        $stmt->close();
        error_log("[WEBHOOK_ORQUESTRAÇÃO] Transferência para canal 3001 registrada: $numero");
    }
}

/**
 * Registrar transferência para humano
 */
function registrarTransferenciaHumano($numero, $mensagem, $departamento, $cliente_id, $mysqli) {
    $sql = "INSERT INTO transferencias_humano (numero_cliente, cliente_id, mensagem_original, departamento, data_transferencia, status) 
            VALUES (?, ?, ?, ?, NOW(), 'pendente')";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('siss', $numero, $cliente_id, $mensagem, $departamento);
        $stmt->execute();
        $stmt->close();
        error_log("[WEBHOOK_ORQUESTRAÇÃO] Transferência para humano registrada: $numero -> $departamento");
    }
}

/**
 * Registrar atendimento por departamento
 */
function registrarAtendimentoDepartamento($numero, $mensagem, $departamento, $cliente_id, $mysqli) {
    $sql = "INSERT INTO atendimentos_departamento (numero_cliente, cliente_id, mensagem, departamento, data_atendimento) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('siss', $numero, $cliente_id, $mensagem, $departamento);
        $stmt->execute();
        $stmt->close();
        error_log("[WEBHOOK_ORQUESTRAÇÃO] Atendimento $departamento registrado: $numero");
    }
}
?> 