<?php
/**
 * WEBHOOK ESPECÍFICO PARA WHATSAPP
 * 
 * Este endpoint recebe mensagens do servidor WhatsApp
 * e as processa no sistema
 */

// Cabeçalhos anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// ===== SISTEMA DE CONTROLE DE CONTEXTO CONVERSACIONAL =====

/**
 * 🔄 REABERTURA AUTOMÁTICA DE CONVERSAS FECHADAS
 * Reabre automaticamente conversas fechadas há mais de 1 dia
 * NOVA LÓGICA: Cria nova conversa quando cliente envia mensagem em conversa arquivada
 */
function verificarReaberturaAutomatica($numero, $cliente_id, $mysqli) {
    // Verificar se há conversa arquivada (fechada)
    $sql_conversa_arquivada = "SELECT 
                                  m.id,
                                  m.data_hora,
                                  TIMESTAMPDIFF(HOUR, m.data_hora, NOW()) as horas_arquivada,
                                  COUNT(*) as total_mensagens
                              FROM mensagens_comunicacao m 
                              WHERE m.numero_whatsapp = ? 
                              AND m.cliente_id = ?
                              AND m.status_conversa = 'fechada'
                              GROUP BY m.data_hora
                              ORDER BY m.data_hora DESC 
                              LIMIT 1";
    
    $stmt = $mysqli->prepare($sql_conversa_arquivada);
    $stmt->bind_param('si', $numero, $cliente_id);
    $stmt->execute();
    $result_conversa_arquivada = $stmt->get_result();
    $conversa_arquivada = $result_conversa_arquivada->fetch_assoc();
    $stmt->close();
    
    if ($conversa_arquivada) {
        // Buscar canal para registrar a nova conversa
        $canal_id = 36;
        $canal_result = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%') LIMIT 1");
        if ($canal_result && $canal_result->num_rows > 0) {
            $canal = $canal_result->fetch_assoc();
            $canal_id = $canal['id'];
        }
        
        // Registrar nova conversa com histórico
        $data_hora = date('Y-m-d H:i:s');
        $mensagem_nova_conversa = "Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)";
        
        $sql_log = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                    VALUES (?, ?, ?, 'sistema', ?, 'enviado', 'enviado', ?)";
        
        $stmt_log = $mysqli->prepare($sql_log);
        $stmt_log->bind_param('iisss', $canal_id, $cliente_id, $mensagem_nova_conversa, $data_hora, $numero);
        $stmt_log->execute();
        $stmt_log->close();
        
        error_log("[WEBHOOK WHATSAPP] 🔄 Nova conversa criada - Cliente: $cliente_id, Mensagens arquivadas: {$conversa_arquivada['total_mensagens']}");
        
        return [
            'reaberta' => true,
            'nova_conversa' => true,
            'mensagens_arquivadas' => $conversa_arquivada['total_mensagens'],
            'horas_arquivada' => $conversa_arquivada['horas_arquivada']
        ];
    }
    
    return ['reaberta' => false];
}

/**
 * ⏸️ VERIFICA SE AUTOMAÇÃO ESTÁ PAUSADA POR ATENDENTE
 * Pausa automação por 24 horas quando cliente solicita atendente
 */
function verificarAutomacaoPausada($numero, $cliente_id, $mysqli) {
    // Verificar se há solicitação de atendente nas últimas 24 horas
    $sql_atendente_recente = "SELECT 
                                 m.data_hora,
                                 TIMESTAMPDIFF(HOUR, m.data_hora, NOW()) as horas_atras
                             FROM mensagens_comunicacao m 
                             WHERE m.numero_whatsapp = ? 
                             AND m.cliente_id = ?
                             AND m.direcao = 'enviado'
                             AND m.mensagem LIKE '%Solicitação de atendente registrada%'
                             AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                             ORDER BY m.data_hora DESC 
                             LIMIT 1";
    
    $stmt = $mysqli->prepare($sql_atendente_recente);
    $stmt->bind_param('si', $numero, $cliente_id);
    $stmt->execute();
    $result_atendente = $stmt->get_result();
    $atendente_recente = $result_atendente->fetch_assoc();
    $stmt->close();
    
    if ($atendente_recente) {
        $horas_restantes = 24 - $atendente_recente['horas_atras'];
        error_log("[WEBHOOK WHATSAPP] ⏸️ Automação pausada por atendente - Restam $horas_restantes horas");
        
        return [
            'pausada' => true,
            'horas_restantes' => $horas_restantes,
            'data_solicitacao' => $atendente_recente['data_hora']
        ];
    }
    
    return ['pausada' => false];
}

