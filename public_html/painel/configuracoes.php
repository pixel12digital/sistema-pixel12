<?php
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Configurações</title>
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
        .config-container { max-width: 700px; margin: 2.5rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem 2.5rem; }
        .config-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .config-bloco { background: #232836; border: 1px solid #a259e6; border-radius: 8px; padding: 1.2rem 1.5rem; margin-bottom: 1.2rem; color: #fff; }
        .config-titulo { color: #a259e6; font-size: 1.1rem; font-weight: bold; margin-bottom: 0.7rem; }
        @media (max-width: 900px) {
            .main-content { margin-left: 70px; }
            .config-container { padding: 1rem; }
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
            <a href="dashboard.php" class="sidebar-link" title="Dashboard">📊<span class="sidebar-label">Dashboard</span></a>
            <a href="configuracoes.php" class="sidebar-link active" title="Configurações">⚙️<span class="sidebar-label">Config.</span></a>
            <a href="logout.php" class="sidebar-link" title="Sair">⏻<span class="sidebar-label">Sair</span></a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar">
            <span class="topbar-title">Configurações - Pixel 12 Digital</span>
        </div>
        <div class="config-container">
            <div class="config-header">
                <h2>Configurações Gerais</h2>
            </div>
            <div class="config-bloco">
                <div class="config-titulo">Em breve</div>
                <p>Área para configurações do sistema, usuários, integrações e preferências.</p>
            </div>
        </div>
    </div>
</body>
</html> 