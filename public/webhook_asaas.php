<?php
/**
 * Webhook do Asaas - Endpoint principal
 * 
 * Este arquivo recebe os webhooks do Asaas e os processa adequadamente.
 * Configurado em: https://asaas.com/customerConfigurations/webhooks
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../painel/config.php';
require_once __DIR__ . '/../painel/db.php';

// Log do webhook para auditoria
function logWebhook($event, $data) {
    $logFile = __DIR__ . '/../logs/webhook_asaas_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " - Evento: $event - Dados: " . json_encode($data) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Função para resposta JSON
function json_response($data, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode($data);
    exit;
}

try {
    // Receber dados do webhook
    $input = file_get_contents('php://input');
    $payload = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logWebhook('INVALID_JSON', ['error' => json_last_error_msg(), 'input' => $input]);
        json_response(['error' => 'JSON inválido'], 400);
    }
    
    // Extrair evento e dados
    $event = $payload['event'] ?? 'UNKNOWN';
    $payment = $payload['payment'] ?? null;
    $subscription = $payload['subscription'] ?? null;
    
    // Log do evento recebido
    logWebhook($event, $payload);
    
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
        'SUBSCRIPTION_PAYMENT_REFUNDED'
    ];
    
    if (!in_array($event, $validEvents)) {
        logWebhook('INVALID_EVENT', ['event' => $event, 'payload' => $payload]);
        json_response(['error' => 'Evento inválido'], 400);
    }
    
    // Processar eventos de pagamento
    if (strpos($event, 'PAYMENT_') === 0 && $payment) {
        $asaas_id = $payment['id'] ?? null;
        $status = $payment['status'] ?? null;
        $customer = $payment['customer'] ?? null;
        $value = $payment['value'] ?? null;
        $dueDate = $payment['dueDate'] ?? null;
        $paymentDate = $payment['paymentDate'] ?? null;
        $description = $payment['description'] ?? '';
        $billingType = $payment['billingType'] ?? '';
        $invoiceUrl = $payment['invoiceUrl'] ?? null;
        
        if (!$asaas_id || !$status) {
            throw new Exception("ID ou status do pagamento não encontrados");
        }
        
        // Buscar cliente local pelo ID do Asaas
        $cliente_id = null;
        if ($customer) {
            $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
            $stmt->bind_param('s', $customer);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $cliente_id = $row['id'];
            }
            $stmt->close();
        }
        
        // Atualizar ou inserir na tabela cobrancas
        $stmt = $mysqli->prepare("
            INSERT INTO cobrancas (
                asaas_payment_id, cliente_id, valor, status, vencimento, 
                data_pagamento, data_atualizacao, descricao, tipo, url_fatura
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status),
                data_pagamento = VALUES(data_pagamento),
                data_atualizacao = VALUES(data_atualizacao),
                url_fatura = VALUES(url_fatura)
        ");
        
        $stmt->bind_param('sisssssss',
            $asaas_id,
            $cliente_id,
            $value,
            $status,
            $dueDate,
            $paymentDate,
            $description,
            $billingType,
            $invoiceUrl
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar cobrança: " . $stmt->error);
        }
        $stmt->close();
        
        logWebhook('PAYMENT_PROCESSED', [
            'asaas_id' => $asaas_id,
            'status' => $status,
            'cliente_id' => $cliente_id,
            'valor' => $value
        ]);
    }
    
    // Processar eventos de assinatura
    if (strpos($event, 'SUBSCRIPTION_') === 0 && $subscription) {
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
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $cliente_id = $row['id'];
            }
            $stmt->close();
        }
        
        // Verificar se tabela assinaturas existe, se não existir, criar
        $tableCheck = $mysqli->query("SHOW TABLES LIKE 'assinaturas'");
        if ($tableCheck->num_rows == 0) {
            $createTable = "
                CREATE TABLE assinaturas (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    cliente_id INT,
                    asaas_id VARCHAR(255) NOT NULL UNIQUE,
                    status VARCHAR(50) NOT NULL,
                    periodicidade VARCHAR(20),
                    start_date DATE,
                    next_due_date DATE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX(cliente_id),
                    INDEX(asaas_id)
                )
            ";
            $mysqli->query($createTable);
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
    
    // Resposta de sucesso
    json_response([
        'success' => true,
        'message' => 'Webhook processado com sucesso',
        'event' => $event,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Log do erro
    logWebhook('ERROR', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'input' => $input ?? 'N/A'
    ]);
    
    // Resposta de erro
    json_response([
        'error' => 'Erro interno do servidor',
        'message' => $e->getMessage()
    ], 500);
} finally {
    // Fechar conexão com banco se existir
    if (isset($mysqli) && $mysqli) {
        $mysqli->close();
    }
}
?> 