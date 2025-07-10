<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/asaasService.php';

$asaas = new AsaasService();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// Listar faturas
if ($method === 'GET' && !$action) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $result = $conn->query('SELECT * FROM invoices ORDER BY id DESC');
    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    json_response($invoices);
}

// Criar fatura
if ($method === 'POST' && !$action) {
    $data = get_json_input();
    $asaasResp = $asaas->criarFatura($data);
    if ($asaasResp['status'] === 200 || $asaasResp['status'] === 201) {
        $body = $asaasResp['body'];
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $stmt = $conn->prepare('INSERT INTO faturas (cliente_id, asaas_id, valor, status, invoice_url, due_date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isdsss', $data['client_id'], $body['id'], $body['value'], $body['status'], $body['invoiceUrl'], $body['dueDate']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        json_response(['message' => 'Fatura criada com sucesso', 'asaas' => $body]);
    } else {
        json_response(['error' => 'Erro ao criar fatura', 'asaas' => $asaasResp['body']], 400);
    }
}

// Reenviar link
if ($method === 'POST' && $action === 'resend' && $id) {
    $asaasResp = $asaas->reenviarLink($id);
    if ($asaasResp['status'] === 200) {
        json_response(['message' => 'Link reenviado com sucesso']);
    } else {
        json_response(['error' => 'Erro ao reenviar link', 'asaas' => $asaasResp['body']], 400);
    }
}

// Cancelar fatura
if ($method === 'POST' && $action === 'cancel' && $id) {
    $asaasResp = $asaas->cancelarFatura($id);
    if ($asaasResp['status'] === 200) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $stmt = $conn->prepare('UPDATE faturas SET status = ? WHERE asaas_id = ?');
        $status = 'CANCELADA';
        $stmt->bind_param('ss', $status, $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        json_response(['message' => 'Fatura cancelada com sucesso']);
    } else {
        json_response(['error' => 'Erro ao cancelar fatura', 'asaas' => $asaasResp['body']], 400);
    }
}

// Obter PDF
if ($method === 'GET' && $action === 'pdf' && $id) {
    $asaasResp = $asaas->obterPDF($id);
    if ($asaasResp['status'] === 200) {
        json_response(['pdf_url' => $asaasResp['body']['identificationField']['pdfUrl'] ?? null]);
    } else {
        json_response(['error' => 'Erro ao obter PDF', 'asaas' => $asaasResp['body']], 400);
    }
}

json_response(['error' => 'Rota não encontrada'], 404); 