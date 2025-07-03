<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/asaasService.php';

echo "Iniciando correção de data_criacao...\n";

$asaas = new AsaasService();
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar registros com data_criacao NULL
$sql = "SELECT id, asaas_payment_id FROM cobrancas WHERE data_criacao IS NULL OR data_criacao = ''";
$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

$total_records = $result->num_rows;
echo "Encontrados $total_records registros com data_criacao NULL\n";

if ($total_records == 0) {
    echo "Nenhum registro para corrigir.\n";
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
    
    if ($resp['status'] === 200 && !empty($resp['body']['dateCreated'])) {
        $dateCreated = $resp['body']['dateCreated'];
        
        // Processar a data
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateCreated)) {
            $data_criacao = $dateCreated . ' 00:00:00';
        } else {
            $data_criacao = date('Y-m-d H:i:s', strtotime($dateCreated));
        }
        
        // Atualizar o registro
        $stmt = $conn->prepare("UPDATE cobrancas SET data_criacao = ? WHERE id = ?");
        $stmt->bind_param('si', $data_criacao, $id);
        
        if ($stmt->execute()) {
            echo "  ✓ Corrigido: $data_criacao\n";
            $fixed++;
        } else {
            echo "  ✗ Erro ao atualizar: " . $stmt->error . "\n";
            $errors++;
        }
        $stmt->close();
    } else {
        echo "  ✗ Não foi possível obter data_criacao da API Asaas\n";
        // Definir data atual como fallback
        $data_criacao = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE cobrancas SET data_criacao = ? WHERE id = ?");
        $stmt->bind_param('si', $data_criacao, $id);
        
        if ($stmt->execute()) {
            echo "  ✓ Definido data atual como fallback: $data_criacao\n";
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
echo "Registros corrigidos: $fixed\n";
echo "Erros: $errors\n";
echo "Correção concluída!\n"; 