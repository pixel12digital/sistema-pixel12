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
            error_log("[WEBHOOK WHATSAPP] Cliente criado automaticamente - ID: $cliente_id, Número: $numero_para_salvar");
        } else {
            error_log("[WEBHOOK WHATSAPP] Erro ao criar cliente: " . $mysqli->error);
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