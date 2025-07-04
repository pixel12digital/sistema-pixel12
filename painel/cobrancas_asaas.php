<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
require_once 'config.php';

function getAsaas($endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY
    ]);
    $result = curl_exec($ch);
    if ($result === false) {
        echo "<h2>Erro ao conectar à API do Asaas: " . curl_error($ch) . "</h2>";
        curl_close($ch);
        exit;
    }
    curl_close($ch);
    return json_decode($result, true);
}

// Buscar todos os clientes do Asaas
$clientes = [];
$page = 0;
do {
    $page++;
    $resp = getAsaas("/customers?limit=100&page=$page");
    if (empty($resp['data'])) break;
    foreach ($resp['data'] as $cli) {
        $clientes[] = $cli;
    }
} while (!empty($resp['data']));

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cobranças Asaas (Tempo Real)</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'menu_lateral.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Cobranças (Tempo Real - Asaas)</span>
    </div>
    <div class="cobrancas-asaas-container">
        <h2>Cobranças Ativas dos Clientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Link Pagamento</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clientes as $cli): ?>
                <?php
                // Buscar cobranças ativas do cliente
                $cobs = getAsaas("/payments?customer=".$cli['id']."&status=PENDING,RECEIVED,OVERDUE");
                if (!empty($cobs['data'])):
                    foreach ($cobs['data'] as $cob):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($cli['name']) ?></td>
                        <td><?= htmlspecialchars($cli['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cli['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cli['customFields'][0]['value'] ?? '') ?></td>
                        <td class="status-<?= strtolower($cob['status']) ?>"><?= htmlspecialchars($cob['status']) ?></td>
                        <td>R$ <?= number_format($cob['value'],2,',','.') ?></td>
                        <td><?= htmlspecialchars($cob['dueDate']) ?></td>
                        <td><?php if (!empty($cob['invoiceUrl'])): ?><a href="<?= htmlspecialchars($cob['invoiceUrl']) ?>" target="_blank" class="btn">Pagar</a><?php endif; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 