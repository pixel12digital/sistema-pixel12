<?php
require_once __DIR__ . '/../config.php';
require_once '../db.php';
require_once '../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=120'); // Cache HTTP de 2 minutos

$termo = trim($_GET['termo'] ?? '');

if (strlen($termo) < 3) {
    echo json_encode([]);
    exit;
}

// Cache para busca de clientes
$clientes = cache_remember("buscar_clientes_" . md5($termo), function() use ($termo, $mysqli) {
    $termo_sql = $mysqli->real_escape_string($termo);
    
    // Query otimizada com índices
    $sql = "SELECT id, nome, email, celular 
            FROM clientes 
            WHERE nome LIKE ? OR email LIKE ? OR celular LIKE ? 
            ORDER BY nome 
            LIMIT 20";
    
    $stmt = $mysqli->prepare($sql);
    $termo_like = "%{$termo}%";
    $stmt->bind_param('sss', $termo_like, $termo_like, $termo_like);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clientes = [];
    while ($cli = $result->fetch_assoc()) {
        $clientes[] = $cli;
    }
    $stmt->close();
    
    return $clientes;
}, 300); // Cache de 5 minutos para buscas

header('X-Cache-Status: ' . (cache_get("buscar_clientes_" . md5($termo)) ? 'HIT' : 'MISS'));
echo json_encode($clientes);
?> 