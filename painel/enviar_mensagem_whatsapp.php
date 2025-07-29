<?php
date_default_timezone_set('America/Sao_Paulo');

// Corrigir caminhos dos includes
require_once __DIR__ . '/../config.php';
require_once 'db.php';

header('Content-Type: application/json');

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
$canal_id = isset($_POST['canal_id']) ? intval($_POST['canal_id']) : 0;
$cobranca_id = isset($_POST['cobranca_id']) ? intval($_POST['cobranca_id']) : null;
$numero_direto = isset($_POST['numero_direto']) ? trim($_POST['numero_direto']) : '';

// Se número direto for fornecido, pular validações de cliente_id
if ($numero_direto) {
    // Validar apenas mensagem, canal e número direto
    if (!$mensagem || !$canal_id || !$numero_direto) {
        echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes para envio direto.']);
        exit;
    }
    // Pular verificação de duplicidade para envio direto
} else {
    // Validação original para cliente_id
    if (!$cliente_id || !$mensagem || !$canal_id) {
        echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
        exit;
    }
}

// Verifica duplicidade apenas se não for envio direto: mesma mensagem para o mesmo cliente, canal e dia
if (!$numero_direto) {
    $hoje = date('Y-m-d');
    $stmt = $mysqli->prepare("SELECT status FROM mensagens_comunicacao WHERE cliente_id = ? AND canal_id = ? AND mensagem = ? AND DATE(data_hora) = ? ORDER BY data_hora DESC LIMIT 1");
    $stmt->bind_param('iiss', $cliente_id, $canal_id, $mensagem, $hoje);
    $stmt->execute();
    $stmt->bind_result($status_existente);
    $ja_enviada = false;
    if ($stmt->fetch()) {
        if ($status_existente === 'enviado') {
            $ja_enviada = true;
        }
    }
    $stmt->close();
    if ($ja_enviada) {
        echo json_encode(['success' => false, 'error' => 'Mensagem já enviada para este cliente hoje.']);
        exit;
    }
}

// Função melhorada para formatar número WhatsApp
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
    
    // Retornar no formato: 55 + DDD + número
    return '55' . $ddd . $telefone;
}

// Buscar número do cliente ou usar número direto
$numero = null;
if ($numero_direto) {
    // Usar número direto fornecido
    $numero = ajustarNumeroWhatsapp($numero_direto);
} else {
    // Buscar número do cliente no banco
    $resCli = $mysqli->query("SELECT celular FROM clientes WHERE id = $cliente_id LIMIT 1");
    if ($resCli && ($rowCli = $resCli->fetch_assoc())) {
        $numero = ajustarNumeroWhatsapp($rowCli['celular']);
    }
}

if (!$numero) {
    $error_msg = $numero_direto ? 'Número direto inválido para envio no WhatsApp.' : 'Número do cliente inválido para envio no WhatsApp.';
    echo json_encode(['success' => false, 'error' => $error_msg]);
    exit;
}

// Buscar porta do canal
$res = $mysqli->query("SELECT porta FROM canais_comunicacao WHERE id = $canal_id LIMIT 1");
$porta = null;
if ($res && ($row = $res->fetch_assoc())) {
    $porta = $row['porta'];
}
if (!$porta) {
    echo json_encode(['success' => false, 'error' => 'Porta do canal não encontrada.']);
    exit;
}

// Usar cURL em vez de file_get_contents para melhor controle
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, WHATSAPP_ROBOT_URL . "/send/text"); // CORRIGIDO: usar /send/text
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'sessionName' => 'default',
    'number' => $numero,
    'message' => $mensagem
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Aumentar timeout para 30 segundos
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout de conexão

