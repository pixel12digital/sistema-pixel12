<?php
/**
 * Conexão com banco de dados para o painel
 */

// Verificar se já existe conexão
if (!isset($mysqli) || !$mysqli) {
    // Incluir configurações
    if (!defined('DB_HOST')) {
        require_once __DIR__ . '/../config.php';
    }
    
    // Criar conexão
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexão
    if ($mysqli->connect_error) {
        die("Erro na conexão com o banco de dados: " . $mysqli->connect_error);
    }
    
    // Configurar charset
    $mysqli->set_charset("utf8mb4");
    
    // Configurar timezone
    $mysqli->query("SET time_zone = '-03:00'");
}

// Função para executar queries com segurança
function executeQuery($sql, $params = [], $types = '') {
    global $mysqli;
    
    if (empty($params)) {
        return $mysqli->query($sql);
    }
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $mysqli->error);
    }
    
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

// Função para buscar uma linha
function fetchOne($sql, $params = [], $types = '') {
    $result = executeQuery($sql, $params, $types);
    return $result ? $result->fetch_assoc() : null;
}

// Função para buscar múltiplas linhas
function fetchAll($sql, $params = [], $types = '') {
    $result = executeQuery($sql, $params, $types);
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// Função para inserir dados
function insert($table, $data) {
    global $mysqli;
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $types = str_repeat('s', count($data));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $mysqli->error);
    }
    
    $stmt->bind_param($types, ...array_values($data));
    $stmt->execute();
    
    return $mysqli->insert_id;
}

// Função para atualizar dados
function update($table, $data, $where, $whereParams = []) {
    global $mysqli;
    
    $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
    $whereClause = $where;
    
    $sql = "UPDATE $table SET $setClause WHERE $whereClause";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $mysqli->error);
    }
    
    $types = str_repeat('s', count($data)) . str_repeat('s', count($whereParams));
    $params = array_merge(array_values($data), $whereParams);
    
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

// Função para deletar dados
function delete($table, $where, $whereParams = []) {
    global $mysqli;
    
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . $mysqli->error);
    }
    
    if (!empty($whereParams)) {
        $types = str_repeat('s', count($whereParams));
        $stmt->bind_param($types, ...$whereParams);
    }
    
    return $stmt->execute();
}
?> 