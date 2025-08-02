<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log para debug
$log_file = 'logs/webhook_canal_37_' . date('Y-m-d') . '.log';
$timestamp = date('Y-m-d H:i:s');

// Função para log
function logWebhook($message) {
    global $log_file, $timestamp;
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Log inicial
logWebhook("=== WEBHOOK CANAL 37 (COMERCIAL) INICIADO ===");

// Verificar método
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logWebhook("❌ Método não permitido: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Obter dados do POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

logWebhook("📨 Dados recebidos: " . substr($input, 0, 500));

if (!$data) {
    logWebhook("❌ Dados JSON inválidos");
    http_response_code(400);
    echo json_encode(['error' => 'Dados JSON inválidos']);
    exit();
}

// Verificar diferentes formatos de mensagem
$from = null;
$body = null;
$message_id = null;

// Formato 1: Evento onmessage (formato padrão do WPPConnect)
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    $from = $message['from'] ?? null;
    $body = $message['text'] ?? $message['body'] ?? '';
    $message_id = $message['id'] ?? '';
    logWebhook("📝 Formato detectado: Evento onmessage");
}

// Formato 2: Mensagem direta
elseif (isset($data['from']) && isset($data['message'])) {
    $from = $data['from'];
    $body = $data['message'];
    $message_id = $data['id'] ?? '';
    logWebhook("📝 Formato detectado: Mensagem direta");
}

// Formato 3: Mensagem com body
elseif (isset($data['from']) && isset($data['body'])) {
    $from = $data['from'];
    $body = $data['body'];
    $message_id = $data['id'] ?? '';
    logWebhook("📝 Formato detectado: Mensagem com body");
}

// Formato 4: Mensagem com text
elseif (isset($data['from']) && isset($data['text'])) {
    $from = $data['from'];
    $body = $data['text'];
    $message_id = $data['id'] ?? '';
    logWebhook("📝 Formato detectado: Mensagem com text");
}

if (!$from || !$body) {
    logWebhook("❌ Dados incompletos - from ou body não encontrados");
    logWebhook("   From: " . ($from ?: 'NULO'));
    logWebhook("   Body: " . ($body ?: 'NULO'));
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos - from ou body não encontrados']);
    exit();
}

// Configuração do banco comercial (Hostinger)
$db_host = 'srv1607.hstgr.io';
$db_user = 'u342734079_wts_com_pixel';
$db_pass = 'Los@ngo#081081';
$db_name = 'u342734079_wts_com_pixel';

try {
    // Conectar ao banco comercial
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($mysqli->connect_error) {
        logWebhook("❌ Erro ao conectar ao banco comercial: " . $mysqli->connect_error);
        throw new Exception("Erro de conexão com banco: " . $mysqli->connect_error);
    }
    
    logWebhook("✅ Conectado ao banco comercial: $db_name");
    
    // Extrair dados da mensagem
    $to = $data['to'] ?? '';
    $timestamp = $data['timestamp'] ?? time();
    
    // Limpar número do WhatsApp
    $numero_whatsapp = str_replace('@c.us', '', $from);
    
    // Converter timestamp
    $data_hora = date('Y-m-d H:i:s', $timestamp);
    
    // Canal ID fixo para comercial
    $canal_id = 37;
    
    // CORREÇÃO: Buscar ou criar cliente no banco comercial
    $cliente_id = null;
    $numero_limpo = preg_replace('/\D/', '', $numero_whatsapp);
    
    logWebhook("🔍 Buscando cliente para número: $numero_limpo");
    
    // Tentar encontrar cliente existente
    $sql_cliente = "SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_limpo%' OR telefone LIKE '%$numero_limpo%' LIMIT 1";
    $result_cliente = $mysqli->query($sql_cliente);
    
    if ($result_cliente && $result_cliente->num_rows > 0) {
        $cliente = $result_cliente->fetch_assoc();
        $cliente_id = $cliente['id'];
        logWebhook("✅ Cliente encontrado: {$cliente['nome']} (ID: $cliente_id)");
    } else {
        // Criar cliente automaticamente no banco comercial
        logWebhook("🆕 Cliente não encontrado, criando novo...");
        
        $nome_cliente = "Cliente WhatsApp Comercial (" . $numero_limpo . ")";
        $data_criacao = date("Y-m-d H:i:s");
        
        $sql_criar = "INSERT INTO clientes (nome, celular, data_criacao, data_atualizacao) 
                      VALUES (?, ?, ?, ?)";
        
        $stmt_criar = $mysqli->prepare($sql_criar);
        $stmt_criar->bind_param('ssss', $nome_cliente, $numero_limpo, $data_criacao, $data_criacao);
        
        if ($stmt_criar->execute()) {
            $cliente_id = $mysqli->insert_id;
            logWebhook("✅ Cliente criado automaticamente - ID: $cliente_id");
        } else {
            logWebhook("❌ Erro ao criar cliente: " . $stmt_criar->error);
        }
        $stmt_criar->close();
    }
    
    logWebhook("📊 Dados processados:");
    logWebhook("   From: $from");
    logWebhook("   To: $to");
    logWebhook("   Body: " . substr($body, 0, 100));
    logWebhook("   Número: $numero_whatsapp");
    logWebhook("   Cliente ID: $cliente_id");
    logWebhook("   Canal ID: $canal_id");
    
    // Inserir mensagem no banco comercial COM cliente_id
    $sql = "INSERT INTO mensagens_comunicacao (
        canal_id, 
        cliente_id,
        numero_whatsapp, 
        mensagem, 
        tipo, 
        data_hora, 
        direcao, 
        status
    ) VALUES (?, ?, ?, ?, 'texto', ?, 'recebido', 'recebido')";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iisss", $canal_id, $cliente_id, $numero_whatsapp, $body, $data_hora);
    
    if ($stmt->execute()) {
        $mensagem_id = $mysqli->insert_id;
        logWebhook("✅ Mensagem salva com sucesso - ID: $mensagem_id");
        
        // Atualizar contador de mensagens do canal (se a tabela existir)
        try {
            $sql_update = "UPDATE canais_comunicacao SET total_mensagens = total_mensagens + 1 WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("i", $canal_id);
            $stmt_update->execute();
            $stmt_update->close();
            logWebhook("✅ Contador do canal atualizado");
        } catch (Exception $e) {
            logWebhook("⚠️ Não foi possível atualizar contador: " . $e->getMessage());
        }
        
        $stmt->close();
        $mysqli->close();
        
        echo json_encode([
            'status' => 'ok',
            'ambiente' => 'PRODUÇÃO',
            'canal' => 'COMERCIAL',
            'mensagem_id' => $mensagem_id,
            'cliente_id' => $cliente_id,
            'numero_limpo' => $numero_limpo,
            'numero_whatsapp' => $numero_whatsapp,
            'debug_cliente_encontrado' => $cliente_id ? 'SIM' : 'NÃO',
            'timestamp' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://app.pixel12digital.com.br/api/webhook_canal_37.php'
        ]);
        
    } else {
        logWebhook("❌ Erro ao salvar mensagem: " . $stmt->error);
        $stmt->close();
        $mysqli->close();
        
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao salvar mensagem']);
    }
    
} catch (Exception $e) {
    logWebhook("❌ Exceção: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

logWebhook("=== WEBHOOK CANAL 37 FINALIZADO ===\n");
?> 