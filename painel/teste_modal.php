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
    <title>Teste Modal</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modal_cliente.js"></script>
    <style>
        body { 
            background: #181c23; 
            color: #f5f5f5; 
            font-family: Arial, sans-serif; 
            padding: 2rem;
        }
        .btn { 
            background: #a259e6; 
            color: #fff; 
            border: none; 
            border-radius: 6px; 
            padding: 10px 20px; 
            font-size: 1rem; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.2s; 
        }
        .btn:hover { 
            background: #7c2ae8; 
        }
    </style>
</head>
<body>
    <h1>Teste do Modal de Cadastro de Cliente</h1>
    <p>Clique no botão abaixo para testar o modal:</p>
    <button class="btn" id="btnTesteModal">+ Novo Cliente (Teste)</button>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modal_cliente.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar modal de cliente com ID personalizado
        new ModalCliente({
            modalId: 'modalCliente',
            btnOpenId: 'btnTesteModal',
            onSuccess: function(resp) {
                alert('Cliente cadastrado com sucesso!');
            },
            onError: function(resp) {
                console.log('Erro:', resp);
            }
        });
    });
    </script>
</body>
</html> 