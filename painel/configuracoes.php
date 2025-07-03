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
</head>
<body>
<?php include 'menu_lateral.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Painel Administrativo - Pixel 12 Digital <span style='color:#a259e6;font-weight:bold;'>| Configurações</span></span>
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