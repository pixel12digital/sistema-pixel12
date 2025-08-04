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
    return '55' . $ddd . $telefone . '@c.us';
}

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

    // Formatar número do celular usando função simplificada
    $numero_formatado = ajustarNumeroWhatsapp($cliente_celular);
    if (!$numero_formatado) {
        throw new Exception("Número de telefone inválido: " . $cliente_celular);
    }

    // Enviar mensagem via VPS
    $payload = json_encode([
        'sessionName' => 'default',
        'number' => $numero_formatado,
        'message' => $mensagem
    ]);
    
    $ch = curl_init("http://212.85.11.238:3000/send/text");
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
    
    // Buscar canal WhatsApp financeiro
    $canal_result = $mysqli->query("SELECT id FROM canais_comunicacao WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%') LIMIT 1");
    $canal = $canal_result->fetch_assoc();
    $canal_id = $canal ? $canal['id'] : 36; // Usar canal 36 como padrão se não encontrar
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp, numero_remetente) 
            VALUES ($canal_id, $cliente_id, '$mensagem_escaped', '$tipo_escaped', '$data_hora', 'enviado', 'enviado', '$cliente_celular', '4797146908')";
    
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