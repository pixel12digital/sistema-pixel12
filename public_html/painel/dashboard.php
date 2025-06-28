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
    <style>
        body { background: #181c23; color: #f5f5f5; }
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 70px; background: #232836; display: flex; flex-direction: column; align-items: center; padding: 1.5rem 0; z-index: 10;
        }
        .sidebar-logo { width: 38px; margin-bottom: 2rem; }
        .sidebar-nav { display: flex; flex-direction: column; gap: 2.2rem; }
        .sidebar-link { color: #a259e6; font-size: 1.7rem; text-decoration: none; display: flex; flex-direction: column; align-items: center; transition: color 0.2s; }
        .sidebar-link.active, .sidebar-link:hover { color: #fff; }
        .sidebar-label { font-size: 0.7rem; margin-top: 0.2rem; letter-spacing: 0.5px; }
        .main-content { margin-left: 90px; min-height: 100vh; }
        .topbar { background: #232836; border-bottom: 2px solid #a259e6; display: flex; align-items: center; padding: 0.7rem 2.5rem; justify-content: space-between; }
        .topbar-title { font-size: 1.3rem; font-weight: bold; color: #fff; letter-spacing: 1px; }
        .cards-resumo { display: flex; gap: 2rem; margin: 2.5rem 0 1.5rem 0; flex-wrap: wrap; }
        .card { background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 1.3rem 2.2rem; min-width: 180px; text-align: center; flex: 1; }
        .card-titulo { color: #a259e6; font-size: 1rem; margin-bottom: 0.5rem; }
        .card-valor { font-size: 2.1rem; font-weight: bold; color: #fff; }
        .dashboard-graficos { margin: 2.5rem auto; max-width: 800px; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem 2.5rem; min-height: 180px; display: flex; align-items: center; justify-content: center; color: #888; font-size: 1.1rem; }
        @media (max-width: 900px) {
            .main-content { margin-left: 70px; }
            .cards-resumo { flex-direction: column; gap: 1rem; }
            .dashboard-graficos { padding: 1rem; }
        }
        @media (max-width: 600px) {
            .main-content { margin-left: 0; }
            .sidebar { position: static; flex-direction: row; width: 100vw; height: 60px; padding: 0.5rem 0; }
            .sidebar-logo { margin-bottom: 0; }
            .sidebar-nav { flex-direction: row; gap: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="assets/images/logo-pixel12digital.png" alt="Pixel 12 Digital" class="sidebar-logo">
        <nav class="sidebar-nav">
            <a href="clientes.php" class="sidebar-link" title="Clientes">👥<span class="sidebar-label">Clientes</span></a>
            <a href="dashboard.php" class="sidebar-link active" title="Dashboard">📊<span class="sidebar-label">Dashboard</span></a>
            <a href="#" class="sidebar-link" title="Configurações">⚙️<span class="sidebar-label">Config.</span></a>
            <a href="logout.php" class="sidebar-link" title="Sair">⏻<span class="sidebar-label">Sair</span></a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar">
            <span class="topbar-title">Dashboard - Pixel 12 Digital</span>
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