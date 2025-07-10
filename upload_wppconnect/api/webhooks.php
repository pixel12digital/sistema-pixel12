<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/utils.php';

// Log de webhook para auditoria
function logWebhook($event, $data) {
    $logFile = __DIR__ . '/../logs/webhook_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " - Evento: $event - Dados: " . json_encode($data) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Recebe eventos do Asaas
$input = get_json_input();
$event = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? 'unknown';

// Log do evento recebido
logWebhook($event, $input);

// Validar se é um evento válido do Asaas
$validEvents = [
    'PAYMENT_RECEIVED',
    'PAYMENT_CONFIRMED', 
    'PAYMENT_OVERDUE',
    'PAYMENT_DELETED',
    'PAYMENT_RESTORED',
    'PAYMENT_REFUNDED',
    'PAYMENT_RECEIVED_IN_CASH_UNDONE',
    'PAYMENT_CHARGEBACK_REQUESTED',
    'PAYMENT_CHARGEBACK_DISPUTE',
    'PAYMENT_AWAITING_CHARGEBACK_REVERSAL',
    'PAYMENT_DUNNING_RECEIVED',
    'PAYMENT_DUNNING_REQUESTED',
    'SUBSCRIPTION_CREATED',
    'SUBSCRIPTION_UPDATED',
    'SUBSCRIPTION_DELETED',
    'SUBSCRIPTION_PAYMENT_RECEIVED',
    'SUBSCRIPTION_PAYMENT_OVERDUE',
    'SUBSCRIPTION_PAYMENT_DELETED',
    'SUBSCRIPTION_PAYMENT_RESTORED',
    'SUBSCRIPTION_PAYMENT_REFUNDED',
    'SUBSCRIPTION_PAYMENT_RECEIVED_IN_CASH_UNDONE',
    'SUBSCRIPTION_PAYMENT_CHARGEBACK_REQUESTED',
    'SUBSCRIPTION_PAYMENT_CHARGEBACK_DISPUTE',
    'SUBSCRIPTION_PAYMENT_AWAITING_CHARGEBACK_REVERSAL',
    'SUBSCRIPTION_PAYMENT_DUNNING_RECEIVED',
    'SUBSCRIPTION_PAYMENT_DUNNING_REQUESTED'
];

if (!in_array($event, $validEvents)) {
    logWebhook('INVALID_EVENT', ['event' => $event, 'input' => $input]);
    json_response(['message' => 'Evento inválido'], 400);
}

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão com banco: " . $mysqli->connect_error);
    }
    
    // Processar eventos de pagamento
    if (strpos($event, 'PAYMENT_') === 0) {
        $payment = $input['payment'] ?? null;
        if (!$payment) {
            throw new Exception("Dados de pagamento não encontrados");
        }
        
        $asaas_id = $payment['id'] ?? null;
        $status = $payment['status'] ?? null;
        $customer = $payment['customer'] ?? null;
        $value = $payment['value'] ?? null;
        $dueDate = $payment['dueDate'] ?? null;
        $paymentDate = $payment['paymentDate'] ?? null;
        
        if (!$asaas_id || !$status) {
            throw new Exception("ID ou status do pagamento não encontrados");
        }
        
        // Buscar cliente local
        $cliente_id = null;
        if ($customer) {
            $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
            $stmt->bind_param('s', $customer);
            $stmt->execute();
            $stmt->bind_result($cliente_id);
            $stmt->fetch();
            $stmt->close();
        }
        
        // Atualizar ou inserir na tabela cobrancas (principal)
        $stmt = $mysqli->prepare("
            INSERT INTO cobrancas (
                asaas_payment_id, cliente_id, valor, status, vencimento, 
                data_pagamento, data_atualizacao, descricao, tipo, url_fatura
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status),
                data_pagamento = VALUES(data_pagamento),
                data_atualizacao = VALUES(data_atualizacao)
        ");
        
        $descricao = $payment['description'] ?? '';
        $tipo = $payment['billingType'] ?? '';
        $url_fatura = $payment['invoiceUrl'] ?? '';
        
        $stmt->bind_param('sidsdsssss',
            $asaas_id,
            $cliente_id,
            $value,
            $status,
            $dueDate,
            $paymentDate,
            $descricao,
            $tipo,
            $url_fatura
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar cobrança: " . $stmt->error);
        }
        $stmt->close();
        
        // Se for pagamento confirmado, atualizar também na tabela faturas
        if ($status === 'RECEIVED' || $status === 'CONFIRMED') {
            $stmt = $mysqli->prepare("
                INSERT INTO faturas (cliente_id, asaas_id, valor, status, due_date, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    updated_at = VALUES(updated_at)
            ");
            $stmt->bind_param('issds', $cliente_id, $asaas_id, $value, $status, $dueDate);
            $stmt->execute();
            $stmt->close();
        }
        
        logWebhook('PAYMENT_PROCESSED', [
            'asaas_id' => $asaas_id,
            'status' => $status,
            'cliente_id' => $cliente_id
        ]);
    }
    
    // Processar eventos de assinatura
    if (strpos($event, 'SUBSCRIPTION_') === 0) {
        $subscription = $input['subscription'] ?? null;
        if (!$subscription) {
            throw new Exception("Dados de assinatura não encontrados");
        }
        
        $asaas_id = $subscription['id'] ?? null;
        $status = $subscription['status'] ?? null;
        $customer = $subscription['customer'] ?? null;
        $cycle = $subscription['cycle'] ?? null;
        $startDate = $subscription['startDate'] ?? null;
        $nextDueDate = $subscription['nextDueDate'] ?? null;
        
        if (!$asaas_id || !$status) {
            throw new Exception("ID ou status da assinatura não encontrados");
        }
        
        // Buscar cliente local
        $cliente_id = null;
        if ($customer) {
            $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
            $stmt->bind_param('s', $customer);
            $stmt->execute();
            $stmt->bind_result($cliente_id);
            $stmt->fetch();
            $stmt->close();
        }
        
        // Atualizar ou inserir na tabela assinaturas
        $stmt = $mysqli->prepare("
            INSERT INTO assinaturas (
                cliente_id, asaas_id, status, periodicidade, start_date, 
                next_due_date, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status),
                next_due_date = VALUES(next_due_date),
                updated_at = VALUES(updated_at)
        ");
        
        $stmt->bind_param('isssss',
            $cliente_id,
            $asaas_id,
            $status,
            $cycle,
            $startDate,
            $nextDueDate
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar assinatura: " . $stmt->error);
        }
        $stmt->close();
        
        logWebhook('SUBSCRIPTION_PROCESSED', [
            'asaas_id' => $asaas_id,
            'status' => $status,
            'cliente_id' => $cliente_id
        ]);
    }
    
    $mysqli->close();
    
    json_response([
        'message' => 'Webhook processado com sucesso',
        'event' => $event,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logWebhook('ERROR', [
        'event' => $event,
        'error' => $e->getMessage(),
        'input' => $input
    ]);
    
    json_response([
        'message' => 'Erro ao processar webhook: ' . $e->getMessage()
    ], 500);
}
?> 