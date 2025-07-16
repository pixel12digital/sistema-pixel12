<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'db.php';
require_once 'cache_invalidator.php'; // Sistema de invalidação de cache

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
$canal_id = isset($_POST['canal_id']) ? intval($_POST['canal_id']) : 0;

// Validações
if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'Cliente ID inválido']);
    exit;
}

if (empty($mensagem)) {
    echo json_encode(['success' => false, 'error' => 'Mensagem não pode estar vazia']);
    exit;
}

if (!$canal_id) {
    echo json_encode(['success' => false, 'error' => 'Canal ID inválido']);
    exit;
}

// Usar cache para verificar cliente (mais rápido)
$cliente = cache_cliente($cliente_id, $mysqli);
if (!$cliente) {
    echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
    exit;
}

// Verificar se canal existe e está conectado usando cache
$canais = cache_status_canais($mysqli);
$canal = null;
foreach ($canais as $c) {
    if ($c['id'] == $canal_id) {
        $canal = $c;
        break;
    }
}

if (!$canal) {
    echo json_encode(['success' => false, 'error' => 'Canal não encontrado ou não conectado']);
    exit;
}

// Processar anexo se houver
$anexo_path = '';
if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido']);
        exit;
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $anexo_path = $upload_dir . $filename;
    
    if (!move_uploaded_file($_FILES['anexo']['tmp_name'], $anexo_path)) {
        echo json_encode(['success' => false, 'error' => 'Falha ao salvar anexo']);
        exit;
    }
}

// Salvar mensagem no banco
$numero = $cliente['celular'];
$data_hora = date('Y-m-d H:i:s');

$sql = "INSERT INTO mensagens_comunicacao (cliente_id, canal_id, mensagem, anexo, direcao, status, data_hora, tipo) VALUES (?, ?, ?, ?, 'enviado', 'enviado', ?, 'texto')";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Erro na preparação da query']);
    exit;
}

$stmt->bind_param('iisss', $cliente_id, $canal_id, $mensagem, $anexo_path, $data_hora);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar mensagem']);
    exit;
}

$mensagem_id = $mysqli->insert_id;

// INVALIDAR CACHE após inserir nova mensagem
invalidate_message_cache($cliente_id, [
    'id' => $mensagem_id,
    'cliente_id' => $cliente_id,
    'mensagem' => $mensagem,
    'data_hora' => $data_hora
]);

// Enviar via API do robô
$enviado_api = false;

try {
    $api_url = "http://localhost:3000/send";
    $api_data = [
        'to' => $numero,
        'message' => $mensagem
    ];
    
    if (!empty($anexo_path)) {
        $api_data['attachment'] = $anexo_path;
    }
    
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
            $enviado_api = true;
            
            // Atualizar com message ID da API se disponível
            if (isset($api_result['messageId'])) {
                $mysqli->query("UPDATE mensagens_comunicacao SET whatsapp_message_id = '" . $mysqli->real_escape_string($api_result['messageId']) . "' WHERE id = $mensagem_id");
                
                // Re-invalidar cache com dados atualizados
                invalidate_message_cache($cliente_id);
            }
        }
    }
} catch (Exception $e) {
    error_log("Erro ao enviar via API: " . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'mensagem_id' => $mensagem_id,
    'enviado_api' => $enviado_api,
    'cache_invalidated' => true // Indicar que cache foi invalidado
]);
?> 