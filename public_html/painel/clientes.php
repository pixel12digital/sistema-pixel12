<?php
// Exemplo de gerenciamento de clientes (simples)
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
// Exemplo de clientes em array (depois será banco)
$clientes = [
    ['nome' => 'Cliente 1', 'banco' => 'cliente1_db'],
    ['nome' => 'Cliente 2', 'banco' => 'cliente2_db'],
];
// Cards de resumo (exemplo)
$totalClientes = count($clientes);
$totalBancos = count(array_unique(array_column($clientes, 'banco')));
require_once 'db.php';
$result = $mysqli->query("SELECT * FROM clientes ORDER BY data_criacao DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Clientes</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: #181c23; color: #f5f5f5; font-family: Arial, sans-serif; }
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
        .cards-resumo { display: flex; gap: 2rem; margin: 2.5rem 0 1.5rem 0; }
        .card { background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 1.3rem 2.2rem; min-width: 180px; text-align: center; }
        .card-titulo { color: #a259e6; font-size: 1rem; margin-bottom: 0.5rem; }
        .card-valor { font-size: 2.1rem; font-weight: bold; color: #fff; }
        .clientes-container { max-width: 700px; margin: 0 auto 2rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem 2.5rem; }
        .clientes-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .btn { background: #a259e6; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.2s; text-decoration: none; display: inline-block; }
        .btn:hover { background: #7c2ae8; }
        .clientes-list { list-style: none; padding: 0; }
        .clientes-list li { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #2d2d3a; }
        .clientes-list li:last-child { border-bottom: none; }
        .cliente-nome { font-weight: bold; color: #fff; }
        .cliente-banco { color: #a259e6; font-size: 0.95rem; margin-left: 10px; }
        .cliente-actions { display: flex; gap: 0.5rem; }
        @media (max-width: 900px) {
            .main-content { margin-left: 70px; }
            .cards-resumo { flex-direction: column; gap: 1rem; }
            .clientes-container { padding: 1rem; }
        }
        @media (max-width: 600px) {
            .main-content { margin-left: 0; }
            .sidebar { position: static; flex-direction: row; width: 100vw; height: 60px; padding: 0.5rem 0; }
            .sidebar-logo { margin-bottom: 0; }
            .sidebar-nav { flex-direction: row; gap: 1.5rem; }
        }
        .container { max-width: 900px; margin: 2.5rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem; }
        h2 { color: #a259e6; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        th, td { padding: 10px 8px; text-align: left; }
        th { color: #a259e6; font-size: 1rem; border-bottom: 1px solid #333; }
        td { color: #fff; border-bottom: 1px solid #232836; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="assets/images/logo-pixel12digital.png" alt="Pixel 12 Digital" class="sidebar-logo">
        <nav class="sidebar-nav">
            <a href="clientes.php" class="sidebar-link active" title="Clientes">👥<span class="sidebar-label">Clientes</span></a>
            <a href="dashboard.php" class="sidebar-link" title="Dashboard">📊<span class="sidebar-label">Dashboard</span></a>
            <a href="clientes_asaas.php" class="sidebar-link" title="Cobranças">💳<span class="sidebar-label">Cobranças</span></a>
            <a href="clientes_asaas.php?filtro=planos" class="sidebar-link" title="Planos Ativos">📋<span class="sidebar-label">Planos</span></a>
            <a href="configuracoes.php" class="sidebar-link" title="Configurações">⚙️<span class="sidebar-label">Config.</span></a>
            <a href="logout.php" class="sidebar-link" title="Sair">⏻<span class="sidebar-label">Sair</span></a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar">
            <span class="topbar-title">Painel Administrativo - Pixel 12 Digital</span>
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
        </div>
        <div class="clientes-container">
            <div class="clientes-header">
                <h2>Clientes</h2>
                <a href="cliente_add.php" class="btn">+ Novo Cliente</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>CPF/CNPJ</th>
                        <th>Data de Criação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cli = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($cli['nome']) ?></td>
                        <td><?= htmlspecialchars($cli['email']) ?></td>
                        <td><?= htmlspecialchars($cli['telefone']) ?></td>
                        <td><?= htmlspecialchars($cli['cpf_cnpj']) ?></td>
                        <td><?= htmlspecialchars($cli['data_criacao']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 