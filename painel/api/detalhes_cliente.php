<?php
require_once __DIR__ . '/../../config.php';
require_once '../db.php';
require_once '../cache_manager.php';
require_once '../components_cliente.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: private, max-age=30'); // Cache HTTP de 30 segundos

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
$modo_edicao = isset($_GET['editar']) && $_GET['editar'] == '1';

if (!$cliente_id) {
    echo '<div class="text-center text-gray-400">Cliente não encontrado.</div>';
    exit;
}

// Incluir o arquivo JavaScript com as funções de edição e exclusão
echo '<script src="../assets/chat-functions.js"></script>';

// Não usar cache se estiver em modo edição
if ($modo_edicao) {
    render_cliente_ficha($cliente_id, true);
} else {
    // Usar cache para detalhes do cliente
    $output = cache_remember("detalhes_cliente_{$cliente_id}", function() use ($cliente_id) {
        ob_start();
        render_cliente_ficha($cliente_id, false);
        return ob_get_clean();
    }, 180); // Cache de 3 minutos
    echo $output;
}
?> 