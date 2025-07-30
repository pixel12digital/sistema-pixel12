<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../painel/db.php';

// Função para formatar número WhatsApp (garante sempre código +55 do Brasil)
function ajustarNumeroWhatsapp($numero) {
    // Remover todos os caracteres não numéricos
    $numero = preg_replace('/\D/', '', $numero);
    
    // Se já tem código do país (55), remover para processar
    if (strpos($numero, '55') === 0) {
        $numero = substr($numero, 2);
    }
    
    // Para números muito longos, pegar apenas os últimos 11 dígitos (DDD + telefone)
    if (strlen($numero) > 11) {
        $numero = substr($numero, -11);
    }
    
    // Verificar se tem pelo menos DDD (2 dígitos) + número (mínimo 7 dígitos)
    if (strlen($numero) < 9) {
        return null; // Número muito curto
    }
    
    // Extrair DDD e número
    $ddd = substr($numero, 0, 2);
    $telefone = substr($numero, 2);
    
    // Verificar se o DDD é válido (deve ser um DDD brasileiro válido)
    $ddds_validos = ['11','12','13','14','15','16','17','18','19','21','22','24','27','28','31','32','33','34','35','37','38','41','42','43','44','45','46','47','48','49','51','53','54','55','61','62','63','64','65','66','67','68','69','71','73','74','75','77','79','81','82','83','84','85','86','87','88','89','91','92','93','94','95','96','97','98','99'];
    
    if (!in_array($ddd, $ddds_validos)) {
        return null; // DDD inválido
    }
    
    // NUNCA ADICIONAR 9 - usar exatamente o número como está no banco
    // Verificar se o número final é válido (deve ter 7, 8 ou 9 dígitos)
    if (strlen($telefone) < 7 || strlen($telefone) > 9) {
        return null; // Número inválido
    }
    
    // GARANTIR SEMPRE o código +55 do Brasil + DDD + número (exatamente como está)
    return '55' . $ddd . $telefone;
}

// Função para log de webhooks
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
    'PAYMENT_RECEIVED_IN_CASH',
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
        
        // Enviar notificação WhatsApp quando cobrança for marcada como recebida
        if (($status === 'RECEIVED' || $status === 'RECEIVED_IN_CASH') && $cliente_id) {
            try {
                // Buscar dados do cliente
                $stmt = $mysqli->prepare("SELECT nome, contact_name, celular FROM clientes WHERE id = ? LIMIT 1");
                $stmt->bind_param('i', $cliente_id);
                $stmt->execute();
                $cliente = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if ($cliente && !empty($cliente['celular'])) {
                    // Buscar canal WhatsApp (ID 36 = Financeiro)
                    $canal_id = 36;
                    
                    // Formatar número WhatsApp
                    $numero_formatado = ajustarNumeroWhatsapp($cliente['celular']);
                    if (!$numero_formatado) {
                        logWebhook('WHATSAPP_INVALID_NUMBER', [
                            'cliente_id' => $cliente_id,
                            'celular_original' => $cliente['celular'],
                            'asaas_id' => $asaas_id
                        ]);
                        // Pular este cliente se número inválido
                    } else {
                        // Preparar mensagem de confirmação
                        $nome = $cliente['contact_name'] ?: $cliente['nome'];
                        $valor_formatado = number_format($value, 2, ',', '.');
                        $data_pagamento_formatada = $paymentDate ? date('d/m/Y', strtotime($paymentDate)) : date('d/m/Y');
                        $data_vencimento_formatada = $dueDate ? date('d/m/Y', strtotime($dueDate)) : 'N/A';
                        
                        $mensagem = "✅ *Pagamento Confirmado!*\n\n";
                        $mensagem .= "Olá {$nome}!\n\n";
                        $mensagem .= "Recebemos seu pagamento de *R$ {$valor_formatado}*\n";
                        $mensagem .= "Data do pagamento: {$data_pagamento_formatada}\n";
                        $mensagem .= "Vencimento original: {$data_vencimento_formatada}\n";
                        $mensagem .= "Referente à cobrança #{$asaas_id}\n\n";
                        $mensagem .= "Obrigado pela confiança! 🙏\n\n";
                        $mensagem .= "Esta é uma mensagem automática.";
                        
                        // Salvar mensagem no banco
                        $data_hora = date('Y-m-d H:i:s');
                        $mensagem_escaped = $mysqli->real_escape_string($mensagem);
                        $celular_escaped = $mysqli->real_escape_string($cliente['celular']);
                        
                        $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES ($canal_id, $cliente_id, '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'enviado')";
                        
                        if ($mysqli->query($sql)) {
                            // Enviar via WhatsApp (se houver integração configurada)
                            // Aqui você pode adicionar a chamada para sua API de WhatsApp
                            // Por exemplo: enviarWhatsApp($cliente['celular'], $mensagem);
                            
                            // Enviar mensagem via API do WhatsApp
                            try {
                                $whatsapp_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';
                                $api_url = $whatsapp_url . "/send/text";
                                
                                $api_data = [
                                    'sessionName' => 'default',
                                    'number' => $numero_formatado,
                                    'message' => $mensagem
                                ];
                                
                                $ch = curl_init($api_url);
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
                                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                                
                                $api_response = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                curl_close($ch);
                                
                                if ($api_response && $http_code === 200) {
                                    $api_result = json_decode($api_response, true);
                                    if ($api_result && isset($api_result['success']) && $api_result['success']) {
                                        // Atualizar mensagem com ID do WhatsApp se disponível
                                        if (isset($api_result['messageId'])) {
                                            $mysqli->query("UPDATE mensagens_comunicacao SET whatsapp_message_id = '" . $mysqli->real_escape_string($api_result['messageId']) . "' WHERE id = " . $mysqli->insert_id);
                                        }
                                        
                                        logWebhook('WHATSAPP_NOTIFICATION_SENT', [
                                            'cliente_id' => $cliente_id,
                                            'celular' => $cliente['celular'],
                                            'asaas_id' => $asaas_id,
                                            'status' => $status,
                                            'whatsapp_response' => $api_result
                                        ]);
                                    } else {
                                        logWebhook('WHATSAPP_API_ERROR', [
                                            'cliente_id' => $cliente_id,
                                            'celular' => $cliente['celular'],
                                            'asaas_id' => $asaas_id,
                                            'api_response' => $api_response,
                                            'http_code' => $http_code
                                        ]);
                                    }
                                } else {
                                    logWebhook('WHATSAPP_HTTP_ERROR', [
                                        'cliente_id' => $cliente_id,
                                        'celular' => $cliente['celular'],
                                        'asaas_id' => $asaas_id,
                                        'http_code' => $http_code,
                                        'api_response' => $api_response
                                    ]);
                                }
                            } catch (Exception $whatsapp_error) {
                                logWebhook('WHATSAPP_EXCEPTION', [
                                    'cliente_id' => $cliente_id,
                                    'celular' => $cliente['celular'],
                                    'asaas_id' => $asaas_id,
                                    'error' => $whatsapp_error->getMessage()
                                ]);
                            }
                        }
                    }
                } // Fechamento do else
            } catch (Exception $e) {
                // Log do erro, mas não interrompe o processamento do webhook
                logWebhook('WHATSAPP_NOTIFICATION_ERROR', [
                    'cliente_id' => $cliente_id,
                    'error' => $e->getMessage(),
                    'asaas_id' => $asaas_id
                ]);
            }
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