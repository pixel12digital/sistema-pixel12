function traduzirStatus(status) {
    switch ((status || '').toUpperCase()) {
        case 'PENDING': return 'Aguardando pagamento';
        case 'OVERDUE': return 'Vencida';
        case 'RECEIVED': return 'Recebida';
        case 'CONFIRMED': return 'Confirmada';
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

let cobrancasFiltradas = [];
let paginaAtual = 1;
const porPagina = 10;

function getFiltros() {
    return {
        status: document.getElementById('filter-status').value,
        data_vencimento_inicio: document.querySelector('.filter-date-due-inicio').value,
        data_vencimento_fim: document.querySelector('.filter-date-due-fim').value,
        cliente_nome: document.getElementById('filter-client').value.trim().toLowerCase()
    };
}

function aplicarFiltroClienteNome(cobrancas, nome) {
    if (!nome) return cobrancas;
    return cobrancas.filter(cob => (cob.cliente_nome || '').toLowerCase().includes(nome));
}

function renderizarTabelaCobrancas() {
    const tbody = document.getElementById('invoices-tbody');
    tbody.innerHTML = '';
    const inicio = (paginaAtual - 1) * porPagina;
    const fim = inicio + porPagina;
    const pageData = cobrancasFiltradas.slice(inicio, fim);
    if (pageData.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="7" style="text-align:center;">Nenhuma cobrança encontrada.</td>';
        tbody.appendChild(tr);
        atualizarTotaisCobrancas([]);
        return;
    }
    pageData.forEach((cob, idx) => {
        const tr = document.createElement('tr');
        tr.style.background = (idx % 2 === 0) ? '#d1d5db' : '#fff';
        tr.innerHTML = `
            <td class="px-3 py-2 text-center">${inicio + idx + 1}</td>
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
    atualizarTotaisCobrancas(cobrancasFiltradas);
    renderizarPaginacao();
}

function renderizarPaginacao() {
    const pagDiv = document.getElementById('pagination');
    pagDiv.innerHTML = '';
    const totalPaginas = Math.ceil(cobrancasFiltradas.length / porPagina) || 1;
    const btnPrev = document.createElement('button');
    btnPrev.textContent = '<';
    btnPrev.disabled = paginaAtual === 1;
    btnPrev.className = 'px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 mx-1';
    btnPrev.onclick = () => { paginaAtual--; renderizarTabelaCobrancas(); };
    pagDiv.appendChild(btnPrev);
    let start = Math.max(1, paginaAtual - 2);
    let end = Math.min(totalPaginas, paginaAtual + 2);
    if (start > 1) {
        const btn1 = document.createElement('button');
        btn1.textContent = '1';
        btn1.className = 'px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 mx-1';
        btn1.onclick = () => { paginaAtual = 1; renderizarTabelaCobrancas(); };
        pagDiv.appendChild(btn1);
        if (start > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'mx-1';
            pagDiv.appendChild(dots);
        }
    }
    for (let i = start; i <= end; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'px-2 py-1 rounded mx-1 ' + (i === paginaAtual ? 'bg-purple-600 text-white font-bold' : 'bg-gray-200 hover:bg-gray-300');
        btn.onclick = () => { paginaAtual = i; renderizarTabelaCobrancas(); };
        pagDiv.appendChild(btn);
    }
    if (end < totalPaginas) {
        if (end < totalPaginas - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'mx-1';
            pagDiv.appendChild(dots);
        }
        const btnLast = document.createElement('button');
        btnLast.textContent = totalPaginas;
        btnLast.className = 'px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 mx-1';
        btnLast.onclick = () => { paginaAtual = totalPaginas; renderizarTabelaCobrancas(); };
        pagDiv.appendChild(btnLast);
    }
    const btnNext = document.createElement('button');
    btnNext.textContent = '>';
    btnNext.disabled = paginaAtual === totalPaginas;
    btnNext.className = 'px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 mx-1';
    btnNext.onclick = () => { paginaAtual++; renderizarTabelaCobrancas(); };
    pagDiv.appendChild(btnNext);
}

function atualizarTotaisCobrancas(data) {
    let pendentes = 0, pendentesValor = 0;
    let vencidas = 0, vencidasValor = 0;
    data.forEach(cob => {
        if (cob.status === 'PENDING') {
            pendentes++;
            pendentesValor += parseFloat(cob.valor);
        } else if (cob.status === 'OVERDUE') {
            vencidas++;
            vencidasValor += parseFloat(cob.valor);
        }
    });
    document.querySelector('.summary-pending .text-xl').textContent = `${pendentes} (R$ ${pendentesValor.toFixed(2)})`;
    document.querySelector('.summary-overdue .text-xl').textContent = `${vencidas} (R$ ${vencidasValor.toFixed(2)})`;
    document.querySelector('.summary-open .text-xl').textContent = `R$ ${(pendentesValor + vencidasValor).toFixed(2)}`;
}

function carregarCobrancas() {
    const filtros = getFiltros();
    const params = new URLSearchParams();
    if (filtros.status) params.append('status', filtros.status);
    if (filtros.data_vencimento_inicio) params.append('data_vencimento_inicio', filtros.data_vencimento_inicio);
    if (filtros.data_vencimento_fim) params.append('data_vencimento_fim', filtros.data_vencimento_fim);
    fetch('/loja-virtual-revenda/api/cobrancas.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            cobrancasFiltradas = aplicarFiltroClienteNome(data, filtros.cliente_nome);
            paginaAtual = 1;
            renderizarTabelaCobrancas();
        })
        .catch(() => {
            const tbody = document.getElementById('invoices-tbody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Erro ao carregar cobranças.</td></tr>';
            cobrancasFiltradas = [];
            atualizarTotaisCobrancas([]);
            renderizarPaginacao();
        });
}

document.addEventListener('DOMContentLoaded', function () {
    carregarCobrancas();
    document.getElementById('btn-aplicar-filtros').addEventListener('click', function(e) {
        e.preventDefault();
        carregarCobrancas();
    });
    document.getElementById('filter-client').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            carregarCobrancas();
        }
    });
});

// Sincronização com barra de progresso realista
const btnSync = document.getElementById('btn-sincronizar');
if (btnSync) {
  btnSync.onclick = function() {
    if (!confirm('Deseja realmente sincronizar as cobranças com o Asaas?')) return;
    btnSync.disabled = true;
    btnSync.innerHTML = 'Sincronizando...';
    // Exibe barra de progresso
    document.getElementById('sync-status').style.display = 'block';
    document.getElementById('sync-bar').style.width = '10%';
    document.getElementById('sync-msg').innerText = 'Preparando sincronização...';
    setTimeout(() => {
      document.getElementById('sync-bar').style.width = '30%';
      document.getElementById('sync-msg').innerText = 'Conectando ao Asaas...';
    }, 300);
    fetch('sincronizar_asaas_ajax.php')
      .then(r => {
        document.getElementById('sync-bar').style.width = '60%';
        document.getElementById('sync-msg').innerText = 'Processando dados...';
        return r.json();
      })
      .then(resp => {
        document.getElementById('sync-bar').style.width = '100%';
        if (resp.success) {
          document.getElementById('sync-msg').innerText = 'Sincronização concluída!';
          setTimeout(() => location.reload(), 1200);
        } else {
          document.getElementById('sync-msg').innerText = 'Erro ao sincronizar!';
          alert('Erro ao sincronizar!\n' + (resp.error || '') + '\n' + (resp.output || ''));
        }
      })
      .catch(() => {
        document.getElementById('sync-msg').innerText = 'Erro ao sincronizar!';
        alert('Erro ao sincronizar!');
      })
      .finally(() => {
        btnSync.disabled = false;
        btnSync.innerHTML = '🔄 Sincronizar com Asaas';
        setTimeout(() => {
          document.getElementById('sync-status').style.display = 'none';
          document.getElementById('sync-bar').style.width = '0%';
        }, 2000);
      });
  };
} 