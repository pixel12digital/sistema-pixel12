<?php
/**
 * 📊 MONITOR DE REQUISIÇÕES
 * Monitora o uso de requisições para evitar exceder o limite de 500/hora
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Configurações
$limite_requisicoes = 500;
$limite_alerta = 400; // Alertar quando chegar a 80%

try {
    // 1. Verificar requisições na última hora
    $sql = "SELECT COUNT(*) as total_ultima_hora 
            FROM notificacoes_push 
            WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    
    $result = $mysqli->query($sql);
    $requisicoes_ultima_hora = $result->fetch_assoc()['total_ultima_hora'];
    
    // 2. Verificar requisições hoje
    $sql = "SELECT COUNT(*) as total_hoje 
            FROM notificacoes_push 
            WHERE DATE(data_hora) = CURDATE()";
    
    $result = $mysqli->query($sql);
    $requisicoes_hoje = $result->fetch_assoc()['total_hoje'];
    
    // 3. Verificar requisições por cliente (top 5)
    $sql = "SELECT cliente_id, COUNT(*) as total 
            FROM notificacoes_push 
            WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            GROUP BY cliente_id 
            ORDER BY total DESC 
            LIMIT 5";
    
    $result = $mysqli->query($sql);
    $top_clientes = [];
    while ($row = $result->fetch_assoc()) {
        $top_clientes[] = $row;
    }
    
    // 4. Calcular percentual de uso
    $percentual_uso = round(($requisicoes_ultima_hora / $limite_requisicoes) * 100, 2);
    $status = 'normal';
    
    if ($percentual_uso >= 90) {
        $status = 'critico';
    } elseif ($percentual_uso >= $limite_alerta) {
        $status = 'alerta';
    }
    
    // 5. Estatísticas de cache
    $cache_dir = sys_get_temp_dir();
    $cache_files = glob($cache_dir . "/push_cache_*.json");
    $cache_hits = 0;
    $cache_misses = 0;
    
    foreach ($cache_files as $file) {
        if (file_exists($file)) {
            $cache_data = json_decode(file_get_contents($file), true);
            if ($cache_data && isset($cache_data['cached'])) {
                $cache_hits++;
            } else {
                $cache_misses++;
            }
        }
    }
    
    $response = [
        'success' => true,
        'monitoramento' => [
            'requisicoes_ultima_hora' => $requisicoes_ultima_hora,
            'requisicoes_hoje' => $requisicoes_hoje,
            'limite_requisicoes' => $limite_requisicoes,
            'percentual_uso' => $percentual_uso,
            'status' => $status,
            'restantes_esta_hora' => max(0, $limite_requisicoes - $requisicoes_ultima_hora),
            'top_clientes' => $top_clientes,
            'cache' => [
                'arquivos_cache' => count($cache_files),
                'cache_hits' => $cache_hits,
                'cache_misses' => $cache_misses
            ]
        ]
    ];
    
    // 6. Alertas se necessário
    if ($status === 'critico') {
        $response['alerta'] = '🚨 CRÍTICO: Limite de requisições quase atingido!';
        error_log("[MONITOR REQUISIÇÕES] 🚨 CRÍTICO: $requisicoes_ultima_hora/$limite_requisicoes requisições na última hora");
    } elseif ($status === 'alerta') {
        $response['alerta'] = '⚠️ ALERTA: Uso de requisições elevado';
        error_log("[MONITOR REQUISIÇÕES] ⚠️ ALERTA: $requisicoes_ultima_hora/$limite_requisicoes requisições na última hora");
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("[MONITOR REQUISIÇÕES] ❌ Erro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
?> 