$resposta = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Verificar se houve erro de conexão
if ($curl_error) {
    $erro = 'Erro de conexão com o robô: ' . $curl_error;
    // Registrar tentativa frustrada no banco
    $notificacao = 'Tentativa de envio de cobrança via WhatsApp em ' . date('d/m/Y H:i') . ' - ERRO: ' . $erro;
    $data_hora = date('Y-m-d H:i:s');
    if ($cobranca_id) {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiiss', $canal_id, $cliente_id, $cobranca_id, $notificacao, $data_hora);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiss', $canal_id, $cliente_id, $notificacao, $data_hora);
    }
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => false, 'error' => $erro]);
    exit;
}

// Verificar se a resposta HTTP é válida
if ($http_code !== 200) {
    $erro = 'Erro HTTP ' . $http_code . ' do robô WhatsApp';
    // Registrar tentativa frustrada no banco
    $notificacao = 'Tentativa de envio via WhatsApp em ' . date('d/m/Y H:i') . ' - ERRO: ' . $erro;
    $data_hora = date('Y-m-d H:i:s');
    if ($cobranca_id) {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiiss', $canal_id, $cliente_id, $cobranca_id, $notificacao, $data_hora);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiss', $canal_id, $cliente_id, $notificacao, $data_hora);
    }
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => false, 'error' => $erro . ' - Resposta: ' . $resposta]);
    exit;
}

// Decodificar resposta JSON
$dados_resposta = json_decode($resposta, true);
if (!$dados_resposta) {
    $erro = 'Resposta inválida do robô WhatsApp';
    // Registrar tentativa frustrada no banco
    $notificacao = 'Tentativa de envio via WhatsApp em ' . date('d/m/Y H:i') . ' - ERRO: ' . $erro;
    $data_hora = date('Y-m-d H:i:s');
    if ($cobranca_id) {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiiss', $canal_id, $cliente_id, $cobranca_id, $notificacao, $data_hora);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiss', $canal_id, $cliente_id, $notificacao, $data_hora);
    }
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => false, 'error' => $erro . ' - Resposta: ' . $resposta]);
    exit;
}

// Verificar se o envio foi bem-sucedido
if (!isset($dados_resposta['success']) || !$dados_resposta['success']) {
    $erro = 'Falha no envio via WhatsApp: ' . ($dados_resposta['message'] ?? 'Erro desconhecido');
    // Registrar tentativa frustrada no banco
    $notificacao = 'Tentativa de envio via WhatsApp em ' . date('d/m/Y H:i') . ' - ERRO: ' . $erro;
    $data_hora = date('Y-m-d H:i:s');
    if ($cobranca_id) {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiiss', $canal_id, $cliente_id, $cobranca_id, $notificacao, $data_hora);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, 'texto', ?, 'enviado', 'erro')");
        $stmt->bind_param('iiss', $canal_id, $cliente_id, $notificacao, $data_hora);
    }
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => false, 'error' => $erro]);
    exit;
}

// Envio bem-sucedido - registrar no banco
$data_hora = date('Y-m-d H:i:s');
$status = 'enviado';
$message_id = $dados_resposta['messageId'] ?? null;

if ($cobranca_id) {
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status, whatsapp_message_id) VALUES (?, ?, ?, ?, 'texto', ?, 'enviado', ?, ?)");
    $stmt->bind_param('iiissss', $canal_id, $cliente_id, $cobranca_id, $mensagem, $data_hora, $status, $message_id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, whatsapp_message_id) VALUES (?, ?, ?, 'texto', ?, 'enviado', ?, ?)");
    $stmt->bind_param('iissss', $canal_id, $cliente_id, $mensagem, $data_hora, $status, $message_id);
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar mensagem no banco: ' . $stmt->error]);
    exit;
}

$mensagem_id = $stmt->insert_id;
$stmt->close();

// Invalidar cache de conversas para atualizar a lista
if (function_exists('cache_forget')) {
    cache_forget('conversas_recentes');
    cache_forget("mensagens_{$cliente_id}");
}

echo json_encode([
    'success' => true,
    'message' => 'Mensagem enviada com sucesso',
    'mensagem_id' => $mensagem_id,
    'whatsapp_message_id' => $message_id,
    'numero_enviado' => $numero
]);
?> 