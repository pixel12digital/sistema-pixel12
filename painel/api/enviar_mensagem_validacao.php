<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

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
    
    // Regras de formatação:
    // 1. Se tem 9 dígitos e começa com 9, manter como está (celular com 9)
    if (strlen($telefone) === 9 && substr($telefone, 0, 1) === '9') {
        // Manter como está - é um celular válido
    }
    // 2. Se tem 8 dígitos, adicionar 9 no início (celular sem 9)
    elseif (strlen($telefone) === 8) {
        $telefone = '9' . $telefone;
    }
    // 3. Se tem 7 dígitos, adicionar 9 no início (telefone fixo convertido para celular)
    elseif (strlen($telefone) === 7) {
        $telefone = '9' . $telefone;
    }
    
    // Verificar se o número final é válido (deve ter 8 ou 9 dígitos)
    if (strlen($telefone) < 8 || strlen($telefone) > 9) {
        return null; // Número inválido
    }
    
    // GARANTIR SEMPRE o código +55 do Brasil + DDD + número
    return '55' . $ddd . $telefone;
}

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cliente_id']) || !isset($input['cliente_celular'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$cliente_id = intval($input['cliente_id']);
$cliente_nome = $input['cliente_nome'] ?? '';
$cliente_celular = $input['cliente_celular'];
$mensagem = $input['mensagem'] ?? '';

try {
    // Buscar canal financeiro padrão
    $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE LOWER(nome_exibicao) = 'financeiro' AND status = 'conectado' LIMIT 1")->fetch_assoc();
    
    if (!$canal) {
        echo json_encode(['success' => false, 'error' => 'Canal financeiro não conectado']);
        exit;
    }

    // Formatar número do celular usando função melhorada
    $numero_formatado = ajustarNumeroWhatsapp($cliente_celular);
    if (!$numero_formatado) {
        throw new Exception("Número do cliente inválido para envio no WhatsApp");
    }
    $numero_formatado .= '@c.us';

    // Enviar mensagem via VPS
    $payload = json_encode([
        'to' => $numero_formatado,
        'message' => $mensagem
    ]);

    $ch = curl_init("http://212.85.11.238:3000/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("Erro de conexão: " . $error);
    }

    $response_data = json_decode($response, true);

    if ($http_code !== 200 || !$response_data || !isset($response_data['success'])) {
        throw new Exception("Erro na resposta da VPS: " . $response);
    }

    if (!$response_data['success']) {
        throw new Exception("Falha no envio: " . ($response_data['error'] ?? 'Erro desconhecido'));
    }

    // Salvar mensagem no banco
    $mensagem_escaped = $mysqli->real_escape_string($mensagem);
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ({$canal['id']}, $cliente_id, '$mensagem_escaped', 'text', '$data_hora', 'enviado', 'enviado')";
    
    if (!$mysqli->query($sql)) {
        error_log("Erro ao salvar mensagem de validação: " . $mysqli->error);
    }

    // Log do envio
    $log_data = date('Y-m-d H:i:s') . " - Mensagem de validação enviada para cliente $cliente_id ($cliente_nome) - $numero_formatado\n";
    file_put_contents('../log_envio_robo.txt', $log_data, FILE_APPEND);

    // Enviar e-mail ao cliente
    $email_cliente = '';
    $res_email = $mysqli->query("SELECT email FROM clientes WHERE id = $cliente_id LIMIT 1");
    if ($res_email && ($row_email = $res_email->fetch_assoc())) {
        $email_cliente = $row_email['email'];
    }
    if ($email_cliente && filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
        $assunto = 'Validação de Cadastro - Pixel12 Digital';
        $headers = "From: Pixel12 Digital <nao-responder@pixel12digital.com.br>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $enviado_email = mail($email_cliente, $assunto, $mensagem, $headers);
        $log_email = date('Y-m-d H:i:s') . " - Email de validacao para $cliente_id ($email_cliente): " . ($enviado_email ? 'ENVIADO' : 'FALHA') . "\n";
        file_put_contents('../log_envio_robo.txt', $log_email, FILE_APPEND);
    } else {
        $log_email = date('Y-m-d H:i:s') . " - Email de validacao para $cliente_id: EMAIL INVALIDO OU NAO ENCONTRADO\n";
        file_put_contents('../log_envio_robo.txt', $log_email, FILE_APPEND);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Mensagem de validação enviada com sucesso',
        'cliente_id' => $cliente_id,
        'numero' => $numero_formatado
    ]);

} catch (Exception $e) {
    error_log("Erro ao enviar mensagem de validação: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 