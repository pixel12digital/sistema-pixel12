<?php
/**
 * WEBHOOK ESPECÍFICO PARA WHATSAPP
 * 
 * Este endpoint recebe mensagens do servidor WhatsApp
 * e as processa no sistema
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once '../painel/db.php';

// Log da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Salvar log
$log_file = '../logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
$log_data = date('Y-m-d H:i:s') . ' - ' . $input . "\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Debug: Log inicial
error_log("[WEBHOOK WHATSAPP] 🚀 Webhook iniciado - " . date('Y-m-d H:i:s'));
error_log("[WEBHOOK WHATSAPP] 📥 Dados recebidos: " . json_encode($data));

// Verificar se é uma mensagem recebida
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'];
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    error_log("[WEBHOOK WHATSAPP] 📥 Mensagem recebida de: $numero - Texto: '$texto'");
    
    // Buscar cliente pelo número com múltiplos formatos e similaridade
    $numero_limpo = preg_replace('/\D/', '', $numero);
    
    // Tentar diferentes formatos de busca para encontrar similaridades
    $formatos_busca = [
        $numero_limpo,                                    // Formato original (554796164699)
        ltrim($numero_limpo, '55'),                       // Remove código do país (4796164699)
        substr($numero_limpo, -11),                       // Últimos 11 dígitos
        substr($numero_limpo, -10),                       // Últimos 10 dígitos
        substr($numero_limpo, -9),                        // Últimos 9 dígitos (sem DDD)
        substr($numero_limpo, 2, 2) . '9' . substr($numero_limpo, 4), // Sem código + 9
    ];
    
    $cliente_id = null;
    $cliente = null;
    $formato_encontrado = null;
    
    // Buscar cliente com similaridade de número
    foreach ($formatos_busca as $formato) {
        if (strlen($formato) >= 9) { // Mínimo 9 dígitos para busca
            $sql = "SELECT id, nome, contact_name, celular, telefone FROM clientes 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                    OR REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%" . substr($formato, -9) . "%'
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%" . substr($formato, -9) . "%'
                    LIMIT 1";
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                $cliente_id = $cliente['id'];
                $formato_encontrado = $formato;
                error_log("[WEBHOOK WHATSAPP] ✅ Cliente encontrado com formato $formato - ID: $cliente_id, Nome: {$cliente['nome']}");
                break;
            }
        }
    }
    
    if (!$cliente) {
        error_log("[WEBHOOK WHATSAPP] ❌ Cliente não encontrado para número: $numero");
    }
    
    // Buscar canal WhatsApp financeiro
    $canal_id = 36; // Canal financeiro padrão
    $canal_result = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%') LIMIT 1");
    if ($canal_result && $canal_result->num_rows > 0) {
        $canal = $canal_result->fetch_assoc();
        $canal_id = $canal['id'];
        error_log("[WEBHOOK WHATSAPP] 📡 Usando canal: {$canal['nome_exibicao']} (ID: $canal_id)");
    } else {
        // Criar canal WhatsApp financeiro se não existir
        $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
                        VALUES ('whatsapp', 'financeiro', 'WhatsApp Financeiro', 'conectado', NOW())");
        $canal_id = $mysqli->insert_id;
        error_log("[WEBHOOK WHATSAPP] 🆕 Canal financeiro criado - ID: $canal_id");
    }
    
    // Verificar se já existe conversa recente para este número específico (últimas 24 horas)
    $numero_escaped = $mysqli->real_escape_string($numero);
    
    // Buscar conversa por número WhatsApp (mais preciso)
    $sql_conversa_recente = "SELECT COUNT(*) as total_mensagens, 
                                   MAX(data_hora) as ultima_mensagem,
                                   MIN(data_hora) as primeira_mensagem,
                                   COUNT(CASE WHEN direcao = 'enviado' AND mensagem LIKE '%Olá%' THEN 1 END) as respostas_automaticas,
                                   COUNT(CASE WHEN direcao = 'enviado' AND mensagem LIKE '%Esta é uma mensagem automática%' THEN 1 END) as mensagens_automaticas
                            FROM mensagens_comunicacao 
                            WHERE canal_id = $canal_id 
                            AND numero_whatsapp = '$numero_escaped'
                            AND data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    
    $result_conversa = $mysqli->query($sql_conversa_recente);
    $conversa_info = $result_conversa->fetch_assoc();
    $total_mensagens = $conversa_info['total_mensagens'];
    $respostas_automaticas = $conversa_info['respostas_automaticas'];
    $mensagens_automaticas = $conversa_info['mensagens_automaticas'];
    $tem_conversa_recente = $total_mensagens > 0;
    
    error_log("[WEBHOOK WHATSAPP] 📊 Conversa recente: $total_mensagens mensagens, $respostas_automaticas respostas automáticas, $mensagens_automaticas mensagens automáticas nas últimas 24h");
    
    // Salvar mensagem recebida COM numero_whatsapp
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido', '$numero_escaped')";
    
    if ($mysqli->query($sql)) {
        $mensagem_id = $mysqli->insert_id;
        error_log("[WEBHOOK WHATSAPP] ✅ Mensagem salva - ID: $mensagem_id, Cliente: $cliente_id, Número: $numero");
        
        // Invalidar cache se cliente existir
        if ($cliente_id) {
            require_once '../painel/cache_invalidator.php';
            invalidate_message_cache($cliente_id);
            if (function_exists('cache_forget')) {
                cache_forget("conversas_recentes");
                cache_forget("mensagens_html_{$cliente_id}");
                cache_forget("historico_html_{$cliente_id}");
            }
            
            // 🚀 NOVA FUNCIONALIDADE: Notificação Push para Atualização Automática
            enviarNotificacaoPush($cliente_id, $numero, $texto, $mensagem_id);
        }
    } else {
        error_log("[WEBHOOK WHATSAPP] ❌ Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Preparar resposta automática baseada na situação
    $resposta_automatica = '';
    $enviar_resposta = false;
    
    // NOVA LÓGICA MELHORADA PARA EVITAR LOOPS:
    // 1. Se é a primeira mensagem da conversa (sem conversa recente)
    // 2. Se a última mensagem foi há mais de 1 hora (nova sessão)
    // 3. Se ainda não foi enviada resposta automática hoje
    // 4. Se é uma mensagem que requer resposta específica (saudação, faturas, etc.)
    
    $texto_lower = strtolower(trim($texto));
    $palavras_chave_saudacao = ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi', 'oie'];
    $palavras_chave_fatura = ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar', 'consulta', 'consultas'];
    $palavras_chave_cpf = ['cpf', 'documento', 'identificação', 'cadastro', 'cnpj'];
    
    $eh_saudacao = false;
    $eh_fatura = false;
    $eh_cpf = false;
    
    // Verificar tipo de mensagem
    foreach ($palavras_chave_saudacao as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_saudacao = true;
            break;
        }
    }
    
    foreach ($palavras_chave_fatura as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_fatura = true;
            break;
        }
    }
    
    foreach ($palavras_chave_cpf as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_cpf = true;
            break;
        }
    }
    
    // Verificar se deve enviar resposta
    if (!$tem_conversa_recente) {
        // Primeira mensagem da conversa - sempre enviar resposta
        $enviar_resposta = true;
        error_log("[WEBHOOK WHATSAPP] 🆕 Primeira mensagem da conversa - enviando resposta");
    } else {
        // Verificar se a última mensagem foi há mais de 1 hora
        $ultima_mensagem = $conversa_info['ultima_mensagem'];
        $tempo_desde_ultima = time() - strtotime($ultima_mensagem);
        
        if ($tempo_desde_ultima > 3600) { // Mais de 1 hora
            $enviar_resposta = true;
            error_log("[WEBHOOK WHATSAPP] ⏰ Conversa retomada após " . round($tempo_desde_ultima/60) . " minutos - enviando resposta");
        } else {
            // Verificar se já foi enviada resposta automática hoje
            if ($mensagens_automaticas == 0) {
                // Verificar se é uma mensagem que requer resposta específica
                if ($eh_saudacao || $eh_fatura || $eh_cpf) {
                    $enviar_resposta = true;
                    error_log("[WEBHOOK WHATSAPP] 👋 Mensagem específica detectada (saudação: $eh_saudacao, fatura: $eh_fatura, cpf: $eh_cpf) - enviando resposta");
                } else {
                    error_log("[WEBHOOK WHATSAPP] 🔇 Conversa em andamento - não enviando resposta automática");
                }
            } else {
                error_log("[WEBHOOK WHATSAPP] 🔇 Resposta automática já enviada hoje - não enviando novamente");
            }
        }
    }
    
    if ($enviar_resposta) {
        // Usar IA para gerar resposta inteligente
        try {
            $payload_ia = [
                'from' => $numero,
                'message' => $texto,
                'type' => $tipo
            ];
            
            error_log("[WEBHOOK WHATSAPP] 🤖 Chamando IA com payload: " . json_encode($payload_ia));
            
            // Chamar endpoint da IA
            $ch_ia = curl_init(($is_local ? 'http://localhost:8080/loja-virtual-revenda' : '') . '/painel/api/processar_mensagem_ia.php');
            curl_setopt($ch_ia, CURLOPT_POST, true);
            curl_setopt($ch_ia, CURLOPT_POSTFIELDS, json_encode($payload_ia));
            curl_setopt($ch_ia, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch_ia, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ia, CURLOPT_TIMEOUT, 15);
            
            $resposta_ia = curl_exec($ch_ia);
            $http_code_ia = curl_getinfo($ch_ia, CURLINFO_HTTP_CODE);
            $error_ia = curl_error($ch_ia);
            curl_close($ch_ia);
            
            error_log("[WEBHOOK WHATSAPP] 🤖 Resposta IA - HTTP: $http_code_ia, Erro: $error_ia, Resposta: $resposta_ia");
            
            if ($resposta_ia && $http_code_ia === 200) {
                $resultado_ia = json_decode($resposta_ia, true);
                if ($resultado_ia && $resultado_ia['success'] && isset($resultado_ia['resposta'])) {
                    $resposta_automatica = $resultado_ia['resposta'];
                    error_log("[WEBHOOK WHATSAPP] 🤖 Resposta IA gerada - Intenção: {$resultado_ia['intencao']}");
                } else {
                    error_log("[WEBHOOK WHATSAPP] ❌ Erro na resposta IA: " . $resposta_ia);
                    // Fallback para resposta padrão
                    $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
                }
            } else {
                error_log("[WEBHOOK WHATSAPP] ❌ Falha na comunicação com IA: HTTP $http_code_ia, Erro: $error_ia");
                // Fallback para resposta padrão
                $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
            }
        } catch (Exception $e) {
            error_log("[WEBHOOK WHATSAPP] ❌ Exceção ao processar IA: " . $e->getMessage());
            // Fallback para resposta padrão
            $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
        }
    }
    
    // Enviar resposta automática via WhatsApp
    if ($resposta_automatica && $enviar_resposta) {
        try {
            // Usar URL do WhatsApp configurada no config.php
            $api_url = WHATSAPP_ROBOT_URL . "/send/text";
            $data_envio = [
                "number" => $numero,
                "message" => $resposta_automatica
            ];
            
            error_log("[WEBHOOK WHATSAPP] 📤 Enviando resposta via: $api_url");
            error_log("[WEBHOOK WHATSAPP] 📤 Dados de envio: " . json_encode($data_envio));
            
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, WHATSAPP_TIMEOUT);
            
            $api_response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error_envio = curl_error($ch);
            curl_close($ch);
            
            error_log("[WEBHOOK WHATSAPP] 📤 Resposta API - HTTP: $http_code, Erro: $error_envio, Resposta: $api_response");
            
            if ($http_code === 200) {
                $api_result = json_decode($api_response, true);
                if ($api_result && isset($api_result["success"]) && $api_result["success"]) {
                    error_log("[WEBHOOK WHATSAPP] ✅ Resposta automática enviada com sucesso");
                    
                    // Salvar resposta enviada COM numero_whatsapp
                    $resposta_escaped = $mysqli->real_escape_string($resposta_automatica);
                    $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                                    VALUES ($canal_id, " . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\", \"$numero_escaped\")";
                    $mysqli->query($sql_resposta);
                } else {
                    error_log("[WEBHOOK WHATSAPP] ❌ Erro ao enviar resposta automática: " . $api_response);
                }
            } else {
                error_log("[WEBHOOK WHATSAPP] ❌ Erro HTTP ao enviar resposta: $http_code, Erro: $error_envio");
            }
        } catch (Exception $e) {
            error_log("[WEBHOOK WHATSAPP] ❌ Exceção ao enviar resposta: " . $e->getMessage());
        }
    } else {
        error_log("[WEBHOOK WHATSAPP] 🔇 Não enviando resposta automática - Condições não atendidas");
    }
    
    // Responder sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem processada com sucesso',
        'cliente_id' => $cliente_id,
        'cliente_nome' => $cliente ? ($cliente['contact_name'] ?: $cliente['nome']) : null,
        'formato_encontrado' => $formato_encontrado,
        'canal_id' => $canal_id,
        'mensagem_id' => $mensagem_id ?? null,
        'resposta_enviada' => $enviar_resposta,
        'tem_conversa_recente' => $tem_conversa_recente,
        'total_mensagens_24h' => $total_mensagens,
        'respostas_automaticas_24h' => $respostas_automaticas,
        'mensagens_automaticas_24h' => $mensagens_automaticas,
        'numero_whatsapp' => $numero,
        'eh_saudacao' => $eh_saudacao,
        'eh_fatura' => $eh_fatura,
        'eh_cpf' => $eh_cpf
    ]);
} else {
    // Responder erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Evento inválido ou dados incompletos',
        'data_recebida' => $data
    ]);
}

/**
 * 🚀 ENVIA NOTIFICAÇÃO PUSH PARA ATUALIZAÇÃO AUTOMÁTICA
 * Aciona atualização imediata do chat quando mensagem é recebida
 */
