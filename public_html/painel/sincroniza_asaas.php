<?php
require_once 'config.php';
require_once 'db.php';

echo date('Y-m-d H:i:s') . " - Iniciando sincronização com Asaas...\n";

function getAsaas($endpoint) {
    global $asaas_api_url, $asaas_api_key;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $asaas_api_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $asaas_api_key
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// 1. Sincronizar clientes
$clientes = [];
$offset = 0;
do {
    $resp = getAsaas("/customers?limit=100&offset=$offset");
    if (!empty($resp['data'])) {
        foreach ($resp['data'] as $cli) {
            $clientes[] = $cli;
            // Upsert cliente no banco local
            $stmt = $mysqli->prepare("REPLACE INTO clientes (asaas_id, nome, email, cpf_cnpj, celular, cep, rua, numero, complemento, bairro, cidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssssss',
                $cli['id'],
                $cli['name'],
                $cli['email'],
                $cli['cpfCnpj'],
                $cli['mobilePhone'],
                $cli['postalCode'],
                $cli['address'],
                $cli['addressNumber'],
                $cli['complement'],
                $cli['province'],
                $cli['city']
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    $offset += 100;
} while (!empty($resp['data']) && count($resp['data']) === 100);
echo "Clientes sincronizados: " . count($clientes) . "\n";

// 2. Sincronizar cobranças
$cobrancas = [];
$offset = 0;
do {
    $resp = getAsaas("/payments?limit=100&offset=$offset");
    if (!empty($resp['data'])) {
        foreach ($resp['data'] as $cob) {
            $cobrancas[] = $cob;
            // Upsert cobrança no banco local
            $stmt = $mysqli->prepare("REPLACE INTO cobrancas (asaas_payment_id, cliente_asaas_id, valor, status, vencimento, descricao, tipo, url_fatura, parcela, assinatura_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdsssssss',
                $cob['id'],
                $cob['customer'],
                $cob['value'],
                $cob['status'],
                $cob['dueDate'],
                $cob['description'],
                $cob['billingType'],
                $cob['invoiceUrl'],
                $cob['installmentNumber'],
                $cob['subscription']
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    $offset += 100;
} while (!empty($resp['data']) && count($resp['data']) === 100);
echo "Cobranças sincronizadas: " . count($cobrancas) . "\n";

// 3. Registrar data/hora da última sincronização
file_put_contents('ultima_sincronizacao.log', date('Y-m-d H:i:s'));
echo "Sincronização concluída em " . date('Y-m-d H:i:s') . "\n"; 