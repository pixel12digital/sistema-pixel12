<?php
$page = 'dashboard.php';
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
$page_title = 'Dashboard';
$custom_header = '';
function render_content() {
    $totalClientes = 2;
    $totalBancos = 2;
    $totalAcessos = 37;
    $totalLogs = 5;
    ?>
    <div class="cards-resumo painel-grid">
        <div class="painel-card">
            <div class="card-titulo">Total de Clientes</div>
            <div class="card-valor"><?= $totalClientes ?></div>
        </div>
        <div class="painel-card">
            <div class="card-titulo">Bancos Ativos</div>
            <div class="card-valor"><?= $totalBancos ?></div>
        </div>
        <div class="painel-card">
            <div class="card-titulo">Acessos Recentes</div>
            <div class="card-valor"><?= $totalAcessos ?></div>
        </div>
        <div class="painel-card">
            <div class="card-titulo">Logs do Sistema</div>
            <div class="card-valor"><?= $totalLogs ?></div>
        </div>
    </div>
    <div class="dashboard-graficos painel-card">
        (Em breve: gráficos e relatórios visuais do sistema!)
    </div>
    <?php
}
include 'template.php'; 