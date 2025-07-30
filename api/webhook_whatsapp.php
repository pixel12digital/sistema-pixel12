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

// Verificar se é uma mensagem recebida
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'];
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    error_log("[WEBHOOK WHATSAPP] 📥 Mensagem recebida de: $numero - Texto: $texto");
    
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
                                   COUNT(CASE WHEN direcao = 'enviado' AND mensagem LIKE '%Olá%Recebemos sua mensagem%' THEN 1 END) as respostas_automaticas
                            FROM mensagens_comunicacao 
                            WHERE canal_id = $canal_id 
                            AND numero_whatsapp = '$numero_escaped'
                            AND data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    
    $result_conversa = $mysqli->query($sql_conversa_recente);
    $conversa_info = $result_conversa->fetch_assoc();
    $total_mensagens = $conversa_info['total_mensagens'];
    $respostas_automaticas = $conversa_info['respostas_automaticas'];
    $tem_conversa_recente = $total_mensagens > 0;
    
    error_log("[WEBHOOK WHATSAPP] 📊 Conversa recente: $total_mensagens mensagens, $respostas_automaticas respostas automáticas nas últimas 24h");
    
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
    
    // Lógica para evitar duplicidade e garantir conversa única:
    // 1. Se é a primeira mensagem da conversa (sem conversa recente)
    // 2. Se a última mensagem foi há mais de 2 horas (nova sessão)
    // 3. Se ainda não foi enviada resposta automática hoje
    
    if (!$tem_conversa_recente) {
        // Primeira mensagem da conversa - sempre enviar resposta
        $enviar_resposta = true;
        error_log("[WEBHOOK WHATSAPP] 🆕 Primeira mensagem da conversa - enviando resposta");
    } else {
        // Verificar se a última mensagem foi há mais de 2 horas
        $ultima_mensagem = $conversa_info['ultima_mensagem'];
        $tempo_desde_ultima = time() - strtotime($ultima_mensagem);
        
        if ($tempo_desde_ultima > 7200) { // Mais de 2 horas
            $enviar_resposta = true;
            error_log("[WEBHOOK WHATSAPP] ⏰ Conversa retomada após " . round($tempo_desde_ultima/60) . " minutos - enviando resposta");
        } else {
            // Verificar se já foi enviada resposta automática hoje
            if ($respostas_automaticas == 0) {
                // Verificar se é uma mensagem que requer resposta específica
                $texto_lower = strtolower(trim($texto));
                $palavras_chave = ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi'];
                
                if (in_array($texto_lower, $palavras_chave)) {
                    $enviar_resposta = true;
                    error_log("[WEBHOOK WHATSAPP] 👋 Saudação detectada - enviando resposta");
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
                error_log("[WEBHOOK WHATSAPP] ❌ Falha na comunicação com IA: HTTP $http_code_ia");
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
                error_log("[WEBHOOK WHATSAPP] ❌ Erro HTTP ao enviar resposta: $http_code");
            }
        } catch (Exception $e) {
            error_log("[WEBHOOK WHATSAPP] ❌ Exceção ao enviar resposta: " . $e->getMessage());
        }
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
        'numero_whatsapp' => $numero
    ]);
} else {
    // Responder erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Evento inválido ou dados incompletos'
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
            'mensagem' => $texto,
            'mensagem_id' => $mensagem_id,
            'timestamp' => time()
        ];
        
        error_log("[WEBHOOK WHATSAPP] 🚀 Enviando notificação push para cliente $cliente_id");
        
        $ch = curl_init($push_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout baixo para não atrasar o webhook
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            error_log("[WEBHOOK WHATSAPP] ✅ Notificação push enviada com sucesso");
        } else {
            error_log("[WEBHOOK WHATSAPP] ⚠️ Erro ao enviar notificação push: HTTP $http_code");
        }
    } catch (Exception $e) {
        error_log("[WEBHOOK WHATSAPP] ❌ Exceção ao enviar notificação push: " . $e->getMessage());
    }
}

/**
 * Gera resposta padrão quando a IA falha
 */
function gerarRespostaPadrao($cliente_id, $cliente) {
    if ($cliente_id) {
        // Cliente encontrado - usar contact_name ou nome
        $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
        $resposta = "Olá $nome_cliente! 👋\n\n";
        $resposta .= "Recebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\n";
        $resposta .= "Como posso ajudá-lo hoje?";
    } else {
        // Cliente não encontrado - mensagem padrão do canal financeiro
        $resposta = "Olá! 👋\n\n";
        $resposta .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
        $resposta .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
        $resposta .= "Entre em contato através do número: *47 997309525*\n\n";
        $resposta .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
        $resposta .= "Por favor, digite seu *CPF* para localizar seu cadastro.\n\n";
        $resposta .= "Aguardo seu retorno! 😊";
    }
    
    return $resposta;
}
?> 