<?php
require_once 'conexao.php';
require_once 'config.php';

// Cria tabela clientes
$conn->query("CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asaas_id VARCHAR(64) NOT NULL,
    nome VARCHAR(255),
    email VARCHAR(255),
    telefone VARCHAR(50),
    plano VARCHAR(100),
    status VARCHAR(50),
    site_url VARCHAR(255),
    acesso_ftp VARCHAR(255),
    data_cadastro DATETIME,
    obs TEXT,
    UNIQUE KEY (asaas_id)
)");
// Cria tabela cobrancas
$conn->query("CREATE TABLE IF NOT EXISTS cobrancas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    asaas_id VARCHAR(64),
    valor DECIMAL(10,2),
    vencimento DATE,
    status VARCHAR(50),
    link_pagamento VARCHAR(255),
    data_criacao DATETIME,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
)");

function getAsaas($endpoint) {
    global $asaas_api_key, $asaas_api_url;
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

$page = 0;
$clientes_importados = 0;
do {
    $page++;
    $resp = getAsaas("/customers?limit=100&page=$page");
    if (empty($resp['data'])) break;
    foreach ($resp['data'] as $cli) {
        $asaas_id = $conn->real_escape_string($cli['id']);
        $nome = $conn->real_escape_string($cli['name']);
        $email = $conn->real_escape_string($cli['email'] ?? '');
        $telefone = $conn->real_escape_string($cli['phone'] ?? '');
        $plano = $conn->real_escape_string($cli['customFields'][0]['value'] ?? '');
        $site_url = $conn->real_escape_string($cli['customFields'][1]['value'] ?? '');
        $acesso_ftp = '';
        $obs = '';
        $data_cadastro = $conn->real_escape_string(date('Y-m-d H:i:s', strtotime($cli['createdAt'] ?? 'now')));
        // Buscar cobranças do cliente
        $cobs = getAsaas("/payments?customer=$asaas_id&status=PENDING,RECEIVED,OVERDUE");
        if (!empty($cobs['data'])) {
            // Inserir cliente
            $conn->query("INSERT IGNORE INTO clientes (asaas_id, nome, email, telefone, plano, status, site_url, acesso_ftp, data_cadastro, obs) VALUES ('$asaas_id', '$nome', '$email', '$telefone', '$plano', 'Ativo', '$site_url', '$acesso_ftp', '$data_cadastro', '$obs')");
            $res = $conn->query("SELECT id FROM clientes WHERE asaas_id='$asaas_id' LIMIT 1");
            $row = $res->fetch_assoc();
            $cliente_id = $row['id'];
            foreach ($cobs['data'] as $cob) {
                $cob_id = $conn->real_escape_string($cob['id']);
                $valor = $conn->real_escape_string($cob['value']);
                $vencimento = $conn->real_escape_string($cob['dueDate']);
                $status = $conn->real_escape_string($cob['status']);
                $link_pagamento = $conn->real_escape_string($cob['invoiceUrl'] ?? '');
                
                // CORREÇÃO: Usar dateCreated (campo correto da API Asaas) em vez de createdAt
                $data_criacao_raw = $cob['dateCreated'] ?? null;
                if ($data_criacao_raw) {
                    // Se dateCreated está no formato YYYY-MM-DD, adicionar hora
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_criacao_raw)) {
                        $data_criacao = $data_criacao_raw . ' 00:00:00';
                    } else {
                        $data_criacao = date('Y-m-d H:i:s', strtotime($data_criacao_raw));
                    }
                } else {
                    // Se não tem data de criação, usar data atual
                    $data_criacao = date('Y-m-d H:i:s');
                }
                $data_criacao = $conn->real_escape_string($data_criacao);
                
                $conn->query("INSERT IGNORE INTO cobrancas (cliente_id, asaas_id, valor, vencimento, status, link_pagamento, data_criacao) VALUES ('$cliente_id', '$cob_id', '$valor', '$vencimento', '$status', '$link_pagamento', '$data_criacao')");
            }
            $clientes_importados++;
        }
    }
} while (!empty($resp['data']));
echo "Importação concluída. Clientes importados: $clientes_importados"; 