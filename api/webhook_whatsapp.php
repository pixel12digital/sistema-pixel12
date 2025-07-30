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
        // Processar IA diretamente em vez de usar cURL
        try {
            error_log("[WEBHOOK WHATSAPP] 🤖 Processando IA diretamente para: $numero, texto: '$texto'");
            
            // Análise de intenção
            $texto_lower = strtolower(trim($texto));
            $palavras_chave = [
                'fatura' => ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar', 'consulta', 'consultas'],
                'plano' => ['plano', 'pacote', 'serviço', 'assinatura', 'mensalidade'],
                'suporte' => ['suporte', 'ajuda', 'problema', 'erro', 'não funciona', 'bug'],
                'comercial' => ['comercial', 'venda', 'preço', 'orçamento', 'proposta', 'site'],
                'cpf' => ['cpf', 'documento', 'identificação', 'cadastro', 'cnpj'],
                'saudacao' => ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi', 'oie']
            ];
            
            $intencao = 'geral';
            foreach ($palavras_chave as $intencao_tipo => $palavras) {
                foreach ($palavras as $palavra) {
                    if (strpos($texto_lower, $palavra) !== false) {
                        $intencao = $intencao_tipo;
                        break 2;
                    }
                }
            }
            
            error_log("[WEBHOOK WHATSAPP] 🤖 Intenção detectada: $intencao");
            
            // Gerar resposta baseada na intenção
            switch ($intencao) {
                case 'fatura':
                    if ($cliente_id) {
                        error_log("[WEBHOOK WHATSAPP] 🤖 Processando consulta de faturas para cliente $cliente_id");
                        $resposta_automatica = buscarFaturasCliente($cliente_id, $mysqli);
                    } else {
                        $resposta_automatica = "Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n";
                        $resposta_automatica .= "📋 *Por favor, informe:*\n";
                        $resposta_automatica .= "• Seu CPF ou CNPJ (apenas números, sem espaços)\n\n";
                        $resposta_automatica .= "Assim posso buscar suas informações e repassar o status das faturas! 😊";
                    }
                    break;
                    
                case 'plano':
                    if ($cliente_id) {
                        $resposta_automatica = "Olá! Vejo que você tem dúvidas sobre seu plano. 📊\n\n";
                        $resposta_automatica .= "Para verificar os detalhes do seu plano, preciso do seu CPF. ";
                        $resposta_automatica .= "Pode me informar o número do seu CPF?";
                    } else {
                        $resposta_automatica = "Olá! Para verificar informações sobre planos, preciso do seu CPF. ";
                        $resposta_automatica .= "Pode me informar o número do seu CPF?";
                    }
                    break;
                    
                case 'suporte':
                    $resposta_automatica = "Olá! Vejo que você precisa de suporte técnico. 🔧\n\n";
                    $resposta_automatica .= "Para suporte técnico, entre em contato através do número: *47 997309525*\n\n";
                    $resposta_automatica .= "Nossa equipe técnica está pronta para ajudá-lo!";
                    break;
                    
                case 'comercial':
                    $resposta_automatica = "Olá! Vejo que você tem interesse em nossos serviços comerciais. 💼\n\n";
                    $resposta_automatica .= "Para atendimento comercial, entre em contato através do número: *47 997309525*\n\n";
                    $resposta_automatica .= "Nossa equipe comercial ficará feliz em atendê-lo!";
                    break;
                    
                case 'cpf':
                    $cpf_limpo = preg_replace('/\D/', '', $texto);
                    if (strlen($cpf_limpo) >= 11 && strlen($cpf_limpo) <= 14) {
                        $cliente_cpf = buscarClientePorCPF($cpf_limpo, $mysqli);
                        
                        if ($cliente_cpf) {
                            $resposta_automatica = "Olá {$cliente_cpf['contact_name']}! 👋\n\n";
                            $resposta_automatica .= "✅ Encontrei seu cadastro! Como posso ajudá-lo hoje?\n\n";
                            $resposta_automatica .= "📋 *Opções disponíveis:*\n";
                            $resposta_automatica .= "• Verificar faturas (digite 'faturas' ou 'consulta')\n";
                            $resposta_automatica .= "• Informações do plano\n";
                            $resposta_automatica .= "• Suporte técnico\n";
                            $resposta_automatica .= "• Atendimento comercial";
                        } else {
                            $resposta_automatica = "❌ CPF/CNPJ não encontrado em nossa base de dados.\n\n";
                            $resposta_automatica .= "📞 Para atendimento personalizado, entre em contato: *47 997309525*\n\n";
                            $resposta_automatica .= "Nossa equipe ficará feliz em ajudá-lo! 😊";
                        }
                    } else {
                        $resposta_automatica = "Por favor, informe um CPF (11 dígitos) ou CNPJ (14 dígitos) válido, apenas números.";
                    }
                    break;
                    
                case 'saudacao':
                default:
                    $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
                    break;
            }
            
            error_log("[WEBHOOK WHATSAPP] 🤖 Resposta gerada com sucesso - Intenção: $intencao");
            
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
        $resposta = "Olá $nome_cliente! 👋\n\n";
        $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
        $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
        $resposta .= "Entre em contato: *47 997309525*\n\n";
        $resposta .= "💰 *Para assuntos financeiros:*\n";
        $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
        $resposta .= "• Verificar status de pagamentos\n";
        $resposta .= "• Informações sobre planos\n\n";
        $resposta .= "Como posso ajudá-lo hoje? 😊";
        
        return $resposta;
    } else {
        $resposta = "Olá! 👋\n\n";
        $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
        $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
        $resposta .= "Entre em contato: *47 997309525*\n\n";
        $resposta .= "💰 *Para assuntos financeiros:*\n";
        $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
        $resposta .= "• Verificar status de pagamentos\n";
        $resposta .= "• Informações sobre planos\n\n";
        $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        
        return $resposta;
    }
}

