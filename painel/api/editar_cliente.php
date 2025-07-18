<?php
// Prevent any HTML output before JSON response
ob_start();

require_once '../config.php';

// Check database connection before including db.php
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    
    // Configurar timeout para evitar conexões órfãs
    $mysqli->query("SET SESSION wait_timeout=300");
    $mysqli->query("SET SESSION interactive_timeout=300");
} catch (Exception $e) {
    // Clear any output buffer
    ob_end_clean();
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Erro de conexão com banco de dados: ' . $e->getMessage()]);
    exit;
}

// Clear any output buffer and set JSON header
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// Log para debug
error_log("[DEBUG] editar_cliente.php - Iniciando processamento");

// Processa salvamento do formulário de edição do cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    error_log("[DEBUG] editar_cliente.php - ID do cliente: $id");
    
    $campos = [
        'nome', 'contact_name', 'cpf_cnpj', 'razao_social', 'data_criacao', 'data_atualizacao', 'asaas_id', 'referencia_externa', 'criado_em_asaas',
        'email', 'emails_adicionais', 'telefone', 'celular',
        'cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais',
        'observacoes', 'plano', 'status'
    ];
    $set = [];
    $params = [];
    $types = '';
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $valor = trim($_POST[$campo]);
            // Limpar telefone/celular para conter apenas números
            if (in_array($campo, ['telefone', 'celular'])) {
                $valor = preg_replace('/\\D/', '', $valor);
            }
            // Limpar e padronizar emails_adicionais para texto simples
            if ($campo === 'emails_adicionais') {
                // Extrair todos os e-mails válidos
                preg_match_all('/[\w\.-]+@[\w\.-]+/', $valor, $matches);
                $emails = $matches[0];
                // Remover o e-mail principal dos adicionais
                $email_principal = $_POST['email'] ?? '';
                $emails = array_filter($emails, function($e) use ($email_principal) {
                  return strtolower($e) !== strtolower($email_principal);
                });
                $valor = $emails ? implode(', ', $emails) : '';
            }
            $set[] = "$campo = ?";
            $params[] = $valor;
            $types .= 's';
            error_log("[DEBUG] editar_cliente.php - Campo $campo: $valor");
        }
    }
    if ($set) {
        $sql = "UPDATE clientes SET ".implode(', ', $set)." WHERE id = ?";
        error_log("[DEBUG] editar_cliente.php - SQL: $sql");
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            error_log("[ERROR] editar_cliente.php - Erro no prepare: " . $mysqli->error);
            echo json_encode(['success' => false, 'error' => 'Erro na preparação da query: ' . $mysqli->error]);
            exit;
        }
        
        $params[] = $id;
        $types .= 'i';
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("[ERROR] editar_cliente.php - Erro no execute: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Erro na execução: ' . $stmt->error]);
        } else {
            error_log("[DEBUG] editar_cliente.php - Cliente atualizado com sucesso");
            echo json_encode(['success' => true, 'message' => 'Cliente atualizado com sucesso']);
        }
        $stmt->close();
    } else {
        error_log("[DEBUG] editar_cliente.php - Nenhum campo para atualizar");
        echo json_encode(['success' => false, 'error' => 'Nenhum campo para atualizar']);
    }
} else {
    error_log("[DEBUG] editar_cliente.php - Método não permitido ou ID não fornecido");
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}

// Close database connection
$mysqli->close();
?> 