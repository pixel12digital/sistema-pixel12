<?php
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
// Exemplo de dados para os cards
$totalClientes = 2;
$totalBancos = 2;
$totalAcessos = 37;
$totalLogs = 5;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'menu_lateral.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Painel Administrativo - Pixel 12 Digital <span style='color:#a259e6;font-weight:bold;'>| Dashboard</span></span>
    </div>
    <div class="cards-resumo">
        <div class="card">
            <div class="card-titulo">Total de Clientes</div>
            <div class="card-valor"><?= $totalClientes ?></div>
        </div>
        <div class="card">
            <div class="card-titulo">Bancos Ativos</div>
            <div class="card-valor"><?= $totalBancos ?></div>
        </div>
        <div class="card">
            <div class="card-titulo">Acessos Recentes</div>
            <div class="card-valor"><?= $totalAcessos ?></div>
        </div>
        <div class="card">
            <div class="card-titulo">Logs do Sistema</div>
            <div class="card-valor"><?= $totalLogs ?></div>
        </div>
    </div>
    <div class="dashboard-graficos">
        (Em breve: gráficos e relatórios visuais do sistema!)
    </div>
</div>
</body>
</html> 