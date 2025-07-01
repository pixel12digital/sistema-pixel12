<?php
namespace App\Controllers\Financeiro;
require_once __DIR__ . '/../../../src/Models/Fatura.php';
require_once __DIR__ . '/../../../config.php';

class FaturasController
{
    public function index()
    {
        global $mysqli;
        $por_pagina = 20;
        $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $offset = ($pagina - 1) * $por_pagina;

        // Filtros
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

        $faturas = \Fatura::paginadasComFiltro($mysqli, $por_pagina, $offset, $status, $date_from, $date_to);
        $total = \Fatura::totalComFiltro($mysqli, $status, $date_from, $date_to);
        $total_paginas = ceil($total / $por_pagina);
        include __DIR__ . '/../../Views/financeiro/faturas/index.php';
    }

    public function show($id)
    {
        global $mysqli;
        $fatura = \Fatura::buscarPorId($mysqli, $id);
        include __DIR__ . '/../../Views/financeiro/faturas/show.php';
    }

    public function sync()
    {
        global $mysqli;
        $total = $this->sincronizarFaturasAsaas($mysqli);
        header('Location: /financeiro/faturas?sync=' . $total);
        exit;
    }

    public function webhook()
    {
        global $mysqli;
        
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Ler o payload JSON
        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload']);
            return;
        }

        // Log do webhook para debug
        error_log('Asaas Webhook received: ' . $input);

        try {
            // Processar diferentes tipos de eventos
            $event = $payload['event'] ?? '';
            $payment = $payload['payment'] ?? [];

            if (empty($payment['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Payment ID not found']);
                return;
            }

            $asaas_id = $payment['id'];
            $new_status = $payment['status'] ?? '';
            $customer_id = $payment['customer'] ?? '';
            $value = $payment['value'] ?? '';
            $invoice_url = $payment['invoiceUrl'] ?? '';
            $due_date = $payment['dueDate'] ?? '';
            $date_updated = $payment['dateUpdated'] ?? date('Y-m-d H:i:s');

            // Atualizar ou inserir a fatura
            $stmt = $mysqli->prepare("
                INSERT INTO faturas (asaas_id, cliente_id, valor, status, invoice_url, due_date, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                updated_at = VALUES(updated_at)
            ");
            
            $stmt->bind_param('sidssss', 
                $asaas_id, 
                $customer_id, 
                $value, 
                $new_status, 
                $invoice_url, 
                $due_date, 
                $date_updated
            );
            
            $stmt->execute();
            $stmt->close();

            // Log do sucesso
            error_log("Fatura atualizada via webhook: asaas_id=$asaas_id, status=$new_status");

            // Responder com sucesso
            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => 'Payment status updated',
                'asaas_id' => $asaas_id,
                'status' => $new_status
            ]);

        } catch (Exception $e) {
            error_log('Webhook error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    private function sincronizarFaturasAsaas($mysqli)
    {
        $asaas_api_key = defined('ASAAS_API_KEY') ? ASAAS_API_KEY : '';
        $asaas_api_url = defined('ASAAS_API_URL') ? ASAAS_API_URL : '';
        $offset = 0;
        $total = 0;
        do {
            $ch = curl_init("$asaas_api_url/payments?offset=$offset&limit=100");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'access_token: ' . $asaas_api_key,
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            $resp = json_decode($result, true);
            if (!isset($resp['data'])) break;
            foreach ($resp['data'] as $item) {
                $stmt = $mysqli->prepare("REPLACE INTO faturas (asaas_id, cliente_id, valor, status, invoice_url, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    'sidsssss',
                    $item['id'],
                    $item['customer'],
                    $item['value'],
                    $item['status'],
                    $item['invoiceUrl'],
                    $item['dueDate'],
                    $item['dateCreated'],
                    $item['dateUpdated'] ?? $item['dateCreated']
                );
                $stmt->execute();
                $stmt->close();
                $total++;
            }
            $offset += 100;
        } while (!empty($resp['data']));
        return $total;
    }
} 