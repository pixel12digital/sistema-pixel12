<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Carregar configurações
require_once __DIR__ . '/config.php';
require_once 'src/db.php';

// Carregar autoloader ou classes necessárias
require_once 'src/Services/AsaasService.php';
require_once 'src/Models/Fatura.php';
require_once 'src/Models/Assinatura.php';
require_once 'src/Controllers/Financeiro/FaturasController.php';
require_once 'src/Controllers/Financeiro/AssinaturasController.php';

// Obter a URL da requisição
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/';
$path = parse_url($request_uri, PHP_URL_PATH); // pega só o path da URL
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}
$path = ltrim($path, '/');

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

// Debug: mostrar a URL processada (SOMENTE para não-webhook)
echo "URL processada: '$path'<br>";

// Página inicial
if ($path === '' || $path === '/') {
    echo '<h1>Bem-vindo ao sistema!</h1>';
    exit;
}

// Debug: mostrar a URL processada
// echo "URL processada: '$path'<br>";

// Roteamento simples baseado na URL
if ($path === 'financeiro/faturas') {
    $controller = new App\Controllers\Financeiro\FaturasController();
    $controller->index();
} elseif (preg_match('/^financeiro\/faturas\/(\d+)$/', $path, $matches)) {
    $controller = new App\Controllers\Financeiro\FaturasController();
    $controller->show($matches[1]);
} elseif ($path === 'financeiro/faturas/sync') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new App\Controllers\Financeiro\FaturasController();
        $controller->sync();
    } else {
        http_response_code(405);
        echo 'Método não permitido';
    }
} elseif ($path === 'webhook/asaas/faturas') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new App\Controllers\Financeiro\FaturasController();
        $controller->webhook();
    } else {
        http_response_code(405);
        echo 'Método não permitido';
    }
} elseif ($path === 'financeiro/assinaturas') {
    $controller = new App\Controllers\Financeiro\AssinaturasController();
    $controller->index();
} elseif (preg_match('/^financeiro\/assinaturas\/(\d+)$/', $path, $matches)) {
    $controller = new App\Controllers\Financeiro\AssinaturasController();
    $controller->show($matches[1]);
} elseif ($path === 'financeiro/assinaturas/sync') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new App\Controllers\Financeiro\AssinaturasController();
        $controller->sync();
    } else {
        http_response_code(405);
        echo 'Método não permitido';
    }
} else {
    http_response_code(404);
    echo 'Página não encontrada: ' . $path;
} 