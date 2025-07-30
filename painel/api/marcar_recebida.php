<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$asaas_payment_id = $input['asaas_payment_id'] ?? null;
$cobranca_id = $input['cobranca_id'] ?? null;

if (!$asaas_payment_id || !$cobranca_id) {
    echo json_encode(['success' => false, 'error' => 'Dados insuficientes para operação.']);
    exit;
}

// Verificar se a chave da API está configurada
if (empty(ASAAS_API_KEY) || ASAAS_API_KEY === '$aact_test_CHAVE_DE_TESTE_AQUI') {
    echo json_encode(['success' => false, 'error' => 'Chave da API do Asaas não configurada corretamente.']);
    exit;
}

// 1. Buscar dados da cobrança para validação
$stmt = $mysqli->prepare("SELECT valor, status FROM cobrancas WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $cobranca_id);
$stmt->execute();
$result = $stmt->get_result();
$cobranca = $result->fetch_assoc();
$stmt->close();

if (!$cobranca) {
    echo json_encode(['success' => false, 'error' => 'Cobrança não encontrada.']);
    exit;
}

// Verificar se a cobrança pode ser marcada como recebida
if ($cobranca['status'] === 'RECEIVED' || $cobranca['status'] === 'RECEIVED_IN_CASH' || $cobranca['status'] === 'PAID') {
    echo json_encode(['success' => false, 'error' => 'Esta cobrança já foi marcada como recebida.']);
    exit;
}

if ($cobranca['status'] === 'CANCELLED' || $cobranca['status'] === 'DELETED') {
    echo json_encode(['success' => false, 'error' => 'Esta cobrança foi cancelada ou excluída e não pode ser marcada como recebida.']);
    exit;
}

// 2. Verificar status atual no Asaas
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . "/payments/$asaas_payment_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "access_token: " . ASAAS_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$status_response = curl_exec($ch);
$status_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status_http_code === 200) {
    $payment_data = json_decode($status_response, true);
    $asaas_status = $payment_data['status'] ?? '';
    
    // Verificar se o status no Asaas permite recebimento em dinheiro
    if ($asaas_status === 'RECEIVED' || $asaas_status === 'RECEIVED_IN_CASH' || $asaas_status === 'PAID') {
        echo json_encode(['success' => false, 'error' => 'Esta cobrança já foi marcada como recebida no Asaas.']);
        exit;
    }
    
    if ($asaas_status === 'CANCELLED' || $asaas_status === 'DELETED') {
        echo json_encode(['success' => false, 'error' => 'Esta cobrança foi cancelada ou excluída no Asaas.']);
        exit;
    }
    
    // Log do status atual
    error_log("Asaas API - Status atual da cobrança: " . $asaas_status);
} else {
    error_log("Asaas API - Erro ao verificar status: HTTP $status_http_code - $status_response");
}

// 3. Validar valor mínimo (R$ 1,00)
$valor = floatval($cobranca['valor']);
if ($valor < 1.00) {
    echo json_encode(['success' => false, 'error' => 'O valor mínimo para cobranças recebidas em dinheiro é R$ 1,00.']);
    exit;
}

// 4. Marcar como recebida no Asaas com data de pagamento
$data_pagamento = date('Y-m-d');
$payload = json_encode([
    'paymentDate' => $data_pagamento
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . "/payments/$asaas_payment_id/receiveInCash");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "access_token: " . ASAAS_API_KEY
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Log para debug
error_log("Asaas API - URL: " . ASAAS_API_URL . "/payments/$asaas_payment_id/receiveInCash");
error_log("Asaas API - Payload: " . $payload);
error_log("Asaas API - Response: " . $response);
error_log("Asaas API - HTTP Code: " . $http_code);
error_log("Asaas API - Curl Error: " . $curl_error);

if ($http_code !== 200) {
    // Tentar endpoint alternativo se o primeiro falhar
    error_log("Asaas API - Primeira tentativa falhou, tentando endpoint alternativo...");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . "/payments/$asaas_payment_id");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'status' => 'RECEIVED_IN_CASH',
        'paymentDate' => $data_pagamento
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "access_token: " . ASAAS_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response2 = curl_exec($ch);
    $http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    error_log("Asaas API - Tentativa 2 - URL: " . ASAAS_API_URL . "/payments/$asaas_payment_id");
    error_log("Asaas API - Tentativa 2 - Response: " . $response2);
    error_log("Asaas API - Tentativa 2 - HTTP Code: " . $http_code2);
    
    if ($http_code2 !== 200) {
        error_log("Asaas API - AMBAS as tentativas falharam. NÃO atualizando banco local.");
        echo json_encode(['success' => false, 'error' => 'Erro ao marcar como recebida no Asaas: ' . $response . ' | Tentativa 2: ' . $response2]);
        exit;
    } else {
        error_log("Asaas API - Segunda tentativa SUCESSO! HTTP Code: " . $http_code2);
        $response = $response2;
        $http_code = $http_code2;
    }
} else {
    error_log("Asaas API - Primeira tentativa SUCESSO! HTTP Code: " . $http_code);
}

