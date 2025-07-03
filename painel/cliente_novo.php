<?php
$page = 'clientes.php';
$page_title = 'Novo Cliente';
$custom_header = '';
include 'template.php';
function render_content() {
?>
<div class="card-form">
  <a href="clientes.php" style="display:inline-block;margin-bottom:18px;background:#ede9fe;color:#7c2ae8;padding:8px 22px;border-radius:8px;font-weight:600;font-size:1rem;text-decoration:none;transition:background 0.18s;">← Voltar</a>
  <h2 class="text-xl font-bold text-purple-700 mb-6">Cadastrar Novo Cliente</h2>
  <form id="formNovoCliente" method="post" autocomplete="off" class="space-y-4">
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="name">Nome completo ou Razão Social *</label>
      <input type="text" name="name" id="name" required class="input-form" maxlength="100">
    </div>
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="contact_name">Contato</label>
      <input type="text" name="contact_name" id="contact_name" class="input-form" maxlength="100" placeholder="Nome do contato principal">
    </div>
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="cpfCnpj">CPF ou CNPJ *</label>
      <input type="text" name="cpfCnpj" id="cpfCnpj" required class="input-form" maxlength="18" placeholder="Somente números">
    </div>
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="email">E-mail *</label>
      <input type="email" name="email" id="email" required class="input-form" maxlength="100">
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-purple-700 font-semibold mb-1" for="phone">Telefone *</label>
        <input type="text" name="phone" id="phone" required class="input-form" maxlength="20" placeholder="DDD + número">
      </div>
      <div>
        <label class="block text-purple-700 font-semibold mb-1" for="mobilePhone">Celular</label>
        <input type="text" name="mobilePhone" id="mobilePhone" class="input-form" maxlength="20" placeholder="DDD + número">
      </div>
    </div>
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="address">Endereço</label>
      <input type="text" name="address" id="address" class="input-form" maxlength="100">
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-purple-700 font-semibold mb-1" for="addressNumber">Número</label>
        <input type="text" name="addressNumber" id="addressNumber" class="input-form" maxlength="10">
      </div>
      <div>
        <label class="block text-purple-700 font-semibold mb-1" for="complement">Complemento</label>
        <input type="text" name="complement" id="complement" class="input-form" maxlength="50">
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-purple-700 font-semibold mb-1" for="province">Bairro</label>
        <input type="text" name="province" id="province" class="input-form" maxlength="50">
      </div>
      <div>
        <label class="block text-purple-700 font-semibold mb-1" for="postalCode">CEP</label>
        <input type="text" name="postalCode" id="postalCode" class="input-form" maxlength="10" placeholder="Somente números">
      </div>
    </div>
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="externalReference">Referência interna</label>
      <input type="text" name="externalReference" id="externalReference" class="input-form" maxlength="50">
    </div>
    <div>
      <label class="block text-purple-700 font-semibold mb-1" for="observations">Observações</label>
      <textarea name="observations" id="observations" class="input-form" maxlength="255" rows="2"></textarea>
    </div>
    <div id="msgNovoCliente" class="mb-2 text-center"></div>
    <div class="flex justify-end gap-4">
      <a href="clientes.php" class="px-4 py-2 rounded bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">Cancelar</a>
      <button type="submit" class="px-6 py-2 rounded bg-purple-700 text-white font-bold hover:bg-purple-800 transition">Cadastrar</button>
    </div>
  </form>
</div>
<div id="modalCpfCnpj" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:1000;display:flex;align-items:center;justify-content:center;">
  <div style="background:#fff;padding:32px 28px;border-radius:16px;box-shadow:0 4px 32px #7c2ae820;max-width:340px;width:100%;text-align:center;">
    <h3 style="color:#7c2ae8;font-size:1.2rem;font-weight:bold;margin-bottom:18px;">Verificar CPF ou CNPJ</h3>
    <input type="text" id="cpfCnpjBusca" maxlength="18" placeholder="Digite o CPF ou CNPJ" style="width:100%;padding:10px 12px;border-radius:6px;border:1.5px solid #a259e6;font-size:1.1rem;margin-bottom:16px;">
    <div id="msgBuscaCpfCnpj" style="min-height:24px;color:#a259e6;font-size:0.98rem;margin-bottom:10px;"></div>
    <button id="btnBuscarCpfCnpj" style="background:#7c2ae8;color:#fff;padding:10px 28px;border:none;border-radius:6px;font-weight:bold;font-size:1rem;cursor:pointer;">Pesquisar</button>
    <div id="acoesClienteExistente" style="margin-top:18px;display:none;">
      <button id="btnIrDetalhesCliente" style="background:#ede9fe;color:#7c2ae8;padding:8px 18px;border:none;border-radius:6px;font-weight:bold;font-size:0.98rem;cursor:pointer;">Ver Detalhes do Cliente</button>
    </div>
  </div>
</div>
<style>
.input-form { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #a259e6; background: #f8f7fa; color: #232836; margin-bottom: 0.5rem; font-size: 1rem; }
.input-form:focus { outline: 2px solid #a259e6; background: #fff; }
.card-form {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px #7c2ae820, 0 1.5px 8px #0001;
  padding: 36px 32px;
  max-width: 700px;
  margin: 40px auto;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
  // Modal CPF/CNPJ
  $('#modalCpfCnpj').show();
  $('#formNovoCliente :input').prop('disabled', true);

  function limparModal() {
    $('#msgBuscaCpfCnpj').text('');
    $('#acoesClienteExistente').hide();
    $('#btnIrDetalhesCliente').data('id', '');
  }

  $('#btnBuscarCpfCnpj').on('click', function() {
    limparModal();
    var cpfCnpj = $('#cpfCnpjBusca').val().replace(/\D/g, '');
    if (!cpfCnpj || (cpfCnpj.length !== 11 && cpfCnpj.length !== 14)) {
      $('#msgBuscaCpfCnpj').css('color','#e11d48').text('Digite um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.');
      return;
    }
    $('#msgBuscaCpfCnpj').css('color','#7c2ae8').text('Pesquisando...');
    $.post('cliente_controller.php', { action: 'buscarPorCpfCnpj', cpf_cnpj: cpfCnpj }, function(resp) {
      if (resp.success && resp.data) {
        $('#msgBuscaCpfCnpj').css('color','#e11d48').text('Já existe um cliente cadastrado com este CPF/CNPJ!');
        $('#acoesClienteExistente').show();
        $('#btnIrDetalhesCliente').data('id', resp.data.id);
      } else {
        $('#msgBuscaCpfCnpj').css('color','#059669').text('CPF/CNPJ liberado!');
        setTimeout(function() {
          $('#modalCpfCnpj').fadeOut(200);
          $('#formNovoCliente :input').prop('disabled', false);
          $('#cpfCnpj').val(cpfCnpj);
        }, 600);
      }
    }, 'json').fail(function() {
      $('#msgBuscaCpfCnpj').css('color','#e11d48').text('Erro ao consultar. Tente novamente.');
    });
  });

  $('#btnIrDetalhesCliente').on('click', function() {
    var id = $(this).data('id');
    if (id) window.location.href = 'cliente_detalhes.php?id=' + id;
  });

  // Enter no input do modal
  $('#cpfCnpjBusca').on('keypress', function(e) {
    if (e.which === 13) $('#btnBuscarCpfCnpj').click();
  });

  $('#formNovoCliente').on('submit', function(e) {
    e.preventDefault();
    $('#msgNovoCliente').removeClass().text('Salvando...');
    const dados = $(this).serialize();
    $.post('cliente_controller.php', dados + '&acao=novo', function(resp) {
      if (resp.success) {
        $('#msgNovoCliente').addClass('text-green-600').text('Cliente cadastrado com sucesso!');
        setTimeout(() => { window.location.href = 'clientes.php'; }, 1200);
      } else {
        $('#msgNovoCliente').addClass('text-red-600').text(resp.message || 'Erro ao cadastrar cliente.');
      }
    }, 'json').fail(function() {
      $('#msgNovoCliente').addClass('text-red-600').text('Erro de comunicação com o servidor.');
    });
  });
});
</script>
<?php } // end render_content 