function verificarContextoConversacional($numero, $cliente_id, $texto, $mysqli) {
    $texto_lower = strtolower(trim($texto));
    
    // Verificar se a conversa está fechada
    $sql_conversa_fechada = "SELECT 
                                m.status_conversa,
                                COUNT(*) as total_mensagens
                            FROM mensagens_comunicacao m 
                            WHERE m.numero_whatsapp = ? 
                            AND m.cliente_id = ?
                            AND m.status_conversa = 'fechada'
                            GROUP BY m.status_conversa";
    
    $stmt = $mysqli->prepare($sql_conversa_fechada);
    $stmt->bind_param('si', $numero, $cliente_id);
    $stmt->execute();
    $result_conversa_fechada = $stmt->get_result();
    $conversa_fechada = $result_conversa_fechada->fetch_assoc();
    $stmt->close();
    
    $eh_conversa_fechada = $conversa_fechada ? true : false;
    
    // Verificar se já foi enviada resposta de faturas recentemente (últimas 2 horas)
    $sql_contexto = "SELECT 
                        m.mensagem, 
                        m.data_hora,
                        m.direcao,
                        TIMESTAMPDIFF(MINUTE, m.data_hora, NOW()) as minutos_atras
                    FROM mensagens_comunicacao m 
                    WHERE m.numero_whatsapp = ? 
                    AND m.direcao = 'enviado'
                    AND m.mensagem LIKE '%fatura%'
                    AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                    ORDER BY m.data_hora DESC 
                    LIMIT 1";
    
    $stmt = $mysqli->prepare($sql_contexto);
    $stmt->bind_param('s', $numero);
    $stmt->execute();
    $result_contexto = $stmt->get_result();
    $contexto_faturas = $result_contexto->fetch_assoc();
    $stmt->close();
    
    // Verificar se é uma solicitação de consolidação ou ação específica
    $palavras_consolidacao = ['boleto só', 'boleto so', 'único', 'unico', 'junto', 'consolidar', 'agregar', 'tudo junto'];
    $eh_solicitacao_consolidacao = false;
    foreach ($palavras_consolidacao as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_solicitacao_consolidacao = true;
            break;
        }
    }
    
    // Verificar se é uma solicitação fora do contexto
    $palavras_fora_contexto = ['negociação', 'negociacao', 'desconto', 'parcelamento', 'renegociar', 'renegociacao', 'atendente', 'atendimento', 'humano', 'pessoa'];
    $frases_atendente = ['falar com atendimento', 'falar com atendente', 'quero falar com atendente', 'quero falar com atendimento', 'preciso de atendimento', 'preciso de atendente'];
    
    $eh_fora_contexto = false;
    foreach ($palavras_fora_contexto as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_fora_contexto = true;
            break;
        }
    }
    
    // Verificar frases completas de atendimento
    foreach ($frases_atendente as $frase) {
        if (strpos($texto_lower, $frase) !== false) {
            $eh_fora_contexto = true;
            break;
        }
    }
    
    return [
        'eh_conversa_fechada' => $eh_conversa_fechada,
        'faturas_enviadas_recentemente' => $contexto_faturas ? true : false,
        'minutos_ultima_fatura' => $contexto_faturas ? $contexto_faturas['minutos_atras'] : null,
        'eh_solicitacao_consolidacao' => $eh_solicitacao_consolidacao,
        'eh_fora_contexto' => $eh_fora_contexto,
        'texto_original' => $texto_lower
    ];
}

