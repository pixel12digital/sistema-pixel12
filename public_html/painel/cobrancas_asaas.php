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
    <style>
        body { background: #181c23; color: #f5f5f5; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 70px; background: #232836; display: flex; flex-direction: column; align-items: center; padding: 1.5rem 0; z-index: 10; }
        .sidebar-logo { width: 38px; margin-bottom: 2rem; }
        .sidebar-nav { display: flex; flex-direction: column; gap: 2.2rem; }
        .sidebar-link { color: #a259e6; font-size: 1.7rem; text-decoration: none; display: flex; flex-direction: column; align-items: center; transition: color 0.2s; }
        .sidebar-link.active, .sidebar-link:hover { color: #fff; }
        .sidebar-label { font-size: 0.7rem; margin-top: 0.2rem; letter-spacing: 0.5px; }
        .main-content { margin-left: 90px; min-height: 100vh; }
        .topbar { background: #232836; border-bottom: 2px solid #a259e6; display: flex; align-items: center; padding: 0.7rem 2.5rem; justify-content: space-between; }
        .topbar-title { font-size: 1.3rem; font-weight: bold; color: #fff; letter-spacing: 1px; }
        .cobrancas-asaas-container { max-width: 1200px; margin: 2.5rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem 2.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        th, td { padding: 10px 8px; text-align: left; }
        th { color: #a259e6; font-size: 1rem; border-bottom: 1px solid #333; }
        td { color: #fff; border-bottom: 1px solid #232836; }
        tr:last-child td { border-bottom: none; }
        .btn { background: #a259e6; color: #fff; border: none; border-radius: 6px; padding: 7px 16px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.2s; text-decoration: none; display: inline-block; }
        .btn:hover { background: #7c2ae8; }
        .status-pending { color: #ffb300; font-weight: bold; }
        .status-received { color: #4caf50; font-weight: bold; }
        .status-overdue { color: #e53935; font-weight: bold; }
        @media (max-width: 900px) {
            .main-content { margin-left: 70px; }
            .cobrancas-asaas-container { padding: 1rem; }
            table, thead, tbody, th, td, tr { display: block; }
            th, td { padding: 8px 4px; }
            th { border-bottom: none; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="assets/images/logo-pixel12digital.png" alt="Pixel 12 Digital" class="sidebar-logo">
        <nav class="sidebar-nav">
            <a href="clientes.php" class="sidebar-link" title="Clientes">👥<span class="sidebar-label">Clientes</span></a>
            <a href="dashboard.php" class="sidebar-link" title="Dashboard">📊<span class="sidebar-label">Dashboard</span></a>
            <a href="cobrancas_asaas.php" class="sidebar-link active" title="Cobranças">💳<span class="sidebar-label">Cobranças</span></a>
            <a href="clientes_asaas.php?filtro=planos" class="sidebar-link" title="Planos Ativos">📋<span class="sidebar-label">Planos</span></a>
            <a href="configuracoes.php" class="sidebar-link" title="Configurações">⚙️<span class="sidebar-label">Config.</span></a>
            <a href="logout.php" class="sidebar-link" title="Sair">⏻<span class="sidebar-label">Sair</span></a>
        </nav>
    </div>
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