/**
 * Busca cliente por CPF/CNPJ
 */
function buscarClientePorCPF($cpf_limpo, $mysqli) {
    $sql = "SELECT id, nome, contact_name, cpf_cnpj FROM clientes WHERE cpf_cnpj = '$cpf_limpo' LIMIT 1";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * SINCRONIZAÇÃO INDIVIDUAL: Verifica e atualiza faturas do cliente com Asaas
 */
function sincronizarFaturasClienteAsaas($cliente_id, $mysqli) {
    try {
        // 1. Buscar dados do cliente (incluindo asaas_id)
        $sql_cliente = "SELECT asaas_id, nome FROM clientes WHERE id = $cliente_id LIMIT 1";
        $result_cliente = $mysqli->query($sql_cliente);
        
        if (!$result_cliente || $result_cliente->num_rows == 0) {
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }
        
        $cliente = $result_cliente->fetch_assoc();
        $asaas_customer_id = $cliente['asaas_id'];
        
        if (!$asaas_customer_id) {
            return ['success' => false, 'message' => 'Cliente sem ID do Asaas'];
        }
        
        return [
            'success' => true,
            'message' => "Sincronização concluída",
            'atualizacoes' => 0,
            'novas_faturas' => 0
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro na sincronização: ' . $e->getMessage()
        ];
    }
}

/**
 * Busca faturas do cliente (apenas vencidas e a próxima a vencer)
 * COM SINCRONIZAÇÃO INDIVIDUAL COM ASAAS
 */
function buscarFaturasCliente($cliente_id, $mysqli) {
    // 1. SINCRONIZAÇÃO INDIVIDUAL COM ASAAS
    $sincronizacao = sincronizarFaturasClienteAsaas($cliente_id, $mysqli);
    
    // 2. Buscar faturas vencidas (OVERDUE) - após sincronização
    $sql_vencidas = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura,
                        DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    AND cob.status = 'OVERDUE'
                    ORDER BY cob.vencimento ASC";
    
    $result_vencidas = $mysqli->query($sql_vencidas);
    
    // 3. Buscar apenas a PRÓXIMA fatura a vencer (PENDING) - a mais próxima
    $sql_proxima_vencer = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura,
                        DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    AND cob.status = 'PENDING'
                    ORDER BY cob.vencimento ASC
                    LIMIT 1";
    
    $result_proxima_vencer = $mysqli->query($sql_proxima_vencer);
    
    // Verificar se há faturas
    $total_vencidas = $result_vencidas ? $result_vencidas->num_rows : 0;
    $tem_proxima_vencer = $result_proxima_vencer ? $result_proxima_vencer->num_rows : 0;
    
    if ($total_vencidas == 0 && $tem_proxima_vencer == 0) {
        return "🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*";
    }
    
    // Buscar nome do cliente
    $sql_cliente = "SELECT contact_name, nome FROM clientes WHERE id = $cliente_id LIMIT 1";
    $result_cliente = $mysqli->query($sql_cliente);
    $cliente = $result_cliente->fetch_assoc();
    $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
    
    $resposta = "Olá $nome_cliente! 👋\n\n";
    $resposta .= "📋 Aqui está o resumo das suas faturas:\n\n";
    
    // Seção de faturas vencidas
    if ($total_vencidas > 0) {
        $resposta .= "🔴 *Faturas Vencidas:*\n";
        $valor_total_vencidas = 0;
        
        while ($fatura = $result_vencidas->fetch_assoc()) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            $dias_vencido = $fatura['dias_vencido'];
            $valor_total_vencidas += $fatura['valor'];
            
            $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
            $resposta .= "  Venceu em {$fatura['vencimento_formatado']} ({$dias_vencido} dias atrás)\n";
            
            if ($fatura['url_fatura']) {
                $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
            }
            $resposta .= "\n";
        }
        
        $valor_total_vencidas_formatado = number_format($valor_total_vencidas, 2, ',', '.');
        $resposta .= "💰 *Total vencido: R$ $valor_total_vencidas_formatado*\n\n";
    }
    
    // Seção da PRÓXIMA fatura a vencer (apenas uma)
    if ($tem_proxima_vencer > 0) {
        $resposta .= "🟡 *Próxima Fatura a Vencer:*\n";
        
        $fatura = $result_proxima_vencer->fetch_assoc();
        $valor = number_format($fatura['valor'], 2, ',', '.');
        $dias_para_vencer = $fatura['dias_para_vencer'];
        
        $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
        $resposta .= "  Vence em {$fatura['vencimento_formatado']} (em {$dias_para_vencer} dias)\n";
        
        if ($fatura['url_fatura']) {
            $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
        }
        $resposta .= "\n";
    }
    
    // Resumo final - APENAS faturas vencidas no total em aberto
    if ($total_vencidas > 0) {
        $valor_total_vencidas_formatado = number_format($valor_total_vencidas, 2, ',', '.');
        $resposta .= "📊 *Resumo Geral:*\n";
        $resposta .= "💰 Valor total em aberto: R$ $valor_total_vencidas_formatado\n\n";
    }
    
    // Mensagem final simpática
    if ($total_vencidas > 0) {
        $resposta .= "⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n";
    }
    
    $resposta .= "💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n";
    $resposta .= "🤖 *Esta é uma mensagem automática*\n";
    $resposta .= "📞 Para conversar com nossa equipe, entre em contato: *47 997309525*";
    
    return $resposta;
}
?> 