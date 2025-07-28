<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
$last_timestamp = isset($_GET['last_timestamp']) ? intval($_GET['last_timestamp']) : 0;

if (!$cliente_id) {
    echo json_encode(['has_new_messages' => false, 'error' => 'Cliente ID inválido']);
    exit;
}

// Cache inteligente adaptativo
$cacheTimeout = 10; // Padrão 10s
$activityFile = sys_get_temp_dir() . "/chat_activity_{$cliente_id}.json";

// Verificar atividade recente
if (file_exists($activityFile)) {
    $activity = json_decode(file_get_contents($activityFile), true);
    $timeSinceActivity = time() - $activity['last_activity'];
    
    if ($timeSinceActivity < 60) {
        // Atividade recente - cache menor
        $cacheTimeout = 5;
    } elseif ($timeSinceActivity < 300) {
        // Atividade moderada - cache médio
        $cacheTimeout = 15;
    } else {
        // Sem atividade - cache longo
        $cacheTimeout = 30;
    }
}

// Cache simples em arquivo para evitar consultas desnecessárias
$cacheFile = sys_get_temp_dir() . "/chat_cache_{$cliente_id}.json";

if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData && (time() - $cacheData['timestamp']) < $cacheTimeout) {
        // Retornar dados do cache se ainda válidos
        echo json_encode([
            'has_new_messages' => $cacheData['latest_timestamp'] > $last_timestamp,
            'count' => $cacheData['latest_timestamp'] > $last_timestamp ? 1 : 0,
            'latest_timestamp' => $cacheData['latest_timestamp'],
            'cached' => true
        ]);
        exit;
    }
}

// Consulta otimizada - apenas buscar o timestamp mais recente
$sql = "SELECT MAX(UNIX_TIMESTAMP(data_hora)) as latest_timestamp 
        FROM mensagens_comunicacao 
        WHERE cliente_id = ? 
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['has_new_messages' => false, 'error' => 'Erro na preparação da query']);
    exit;
}

$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$latest_timestamp = $row['latest_timestamp'] ?: $last_timestamp;
$has_new = $latest_timestamp > $last_timestamp;

// Só contar mensagens se realmente houver novas
$count = 0;
if ($has_new) {
    $count_sql = "SELECT COUNT(*) as count 
                  FROM mensagens_comunicacao 
                  WHERE cliente_id = ? 
                  AND UNIX_TIMESTAMP(data_hora) > ?";
    
    $stmt_count = $mysqli->prepare($count_sql);
    $stmt_count->bind_param('ii', $cliente_id, $last_timestamp);
    $stmt_count->execute();
    $count_result = $stmt_count->get_result();
    $count_row = $count_result->fetch_assoc();
    $count = intval($count_row['count']);
    $stmt_count->close();
}

// Salvar no cache
$cacheData = [
    'timestamp' => time(),
    'latest_timestamp' => $latest_timestamp,
    'count' => $count
];
file_put_contents($cacheFile, json_encode($cacheData));

echo json_encode([
    'has_new_messages' => $has_new,
    'count' => $count,
    'latest_timestamp' => intval($latest_timestamp),
    'cached' => false
]);
?> 