<?php
ini_set('display_errors', 1);
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Entrou no script\n", FILE_APPEND);
header('Content-Type: application/json');
error_reporting(E_ALL);

try {
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Antes do require config\n", FILE_APPEND);
require_once __DIR__ . '/../config.php';
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Depois do require config\n", FILE_APPEND);
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Antes do require db\n", FILE_APPEND);
require_once 'db.php';
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Depois do require db\n", FILE_APPEND);
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Antes do require cache_invalidator\n", FILE_APPEND);
require_once 'cache_invalidator.php'; // Sistema de invalidação de cache
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Depois do require cache_invalidator\n", FILE_APPEND);
require_once 'cache_manager.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: método não permitido\n", FILE_APPEND);
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou validação método POST\n", FILE_APPEND);

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
$canal_id = isset($_POST['canal_id']) ? intval($_POST['canal_id']) : 0;

// Validações
if (!$cliente_id) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Cliente ID inválido\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Cliente ID inválido']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou validação cliente_id\n", FILE_APPEND);

if (empty($mensagem)) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Mensagem vazia\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Mensagem não pode estar vazia']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou validação mensagem\n", FILE_APPEND);

if (!$canal_id) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Canal ID inválido\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Canal ID inválido']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou validação canal_id\n", FILE_APPEND);

// Usar cache para verificar cliente (mais rápido)
$cliente = cache_cliente($cliente_id, $mysqli);
if (!$cliente) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Cliente não encontrado\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou validação cliente encontrado\n", FILE_APPEND);

// Verificar se canal existe e está conectado usando cache
$canais = cache_status_canais($mysqli);
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - [chat_enviar] canais retornados: " . var_export($canais, true) . "\n", FILE_APPEND);
$canal = null;
foreach ($canais as $c) {
    if ($c['id'] == $canal_id) {
        $canal = $c;
        break;
    }
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - [chat_enviar] canal encontrado: " . var_export($canal, true) . "\n", FILE_APPEND);
if (!$canal) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Canal não encontrado ou não conectado\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Canal não encontrado ou não conectado']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou validação canal conectado\n", FILE_APPEND);

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
        file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Tipo de arquivo não permitido\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Tipo de arquivo não permitido']);
        exit;
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $anexo_path = $upload_dir . $filename;
    
    if (!move_uploaded_file($_FILES['anexo']['tmp_name'], $anexo_path)) {
        file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Falha ao salvar anexo\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Falha ao salvar anexo']);
        exit;
    }
}

file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Antes de salvar mensagem no banco\n", FILE_APPEND);
// Salvar mensagem no banco
$numero = $cliente['celular'];

// CORREÇÃO: Formatar número corretamente para WhatsApp
$numero_limpo = preg_replace('/\D/', '', $numero);

// Verificar se já tem código 55 no início
if (strpos($numero_limpo, '55') === 0) {
    // Se já tem 55, usar como está
    $numero_formatado = $numero_limpo . '@c.us';
} else {
    // Se não tem 55, adicionar
    $numero_formatado = '55' . $numero_limpo . '@c.us';
}

file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Número original: $numero\n", FILE_APPEND);
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Número formatado: $numero_formatado\n", FILE_APPEND);

$data_hora = date('Y-m-d H:i:s');

$sql = "INSERT INTO mensagens_comunicacao (cliente_id, canal_id, mensagem, anexo, direcao, status, data_hora, tipo) VALUES (?, ?, ?, ?, 'enviado', 'enviado', ?, 'texto')";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Erro na preparação da query\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Erro na preparação da query']);
    exit;
}
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Passou preparação da query\n", FILE_APPEND);

$stmt->bind_param('iisss', $cliente_id, $canal_id, $mensagem, $anexo_path, $data_hora);

if (!$stmt->execute()) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Saiu por: Erro ao salvar mensagem\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar mensagem']);
    exit;
}

$mensagem_id = $mysqli->insert_id;
file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Mensagem salva no banco, id: $mensagem_id\n", FILE_APPEND);

// INVALIDAR CACHE após inserir nova mensagem
invalidate_message_cache($cliente_id);
// Forçar limpeza de todos os caches relevantes
if (function_exists('cache_forget')) {
    cache_forget("mensagens_{$cliente_id}");
    cache_forget("historico_html_{$cliente_id}");
    cache_forget("mensagens_html_{$cliente_id}");
}

// Enviar via API do robô
$enviado_api = false;

try {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Antes do envio via API do robô\n", FILE_APPEND);
    
    // Buscar informações do canal selecionado
    $canal_info = $mysqli->query("SELECT porta, nome_exibicao FROM canais_comunicacao WHERE id = $canal_id")->fetch_assoc();
    $porta_canal = $canal_info ? $canal_info['porta'] : 3000; // Fallback para porta 3000
    $nome_canal = $canal_info ? $canal_info['nome_exibicao'] : 'Financeiro';
    
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Canal selecionado: $nome_canal (Porta: $porta_canal)\n", FILE_APPEND);
    
    // Determinar a URL da API baseada na porta do canal
    $api_url = WHATSAPP_ROBOT_URL;
    if ($porta_canal == 3001) {
        $api_url = str_replace(':3000', ':3001', WHATSAPP_ROBOT_URL);
    }
    $api_url .= "/send/text";
    
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - URL da API: $api_url\n", FILE_APPEND);
    
    $api_data = [
        'sessionName' => ($porta_canal == 3001) ? 'comercial' : 'default',
        'number' => $numero_formatado,
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
    
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Resposta da API: $api_response | HTTP: $http_code\n", FILE_APPEND);
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
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Erro ao enviar via API: " . $e->getMessage() . "\n", FILE_APPEND);
    error_log("Erro ao enviar via API: " . $e->getMessage());
}

file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Finalizando script com sucesso\n", FILE_APPEND);
echo json_encode([
    'success' => true,
    'mensagem_id' => $mensagem_id,
    'enviado_api' => $enviado_api,
    'cache_invalidated' => true // Indicar que cache foi invalidado
]);

} catch (Throwable $e) {
    file_put_contents('debug_chat_enviar.log', date('Y-m-d H:i:s') . " - Erro fatal: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Erro fatal: ' . $e->getMessage()]);
}
?> 