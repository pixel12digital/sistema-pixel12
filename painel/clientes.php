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
require_once 'config.php';
require_once 'db.php';
$result = $mysqli->query("SELECT * FROM clientes ORDER BY data_criacao DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Clientes</title>
    <link rel="stylesheet" href="assets/style.css">
    <base href="/loja-virtual-revenda/">
</head>
<body>
<?php include 'menu_lateral.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Painel Administrativo - Pixel 12 Digital <span style='color:#a259e6;font-weight:bold;'>| Clientes</span></span>
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
            <button class="btn" id="btnNovoCliente">+ Novo Cliente</button>
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
<script src="modal_cliente.js"></script>
<script>
$(document).ready(function() {
    // Inicializar modal de cliente
    new ModalCliente({
        btnOpenId: 'btnNovoCliente',
        onSuccess: function(resp) {
            location.reload();
        }
    });
});
</script>
</body>
</html> 