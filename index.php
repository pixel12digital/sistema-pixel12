<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Carregar configurações
require_once __DIR__ . '/config.php';
require_once 'src/db.php';

// Carregar autoloader ou classes necessárias
require_once 'src/Services/AsaasService.php';
require_once 'src/Services/WhatsAppService.php';
require_once 'src/Models/Fatura.php';
require_once 'src/Models/Assinatura.php';
require_once 'src/Controllers/Financeiro/FaturasController.php';
require_once 'src/Controllers/Financeiro/AssinaturasController.php';
require_once 'src/Controllers/WhatsAppController.php';

// Usar namespaces
use App\Controllers\WhatsAppController;
use App\Controllers\Financeiro\FaturasController;
use App\Controllers\Financeiro\AssinaturasController;

// Obter a URL da requisição
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/';
$path = parse_url($request_uri, PHP_URL_PATH); // pega só o path da URL

// Debug: Log da URL processada
error_log("[ROTEAMENTO_DEBUG] Request URI: $request_uri | Path: $path");

// Remover o base_path se necessário
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}
$path = ltrim($path, '/');

// Remover o diretório base do projeto se presente
if (strpos($path, 'loja-virtual-revenda/') === 0) {
    $path = substr($path, strlen('loja-virtual-revenda/'));
}

// Debug: Log do path final
error_log("[ROTEAMENTO_DEBUG] Path final: '$path'");

// 🚨 ROTA WEBHOOK ANA - PRIORIDADE MÁXIMA (ANTES DO DEBUG)
if ($path === 'webhook.php' || $path === 'webhook' || $path === 'webhook_ana.php') {
    header('Content-Type: application/json');
    
    try {
        // Log de entrada
        error_log("[WEBHOOK_ROTEAMENTO] " . date('Y-m-d H:i:s') . " - Webhook Ana ativado via: $path");
        
        // Capturar dados
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: $_GET;
        
        $from = $data['from'] ?? $data['number'] ?? 'desconhecido';
        $body = $data['body'] ?? $data['message'] ?? '';
        
        // Log dados
        error_log("[WEBHOOK_ROTEAMENTO] From: $from | Body: $body");
        
        // Resposta simples
        $resposta = "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo?";
        
        // Detectar intenção
        $msg = strtolower($body);
        $acao = 'nenhuma';
        
        if (strpos($msg, 'site') !== false || strpos($msg, 'loja') !== false) {
            $resposta = "🌐 Vou conectar você com Rafael para sites e lojas virtuais!";
            $acao = 'transfer_rafael';
        } elseif (strpos($msg, 'problema') !== false || strpos($msg, 'erro') !== false) {
            $resposta = "🔧 Transferindo para suporte técnico!";
            $acao = 'transfer_suporte';
        } elseif (strpos($msg, 'pessoa') !== false || strpos($msg, 'humano') !== false) {
            $resposta = "👥 Conectando com atendimento humano!";
            $acao = 'transfer_humano';
        }
        
        // Log ação
        if ($acao !== 'nenhuma') {
            error_log("[WEBHOOK_ROTEAMENTO] AÇÃO: $acao | Cliente: $from");
        }
        
        // Resposta JSON
        $response = array(
            'success' => true,
            'ana_response' => $resposta,
            'action_taken' => $acao,
            'timestamp' => date('Y-m-d H:i:s'),
            'webhook_version' => 'roteamento_integrado'
        );
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        error_log("[WEBHOOK_ROTEAMENTO] ERRO: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'ana_response' => 'Sistema em manutenção',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// 🚨 ROTA WEBHOOK_SEM_REDIRECT - PRIORIDADE ALTA
if (strpos($path, 'webhook_sem_redirect/') === 0) {
    $file_path = __DIR__ . '/' . $path;
    
    // Verificar se o arquivo existe
    if (file_exists($file_path)) {
        // Incluir o arquivo diretamente
        include $file_path;
        exit;
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Arquivo não encontrado',
            'path' => $path,
            'file_path' => $file_path
        ]);
        exit;
    }
}

// 🚀 ROTAS WHATSAPP - NOVA SOLUÇÃO
if (strpos($path, 'whatsapp/') === 0 || $path === 'whatsapp') {
    error_log("[ROTEAMENTO_DEBUG] WhatsApp route detectada: $path");
    
    try {
        $whatsappController = new WhatsAppController();
        
        // Extrair a ação da URL
        $whatsapp_path = $path === 'whatsapp' ? '' : substr($path, 9); // Remove 'whatsapp/'
        
        error_log("[ROTEAMENTO_DEBUG] WhatsApp path: '$whatsapp_path'");
        
        switch ($whatsapp_path) {
            case '':
            case 'index':
                $whatsappController->index();
                break;
            case 'dashboard':
                $whatsappController->dashboard();
                break;
            case 'config':
                $whatsappController->config();
                break;
            case 'logs':
                $whatsappController->logs();
                break;
            case 'status':
                $whatsappController->getStatus();
                break;
            case 'qr':
                $whatsappController->getQRCode();
                break;
            case 'send':
                $whatsappController->sendMessage();
                break;
            case 'webhook':
                $whatsappController->configureWebhook();
                break;
            case 'test':
                $whatsappController->testConnection();
                break;
            case 'session':
                $whatsappController->getSessionInfo();
                break;
            case 'disconnect':
                $whatsappController->disconnectSession();
                break;
            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Endpoint WhatsApp não encontrado',
                    'path' => $whatsapp_path,
                    'debug' => [
                        'original_path' => $path,
                        'whatsapp_path' => $whatsapp_path
                    ]
                ]);
                break;
        }
        exit;
    } catch (Exception $e) {
        error_log("[ROTEAMENTO_DEBUG] Erro no WhatsAppController: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erro interno do servidor',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Página inicial
if ($path === '' || $path === '/') {
    echo '<h1>Bem-vindo ao sistema!</h1>';
    echo '<p><a href="/loja-virtual-revenda/whatsapp">Gerenciar WhatsApp</a></p>';
    echo '<p><a href="/loja-virtual-revenda/financeiro/faturas">Financeiro</a></p>';
    exit;
}

// Roteamento simples baseado na URL
if ($path === 'financeiro/faturas') {
    $controller = new FaturasController();
    $controller->index();
} elseif (preg_match('/^financeiro\/faturas\/(\d+)$/', $path, $matches)) {
    $controller = new FaturasController();
    $controller->show($matches[1]);
} elseif ($path === 'financeiro/faturas/sync') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new FaturasController();
        $controller->sync();
    } else {
        http_response_code(405);
        echo 'Método não permitido';
    }
} elseif ($path === 'webhook/asaas/faturas') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new FaturasController();
        $controller->webhook();
    } else {
        http_response_code(405);
        echo 'Método não permitido';
    }
} elseif ($path === 'financeiro/assinaturas') {
    $controller = new AssinaturasController();
    $controller->index();
} elseif (preg_match('/^financeiro\/assinaturas\/(\d+)$/', $path, $matches)) {
    $controller = new AssinaturasController();
    $controller->show($matches[1]);
} elseif ($path === 'financeiro/assinaturas/sync') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new AssinaturasController();
        $controller->sync();
    } else {
        http_response_code(405);
        echo 'Método não permitido';
    }
} else {
    http_response_code(404);
    echo 'Página não encontrada: ' . $path;
} 