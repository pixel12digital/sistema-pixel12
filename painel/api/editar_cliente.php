<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

// Capturar output para evitar HTML misturado com JSON
ob_start();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID de cliente inválido');
    }

    $id = intval($_POST['id']);
    error_log("[DEBUG] editar_cliente.php - ID do cliente: $id");
    
    // TODOS os campos da tabela clientes
    $campos = [
        'nome', 'contact_name', 'cpf_cnpj', 'razao_social',
        'email', 'emails_adicionais', 'telefone', 'celular',
        'cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais',
        'notificacao_desativada', 'referencia_externa', 'observacoes',
        'asaas_id', 'criado_em_asaas'
    ];
    
    // Campos que devem ser marcados como editados manualmente
    $campos_protecao = [
        'nome' => 'nome_editado_manual',
        'email' => 'email_editado_manual',
        'telefone' => 'telefone_editado_manual',
        'celular' => 'celular_editado_manual',
        'endereco' => 'endereco_editado_manual'
    ];
    
    $set = [];
    $params = [];
    $types = '';
    $campos_alterados = [];
    
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
            
            // Tratar campo boolean notificacao_desativada
            if ($campo === 'notificacao_desativada') {
                $valor = ($valor === '1' || $valor === 'true' || $valor === 'Sim') ? 1 : 0;
            }
            
            $set[] = "$campo = ?";
            $params[] = $valor;
            $types .= 's';
            $campos_alterados[] = $campo;
            error_log("[DEBUG] editar_cliente.php - Campo $campo: $valor");
        }
    }
    
    if (empty($set)) {
        throw new Exception('Nenhum campo para atualizar');
    }
    
    // Adicionar data_atualizacao automaticamente
    $set[] = "data_atualizacao = NOW()";
    
    // Marcar campos como editados manualmente se foram alterados
    foreach ($campos_alterados as $campo_alterado) {
        if (isset($campos_protecao[$campo_alterado])) {
            $campo_protecao = $campos_protecao[$campo_alterado];
            $set[] = "$campo_protecao = 1";
            error_log("[DEBUG] editar_cliente.php - Marcando $campo_protecao = 1");
        }
    }
    
    // Adicionar data_ultima_edicao_manual se houve alterações
    if (!empty($campos_alterados)) {
        $set[] = "data_ultima_edicao_manual = NOW()";
    }
    
    $sql = "UPDATE clientes SET " . implode(', ', $set) . " WHERE id = ?";
    $params[] = $id;
    $types .= 'i';
    
    error_log("[DEBUG] editar_cliente.php - SQL: $sql");
    error_log("[DEBUG] editar_cliente.php - Params: " . json_encode($params));
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar query: ' . $mysqli->error);
    }
    
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao executar query: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        // Limpar qualquer output anterior
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente atualizado com sucesso',
            'affected_rows' => $stmt->affected_rows,
            'campos_alterados' => $campos_alterados
        ]);
    } else {
        // Limpar qualquer output anterior
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Nenhuma alteração foi necessária',
            'affected_rows' => 0
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("[ERROR] editar_cliente.php - " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("[ERROR] editar_cliente.php - " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
}

// Garantir que nada mais seja enviado
ob_end_flush();
?> 