<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/asaasService.php';
require_once 'db.php';

$asaas = new AsaasService();
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$page = 0;
$total_importadas = 0;
$total_erros = 0;

$config = $conn->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
$api_key = $config ? $config['valor'] : '';

class AsaasService {
    private $apiKey;
    public function __construct() {
        global $api_key;
        $this->apiKey = $api_key;
    }
    public function getApiKey() {
        return $this->apiKey;
    }
    public function getApiUrl() {
        return ASAAS_API_URL;
    }
    public function request($method, $endpoint, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiUrl() . '/' . ltrim($endpoint, '/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey
        ]);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'body' => json_decode($result, true),
            'http_code' => $httpCode
        ];
    }
}

echo "Chave Asaas usada: ".$asaas->getApiKey()."\n";
echo "Endpoint Asaas: ".$asaas->getApiUrl()."\n";

do {
    $resp = $asaas->request('GET', "payments?limit=100&offset=" . ($page * 100));
    if ($page === 0) {
        file_put_contents(__DIR__ . '/../asaas_debug.json', json_encode($resp, JSON_PRETTY_PRINT));
        echo "Resposta bruta da API Asaas:\n";
        print_r($resp);
    }
    
    if (!empty($resp['body']['data'])) {
        foreach ($resp['body']['data'] as $fatura) {
            // Extrair campos principais
            $asaas_payment_id = $fatura['id'] ?? null;
            $cliente_id = $fatura['customer'] ?? null;
            $valor = $fatura['value'] ?? null;
            $status = $fatura['status'] ?? null;
            $vencimento = $fatura['dueDate'] ?? null;
            $data_pagamento = $fatura['paymentDate'] ?? null;
            
            // CORREÇÃO: Usar dateCreated (campo correto da API Asaas)
            $data_criacao = $fatura['dateCreated'] ?? null;
            
            $data_atualizacao = date('Y-m-d H:i:s');
            $descricao = $fatura['description'] ?? null;
            $tipo = $fatura['billingType'] ?? null;
            $url_fatura = $fatura['invoiceUrl'] ?? ($fatura['bankSlipUrl'] ?? null);
            $parcela = $fatura['installmentNumber'] ?? null;
            $assinatura_id = $fatura['subscription'] ?? null;

            // Converter datas para formato correto
            $vencimento = $vencimento ? date('Y-m-d', strtotime($vencimento)) : null;
            $data_pagamento = $data_pagamento ? date('Y-m-d', strtotime($data_pagamento)) : null;
            
            // CORREÇÃO: Melhor tratamento da data de criação
            if ($data_criacao) {
                // Se dateCreated está no formato YYYY-MM-DD, adicionar hora
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_criacao)) {
                    $data_criacao = $data_criacao . ' 00:00:00';
                } else {
                    $data_criacao = date('Y-m-d H:i:s', strtotime($data_criacao));
                }
            } else {
                // Se não tem data de criação, usar data atual
                $data_criacao = date('Y-m-d H:i:s');
                echo "AVISO: data_criacao não encontrada para payment_id: $asaas_payment_id, usando data atual\n";
            }

            // Log para debug
            if ($page === 0) {
                echo "DEBUG - Payment ID: $asaas_payment_id\n";
                echo "DEBUG - dateCreated original: " . ($fatura['dateCreated'] ?? 'NULL') . "\n";
                echo "DEBUG - data_criacao processada: $data_criacao\n";
            }

            // Upsert (INSERT ou UPDATE)
            $stmt = $conn->prepare("SELECT id FROM cobrancas WHERE asaas_payment_id = ?");
            $stmt->bind_param('s', $asaas_payment_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                // UPDATE
                $stmt->close();
                $stmt = $conn->prepare("UPDATE cobrancas SET cliente_id=?, valor=?, status=?, vencimento=?, data_pagamento=?, data_criacao=?, data_atualizacao=?, descricao=?, tipo=?, tipo_pagamento=?, url_fatura=?, parcela=?, assinatura_id=? WHERE asaas_payment_id=?");
                $stmt->bind_param('idssssssssssss', $cliente_id, $valor, $status, $vencimento, $data_pagamento, $data_criacao, $data_atualizacao, $descricao, $tipo, $tipo, $url_fatura, $parcela, $assinatura_id, $asaas_payment_id);
            } else {
                // INSERT
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO cobrancas (asaas_payment_id, cliente_id, valor, status, vencimento, data_pagamento, data_criacao, data_atualizacao, descricao, tipo, tipo_pagamento, url_fatura, parcela, assinatura_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sidsssssssssss', $asaas_payment_id, $cliente_id, $valor, $status, $vencimento, $data_pagamento, $data_criacao, $data_atualizacao, $descricao, $tipo, $tipo, $url_fatura, $parcela, $assinatura_id);
            }
            
            if ($stmt->execute()) {
                $total_importadas++;
            } else {
                $total_erros++;
                echo "ERRO ao processar payment_id: $asaas_payment_id - " . $stmt->error . "\n";
            }
            $stmt->close();
        }
        $page++;
    } else {
        break;
    }
} while (!empty($resp['body']['data']));

$conn->close();
echo "Cobranças importadas/atualizadas: $total_importadas\n";
echo "Erros encontrados: $total_erros\n"; 