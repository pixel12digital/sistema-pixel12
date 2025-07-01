<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Carregar configurações
require_once 'config.php';
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

// Debug: mostrar a URL processada
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