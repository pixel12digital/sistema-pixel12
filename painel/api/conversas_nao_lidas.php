<?php
require_once '../config.php';
require_once '../db.php';
require_once '../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=30'); // Cache HTTP de 30 segundos

// Buscar conversas com mensagens não lidas
$conversas_nao_lidas = cache_remember("conversas_nao_lidas", function() use ($mysqli) {
    $sql = "SELECT DISTINCT
                c.id as cliente_id,
                c.nome,
                c.celular,
                c.telefone,
                ch.nome_exibicao as canal_nome,
                COUNT(mc.id) as total_nao_lidas,
                MAX(mc.data_hora) as ultima_nao_lida
            FROM mensagens_comunicacao mc
            INNER JOIN clientes c ON mc.cliente_id = c.id
            LEFT JOIN canais_comunicacao ch ON mc.canal_id = ch.id
            WHERE mc.direcao = 'recebido' 
            AND mc.status != 'lido'
            AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY c.id, c.nome, c.celular, c.telefone, ch.nome_exibicao
            ORDER BY ultima_nao_lida DESC
            LIMIT 50";
    
    $result = $mysqli->query($sql);
    $conversas = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $conversas[] = $row;
        }
    }
    
    return $conversas;
}, 30); // Cache de 30 segundos

// Contar total de mensagens não lidas globalmente
$total_global = cache_remember("total_mensagens_nao_lidas", function() use ($mysqli) {
    $sql = "SELECT COUNT(*) as total 
            FROM mensagens_comunicacao 
            WHERE direcao = 'recebido' 
            AND status != 'lido'
            AND data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    
    return intval($row['total']);
}, 30); // Cache de 30 segundos

echo json_encode([
    'success' => true,
    'conversas' => $conversas_nao_lidas,
    'total_global' => $total_global,
    'timestamp' => time()
]);
?> 