// 5. SÓ ATUALIZAR BANCO LOCAL SE ASAAS CONFIRMOU (HTTP 200)
if ($http_code === 200) {
    error_log("Asaas API - CONFIRMADO! Atualizando banco local...");
    
    $stmt = $mysqli->prepare("UPDATE cobrancas SET status = 'RECEIVED_IN_CASH', data_pagamento = ? WHERE id = ?");
    $stmt->bind_param('si', $data_pagamento, $cobranca_id);
    if ($stmt->execute()) {
        error_log("Banco local - ATUALIZADO com sucesso! Enviando WhatsApp...");
        
        // 6. SÓ ENVIAR WHATSAPP SE BANCO FOI ATUALIZADO COM SUCESSO
        try {
            // Buscar dados do cliente
            $stmt_cliente = $mysqli->prepare("SELECT c.nome, c.contact_name, c.celular, cob.valor FROM clientes c INNER JOIN cobrancas cob ON c.id = cob.cliente_id WHERE cob.id = ? LIMIT 1");
            $stmt_cliente->bind_param('i', $cobranca_id);
            $stmt_cliente->execute();
            $cliente = $stmt_cliente->get_result()->fetch_assoc();
            $stmt_cliente->close();
            
            if ($cliente && !empty($cliente['celular'])) {
                error_log("WhatsApp - Cliente encontrado: " . $cliente['nome'] . " | Celular: " . $cliente['celular']);
                
                // Preparar mensagem de confirmação
                $nome = $cliente['contact_name'] ?: $cliente['nome'];
                $valor_formatado = number_format($cliente['valor'], 2, ',', '.');
                $data_pagamento_formatada = date('d/m/Y');
                
                $mensagem = "✅ *Pagamento Confirmado!*\n\n";
                $mensagem .= "Olá {$nome}!\n\n";
                $mensagem .= "Recebemos seu pagamento de *R$ {$valor_formatado}*\n";
                $mensagem .= "Data do pagamento: {$data_pagamento_formatada}\n";
                $mensagem .= "Referente à cobrança #{$asaas_payment_id}\n\n";
                $mensagem .= "Obrigado pela confiança! 🙏\n\n";
                $mensagem .= "Esta é uma mensagem automática.";
                
                // Salvar mensagem no banco
                $data_hora = date('Y-m-d H:i:s');
                $mensagem_escaped = $mysqli->real_escape_string($mensagem);
                $canal_id = 36; // Canal Financeiro
                
                $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES ($canal_id, (SELECT cliente_id FROM cobrancas WHERE id = $cobranca_id), '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'enviado')";
                
                if ($mysqli->query($sql)) {
                    error_log("WhatsApp - Mensagem salva no banco. Enviando via API...");
                    
                    // Enviar via API do WhatsApp
                    $whatsapp_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';
                    $api_url = $whatsapp_url . "/send/text";
                    
                    $api_data = [
                        'sessionName' => 'default',
                        'number' => $cliente['celular'],
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
                            error_log("WhatsApp - ENVIADO com sucesso! Message ID: " . ($api_result['messageId'] ?? 'N/A'));
                            // Atualizar mensagem com ID do WhatsApp se disponível
                            if (isset($api_result['messageId'])) {
                                $mysqli->query("UPDATE mensagens_comunicacao SET whatsapp_message_id = '" . $mysqli->real_escape_string($api_result['messageId']) . "' WHERE id = " . $mysqli->insert_id);
                            }
                        } else {
                            error_log("WhatsApp - Erro na resposta da API: " . $api_response);
                        }
                    } else {
                        error_log("WhatsApp - Erro HTTP: " . $http_code . " | Response: " . $api_response);
                    }
                } else {
                    error_log("WhatsApp - Erro ao salvar mensagem no banco: " . $mysqli->error);
                }
            } else {
                error_log("WhatsApp - Cliente não encontrado ou sem celular. Cliente ID: " . $cobranca_id);
            }
        } catch (Exception $e) {
            // Log do erro, mas não interrompe o processamento
            error_log("Erro ao enviar notificação WhatsApp: " . $e->getMessage());
        }
        
        echo json_encode(['success' => true]);
    } else {
        error_log("Banco local - ERRO ao atualizar: " . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Erro ao atualizar cobrança local: ' . $stmt->error]);
    }
} else {
    // Se chegou aqui, algo deu errado - não deveria acontecer
    error_log("ERRO INESPERADO: Asaas não confirmou mas não retornou erro específico. HTTP Code: " . $http_code);
    echo json_encode(['success' => false, 'error' => 'Erro inesperado: Asaas não confirmou mas não retornou erro específico']);
}
$stmt->close(); 