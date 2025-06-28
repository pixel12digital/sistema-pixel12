// cobranca_add.js
$(function() {
    let clienteEncontrado = false;
    let clienteId = null;
    // Etapas
    function showStep(n) {
        $('.step').removeClass('active');
        $('.step-' + n).addClass('active');
    }
    // Busca cliente
    $('#btnBuscarCliente').on('click', function() {
        const cpfCnpj = $('#cpfCnpj').val().replace(/\D/g, '');
        if (!cpfCnpj) { $('#msg').text('Informe o CPF ou CNPJ.'); return; }
        $('#msg').text('Buscando cliente...');
        $.get('cliente_busca.php', { cpfCnpj }, function(resp) {
            if (resp.success) {
                clienteEncontrado = true;
                clienteId = resp.data.id;
                $('#clienteForm').removeClass('hidden');
                $('#nome').val(resp.data.name).prop('readonly', true);
                $('#email').val(resp.data.email).prop('readonly', true);
                $('#celular').val(resp.data.mobilePhone).prop('readonly', true);
                $('#cep').val(resp.data.postalCode).prop('readonly', true);
                $('#rua').val(resp.data.address).prop('readonly', true);
                $('#numero').val(resp.data.addressNumber).prop('readonly', true);
                $('#complemento').val(resp.data.complement).prop('readonly', true);
                $('#bairro').val(resp.data.province).prop('readonly', true);
                $('#cidade').val(resp.data.city).prop('readonly', true);
                $('#msg').text('Cliente encontrado!');
                $('#btnProx1').prop('disabled', false);
            } else {
                clienteEncontrado = false;
                clienteId = null;
                $('#clienteForm').removeClass('hidden');
                $('#clienteForm input').val('').prop('readonly', false);
                $('#msg').text('Cliente não encontrado. Preencha os dados para cadastrar.');
                $('#btnProx1').prop('disabled', false);
            }
        }, 'json');
    });
    // Avançar para etapa 2
    $('#btnProx1').on('click', function() {
        showStep(2);
    });
    // Voltar para etapa 1
    $('#btnVoltar1').on('click', function() {
        showStep(1);
    });
    // Avançar para etapa 3 (resumo)
    $('#btnProx2').on('click', function() {
        // Montar resumo
        let resumo = '<b>Tipo de cobrança:</b> ' + $('#tipoCobranca').val() + '<br>';
        $('#resumoCobranca').html(resumo + '<i>Resumo detalhado em breve...</i>');
        showStep(3);
    });
    // Voltar para etapa 2
    $('#btnVoltar2').on('click', function() {
        showStep(2);
    });
    // Troca tipo de cobrança
    $('#tipoCobranca').on('change', function() {
        let tipo = $(this).val();
        let html = '';
        if (tipo === 'avulsa') {
            html += '<label>Valor (R$):</label><input type="text" name="valor" required>';
            html += '<label>Vencimento:</label><input type="date" name="vencimento" required>';
            html += '<label>Descrição:</label><input type="text" name="descricao" required>';
            html += '<label>Forma de pagamento:</label><select name="billingType"><option value="BOLETO">Boleto</option><option value="PIX">Pix</option><option value="CREDIT_CARD">Cartão de Crédito</option><option value="DEBIT_CARD">Cartão de Débito</option></select>';
        } else if (tipo === 'parcelamento') {
            html += '<label>Valor total (R$):</label><input type="text" name="totalValue" required>';
            html += '<label>Nº de parcelas:</label><input type="number" name="installmentCount" min="2" max="21" required>';
            html += '<label>Vencimento da 1ª parcela:</label><input type="date" name="dueDate" required>';
            html += '<label>Descrição:</label><input type="text" name="descricao" required>';
            html += '<label>Forma de pagamento:</label><select name="billingType"><option value="BOLETO">Boleto</option><option value="PIX">Pix</option><option value="CREDIT_CARD">Cartão de Crédito</option></select>';
        } else if (tipo === 'assinatura') {
            html += '<label>Valor (R$):</label><input type="text" name="valor" required>';
            html += '<label>Data inicial:</label><input type="date" name="nextDueDate" required>';
            html += '<label>Descrição:</label><input type="text" name="descricao" required>';
            html += '<label>Ciclo:</label><select name="cycle"><option value="MONTHLY">Mensal</option><option value="WEEKLY">Semanal</option><option value="BIWEEKLY">Quinzenal</option><option value="QUARTERLY">Trimestral</option><option value="SEMIANNUALLY">Semestral</option><option value="YEARLY">Anual</option></select>';
            html += '<label>Forma de pagamento:</label><select name="billingType"><option value="BOLETO">Boleto</option><option value="PIX">Pix</option><option value="CREDIT_CARD">Cartão de Crédito</option></select>';
        }
        $('#dadosCobranca').html(html);
    });
    // Inicializa tipo de cobrança
    $('#tipoCobranca').trigger('change');
    // Envio final do formulário
    $('#formCobranca').on('submit', function(e) {
        e.preventDefault();
        $('#msg').text('Enviando...');
        let dados = $(this).serializeArray();
        if (clienteEncontrado) dados.push({name:'clienteId', value:clienteId});
        $.post('cobranca_criar.php', dados, function(resp) {
            if (resp.success) {
                $('#msg').css('color','green').text('Cobrança cadastrada com sucesso!');
                setTimeout(()=>window.parent.location.reload(), 1200);
            } else {
                $('#msg').css('color','red').text(resp.error||'Erro ao cadastrar cobrança.');
            }
        }, 'json');
    });
}); 