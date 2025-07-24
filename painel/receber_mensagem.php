<?php
require_once __DIR__ . '/config.php';
require_once 'db.php';
require_once 'cache_invalidator.php';

header('Content-Type: application/json');

// LOG: Capturar dados recebidos
$input = file_get_contents('php://input');
error_log("[RECEBIMENTO] Dados recebidos: " . $input);

$data = json_decode($input, true);

if (!isset($data['from']) || !isset($data['body'])) {
    error_log("[RECEBIMENTO] ERRO: Dados incompletos - from ou body não encontrados");
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

error_log("[RECEBIMENTO] Processando mensagem de: " . $data['from'] . " - Conteúdo: " . $data['body']);

$from = $mysqli->real_escape_string($data['from']);
$body = $mysqli->real_escape_string($data['body']);
$timestamp = isset($data['timestamp']) ? intval($data['timestamp']) : time();

// Tenta encontrar canal pelo identificador (número)
$numero = preg_replace('/\D/', '', $from);
error_log("[RECEBIMENTO] Número limpo: " . $numero);

// Buscar canal pelo número do robô (554797146908)
$canal = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE identificador LIKE '%554797146908%' OR identificador LIKE '%4797146908%' LIMIT 1")->fetch_assoc();
$canal_id = $canal ? intval($canal['id']) : 36; // Usar canal 36 (Financeiro) como padrão
error_log("[RECEBIMENTO] Canal encontrado: " . ($canal ? $canal['nome_exibicao'] . ' (ID: ' . $canal['id'] . ')' : 'NÃO ENCONTRADO, usando ID 36'));

// Opcional: tentar encontrar cliente pelo número
$numero_limpo = preg_replace('/\D/', '', $from);
error_log("[RECEBIMENTO] Número limpo: " . $numero_limpo);

// Tentar diferentes formatos do número
$formatos_numero = [];
$formatos_numero[] = $numero_limpo; // Formato original (554796164699)
$formatos_numero[] = substr($numero_limpo, 2); // Sem código do país (4796164699)
$formatos_numero[] = substr($numero_limpo, 0, 2) . '9' . substr($numero_limpo, 2); // Com 9 (554796164699)
$formatos_numero[] = substr($numero_limpo, 2, 2) . '9' . substr($numero_limpo, 4); // Sem código + 9 (4796164699)
$ddd_mais_numero = substr($numero_limpo, -10); // DDD + número
$formatos_numero[] = $ddd_mais_numero;

error_log("[RECEBIMENTO] Formatos a testar: " . implode(', ', $formatos_numero));

$cliente = null;
$cliente_id = null;

foreach ($formatos_numero as $formato) {
    $formato_escaped = $mysqli->real_escape_string($formato);
    $result = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE celular LIKE '%$formato_escaped%' OR telefone LIKE '%$formato_escaped%' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        $cliente_id = intval($cliente['id']);
        error_log("[RECEBIMENTO] Cliente encontrado com formato '$formato': " . $cliente['nome'] . " (ID: " . $cliente_id . ")");
        break;
    }
}

if (!$cliente) {
    error_log("[RECEBIMENTO] Cliente não encontrado com nenhum formato");
}

$data_hora = date('Y-m-d H:i:s', $timestamp);

if ($cliente_id) {
  // Cliente existe, salva normalmente
  error_log("[RECEBIMENTO] Salvando mensagem para cliente existente ID: " . $cliente_id);
  $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES ($canal_id, $cliente_id, '$body', 'texto', '$data_hora', 'recebido', 'recebido')";
  if ($mysqli->query($sql)) {
    error_log("[RECEBIMENTO] SUCESSO: Mensagem salva no banco, ID: " . $mysqli->insert_id);
    invalidate_message_cache($cliente_id);
    // Forçar limpeza de todos os caches relevantes
    if (function_exists('cache_forget')) {
        cache_forget("mensagens_{$cliente_id}");
        cache_forget("historico_html_{$cliente_id}");
        cache_forget("mensagens_html_{$cliente_id}");
    }
    echo json_encode(['success' => true, 'mensagem_id' => $mysqli->insert_id]);
  } else {
    error_log("[RECEBIMENTO] ERRO SQL: " . $mysqli->error);
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
  }
} else {
  // Cliente não existe, salva na tabela temporária
  error_log("[RECEBIMENTO] Cliente não encontrado, salvando em mensagens_pendentes");
  $sql = "INSERT INTO mensagens_pendentes (canal_id, numero, mensagem, tipo, data_hora) VALUES ($canal_id, '$numero', '$body', 'texto', '$data_hora')";
  if ($mysqli->query($sql)) {
    error_log("[RECEBIMENTO] SUCESSO: Mensagem salva em pendentes, ID: " . $mysqli->insert_id);
    echo json_encode(['success' => true, 'pendente' => true, 'mensagem_id' => $mysqli->insert_id]);
  } else {
    error_log("[RECEBIMENTO] ERRO SQL pendentes: " . $mysqli->error);
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
  }
} 