function gerarFallbackInteligente($contexto, $cliente_id, $mysqli) {
    if ($contexto['eh_conversa_fechada']) {
        return "�� *Esta conversa foi fechada.*\n\n" .
               "Para reabrir a conversa e receber atendimento, entre em contato através do número: *47 997309525*\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    if ($contexto['eh_fora_contexto']) {
        return "Olá! 👋\n\n" .
               "📋 *Este canal é específico para consulta de faturas.*\n\n" .
               "Para negociações diferenciadas ou outros assuntos, digite *1* para falar com um atendente.\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    if ($contexto['eh_solicitacao_consolidacao']) {
        return "Olá! 👋\n\n" .
               "Entendi que você gostaria de consolidar suas faturas em um único pagamento.\n\n" .
               "Para essa solicitação específica, digite *1* para falar com um atendente que poderá ajudá-lo com essa negociação.\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    if ($contexto['faturas_enviadas_recentemente']) {
        $minutos = $contexto['minutos_ultima_fatura'];
        return "Olá! 👋\n\n" .
               "As informações das suas faturas foram enviadas há $minutos minutos.\n\n" .
               "Se precisar de algo específico ou negociação diferenciada, digite *1* para falar com um atendente.\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    // Fallback genérico para situações não compreendidas
    return "Olá! 👋\n\n" .
           "Não entendi completamente sua solicitação.\n\n" .
           "📋 *Este canal é para consulta de faturas.*\n\n" .
           "Para outros assuntos ou atendimento personalizado, digite *1* para falar com um atendente.\n\n" .
           "🤖 *Esta é uma mensagem automática*";
}

function processarSolicitacaoAtendente($numero, $cliente_id, $mysqli) {
    // Verificar se já existe uma solicitação de atendente em andamento
    $sql_atendente = "SELECT 
                        m.mensagem, 
                        m.data_hora,
                        TIMESTAMPDIFF(MINUTE, m.data_hora, NOW()) as minutos_atras
                    FROM mensagens_comunicacao m 
                    WHERE m.numero_whatsapp = ? 
                    AND m.direcao = 'enviado'
                    AND m.mensagem LIKE '%atendente%'
                    AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                    ORDER BY m.data_hora DESC 
                    LIMIT 1";
    
    $stmt = $mysqli->prepare($sql_atendente);
    $stmt->bind_param('s', $numero);
    $stmt->execute();
    $result_atendente = $stmt->get_result();
    $solicitacao_anterior = $result_atendente->fetch_assoc();
    $stmt->close();
    
    if ($solicitacao_anterior) {
        return "Sua solicitação de atendente já foi registrada! 📞\n\n" .
               "Um atendente entrará em contato em breve através do número: *47 997309525*\n\n" .
               "Aguarde o contato! 😊\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    // Buscar dados do cliente para melhor atendimento
    $dados_cliente = null;
    if ($cliente_id) {
        $sql_cliente = "SELECT nome, contact_name, cpf_cnpj, celular, telefone FROM clientes WHERE id = ? LIMIT 1";
        $stmt_cliente = $mysqli->prepare($sql_cliente);
        $stmt_cliente->bind_param('i', $cliente_id);
        $stmt_cliente->execute();
        $result_cliente = $stmt_cliente->get_result();
        $dados_cliente = $result_cliente->fetch_assoc();
        $stmt_cliente->close();
    }
    
    // Buscar canal WhatsApp financeiro (mesmo usado no webhook principal)
    $canal_id = 36; // Canal financeiro padrão
    $canal_result = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%') LIMIT 1");
    if ($canal_result && $canal_result->num_rows > 0) {
        $canal = $canal_result->fetch_assoc();
        $canal_id = $canal['id'];
    } else {
        // Criar canal WhatsApp financeiro se não existir
        $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
                        VALUES ('whatsapp', 'financeiro', 'WhatsApp Financeiro', 'conectado', NOW())");
        $canal_id = $mysqli->insert_id;
    }
    
    // Registrar solicitação de atendente com dados do cliente
    $data_hora = date('Y-m-d H:i:s');
    $nome_cliente = $dados_cliente ? ($dados_cliente['contact_name'] ?: $dados_cliente['nome']) : 'Cliente não identificado';
    $cpf_cliente = $dados_cliente ? $dados_cliente['cpf_cnpj'] : 'N/A';
    
    $mensagem_atendente = "Solicitação de atendente registrada - Cliente: $nome_cliente (CPF: $cpf_cliente) solicitou transferência para atendente humano";
    
    $sql_insert = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                   VALUES (?, ?, ?, 'sistema', ?, 'enviado', 'enviado', ?)";
    
    $stmt = $mysqli->prepare($sql_insert);
    $stmt->bind_param('iisss', $canal_id, $cliente_id, $mensagem_atendente, $data_hora, $numero);
    $stmt->execute();
    $stmt->close();
    
    // Gerar resposta personalizada
    $resposta = "✅ *Solicitação de atendente registrada com sucesso!*\n\n";
    
    if ($dados_cliente) {
        $resposta .= "👤 *Dados do cliente:*\n";
        $resposta .= "• Nome: $nome_cliente\n";
        $resposta .= "• CPF/CNPJ: $cpf_cliente\n";
        $resposta .= "• Telefone: " . ($dados_cliente['celular'] ?: $dados_cliente['telefone'] ?: 'N/A') . "\n\n";
    }
    
    $resposta .= "📞 *Atendimento:*\n";
    $resposta .= "Um atendente entrará em contato em breve através do número: *47 997309525*\n\n";
    $resposta .= "⏰ *Tempo estimado:* 5-15 minutos\n\n";
    $resposta .= "⏸️ *Automação pausada:* Por 24 horas para permitir atendimento personalizado\n\n";
    $resposta .= "💡 *Dica:* Tenha seu CPF/CNPJ em mãos para agilizar o atendimento!\n\n";
    $resposta .= "🤖 *Esta é uma mensagem automática*";
    
    return $resposta;
}

// ===== FIM DO SISTEMA DE CONTROLE DE CONTEXTO =====

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
    
    // Buscar conversa por número WhatsApp (mais preciso) - ANTES de salvar a mensagem atual
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
    
    // ===== IDENTIFICAÇÃO AUTOMÁTICA DO CANAL =====
    $canal_id = null;
    $canal_nome = null;
    $numero_origem = null;
    
    // Função para identificar canal por mensagem
    function identificarCanalPorMensagem($numero, $mensagem, $mysqli) {
        $canal_id = null;
        $canal_nome = null;
        $numero_origem = null;
        
        // 1. Tentar identificar por identificador do canal
        $canais_result = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id");
        
        if ($canais_result && $canais_result->num_rows > 0) {
            while ($canal = $canais_result->fetch_assoc()) {
                $identificador = $canal['identificador'];
                
                // Verificar se a mensagem veio deste canal específico
                if ($identificador && strpos($numero, $identificador) !== false) {
                    $canal_id = $canal['id'];
                    $canal_nome = $canal['nome_exibicao'];
                    $numero_origem = $identificador;
                    error_log("[WEBHOOK WHATSAPP] 📡 Canal identificado: {$canal_nome} (ID: $canal_id) - Número: $identificador");
                    break;
                }
            }
        }
        
        // 2. Se não identificou, tentar por conteúdo da mensagem
        if (!$canal_id) {
            if (strpos($mensagem, '554797146908') !== false || strpos($mensagem, 'canal 3000') !== false) {
                $canal_id = 36; // Canal 3000 - Pixel12Digital
                $canal_nome = 'Pixel12Digital';
                $numero_origem = '554797146908@c.us';
                error_log("[WEBHOOK WHATSAPP] 📡 Canal identificado por conteúdo: {$canal_nome} (ID: $canal_id)");
            } elseif (strpos($mensagem, '554797309525') !== false || strpos($mensagem, 'canal 3001') !== false) {
                $canal_id = 37; // Canal 3001 - Pixel - Comercial
                $canal_nome = 'Pixel - Comercial';
                $numero_origem = '554797309525@c.us';
                error_log("[WEBHOOK WHATSAPP] 📡 Canal identificado por conteúdo: {$canal_nome} (ID: $canal_id)");
            }
        }
        
        // 3. Se ainda não identificou, usar canal padrão
        if (!$canal_id) {
            $canal_padrao = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id LIMIT 1");
            if ($canal_padrao && $canal_padrao->num_rows > 0) {
                $canal = $canal_padrao->fetch_assoc();
                $canal_id = $canal['id'];
                $canal_nome = $canal['nome_exibicao'];
                $numero_origem = 'Canal Padrão';
                error_log("[WEBHOOK WHATSAPP] 📡 Usando canal padrão: {$canal_nome} (ID: $canal_id)");
            }
        }
        
        return [
            'canal_id' => $canal_id,
            'canal_nome' => $canal_nome,
            'numero_origem' => $numero_origem
        ];
    }
    
    // Aplicar a nova lógica de identificação de canal
    $canal_info = identificarCanalPorMensagem($numero, $texto, $mysqli);
    $canal_id = $canal_info['canal_id'];
    $canal_nome = $canal_info['canal_nome'];
    $numero_origem = $canal_info['numero_origem'];
    
    $sql = "INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, direcao, data_hora, status, numero_whatsapp, canal_id, canal_nome) 
            VALUES (?, ?, ?, 'recebido', NOW(), 'nao_lido', ?, ?, ?)";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('isssss', $cliente_id, $texto, $tipo, $numero, $canal_id, $canal_nome);
    
    if ($stmt->execute()) {
        $mensagem_id = $mysqli->insert_id;
        error_log("[WEBHOOK WHATSAPP] ✅ Mensagem salva - ID: $mensagem_id, Cliente: $cliente_id, Número: $numero, Canal: {$canal_nome}");
        
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
            // ATUALIZADO: Passa o tipo de mensagem para verificação
            enviarNotificacaoPush($cliente_id, $numero, $texto, $mensagem_id, $tipo);
        }
    } else {
        error_log("[WEBHOOK WHATSAPP] ❌ Erro ao salvar mensagem: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Preparar resposta automática baseada na situação
    $resposta_automatica = '';
    $enviar_resposta = false;
    $resposta_enviada = false; // Inicializar como false
    
    // ===== NOVA LÓGICA SIMPLIFICADA PARA CANAL FINANCEIRO =====
    
    // 0. 🔄 VERIFICAR REABERTURA AUTOMÁTICA (ANTES DE TUDO)
    if ($cliente_id) {
        $reabertura_info = verificarReaberturaAutomatica($numero, $cliente_id, $mysqli);
        if ($reabertura_info['reaberta']) {
            error_log("[WEBHOOK WHATSAPP] 🔄 Nova conversa criada após conversa arquivada - {$reabertura_info['mensagens_arquivadas']} mensagens no histórico");
            // Enviar mensagem informando que uma nova conversa foi criada
            $resposta_automatica = "🔄 *Nova conversa iniciada!*\n\n" .
                                  "Olá! Iniciei uma nova conversa e carreguei todo o histórico anterior.\n\n" .
                                  "Como posso ajudá-lo hoje? 😊\n\n" .
                                  "🤖 *Esta é uma mensagem automática*";
            $enviar_resposta = true;
        }
    }
    
    // 1. LÓGICA SIMPLIFICADA - SEMPRE RESPONDER
    if (!$enviar_resposta) {
        // SEMPRE enviar resposta para qualquer mensagem
        $enviar_resposta = true;
        error_log("[WEBHOOK WHATSAPP] 🔄 SEMPRE RESPONDER - Ativado para qualquer mensagem");
    }
    
    // Debug: Log do status da decisão
    error_log("[WEBHOOK WHATSAPP] 🔍 DEBUG - Status da decisão:");
    error_log("[WEBHOOK WHATSAPP] 🔍 - enviar_resposta: " . ($enviar_resposta ? 'true' : 'false'));
    error_log("[WEBHOOK WHATSAPP] 🔍 - tem_conversa_recente: " . ($tem_conversa_recente ? 'true' : 'false'));
    error_log("[WEBHOOK WHATSAPP] 🔍 - mensagens_automaticas: $mensagens_automaticas");
    error_log("[WEBHOOK WHATSAPP] 🔍 - total_mensagens: $total_mensagens");
    error_log("[WEBHOOK WHATSAPP] 🔍 - tipo_mensagem: $tipo");
    
    if ($enviar_resposta) {
        // Processar IA diretamente em vez de usar cURL
        try {
            error_log("[WEBHOOK WHATSAPP] 🤖 Processando IA diretamente para: $numero, texto: '$texto', tipo: '$tipo'");
            
            // Verificar se é mídia e gerar resposta específica
            if ($tipo === 'audio' || $tipo === 'image' || $tipo === 'video' || $tipo === 'document') {
                $resposta_automatica = gerarMensagemReforco();
                error_log("[WEBHOOK WHATSAPP] 🎵 Gerando resposta específica para mídia ($tipo)");
            } else {
                // SIMPLIFICAR: Sempre gerar resposta padrão para testar
                $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
                error_log("[WEBHOOK WHATSAPP] 🤖 Gerando resposta padrão para texto");
            }
            
            error_log("[WEBHOOK WHATSAPP] 🤖 Resposta gerada: " . substr($resposta_automatica, 0, 100) . "...");
            error_log("[WEBHOOK WHATSAPP] 🤖 Tamanho da resposta: " . strlen($resposta_automatica));
            
        } catch (Exception $e) {
            error_log("[WEBHOOK WHATSAPP] ❌ Exceção ao processar IA: " . $e->getMessage());
            // Fallback para resposta padrão
            $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
        }
    } else {
        error_log("[WEBHOOK WHATSAPP] 🔇 Não processando IA - enviar_resposta = false");
    }
    
    // Debug: Log do status da resposta
    error_log("[WEBHOOK WHATSAPP] 🔍 DEBUG - Status da resposta:");
    error_log("[WEBHOOK WHATSAPP] 🔍 - enviar_resposta: " . ($enviar_resposta ? 'true' : 'false'));
    error_log("[WEBHOOK WHATSAPP] 🔍 - resposta_automatica vazia: " . (empty($resposta_automatica) ? 'true' : 'false'));
    error_log("[WEBHOOK WHATSAPP] 🔍 - tamanho resposta: " . strlen($resposta_automatica));
    
    // Enviar resposta automática via WhatsApp
    error_log("[WEBHOOK WHATSAPP] 🔍 VERIFICANDO ENVIO - resposta_automatica: " . (empty($resposta_automatica) ? 'vazia' : 'preenchida') . ", enviar_resposta: " . ($enviar_resposta ? 'true' : 'false'));
    
    if ($resposta_automatica && $enviar_resposta) {
        error_log("[WEBHOOK WHATSAPP] 📤 INICIANDO ENVIO - Resposta: " . substr($resposta_automatica, 0, 50) . "...");
        try {
            // Usar URL do WhatsApp configurada no config.php
            $api_url = WHATSAPP_ROBOT_URL . "/send";
            $data_envio = [
                "to" => $numero,
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
                    $resposta_enviada = true; // Definir como true quando a resposta é enviada com sucesso
                    error_log("[WEBHOOK WHATSAPP] ✅ Resposta salva no banco e marcada como enviada");
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
        error_log("[WEBHOOK WHATSAPP] 🔇 Não enviando resposta automática - Resposta: " . ($resposta_automatica ? 'Sim' : 'Não') . ", Enviar: " . ($enviar_resposta ? 'Sim' : 'Não'));
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
        'resposta_enviada' => $resposta_enviada,
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
 * 🔍 VERIFICA SE DEVE ENVIAR NOTIFICAÇÃO
 * Considera status da conversa e tipo de mensagem
 */
function deveEnviarNotificacao($cliente_id, $numero, $tipo_mensagem, $mysqli) {
    // Se não tem cliente_id, não enviar notificação
    if (!$cliente_id) {
        return false;
    }
    
    // Verificar se a conversa está fechada
    $sql_conversa_fechada = "SELECT COUNT(*) as total 
                            FROM mensagens_comunicacao 
                            WHERE cliente_id = ? 
                            AND status_conversa = 'fechada' 
                            AND data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    
    $stmt = $mysqli->prepare($sql_conversa_fechada);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversa_fechada = $result->fetch_assoc();
    $stmt->close();
    
    // Se conversa está fechada, verificar tipo de mensagem
    if ($conversa_fechada['total'] > 0) {
        // Tipos de mensagem que DEVEM ser enviadas mesmo com conversa fechada
        $tipos_importantes = [
            'monitoramento',    // Alertas de sistema
            'agendamento',      // Lembretes de agendamento
            'fatura',           // Notificações de fatura
            'cobranca',         // Cobranças importantes
            'sistema',          // Mensagens do sistema
            'emergencia'        // Emergências
        ];
        
        // Se é um tipo importante, enviar notificação
        if (in_array($tipo_mensagem, $tipos_importantes)) {
            error_log("[WEBHOOK WHATSAPP] 📡 Notificação enviada (conversa fechada, mas tipo importante: $tipo_mensagem)");
            return true;
        }
        
        // Se não é tipo importante, não enviar notificação
        error_log("[WEBHOOK WHATSAPP] 🔇 Notificação bloqueada (conversa fechada, tipo: $tipo_mensagem)");
        return false;
    }
    
    // Se conversa está aberta, enviar notificação normalmente
    error_log("[WEBHOOK WHATSAPP] 📡 Notificação enviada (conversa aberta)");
    return true;
}

/**
 * 🚀 ENVIA NOTIFICAÇÃO PUSH PARA ATUALIZAÇÃO AUTOMÁTICA
 * Aciona atualização imediata do chat quando mensagem é recebida
 * ATUALIZADO: Verifica se deve enviar baseado no status da conversa
 */
function enviarNotificacaoPush($cliente_id, $numero, $texto, $mensagem_id, $tipo_mensagem = 'texto') {
    try {
        // Verificar se deve enviar notificação
        global $mysqli;
        if (!deveEnviarNotificacao($cliente_id, $numero, $tipo_mensagem, $mysqli)) {
            error_log("[WEBHOOK WHATSAPP] 🔇 Notificação push bloqueada (conversa fechada ou tipo não importante)");
            return;
        }
        
        // URL do endpoint de notificação push
        $push_url = ($GLOBALS['is_local'] ? 'http://localhost:8080/loja-virtual-revenda' : '') . '/painel/api/push_notification.php';
        
        $payload = [
            'action' => 'new_message',
            'cliente_id' => $cliente_id,
            'numero' => $numero,
            'texto' => $texto,
            'mensagem_id' => $mensagem_id,
            'tipo_mensagem' => $tipo_mensagem
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
        $resposta .= "🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n";
        $resposta .= "💰 Para consultar suas faturas, digite: faturas\n\n";
        $resposta .= "📞 Para outros assuntos ou falar com nossa equipe:\n";
        $resposta .= "Entre em contato diretamente: 47 997309525";
        
        return $resposta;
    } else {
        $resposta = "Olá! 👋\n\n";
        $resposta .= "🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n";
        $resposta .= "💰 Para consultar suas faturas, digite: faturas\n\n";
        $resposta .= "📞 Para outros assuntos ou falar com nossa equipe:\n";
        $resposta .= "Entre em contato diretamente: 47 997309525\n\n";
        $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        
        return $resposta;
    }
}

/**
 * Gera mensagem de reforço para mensagens subsequentes
 */
function gerarMensagemReforco() {
    $resposta = "🤖 Este é um canal exclusivo para cobranças automatizadas.\n\n";
    $resposta .= "💰 Para consultar faturas: digite \"faturas\"\n";
    $resposta .= "📞 Para outros assuntos: entre em contato diretamente com nossa equipe\n";
    $resposta .= "📱 Telefone: 47 997309525";
    
    return $resposta;
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
        
        // 2. Buscar faturas locais do cliente (PENDING e OVERDUE)
        $sql_faturas_locais = "SELECT asaas_payment_id, status, valor, vencimento 
                               FROM cobrancas 
                               WHERE cliente_id = $cliente_id 
                               AND status IN ('PENDING', 'OVERDUE')";
        $result_faturas_locais = $mysqli->query($sql_faturas_locais);
        
        $faturas_locais = [];
        if ($result_faturas_locais) {
            while ($fatura = $result_faturas_locais->fetch_assoc()) {
                $faturas_locais[$fatura['asaas_payment_id']] = $fatura;
            }
        }
        
        // 3. Buscar faturas do Asaas
        $faturas_asaas = buscarFaturasAsaas($asaas_customer_id);
        
        if (!$faturas_asaas['success']) {
            return $faturas_asaas;
        }
        
        $atualizacoes = 0;
        $novas_faturas = 0;
        
        // 4. Processar faturas do Asaas
        foreach ($faturas_asaas['payments'] as $payment) {
            $asaas_payment_id = $payment['id'];
            $status_asaas = $payment['status'];
            $valor_asaas = $payment['value'];
            $vencimento_asaas = $payment['dueDate'];
            
            // Converter status do Asaas para nosso formato
            $status_local = '';
            switch ($status_asaas) {
                case 'PENDING':
                    $status_local = 'PENDING';
                    break;
                case 'OVERDUE':
                    $status_local = 'OVERDUE';
                    break;
                case 'RECEIVED':
                case 'CONFIRMED':
                    $status_local = 'RECEIVED';
                    break;
                case 'CANCELLED':
                    $status_local = 'CANCELLED';
                    break;
                default:
                    $status_local = 'PENDING';
            }
            
            // Verificar se a fatura já existe localmente
            if (isset($faturas_locais[$asaas_payment_id])) {
                $fatura_local = $faturas_locais[$asaas_payment_id];
                
                // Atualizar se houver diferenças
                if ($fatura_local['status'] !== $status_local || 
                    $fatura_local['valor'] != $valor_asaas ||
                    $fatura_local['vencimento'] !== $vencimento_asaas) {
                    
                    $sql_update = "UPDATE cobrancas SET 
                                   status = '$status_local',
                                   valor = $valor_asaas,
                                   vencimento = '$vencimento_asaas'
                                   WHERE asaas_payment_id = '$asaas_payment_id'";
                    
                    if ($mysqli->query($sql_update)) {
                        $atualizacoes++;
                    }
                }
            } else {
                // Inserir nova fatura
                $sql_insert = "INSERT INTO cobrancas 
                              (cliente_id, asaas_payment_id, status, valor, vencimento, url_fatura) 
                              VALUES 
                              ($cliente_id, '$asaas_payment_id', '$status_local', $valor_asaas, '$vencimento_asaas', 'https://www.asaas.com/i/$asaas_payment_id')";
                
                if ($mysqli->query($sql_insert)) {
                    $novas_faturas++;
                }
            }
        }
        
        return [
            'success' => true,
            'message' => "Sincronização concluída",
            'atualizacoes' => $atualizacoes,
            'novas_faturas' => $novas_faturas
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
    
    // Mensagem final com contexto e instruções
    if ($total_vencidas > 0) {
        $resposta .= "⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n";
    }
    
    $resposta .= "💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n";
    $resposta .= "🤖 *Esta é uma mensagem automática*\n";
    $resposta .= "📞 Para atendimento personalizado ou negociações, digite *1* para falar com um atendente.";
    
    return $resposta;
}

/**
 * Busca faturas do Asaas para um cliente específico
 */
function buscarFaturasAsaas($asaas_customer_id) {
    try {
        $url = ASAAS_API_URL . "/payments?customer=" . $asaas_customer_id;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "access_token: " . ASAAS_API_KEY,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Erro na comunicação com Asaas: ' . $error
            ];
        }
        
        if ($http_code !== 200) {
            return [
                'success' => false,
                'message' => 'Erro HTTP do Asaas: ' . $http_code
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['data'])) {
            return [
                'success' => false,
                'message' => 'Resposta inválida do Asaas'
            ];
        }
        
        return [
            'success' => true,
            'payments' => $data['data']
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Exceção ao buscar faturas do Asaas: ' . $e->getMessage()
        ];
    }
}
?> 