function enviarNotificacaoPush($cliente_id, $numero, $texto, $mensagem_id) {
    try {
        // URL do endpoint de notificação push
        $push_url = ($GLOBALS['is_local'] ? 'http://localhost:8080/loja-virtual-revenda' : '') . '/painel/api/push_notification.php';
        
        $payload = [
            'action' => 'new_message',
            'cliente_id' => $cliente_id,
            'numero' => $numero,
            'texto' => $texto,
            'mensagem_id' => $mensagem_id
        ];
        
        $ch = curl_init($push_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        error_log("[WEBHOOK WHATSAPP] 📡 Notificação push enviada: $response");
    } catch (Exception $e) {
        error_log("[WEBHOOK WHATSAPP] ❌ Erro ao enviar notificação push: " . $e->getMessage());
    }
}

/**
 * 🔄 GERA RESPOSTA PADRÃO QUANDO IA FALHA
 */
function gerarRespostaPadrao($cliente_id, $cliente) {
    if ($cliente_id && $cliente) {
        $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
        return "Olá $nome_cliente! 👋\n\nComo posso ajudá-lo hoje?\n\n📋 *Opções disponíveis:*\n• Verificar faturas (digite 'faturas' ou 'consulta')\n• Informações do plano\n• Suporte técnico\n• Atendimento comercial";
    } else {
        return "Olá! 👋\n\nEste é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n📞 *Para atendimento comercial ou suporte técnico:*\nEntre em contato através do número: *47 997309525*\n\n📋 *Para informações sobre seu plano, faturas, etc.:*\nDigite 'faturas' ou 'consulta' para verificar suas pendências.\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
    }
}
?> 