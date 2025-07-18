<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

// Parâmetros de filtro
$status = $_GET['status'] ?? '';
$prioridade = $_GET['prioridade'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$cliente_id = $_GET['cliente_id'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = intval($_GET['limit'] ?? 20);
$offset = ($page - 1) * $limit;

try {
    // Construir query base
    $sql = "SELECT 
                t.id,
                t.numero,
                t.cliente_id,
                t.titulo,
                t.descricao,
                t.status,
                t.prioridade,
                t.categoria,
                t.atendente_id,
                t.data_criacao,
                t.data_atualizacao,
                t.data_fechamento,
                c.nome as cliente_nome,
                c.celular as cliente_celular,
                c.email as cliente_email,
                u.nome as atendente_nome
            FROM tickets t
            LEFT JOIN clientes c ON t.cliente_id = c.id
            LEFT JOIN usuarios u ON t.atendente_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
    if ($status) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }
    
    if ($prioridade) {
        $sql .= " AND t.prioridade = ?";
        $params[] = $prioridade;
    }
    
    if ($categoria) {
        $sql .= " AND t.categoria = ?";
        $params[] = $categoria;
    }
    
    if ($cliente_id) {
        $sql .= " AND t.cliente_id = ?";
        $params[] = $cliente_id;
    }
    
    // Ordenar por data de criação (mais recentes primeiro)
    $sql .= " ORDER BY t.data_criacao DESC";
    
    // Aplicar paginação
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Preparar e executar query
    $stmt = $mysqli->prepare($sql);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tickets = [];
    while ($row = $result->fetch_assoc()) {
        // Formatar datas
        $row['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($row['data_criacao']));
        $row['data_atualizacao_formatada'] = date('d/m/Y H:i', strtotime($row['data_atualizacao']));
        
        if ($row['data_fechamento']) {
            $row['data_fechamento_formatada'] = date('d/m/Y H:i', strtotime($row['data_fechamento']));
        }
        
        // Buscar comentários do ticket
        $comentarios_sql = "SELECT COUNT(*) as total FROM tickets_comentarios WHERE ticket_id = {$row['id']}";
        $comentarios_result = $mysqli->query($comentarios_sql);
        $row['total_comentarios'] = $comentarios_result->fetch_assoc()['total'];
        
        $tickets[] = $row;
    }
    
    // Buscar total de registros para paginação
    $count_sql = "SELECT COUNT(*) as total FROM tickets t WHERE 1=1";
    
    if ($status) {
        $count_sql .= " AND t.status = '$status'";
    }
    if ($prioridade) {
        $count_sql .= " AND t.prioridade = '$prioridade'";
    }
    if ($categoria) {
        $count_sql .= " AND t.categoria = '$categoria'";
    }
    if ($cliente_id) {
        $count_sql .= " AND t.cliente_id = $cliente_id";
    }
    
    $count_result = $mysqli->query($count_sql);
    $total = $count_result->fetch_assoc()['total'];
    
    // Calcular informações de paginação
    $total_pages = ceil($total / $limit);
    
    echo json_encode([
        'success' => true,
        'tickets' => $tickets,
        'paginacao' => [
            'pagina_atual' => $page,
            'total_paginas' => $total_pages,
            'total_registros' => $total,
            'registros_por_pagina' => $limit
        ]
    ]);

} catch (Exception $e) {
    error_log("Erro ao listar tickets: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 