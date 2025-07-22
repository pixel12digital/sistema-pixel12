<?php
/**
 * WEBHOOK ESPECÍFICO PARA WHATSAPP
 * 
 * Este endpoint recebe mensagens do servidor WhatsApp
 * e as processa no sistema
 */

header('Content-Type: application/json');
require_once '../painel/config.php';
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
    
    // Buscar cliente pelo número com múltiplos formatos
    $numero_limpo = preg_replace('/\D/', '', $numero);
    
    // Tentar diferentes formatos de busca
    $formatos_busca = [
        $numero_limpo,
        ltrim($numero_limpo, '55'), // Remove código do país
        substr($numero_limpo, -11), // Últimos 11 dígitos
        substr($numero_limpo, -10)  // Últimos 10 dígitos
    ];
    
    $cliente_id = null;
    $cliente = null;
    
    foreach ($formatos_busca as $formato) {
        if (strlen($formato) >= 10) { // Mínimo 10 dígitos
            $sql = "SELECT id, nome, celular FROM clientes 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                    LIMIT 1";
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                $cliente_id = $cliente['id'];
                error_log("[WEBHOOK WHATSAPP] Cliente encontrado com formato $formato - ID: $cliente_id, Nome: {$cliente['nome']}");
                break;
            }
        }
    }
    
    // Cadastro em sistema de APROVAÇÃO MANUAL (similar ao Kommo CRM)
    if (!$cliente_id) {
        // Formatar número para salvar (remover código do país se presente)
        $numero_para_salvar = $numero_limpo;
        if (strpos($numero_limpo, "55") === 0 && strlen($numero_limpo) > 11) {
            $numero_para_salvar = substr($numero_limpo, 2);
        }
        
        // Formatar número para exibição
        $numero_formatado = $numero_para_salvar;
        if (strlen($numero_formatado) == 11) {
            $numero_formatado = '(' . substr($numero_formatado, 0, 2) . ') ' . 
                              substr($numero_formatado, 2, 5) . '-' . 
                              substr($numero_formatado, 7);
        } elseif (strlen($numero_formatado) == 10) {
            $numero_formatado = '(' . substr($numero_formatado, 0, 2) . ') ' . 
                              substr($numero_formatado, 2, 4) . '-' . 
                              substr($numero_formatado, 6);
        }
        
        // Verificar se já existe na tabela de pendentes
        $sql_check_pendente = "SELECT id, total_mensagens FROM clientes_pendentes 
                              WHERE numero_whatsapp = '" . $mysqli->real_escape_string($numero_para_salvar) . "' 
                              AND status = 'pendente' LIMIT 1";
        $result_pendente = $mysqli->query($sql_check_pendente);
        
        if ($result_pendente && $result_pendente->num_rows > 0) {
            // Cliente já está pendente - atualizar informações
            $pendente = $result_pendente->fetch_assoc();
            $cliente_pendente_id = $pendente['id'];
            $novo_total = $pendente['total_mensagens'] + 1;
            
            $sql_update_pendente = "UPDATE clientes_pendentes SET 
                                   ultima_mensagem = '" . $mysqli->real_escape_string($texto) . "',
                                   data_ultima_mensagem = '$data_hora',
                                   total_mensagens = $novo_total
                                   WHERE id = $cliente_pendente_id";
            $mysqli->query($sql_update_pendente);
            
            error_log("[WEBHOOK WHATSAPP] 🟡 Cliente pendente atualizado - ID: $cliente_pendente_id, Total mensagens: $novo_total");
        } else {
            // Novo cliente - criar na tabela de pendentes
            $texto_escaped = $mysqli->real_escape_string($texto);
            $sql_pendente = "INSERT INTO clientes_pendentes 
                           (numero_whatsapp, numero_formatado, primeira_mensagem, data_primeira_mensagem, 
                            ultima_mensagem, data_ultima_mensagem, dados_extras) 
                           VALUES (
                               '" . $mysqli->real_escape_string($numero_para_salvar) . "',
                               '" . $mysqli->real_escape_string($numero_formatado) . "',
                               '$texto_escaped', '$data_hora', 
                               '$texto_escaped', '$data_hora',
                               '" . $mysqli->real_escape_string(json_encode(['numero_original' => $numero, 'webhook_data' => $data])) . "'
                           )";
            
            if ($mysqli->query($sql_pendente)) {
                $cliente_pendente_id = $mysqli->insert_id;
                error_log("[WEBHOOK WHATSAPP] 🆕 NOVO CLIENTE PENDENTE - ID: $cliente_pendente_id, Número: $numero_formatado");
            } else {
                error_log("[WEBHOOK WHATSAPP] ❌ Erro ao criar cliente pendente: " . $mysqli->error);
                // Responder erro e sair
                echo json_encode(['success' => false, 'error' => 'Erro interno']);
                exit;
            }
        }
        
        // Salvar mensagem na tabela de mensagens pendentes
        if (isset($cliente_pendente_id)) {
            $texto_escaped = $mysqli->real_escape_string($texto);
            $tipo_escaped = $mysqli->real_escape_string($tipo);
            $webhook_data_escaped = $mysqli->real_escape_string(json_encode($data));
            
            $sql_msg_pendente = "INSERT INTO mensagens_pendentes 
                               (cliente_pendente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, dados_webhook) 
                               VALUES ($cliente_pendente_id, '" . $mysqli->real_escape_string($numero_para_salvar) . "', 
                                      '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', '$webhook_data_escaped')";
            
            if ($mysqli->query($sql_msg_pendente)) {
                error_log("[WEBHOOK WHATSAPP] 📝 Mensagem pendente salva para cliente pendente ID: $cliente_pendente_id");
            } else {
                error_log("[WEBHOOK WHATSAPP] ❌ Erro ao salvar mensagem pendente: " . $mysqli->error);
            }
        }
        
        // Resposta de sucesso para cliente pendente
        echo json_encode([
            'success' => true,
            'message' => 'Cliente salvo como pendente para aprovação',
            'cliente_pendente_id' => $cliente_pendente_id ?? null,
            'status' => 'pendente'
        ]);
        exit; // Importante: sair aqui para não continuar o processamento
    } else {
        error_log("[WEBHOOK WHATSAPP] ✅ Cliente existente encontrado - ID: $cliente_id, Nome: {$cliente['nome']}");
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
        error_log("[WEBHOOK WHATSAPP] Mensagem salva - ID: $mensagem_id, Cliente: $cliente_id, Número: $numero");
        require_once '../painel/cache_invalidator.php';
        if ($cliente_id) {
            invalidate_message_cache($cliente_id);
            // Forçar limpeza adicional para atualização imediata
            if (function_exists('cache_forget')) {
                cache_forget("conversas_recentes");
                cache_forget("mensagens_html_{$cliente_id}");
                cache_forget("historico_html_{$cliente_id}");
            }
        }
    } else {
        error_log("[WEBHOOK WHATSAPP] Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Responder sucesso sem enviar resposta automática
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem processada com sucesso',
        'cliente_id' => $cliente_id,
        'mensagem_id' => $mensagem_id ?? null
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