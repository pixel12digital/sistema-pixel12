<?php
header('Content-Type: application/json');
require_once '../painel/config.php';
require_once '../painel/db.php';
require_once 'whatsapp_wppconnect.php';

// Configurar WPPConnect
$whatsapp = new WhatsAppWPPConnect($mysqli, 'https://wpp.yourdomain.com');

// Log do webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("Webhook Asaas recebido: " . json_encode($data));

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

try {
    $event = $data['event'] ?? '';
    $payment = $data['payment'] ?? [];
    
    // Buscar cliente pelo ID do Asaas
    $asaas_customer_id = $payment['customer'] ?? '';
    $cliente = null;
    
    if ($asaas_customer_id) {
        $sql = "SELECT * FROM clientes WHERE asaas_customer_id = '" . $mysqli->real_escape_string($asaas_customer_id) . "'";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
        }
    }
    
    // Buscar cobrança pelo ID do Asaas
    $asaas_payment_id = $payment['id'] ?? '';
    $cobranca = null;
    
    if ($asaas_payment_id) {
        $sql = "SELECT * FROM cobrancas WHERE asaas_payment_id = '" . $mysqli->real_escape_string($asaas_payment_id) . "'";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            $cobranca = $result->fetch_assoc();
        }
    }
    
    // Processar eventos
    switch ($event) {
        case 'PAYMENT_RECEIVED':
            // Pagamento recebido - enviar confirmação
            if ($cliente && $cobranca) {
                $mensagem = "✅ *Pagamento Confirmado!*\n\n";
                $mensagem .= "Olá {$cliente['nome']}!\n\n";
                $mensagem .= "Recebemos seu pagamento de *R$ " . number_format($payment['value'], 2, ',', '.') . "*\n";
                $mensagem .= "Referente à cobrança #{$cobranca['id']}\n\n";
                $mensagem .= "Obrigado pela confiança! 🙏\n";
                $mensagem .= "Em breve entraremos em contato.";
                
                $whatsapp->sendText('default', $cliente['celular'], $mensagem);
                
                // Atualizar status da cobrança
                $mysqli->query("UPDATE cobrancas SET status = 'pago', data_pagamento = NOW() WHERE id = {$cobranca['id']}");
            }
            break;
            
        case 'PAYMENT_OVERDUE':
            // Pagamento em atraso - enviar lembrete
            if ($cliente && $cobranca) {
                $mensagem = "⚠️ *Pagamento em Atraso*\n\n";
                $mensagem .= "Olá {$cliente['nome']}!\n\n";
                $mensagem .= "Sua cobrança de *R$ " . number_format($payment['value'], 2, ',', '.') . "* está em atraso.\n";
                $mensagem .= "Vencimento: " . date('d/m/Y', strtotime($payment['dueDate'])) . "\n\n";
                $mensagem .= "🔗 *Link para pagamento:*\n";
                $mensagem .= "{$payment['invoiceUrl']}\n\n";
                $mensagem .= "Entre em contato conosco se precisar de ajuda.";
                
                $whatsapp->sendText('default', $cliente['celular'], $mensagem);
                
                // Atualizar status da cobrança
                $mysqli->query("UPDATE cobrancas SET status = 'atrasado' WHERE id = {$cobranca['id']}");
            }
            break;
            
        case 'PAYMENT_DELETED':
            // Pagamento cancelado
            if ($cliente && $cobranca) {
                $mensagem = "❌ *Cobrança Cancelada*\n\n";
                $mensagem .= "Olá {$cliente['nome']}!\n\n";
                $mensagem .= "Sua cobrança de *R$ " . number_format($payment['value'], 2, ',', '.') . "* foi cancelada.\n\n";
                $mensagem .= "Entre em contato conosco para mais informações.";
                
                $whatsapp->sendText('default', $cliente['celular'], $mensagem);
                
                // Atualizar status da cobrança
                $mysqli->query("UPDATE cobrancas SET status = 'cancelado' WHERE id = {$cobranca['id']}");
            }
            break;
            
        case 'PAYMENT_UPDATED':
            // Pagamento atualizado
            if ($cliente && $cobranca) {
                $mensagem = "📝 *Cobrança Atualizada*\n\n";
                $mensagem .= "Olá {$cliente['nome']}!\n\n";
                $mensagem .= "Sua cobrança foi atualizada para *R$ " . number_format($payment['value'], 2, ',', '.') . "*\n";
                $mensagem .= "Novo vencimento: " . date('d/m/Y', strtotime($payment['dueDate'])) . "\n\n";
                $mensagem .= "🔗 *Link para pagamento:*\n";
                $mensagem .= "{$payment['invoiceUrl']}";
                
                $whatsapp->sendText('default', $cliente['celular'], $mensagem);
            }
            break;
    }
    
    // Salvar log do webhook
    $event_escaped = $mysqli->real_escape_string($event);
    $data_json = $mysqli->real_escape_string(json_encode($data));
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO webhook_logs (evento, dados, data_hora) VALUES ('$event_escaped', '$data_json', '$data_hora')";
    $mysqli->query($sql);
    
    echo json_encode(['success' => true, 'message' => 'Webhook processado']);
    
} catch (Exception $e) {
    error_log("Erro no webhook Asaas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 