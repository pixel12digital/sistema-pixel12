<?php
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=60'); // Cache de 1 minuto

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'sem-conversa';

// Cache em arquivo para reduzir consultas ao banco
$cacheFile = sys_get_temp_dir() . "/clientes_nova_conversa_{$filtro}.json";
$cacheTimeout = 300; // 5 minutos de cache

if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData && (time() - $cacheData['timestamp']) < $cacheTimeout) {
        // Retornar dados do cache
        header('X-Cache: HIT');
        echo json_encode([
            'success' => true,
            'filtro' => $filtro,
            'total' => count($cacheData['clientes']),
            'clientes' => $cacheData['clientes'],
            'cached' => true
        ]);
        exit;
    }
}

header('X-Cache: MISS');

try {
    $clientes = [];
    
    switch ($filtro) {
        case 'sem-conversa':
            // Consulta otimizada - usar NOT EXISTS é mais eficiente que LEFT JOIN
            $sql = "SELECT 
                c.id, c.nome, c.email, c.telefone, c.celular, c.data_criacao
                FROM clientes c 
                WHERE NOT EXISTS (
                    SELECT 1 FROM mensagens_comunicacao m 
                    WHERE m.cliente_id = c.id LIMIT 1
                )
                AND c.data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                ORDER BY c.data_criacao DESC 
                LIMIT 30"; // Reduzido de 50 para 30
            break;
            
        case 'inativo':
            // Consulta otimizada com subquery mais eficiente
            $sql = "SELECT 
                c.id, c.nome, c.email, c.telefone, c.celular, c.data_criacao,
                (SELECT MAX(m.data_hora) 
                 FROM mensagens_comunicacao m 
                 WHERE m.cliente_id = c.id) as ultima_conversa
                FROM clientes c 
                WHERE EXISTS (
                    SELECT 1 FROM mensagens_comunicacao m 
                    WHERE m.cliente_id = c.id 
                    AND m.data_hora < DATE_SUB(NOW(), INTERVAL 30 DAY)
                    LIMIT 1
                )
                AND NOT EXISTS (
                    SELECT 1 FROM mensagens_comunicacao m2
                    WHERE m2.cliente_id = c.id 
                    AND m2.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    LIMIT 1
                )
                ORDER BY (
                    SELECT MAX(m.data_hora) 
                    FROM mensagens_comunicacao m 
                    WHERE m.cliente_id = c.id
                ) ASC 
                LIMIT 30"; // Reduzido de 50 para 30
            break;
            
        default:
            // Todos os clientes com otimização
            $sql = "SELECT 
                c.id, c.nome, c.email, c.telefone, c.celular, c.data_criacao
                FROM clientes c 
                WHERE NOT EXISTS (
                    SELECT 1 FROM mensagens_comunicacao m2 
                    WHERE m2.cliente_id = c.id 
                    AND m2.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    LIMIT 1
                )
                ORDER BY c.data_criacao DESC 
                LIMIT 50"; // Reduzido de 100 para 50
            break;
    }
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro na preparação da query: ' . $mysqli->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Formatar dados do cliente
        $cliente = [
            'id' => intval($row['id']),
            'nome' => $row['nome'],
            'email' => $row['email'] ?: null,
            'telefone' => $row['telefone'] ?: null,
            'celular' => $row['celular'] ?: null,
            'data_criacao' => $row['data_criacao']
        ];
        
        // Calcular dias de inatividade apenas se necessário
        if ($filtro === 'inativo' && isset($row['ultima_conversa'])) {
            $cliente['ultima_conversa'] = $row['ultima_conversa'];
            $cliente['dias_inativo'] = floor((time() - strtotime($row['ultima_conversa'])) / 86400);
        } else {
            $cliente['ultima_conversa'] = null;
            $cliente['dias_inativo'] = null;
        }
        
        $clientes[] = $cliente;
    }
    
    $stmt->close();
    
    // Salvar no cache
    $cacheData = [
        'timestamp' => time(),
        'clientes' => $clientes
    ];
    file_put_contents($cacheFile, json_encode($cacheData));
    
    echo json_encode([
        'success' => true,
        'filtro' => $filtro,
        'total' => count($clientes),
        'clientes' => $clientes,
        'cached' => false
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 