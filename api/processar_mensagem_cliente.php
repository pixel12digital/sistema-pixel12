<?php
header('Content-Type: application/json');
require_once '../painel/config.php';
require_once '../painel/db.php';

// Receber dados da mensagem
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['from']) || !isset($input['message'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$numero_cliente = $input['from'];
$mensagem = trim($input['message']);
$tipo_mensagem = $input['type'] ?? 'text';

try {
    // Extrair número do cliente (remover @c.us)
    $numero_limpo = str_replace('@c.us', '', $numero_cliente);
    $numero_limpo = str_replace('55', '', $numero_limpo);
    
    // Buscar cliente pelo número
    $sql = "SELECT id, nome, celular FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
    $result = $mysqli->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        // Cliente não encontrado - enviar mensagem padrão
        $resposta = "Olá! Este é o contato financeiro da Pixel12 Digital.\n\n";
        $resposta .= "Para consultar suas faturas, digite \"faturas\" ou \"consulta\"\n";
        $resposta .= "Para links de pagamento, digite \"pagar\" ou \"pagamento\"\n";
        $resposta .= "Para abrir um ticket de atendimento, digite \"atendente\"\n\n";
        $resposta .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
        
        echo json_encode([
            'success' => true,
            'resposta' => $resposta,
            'tipo' => 'cliente_nao_encontrado'
        ]);
        exit;
    }
    
    $cliente = $result->fetch_assoc();
    $cliente_id = $cliente['id'];
    
    // Verificar se cliente está sendo monitorado
    $monitorado = $mysqli->query("SELECT monitorado FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1")->fetch_assoc();
    
    if (!$monitorado || $monitorado['monitorado'] != 1) {
        // Cliente não monitorado - enviar mensagem padrão
        $resposta = "Olá! Este é o contato financeiro da Pixel12 Digital.\n\n";
        $resposta .= "Para consultar suas faturas, digite \"faturas\" ou \"consulta\"\n";
        $resposta .= "Para links de pagamento, digite \"pagar\" ou \"pagamento\"\n";
        $resposta .= "Para abrir um ticket de atendimento, digite \"atendente\"\n\n";
        $resposta .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
        
        echo json_encode([
            'success' => true,
            'resposta' => $resposta,
            'tipo' => 'cliente_nao_monitorado'
        ]);
        exit;
    }
    
    // Salvar mensagem recebida
    $mensagem_escaped = $mysqli->real_escape_string($mensagem);
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES (1, $cliente_id, '$mensagem_escaped', '$tipo_mensagem', '$data_hora', 'recebido', 'recebido')";
    
    $mysqli->query($sql);
    
    // Processar mensagem e gerar resposta
    $resposta = processarMensagemCliente($cliente_id, $mensagem, $mysqli);
    
    echo json_encode([
        'success' => true,
        'resposta' => $resposta,
        'cliente_id' => $cliente_id,
        'tipo' => 'resposta_automatica'
    ]);

} catch (Exception $e) {
    error_log("Erro ao processar mensagem do cliente: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Processa a mensagem do cliente e retorna resposta apropriada
 */
function processarMensagemCliente($cliente_id, $mensagem, $mysqli) {
    $mensagem_lower = strtolower($mensagem);
    
    // Verificar palavras-chave
    if (strpos($mensagem_lower, 'fatura') !== false || 
        strpos($mensagem_lower, 'consulta') !== false || 
        strpos($mensagem_lower, 'faturas') !== false) {
        
        return buscarFaturasCliente($cliente_id, $mysqli);
        
    } elseif (strpos($mensagem_lower, 'pagar') !== false || 
              strpos($mensagem_lower, 'pagamento') !== false) {
        
        return buscarLinksPagamento($cliente_id, $mysqli);
        
    } elseif (strpos($mensagem_lower, 'atendente') !== false || 
              strpos($mensagem_lower, 'humano') !== false ||
              strpos($mensagem_lower, 'ticket') !== false) {
        
        return abrirTicketAtendimento($cliente_id, $mensagem, $mysqli);
        
    } else {
        // Mensagem padrão
        $resposta = "Olá! Como posso ajudá-lo?\n\n";
        $resposta .= "Para consultar suas faturas, digite \"faturas\" ou \"consulta\"\n";
        $resposta .= "Para links de pagamento, digite \"pagar\" ou \"pagamento\"\n";
        $resposta .= "Para abrir um ticket de atendimento, digite \"atendente\"\n\n";
        $resposta .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
        
        return $resposta;
    }
}

/**
 * Abre ticket de atendimento automaticamente
 */
function abrirTicketAtendimento($cliente_id, $mensagem_original, $mysqli) {
    try {
        // Buscar dados do cliente
        $cliente = $mysqli->query("SELECT nome, celular, email FROM clientes WHERE id = $cliente_id LIMIT 1")->fetch_assoc();
        
        if (!$cliente) {
            return "Erro ao identificar cliente. Por favor, tente novamente.";
        }
        
        // Gerar número do ticket
        $numero_ticket = 'TKT-' . date('Ymd') . '-' . str_pad($cliente_id, 4, '0', STR_PAD_LEFT);
        
        // Criar título do ticket
        $titulo = "Solicitação de Atendimento - " . $cliente['nome'];
        
        // Criar descrição do ticket
        $descricao = "Ticket aberto automaticamente via WhatsApp\n\n";
        $descricao .= "Cliente: " . $cliente['nome'] . "\n";
        $descricao .= "Telefone: " . $cliente['celular'] . "\n";
        $descricao .= "Email: " . ($cliente['email'] ?? 'Não informado') . "\n";
        $descricao .= "Data/Hora: " . date('d/m/Y H:i:s') . "\n\n";
        $descricao .= "Mensagem original: \"$mensagem_original\"\n\n";
        $descricao .= "Este ticket foi aberto automaticamente quando o cliente solicitou atendimento humano via WhatsApp.";
        
        // Inserir ticket no banco
        $titulo_escaped = $mysqli->real_escape_string($titulo);
        $descricao_escaped = $mysqli->real_escape_string($descricao);
        $numero_ticket_escaped = $mysqli->real_escape_string($numero_ticket);
        
        $sql = "INSERT INTO tickets (numero, cliente_id, titulo, descricao, status, prioridade, categoria, data_criacao, data_atualizacao) 
                VALUES ('$numero_ticket_escaped', $cliente_id, '$titulo_escaped', '$descricao_escaped', 'aberto', 'normal', 'financeiro', NOW(), NOW())";
        
        if (!$mysqli->query($sql)) {
            throw new Exception("Erro ao criar ticket: " . $mysqli->error);
        }
        
        $ticket_id = $mysqli->insert_id;
        
        // Log da criação do ticket
        $log_data = date('Y-m-d H:i:s') . " - Ticket $numero_ticket criado automaticamente para cliente $cliente_id via WhatsApp\n";
        file_put_contents('../painel/logs/tickets_automaticos.log', $log_data, FILE_APPEND);
        
        // Retornar mensagem de confirmação
        $resposta = "✅ Ticket de atendimento aberto com sucesso!\n\n";
        $resposta .= "📋 Número do ticket: $numero_ticket\n";
        $resposta .= "👤 Cliente: " . $cliente['nome'] . "\n";
        $resposta .= "📅 Data: " . date('d/m/Y H:i:s') . "\n\n";
        $resposta .= "Um de nossos atendentes entrará em contato em breve para ajudá-lo.\n\n";
        $resposta .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
        
        return $resposta;
        
    } catch (Exception $e) {
        error_log("Erro ao abrir ticket: " . $e->getMessage());
        
        return "Desculpe, houve um erro ao abrir o ticket. Por favor, tente novamente em alguns minutos ou entre em contato pelo telefone.";
    }
}

/**
 * Busca faturas do cliente
 */
function buscarFaturasCliente($cliente_id, $mysqli) {
    $sql = "SELECT 
                cob.id,
                cob.valor,
                cob.status,
                DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                cob.url_fatura
            FROM cobrancas cob
            WHERE cob.cliente_id = $cliente_id
            ORDER BY cob.vencimento DESC
            LIMIT 10";
    
    $result = $mysqli->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        return "Você não possui faturas cadastradas no momento.";
    }
    
    $resposta = "📋 Suas faturas:\n\n";
    
    while ($fatura = $result->fetch_assoc()) {
        $status = traduzirStatus($fatura['status']);
        $valor = number_format($fatura['valor'], 2, ',', '.');
        
        $resposta .= "Fatura #{$fatura['id']}\n";
        $resposta .= "Valor: R$ $valor\n";
        $resposta .= "Vencimento: {$fatura['vencimento_formatado']}\n";
        $resposta .= "Status: $status\n";
        
        if ($fatura['url_fatura']) {
            $resposta .= "Link: {$fatura['url_fatura']}\n";
        }
        
        $resposta .= "\n";
    }
    
    return $resposta;
}

/**
 * Busca links de pagamento
 */
function buscarLinksPagamento($cliente_id, $mysqli) {
    $sql = "SELECT 
                cob.id,
                cob.valor,
                cob.url_fatura,
                DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado
            FROM cobrancas cob
            WHERE cob.cliente_id = $cliente_id
            AND cob.status IN ('PENDING', 'OVERDUE')
            ORDER BY cob.vencimento ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        return "Você não possui faturas pendentes no momento.";
    }
    
    $resposta = "💳 Links para pagamento:\n\n";
    
    while ($fatura = $result->fetch_assoc()) {
        $valor = number_format($fatura['valor'], 2, ',', '.');
        
        $resposta .= "Fatura #{$fatura['id']} - R$ $valor\n";
        $resposta .= "Vencimento: {$fatura['vencimento_formatado']}\n";
        $resposta .= "{$fatura['url_fatura']}\n\n";
    }
    
    return $resposta;
}

/**
 * Traduz status da fatura
 */
function traduzirStatus($status) {
    $statusMap = [
        'PENDING' => 'Aguardando pagamento',
        'OVERDUE' => 'Vencida',
        'RECEIVED' => 'Paga',
        'CONFIRMED' => 'Confirmada',
        'CANCELLED' => 'Cancelada'
    ];
    return $statusMap[$status] ?? $status;
}
?> 