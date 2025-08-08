<?php
/**
 * API para sincronizar faturas com o Asaas
 */

header('Content-Type: application/json');
session_start();

// Verificar se está logado
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Ler dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    $fatura_id = $input['fatura_id'] ?? null;

    if (!$fatura_id) {
        throw new Exception('ID da fatura não fornecido');
    }

    // Buscar fatura
    $fatura = fetchOne("SELECT * FROM faturas WHERE id = ?", [$fatura_id], 'i');
    
    if (!$fatura) {
        throw new Exception('Fatura não encontrada');
    }

    // Verificar se tem asaas_id
    if (empty($fatura['asaas_id'])) {
        throw new Exception('Fatura não possui ID do Asaas');
    }

    // Configurar cURL para buscar dados do Asaas
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/payments/' . $fatura['asaas_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'access_token: ' . ASAAS_API_KEY,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Erro ao buscar dados do Asaas: HTTP ' . $http_code);
    }

    $asaas_data = json_decode($response, true);

    if (!$asaas_data) {
        throw new Exception('Resposta inválida do Asaas');
    }

    // Atualizar fatura com dados do Asaas
    $update_data = [
        'status' => $asaas_data['status'] ?? $fatura['status'],
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Adicionar campos específicos se existirem
    if (isset($asaas_data['value'])) {
        $update_data['valor'] = $asaas_data['value'];
    }
    
    if (isset($asaas_data['dueDate'])) {
        $update_data['due_date'] = date('Y-m-d', strtotime($asaas_data['dueDate']));
    }
    
    if (isset($asaas_data['invoiceUrl'])) {
        $update_data['invoice_url'] = $asaas_data['invoiceUrl'];
    }

    // Executar update
    $success = update('faturas', $update_data, 'id = ?', [$fatura_id]);

    if (!$success) {
        throw new Exception('Erro ao atualizar fatura no banco de dados');
    }

    // Log da sincronização
    $log_data = [
        'fatura_id' => $fatura_id,
        'asaas_id' => $fatura['asaas_id'],
        'status_anterior' => $fatura['status'],
        'status_novo' => $update_data['status'],
        'data_sincronizacao' => date('Y-m-d H:i:s'),
        'usuario' => $_SESSION['usuario'] ?? 'admin'
    ];

    // Inserir log (se a tabela existir)
    try {
        insert('logs_sincronizacao_faturas', $log_data);
    } catch (Exception $e) {
        // Ignorar erro se a tabela não existir
    }

    echo json_encode([
        'success' => true,
        'message' => 'Fatura sincronizada com sucesso',
        'data' => [
            'fatura_id' => $fatura_id,
            'status_anterior' => $fatura['status'],
            'status_novo' => $update_data['status']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 