<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/asaasService.php';

echo "Iniciando adição da coluna tipo_pagamento...\n";

$asaas = new AsaasService();
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// 1. Adicionar a coluna tipo_pagamento se não existir
echo "Verificando se a coluna tipo_pagamento existe...\n";
$result = $conn->query("SHOW COLUMNS FROM cobrancas LIKE 'tipo_pagamento'");
if ($result->num_rows == 0) {
    echo "Adicionando coluna tipo_pagamento...\n";
    $sql = "ALTER TABLE cobrancas ADD COLUMN tipo_pagamento VARCHAR(20) DEFAULT NULL AFTER tipo";
    if ($conn->query($sql)) {
        echo "✓ Coluna tipo_pagamento adicionada com sucesso!\n";
    } else {
        die("✗ Erro ao adicionar coluna: " . $conn->error . "\n");
    }
} else {
    echo "✓ Coluna tipo_pagamento já existe!\n";
}

// 2. Buscar registros que não têm tipo_pagamento preenchido
echo "Buscando registros sem tipo_pagamento...\n";
$sql = "SELECT id, asaas_payment_id FROM cobrancas WHERE tipo_pagamento IS NULL OR tipo_pagamento = ''";
$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

$total_records = $result->num_rows;
echo "Encontrados $total_records registros sem tipo_pagamento\n";

if ($total_records == 0) {
    echo "Nenhum registro para atualizar.\n";
    exit;
}

$fixed = 0;
$errors = 0;

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $asaas_payment_id = $row['asaas_payment_id'];
    
    echo "Processando ID: $id, Asaas Payment ID: $asaas_payment_id\n";
    
    // Buscar dados do pagamento na API Asaas
    $resp = $asaas->request('GET', "payments/$asaas_payment_id");
    
    if ($resp['status'] === 200 && !empty($resp['body']['billingType'])) {
        $tipo_pagamento = $resp['body']['billingType'];
        
        // Atualizar o registro
        $stmt = $conn->prepare("UPDATE cobrancas SET tipo_pagamento = ? WHERE id = ?");
        $stmt->bind_param('si', $tipo_pagamento, $id);
        
        if ($stmt->execute()) {
            echo "  ✓ Atualizado: $tipo_pagamento\n";
            $fixed++;
        } else {
            echo "  ✗ Erro ao atualizar: " . $stmt->error . "\n";
            $errors++;
        }
        $stmt->close();
    } else {
        echo "  ✗ Não foi possível obter billingType da API Asaas\n";
        // Definir UNDEFINED como fallback
        $tipo_pagamento = 'UNDEFINED';
        $stmt = $conn->prepare("UPDATE cobrancas SET tipo_pagamento = ? WHERE id = ?");
        $stmt->bind_param('si', $tipo_pagamento, $id);
        
        if ($stmt->execute()) {
            echo "  ✓ Definido UNDEFINED como fallback\n";
            $fixed++;
        } else {
            echo "  ✗ Erro ao definir fallback: " . $stmt->error . "\n";
            $errors++;
        }
        $stmt->close();
    }
}

$conn->close();

echo "\n=== RESUMO ===\n";
echo "Total de registros processados: $total_records\n";
echo "Registros atualizados: $fixed\n";
echo "Erros: $errors\n";
echo "Coluna tipo_pagamento adicionada e populada com sucesso!\n"; 