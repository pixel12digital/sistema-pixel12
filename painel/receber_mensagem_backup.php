<?php
require_once __DIR__ . '/../config.php';
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

// NOVA LÓGICA: Buscar canal pelo número de destino (to) se disponível
$canal_id = 36; // Padrão: Financeiro
$canal_nome = "Financeiro";
$canal_porta = 3000; // Padrão: Porta 3000

if (isset($data['to'])) {
    $to = $mysqli->real_escape_string($data['to']);
    error_log("[RECEBIMENTO] Número de destino (to): " . $to);
    
    // Buscar canal pelo identificador de destino
    $canal = $mysqli->query("SELECT id, nome_exibicao, porta FROM canais_comunicacao WHERE identificador = '$to' LIMIT 1")->fetch_assoc();
    
    if ($canal) {
        $canal_id = intval($canal['id']);
        $canal_nome = $canal['nome_exibicao'];
        $canal_porta = intval($canal['porta']);
        error_log("[RECEBIMENTO] Canal encontrado pelo destino: " . $canal_nome . " (ID: " . $canal_id . ", Porta: " . $canal_porta . ")");
    } else {
        error_log("[RECEBIMENTO] Canal não encontrado pelo destino '$to', usando padrão Financeiro");
    }
} else {
    // Fallback: Buscar canal pelo número do robô (554797146908) - lógica antiga
    $canal = $mysqli->query("SELECT id, nome_exibicao, porta FROM canais_comunicacao WHERE identificador LIKE '%554797146908%' OR identificador LIKE '%4797146908%' LIMIT 1")->fetch_assoc();
    if ($canal) {
        $canal_id = intval($canal['id']);
        $canal_nome = $canal['nome_exibicao'];
        $canal_porta = intval($canal['porta']);
        error_log("[RECEBIMENTO] Canal encontrado pelo robô: " . $canal_nome . " (ID: " . $canal_id . ", Porta: " . $canal_porta . ")");
    } else {
        error_log("[RECEBIMENTO] Canal não encontrado, usando ID 36 (Financeiro) como padrão");
    }
}

// NOVA LÓGICA: Conectar ao banco correto baseado na porta do canal
$mysqli_canal = $mysqli; // Padrão: banco principal

