<?php
/**
 * WEBHOOK ESPECÍFICO - CANAL COMERCIAL
 * Porta: 3001 | Canal ID: 37
 * 
 * Este webhook processa mensagens específicas do canal comercial
 * e salva no banco separado pixel12digital_comercial
 */

header('Content-Type: application/json');

// Incluir configuração do canal comercial
require_once __DIR__ . '/../canais/comercial/canal_config.php';

// LOG: Capturar dados recebidos
$input = file_get_contents('php://input');
error_log(CANAL_LOG_PREFIXO . " Dados recebidos: " . $input);

$data = json_decode($input, true);

if (!isset($data['from']) || !isset($data['body'])) {
    error_log(CANAL_LOG_PREFIXO . " ERRO: Dados incompletos - from ou body não encontrados");
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

error_log(CANAL_LOG_PREFIXO . " Processando mensagem de: " . $data['from'] . " - Conteúdo: " . $data['body']);

// Verificar se a mensagem é para este canal
$to = isset($data['to']) ? $data['to'] : '';
$canal_esperado = CANAL_WHATSAPP_COMPLETO;

if ($to && $to !== $canal_esperado) {
    error_log(CANAL_LOG_PREFIXO . " Mensagem não é para este canal. Esperado: $canal_esperado, Recebido: $to");
    echo json_encode(['success' => false, 'error' => 'Canal incorreto']);
    exit;
}

// Salvar mensagem usando a função do canal
$resultado = salvarMensagemCanal($data);

if ($resultado) {
    error_log(CANAL_LOG_PREFIXO . " SUCESSO: Mensagem processada e salva");
    echo json_encode([
        'success' => true, 
        'canal' => CANAL_NOME,
        'canal_id' => CANAL_NUMERO,
        'banco' => CANAL_BANCO_NOME
    ]);
} else {
    error_log(CANAL_LOG_PREFIXO . " ERRO: Falha ao processar mensagem");
    echo json_encode(['success' => false, 'error' => 'Falha ao processar mensagem']);
}
?> 