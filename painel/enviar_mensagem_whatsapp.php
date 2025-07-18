<?php
date_default_timezone_set('America/Sao_Paulo');
require_once '../config.php';
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
// Função simplificada para formatar número (apenas código do país + DDD + número)
function ajustarNumeroWhatsapp($numero) {
    // Remover todos os caracteres não numéricos
    $numero = preg_replace('/\D/', '', $numero);
    
    // Se já tem código do país (55), remover para processar
    if (strpos($numero, '55') === 0) {
        $numero = substr($numero, 2);
    }
    
    // Verificar se tem pelo menos DDD (2 dígitos) + número (8 dígitos)
    if (strlen($numero) < 10) {
        return null; // Número muito curto
    }
    
    // Extrair DDD e número
    $ddd = substr($numero, 0, 2);
    $telefone = substr($numero, 2);
    
    // Retornar no formato: 55 + DDD + número
    // Deixar o número como está (você gerencia as regras no cadastro)
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
// Montar payload correto para o robô
$payload = [
    'to' => $numero,
    'message' => $mensagem
];

// Usar cURL em vez de file_get_contents para melhor controle
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, WHATSAPP_ROBOT_URL . "/send/text");
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
if (!$resposta || $http_code !== 200) {
    $erro = 'Robô não respondeu corretamente. HTTP: ' . $http_code . ' - Resposta: ' . $resposta;
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

$resposta_json = json_decode($resposta, true);

// Verificar se a resposta JSON é válida e se o envio foi bem-sucedido
if (!$resposta_json) {
    $erro = 'Resposta inválida do robô: ' . $resposta;
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

// Verificar se o envio foi bem-sucedido
$success_check = isset($resposta_json['success']) && $resposta_json['success'] === true;

if (!$success_check) {
    $erro = isset($resposta_json['error']) ? $resposta_json['error'] : 'Falha ao enviar mensagem pelo robô.';
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

// Extrair ID da mensagem se disponível
$message_id = isset($resposta_json['messageId']) ? $resposta_json['messageId'] : null;
$status_envio = isset($resposta_json['status']) ? $resposta_json['status'] : 'enviado';

// Registra mensagem curta APENAS se o envio foi sucesso
$notificacao = 'Cobrança enviada via WhatsApp em ' . date('d/m/Y H:i');
$data_hora = date('Y-m-d H:i:s');

if ($cobranca_id) {
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status, anexo) VALUES (?, ?, ?, ?, 'texto', ?, 'enviado', ?, ?)");
    $stmt->bind_param('iiissss', $canal_id, $cliente_id, $cobranca_id, $notificacao, $data_hora, $status_envio, $message_id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, anexo) VALUES (?, ?, ?, 'texto', ?, 'enviado', ?, ?)");
    $stmt->bind_param('iissss', $canal_id, $cliente_id, $notificacao, $data_hora, $status_envio, $message_id);
}
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true, 
    'msg' => 'Mensagem registrada e enviada ao robô.',
    'messageId' => $message_id,
    'status' => $status_envio
]); 