if ($canal_porta === 3001) {
    // Canal Comercial - usar configuração específica do canal
    error_log("[RECEBIMENTO] Conectando ao banco comercial...");
    try {
        require_once __DIR__ . '/../canais/comercial/canal_config.php';
        $mysqli_comercial = conectarBancoCanal();
        if ($mysqli_comercial) {
            $mysqli_canal = $mysqli_comercial;
            error_log("[RECEBIMENTO] ✅ Conectado ao banco comercial usando configuração do canal");
        } else {
            error_log("[RECEBIMENTO] ❌ Erro ao conectar ao banco comercial");
            error_log("[RECEBIMENTO] ⚠️ Usando banco principal como fallback");
        }
    } catch (Exception $e) {
        error_log("[RECEBIMENTO] ❌ Exceção ao conectar ao banco comercial: " . $e->getMessage());
        error_log("[RECEBIMENTO] ⚠️ Usando banco principal como fallback");
    }
} elseif ($canal_porta === 3002) {
    // Canal Suporte - usar banco separado
    error_log("[RECEBIMENTO] Conectando ao banco suporte...");
    try {
        $mysqli_suporte = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'pixel12digital_suporte');
        if (!$mysqli_suporte->connect_error) {
            $mysqli_canal = $mysqli_suporte;
            error_log("[RECEBIMENTO] ✅ Conectado ao banco suporte");
        } else {
            error_log("[RECEBIMENTO] ❌ Erro ao conectar ao banco suporte: " . $mysqli_suporte->connect_error);
            error_log("[RECEBIMENTO] ⚠️ Usando banco principal como fallback");
        }
    } catch (Exception $e) {
        error_log("[RECEBIMENTO] ❌ Exceção ao conectar ao banco suporte: " . $e->getMessage());
        error_log("[RECEBIMENTO] ⚠️ Usando banco principal como fallback");
    }
} elseif ($canal_porta === 3003) {
    // Canal Vendas - usar banco separado
    error_log("[RECEBIMENTO] Conectando ao banco vendas...");
    try {
        $mysqli_vendas = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'pixel12digital_vendas');
        if (!$mysqli_vendas->connect_error) {
            $mysqli_canal = $mysqli_vendas;
            error_log("[RECEBIMENTO] ✅ Conectado ao banco vendas");
        } else {
            error_log("[RECEBIMENTO] ❌ Erro ao conectar ao banco vendas: " . $mysqli_vendas->connect_error);
            error_log("[RECEBIMENTO] ⚠️ Usando banco principal como fallback");
        }
    } catch (Exception $e) {
        error_log("[RECEBIMENTO] ❌ Exceção ao conectar ao banco vendas: " . $e->getMessage());
        error_log("[RECEBIMENTO] ⚠️ Usando banco principal como fallback");
    }
} else {
    error_log("[RECEBIMENTO] Usando banco principal (porta $canal_porta)");
}

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
    $formato_escaped = $mysqli_canal->real_escape_string($formato);
    $result = $mysqli_canal->query("SELECT id, nome, celular FROM clientes WHERE celular LIKE '%$formato_escaped%' OR telefone LIKE '%$formato_escaped%' LIMIT 1");
    
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
  error_log("[RECEBIMENTO] Salvando mensagem para cliente existente ID: " . $cliente_id . " no canal: " . $canal_nome . " (ID: " . $canal_id . ")");
  $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES ($canal_id, $cliente_id, '$body', 'texto', '$data_hora', 'recebido', 'recebido')";
  if ($mysqli_canal->query($sql)) {
    error_log("[RECEBIMENTO] SUCESSO: Mensagem salva no banco, ID: " . $mysqli_canal->insert_id . " no canal: " . $canal_nome);
    invalidate_message_cache($cliente_id);
    // Forçar limpeza de todos os caches relevantes
    if (function_exists('cache_forget')) {
        cache_forget("mensagens_{$cliente_id}");
        cache_forget("historico_html_{$cliente_id}");
        cache_forget("mensagens_html_{$cliente_id}");
    }
    echo json_encode(['success' => true, 'mensagem_id' => $mysqli_canal->insert_id, 'canal' => $canal_nome]);
  } else {
    error_log("[RECEBIMENTO] ERRO SQL: " . $mysqli_canal->error);
    echo json_encode(['success' => false, 'error' => $mysqli_canal->error]);
  }
} else {
  // Cliente não existe, salva na tabela temporária
  error_log("[RECEBIMENTO] Cliente não encontrado, salvando em mensagens_pendentes no canal: " . $canal_nome . " (ID: " . $canal_id . ")");
  $sql = "INSERT INTO mensagens_pendentes (canal_id, numero, mensagem, tipo, data_hora) VALUES ($canal_id, '$numero', '$body', 'texto', '$data_hora')";
  if ($mysqli_canal->query($sql)) {
    error_log("[RECEBIMENTO] SUCESSO: Mensagem salva em pendentes, ID: " . $mysqli_canal->insert_id . " no canal: " . $canal_nome);
    echo json_encode(['success' => true, 'pendente' => true, 'mensagem_id' => $mysqli_canal->insert_id, 'canal' => $canal_nome]);
  } else {
    error_log("[RECEBIMENTO] ERRO SQL pendentes: " . $mysqli_canal->error);
    echo json_encode(['success' => false, 'error' => $mysqli_canal->error]);
  }
}

// Fechar conexões específicas se foram criadas
if ($canal_porta === 3001 && isset($mysqli_comercial) && $mysqli_comercial !== $mysqli) {
    $mysqli_comercial->close();
} elseif ($canal_porta === 3002 && isset($mysqli_suporte) && $mysqli_suporte !== $mysqli) {
    $mysqli_suporte->close();
} elseif ($canal_porta === 3003 && isset($mysqli_vendas) && $mysqli_vendas !== $mysqli) {
    $mysqli_vendas->close();
} 