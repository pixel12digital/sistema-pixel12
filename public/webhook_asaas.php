<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Services/Database.php';
require_once __DIR__ . '/../src/Controllers/FinanceiroController.php';

use Controllers\FinanceiroController;

header('Content-Type: text/plain');

try {
    // Lê o payload JSON do Asaas
    $payload = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo 'Invalid JSON';
        exit;
    }

    // Processa o webhook
    $controller = new FinanceiroController();
    $controller->webhook();

    // Responde OK
    http_response_code(200);
    echo 'OK';
} catch (\Exception $e) {
    http_response_code(500);
    error_log('Webhook Asaas error: ' . $e->getMessage());
    echo 'Error';
} 