<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    echo 'Erro ao iniciar sessão!';
    exit;
}
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    if ($usuario === 'admin' && $senha === 'admin123') {
        $_SESSION['logado'] = true;
        header('Location: clientes.php');
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos!';
    }
}
if (isset($_SESSION['logado']) && $_SESSION['logado']) {
    header('Location: clientes.php');
    exit;
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body.login-bg {
            background: #181c23;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #232836;
            border-radius: 14px;
            box-shadow: 0 2px 24px #1a1a1a55;
            padding: 38px 32px 28px 32px;
            max-width: 350px;
            width: 100%;
            text-align: center;
        }
        .login-logo {
            width: 120px;
            margin-bottom: 18px;
        }
        .login-form input {
            border: 1px solid #a259e6;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
            width: 90%;
            font-size: 1rem;
            background: #232836;
            color: #fff;
        }
        .login-form input:focus {
            outline: 2px solid #a259e6;
        }
        .login-form button {
            background: #a259e6;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px;
            width: 100%;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-form button:hover {
            background: #7c2ae8;
        }
        .login-erro {
            color: #ff6b6b;
            margin-bottom: 10px;
            font-weight: bold;
        }
        h2 { color: #fff; margin-bottom: 18px; }
        @media (max-width: 600px) {
            .login-container { padding: 18px 6px 18px 6px; }
        }
    </style>
</head>
<body class="login-bg">
    <div class="login-container">
        <img src="assets/images/logo-pixel12digital.png" alt="Pixel 12 Digital" class="login-logo">
        <h2>Login do Painel</h2>
        <?php if ($erro): ?><p class="login-erro"><?= $erro ?></p><?php endif; ?>
        <form method="post" class="login-form">
            <input type="text" name="usuario" placeholder="Usuário" required autofocus><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html> 