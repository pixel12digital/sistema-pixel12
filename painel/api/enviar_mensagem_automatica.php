<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cliente_id']) || !isset($input['mensagem'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$cliente_id = intval($input['cliente_id']);
$mensagem = $input['mensagem'];
$tipo = $input['tipo'] ?? 'automatica';
$cliente_celular = $input['cliente_celular'] ?? '';

try {
    // Buscar dados do cliente se celular não fornecido
    if (!$cliente_celular) {
        $cliente = $mysqli->query("SELECT celular FROM clientes WHERE id = $cliente_id LIMIT 1")->fetch_assoc();
        if (!$cliente || !$cliente['celular']) {
            throw new Exception("Cliente sem número de celular cadastrado");
        }
        $cliente_celular = $cliente['celular'];
    }

    // Buscar canal financeiro padrão
    $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE LOWER(nome_exibicao) = 'financeiro' AND status = 'conectado' LIMIT 1")->fetch_assoc();
    
    if (!$canal) {
        throw new Exception("Canal financeiro não conectado");
    }

    // Formatar número do celular
    $numero_limpo = preg_replace('/\D/', '', $cliente_celular);
    $numero_formatado = '55' . $numero_limpo . '@c.us';

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
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ({$canal['id']}, $cliente_id, '$mensagem_escaped', '$tipo_escaped', '$data_hora', 'enviado', 'enviado')";
    
    if (!$mysqli->query($sql)) {
        error_log("Erro ao salvar mensagem automática: " . $mysqli->error);
    }

    // Log do envio
    $log_data = date('Y-m-d H:i:s') . " - Mensagem automática ($tipo) enviada para cliente $cliente_id - $numero_formatado\n";
    file_put_contents('../log_envio_robo.txt', $log_data, FILE_APPEND);

    echo json_encode([
        'success' => true,
        'message' => 'Mensagem automática enviada com sucesso',
        'cliente_id' => $cliente_id,
        'numero' => $numero_formatado,
        'tipo' => $tipo
    ]);

} catch (Exception $e) {
    error_log("Erro ao enviar mensagem automática: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 