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
        substr($numero_limpo, 2, 2) . '9' . substr($numero_limpo, 4), // Sem código + 9 (4796164699)
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
    
    // Salvar mensagem recebida
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
    
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
        }
    } else {
        error_log("[WEBHOOK WHATSAPP] ❌ Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Preparar resposta automática baseada na situação
    $resposta_automatica = '';
    
    if ($cliente_id) {
        // Cliente encontrado - usar contact_name ou nome
        $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
        $resposta_automatica = "Olá $nome_cliente! 👋\n\n";
        $resposta_automatica .= "Recebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\n";
        $resposta_automatica .= "Como posso ajudá-lo hoje?";
        
        error_log("[WEBHOOK WHATSAPP] 👤 Resposta para cliente conhecido: $nome_cliente");
    } else {
        // Cliente não encontrado - mensagem padrão do canal financeiro
        $resposta_automatica = "Olá! 👋\n\n";
        $resposta_automatica .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
        $resposta_automatica .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
        $resposta_automatica .= "Entre em contato através do número: *47 997309525*\n\n";
        $resposta_automatica .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
        $resposta_automatica .= "Por favor, digite seu *CPF* para localizar seu cadastro.\n\n";
        $resposta_automatica .= "Aguardo seu retorno! 😊";
        
        error_log("[WEBHOOK WHATSAPP] 🆕 Resposta para cliente não encontrado");
    }
    
    // Enviar resposta automática via WhatsApp
    if ($resposta_automatica) {
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
                    
                    // Salvar resposta enviada
                    $resposta_escaped = $mysqli->real_escape_string($resposta_automatica);
                    $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                                    VALUES ($canal_id, " . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\")";
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
        'resposta_enviada' => !empty($resposta_automatica)
    ]);
} else {
    // Responder erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Evento inválido ou dados incompletos'
    ]);
}
?> 