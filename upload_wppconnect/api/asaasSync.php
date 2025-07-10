<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/asaasService.php';

$asaas = new AsaasService();
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Buscar todas as faturas não pagas/canceladas
$result = $conn->query("SELECT asaas_id, status FROM faturas WHERE status NOT IN ('PAGO', 'CANCELADA')");
while ($row = $result->fetch_assoc()) {
    $asaas_id = $row['asaas_id'];
    $asaasResp = $asaas->request('GET', "payments/$asaas_id");
    if ($asaasResp['status'] === 200 && isset($asaasResp['body']['status'])) {
        $novoStatus = $asaasResp['body']['status'];
        if ($novoStatus !== $row['status']) {
            $stmt = $conn->prepare('UPDATE faturas SET status = ? WHERE asaas_id = ?');
            $stmt->bind_param('ss', $novoStatus, $asaas_id);
            $stmt->execute();
            $stmt->close();
            echo "Fatura $asaas_id atualizada para $novoStatus\n";
        }
    }
}
$conn->close();
echo "Sincronização concluída.\n"; 