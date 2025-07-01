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
    <title>Exemplo de Uso do Modal</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { 
            background: #181c23; 
            color: #f5f5f5; 
            font-family: Arial, sans-serif; 
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
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
            margin: 10px;
        }
        .btn:hover { 
            background: #7c2ae8; 
        }
        .example-section {
            background: #232836;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
        }
        .code-block {
            background: #181c23;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Exemplos de Uso do Modal de Cliente</h1>
        
        <div class="example-section">
            <h2>1. Uso Básico</h2>
            <p>Modal simples que recarrega a página após cadastro:</p>
            <button class="btn" id="btnExemplo1">+ Novo Cliente (Básico)</button>
            
            <div class="code-block">
&lt;script src="modal_cliente.js"&gt;&lt;/script&gt;
&lt;script&gt;
new ModalCliente({
    btnOpenId: 'btnExemplo1'
});
&lt;/script&gt;
            </div>
        </div>

        <div class="example-section">
            <h2>2. Com Callbacks Personalizados</h2>
            <p>Modal com ações personalizadas após cadastro:</p>
            <button class="btn" id="btnExemplo2">+ Novo Cliente (Callbacks)</button>
            
            <div class="code-block">
&lt;script src="modal_cliente.js"&gt;&lt;/script&gt;
&lt;script&gt;
new ModalCliente({
    btnOpenId: 'btnExemplo2',
    onSuccess: function(resp) {
        alert('Cliente cadastrado: ' + resp.message);
        // Atualizar lista, etc.
    },
    onError: function(resp) {
        console.log('Erro:', resp.message);
    }
});
&lt;/script&gt;
            </div>
        </div>

        <div class="example-section">
            <h2>3. Múltiplos Modais</h2>
            <p>Página com múltiplos modais usando IDs diferentes:</p>
            <button class="btn" id="btnModal1">Modal 1</button>
            <button class="btn" id="btnModal2">Modal 2</button>
            
            <div class="code-block">
&lt;script src="modal_cliente.js"&gt;&lt;/script&gt;
&lt;script&gt;
new ModalCliente({
    modalId: 'modalCliente1',
    btnOpenId: 'btnModal1'
});

new ModalCliente({
    modalId: 'modalCliente2',
    btnOpenId: 'btnModal2'
});
&lt;/script&gt;
            </div>
        </div>

        <div class="example-section">
            <h2>4. Integração com Outras Funcionalidades</h2>
            <p>Modal que atualiza uma lista após cadastro:</p>
            <button class="btn" id="btnExemplo4">+ Novo Cliente (Integrado)</button>
            <div id="listaClientes">
                <p>Lista de clientes será atualizada aqui...</p>
            </div>
            
            <div class="code-block">
&lt;script src="modal_cliente.js"&gt;&lt;/script&gt;
&lt;script&gt;
new ModalCliente({
    btnOpenId: 'btnExemplo4',
    onSuccess: function(resp) {
        // Atualizar lista sem recarregar página
        atualizarListaClientes();
    }
});

function atualizarListaClientes() {
    $.get('buscar_clientes.php', function(data) {
        $('#listaClientes').html(data);
    });
}
&lt;/script&gt;
            </div>
        </div>
    </div>

    <script src="modal_cliente.js"></script>
    <script>
    $(document).ready(function() {
        // Exemplo 1: Uso básico
        new ModalCliente({
            btnOpenId: 'btnExemplo1'
        });

        // Exemplo 2: Com callbacks
        new ModalCliente({
            btnOpenId: 'btnExemplo2',
            onSuccess: function(resp) {
                alert('Cliente cadastrado com sucesso!');
            },
            onError: function(resp) {
                console.log('Erro:', resp.message);
            }
        });

        // Exemplo 3: Múltiplos modais
        new ModalCliente({
            modalId: 'modalCliente1',
            btnOpenId: 'btnModal1'
        });

        new ModalCliente({
            modalId: 'modalCliente2',
            btnOpenId: 'btnModal2'
        });

        // Exemplo 4: Integração
        new ModalCliente({
            btnOpenId: 'btnExemplo4',
            onSuccess: function(resp) {
                $('#listaClientes').html('<p style="color: #4caf50;">Cliente cadastrado! Lista atualizada.</p>');
            }
        });
    });
    </script>
</body>
</html> 