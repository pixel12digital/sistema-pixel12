<?php
require_once 'db.php';
require_once 'config.php';
?>
<div class="form-container">
    <h2>Cadastrar Cliente</h2>
    <form id="formCliente" method="post" autocomplete="off">
        <div class="msg" id="msgCliente"></div>
        <label for="cpf_cnpj">CPF ou CNPJ:</label>
        <input type="text" name="cpf_cnpj" id="cpf_cnpj" required maxlength="18" placeholder="Digite e clique em buscar">
        <button type="button" id="btnBuscarCliente">Buscar</button>
        <div id="clienteFormFields" class="hidden">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" required>
            <button type="submit">Cadastrar</button>
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('#btnBuscarCliente').on('click', function() {
        const cpfCnpj = $('#cpf_cnpj').val().replace(/\D/g, '');
        if (!cpfCnpj) { $('#msgCliente').text('Informe o CPF ou CNPJ.'); return; }
        $('#msgCliente').text('Buscando cliente...');
        $.get('cliente_busca.php', { cpfCnpj }, function(resp) {
            if (resp.success) {
                $('#clienteFormFields').removeClass('hidden');
                $('#nome').val(resp.data.name).prop('readonly', true);
                $('#email').val(resp.data.email).prop('readonly', true);
                $('#telefone').val(resp.data.mobilePhone).prop('readonly', true);
                $('#msgCliente').text('Cliente já cadastrado no Asaas!');
                $('#formCliente button[type=submit]').prop('disabled', true);
            } else {
                $('#clienteFormFields').removeClass('hidden');
                $('#nome, #email, #telefone').val('').prop('readonly', false);
                $('#msgCliente').text('Cliente não encontrado. Preencha os dados para cadastrar.');
                $('#formCliente button[type=submit]').prop('disabled', false);
            }
        }, 'json');
    });
});
</script>
<style>
.form-container { max-width: 400px; margin: 3rem auto; background: #232836; border-radius: 10px; box-shadow: 0 2px 12px #1a1a1a44; padding: 2rem; }
label { display: block; margin-bottom: 0.5rem; color: #a259e6; }
input[type=text], input[type=email] { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #a259e6; background: #232836; color: #fff; margin-bottom: 1.2rem; }
button { background: #a259e6; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.2s; }
button:hover { background: #7c2ae8; }
.msg { margin-bottom: 1rem; }
.hidden { display: none; }
</style> 