function traduzirStatus(status) {
    switch ((status || '').toUpperCase()) {
        case 'PENDING': return 'Pendente';
        case 'OVERDUE': return 'Vencida';
        case 'PAID': return 'Paga';
        case 'CANCELED': return 'Cancelada';
        default: return status;
    }
}

function formatarDataBR(data) {
    if (!data) return '-';
    const partes = data.split('-');
    if (partes.length === 3) return `${partes[2]}/${partes[1]}/${partes[0]}`;
    return data;
}

function traduzirTipoPagamento(tipo) {
    switch ((tipo || '').toUpperCase()) {
        case 'PIX': return 'Pix';
        case 'BOLETO': return 'Boleto';
        case 'CREDIT_CARD': return 'Cartão de Crédito';
        case 'DEBIT_CARD': return 'Cartão de Débito';
        case 'UNDEFINED': return '-';
        default: return tipo || '-';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    fetch('/loja-virtual-revenda/api/cobrancas.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('invoices-tbody');
            tbody.innerHTML = '';
            if (!data || data.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="7" style="text-align:center;">Nenhuma cobrança encontrada.</td>';
                tbody.appendChild(tr);
                return;
            }
            data.forEach((cob, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-3 py-2 text-center">${idx + 1}</td>
                    <td class="px-2 py-2 text-left whitespace-nowrap max-w-xs" style="min-width:160px; max-width:260px; overflow:hidden; text-overflow:ellipsis;">${cob.cliente_nome || '-'}</td>
                    <td class="px-2 py-2 text-right" style="min-width:90px;">R$ ${parseFloat(cob.valor).toFixed(2)}</td>
                    <td class="px-3 py-2 text-center">${formatarDataBR(cob.data_criacao ? cob.data_criacao.substring(0, 10) : '')}</td>
                    <td class="px-3 py-2 text-center">${formatarDataBR(cob.vencimento)}</td>
                    <td class="px-3 py-2 text-center">${traduzirTipoPagamento(cob.tipo_pagamento)}</td>
                    <td class="px-3 py-2 text-center">${traduzirStatus(cob.status)}</td>
                    <td class="px-3 py-2 text-center"><a href="${cob.url_fatura}" target="_blank" class="text-purple-700 underline">Ver boleto</a></td>
                `;
                tbody.appendChild(tr);
            });
            // Alinhar o cabeçalho da coluna Valor à direita e Cliente à esquerda
            const ths = document.querySelectorAll('.invoices-table thead th');
            if (ths[1]) { ths[1].classList.add('text-left'); ths[1].style.minWidth = '160px'; ths[1].style.maxWidth = '260px'; }
            if (ths[2]) { ths[2].classList.add('text-right'); ths[2].style.minWidth = '90px'; }
        })
        .catch(() => {
            const tbody = document.getElementById('invoices-tbody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Erro ao carregar cobranças.</td></tr>';
        });
}); 