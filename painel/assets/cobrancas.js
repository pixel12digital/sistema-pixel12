// Função para detectar o caminho base dinamicamente
function getBasePath() {
    const currentPath = window.location.pathname;
    if (currentPath.includes('loja-virtual-revenda')) {
        return '/loja-virtual-revenda';
    }
    return ''; // Para produção (raiz do domínio)
}

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

function formatarDataHoraBR(data) {
    if (!data) return '-';
    // Garante que a data seja tratada como UTC se vier sem timezone
    let d = new Date(data.replace(' ', 'T'));
    if (isNaN(d.getTime())) d = new Date(data);
    if (isNaN(d.getTime())) return '-';
    // Ajusta para o fuso de São Paulo
    return d.toLocaleString('pt-BR', {
        timeZone: 'America/Sao_Paulo',
        hour: '2-digit',
        minute: '2-digit',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
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
        tr.innerHTML = '<td colspan="10" style="text-align:center;">Nenhuma cobrança encontrada.</td>';
        tbody.appendChild(tr);
        atualizarTotaisCobrancas([]);
        atualizarTotalFaturasInfo([]);
        return;
    }
    pageData.forEach((cob, idx) => {
        const tr = document.createElement('tr');
        tr.style.background = (idx % 2 === 0) ? '#d1d5db' : '#fff';
        // Adiciona data-cobranca-id e data-cliente-id para facilitar update instantâneo
        tr.setAttribute('data-cobranca-id', cob.id);
        tr.setAttribute('data-cliente-id', cob.cliente_id);
        // Ícones SVG
        const iconeBoleto = `<a href="${cob.url_fatura}" target="_blank" title="Ver boleto" style="margin-right:8px;"><svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' viewBox='0 0 24 24'><rect x='4' y='4' width='16' height='16' rx='2' stroke='#7c3aed' stroke-width='2' fill='none'/><path d='M8 8h8M8 12h8M8 16h4' stroke='#7c3aed' stroke-width='2' stroke-linecap='round'/></svg></a>`;
        // Substituir o iconeCliente para não abrir nova guia
        const iconeCliente = `<a href="#" class="btn-abrir-modal-cliente" data-cliente-id="${cob.cliente_id}" title="Visualizar cliente" style="margin-right:8px;"><svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' viewBox='0 0 24 24'><circle cx='12' cy='8' r='4' stroke='#6366f1' stroke-width='2' fill='none'/><path d='M4 20c0-4 4-7 8-7s8 3 8 7' stroke='#6366f1' stroke-width='2' fill='none'/></svg></a>`;
        const iconeWhats = `<a href='#' class='btn-whatsapp' data-cliente='${cob.cliente_id}' title='Enviar WhatsApp'><svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' viewBox='0 0 24 24'><path d='M20.52 3.48A12 12 0 0 0 3.48 20.52l-1.11 4.07a1 1 0 0 0 1.22 1.22l4.07-1.11A12 12 0 1 0 20.52 3.48ZM12 22a10 10 0 1 1 10-10A10 10 0 0 1 12 22Zm4.29-7.71-2.54-1.09a1 1 0 0 0-1.13.21l-.54.54a7.72 7.72 0 0 1-3.36-3.36l.54-.54a1 1 0 0 0 .21-1.13l-1.09-2.54a1 1 0 0 0-1.13-.58A8 8 0 0 0 4 12a8 8 0 0 0 8 8 8 8 0 0 0 7.29-4.29 1 1 0 0 0-.58-1.13Z' fill='#22c55e'/></svg></a>`;
        // Status WhatsApp
        let statusHtml = '';
        if (cob.whatsapp_status === 'enviado') {
          // Sempre clicável, mesmo sem ID
          let dataMsgId = cob.whatsapp_msg_id && Number(cob.whatsapp_msg_id) > 0 ? `data-msg-id='${cob.whatsapp_msg_id}'` : '';
          statusHtml = `<span class='status-enviado-clickable' ${dataMsgId} data-cliente-id='${cob.cliente_id}' data-vencimento='${cob.vencimento}' style='color:green;font-weight:bold;cursor:pointer;text-decoration:underline dotted;' title='Clique para marcar como Pendente'>✔️ Enviado</span>`;
        } else if (cob.whatsapp_status === 'erro') {
            statusHtml = `<span style='color:red;font-weight:bold;' title='${cob.whatsapp_motivo_erro || 'Erro ao enviar'}'>❌ Erro</span>`;
        } else if (cob.whatsapp_status === 'pendente') {
            statusHtml = `<span class='status-pendente-clickable' data-msg-id='${cob.whatsapp_msg_id || ''}' style='color:orange;font-weight:bold;cursor:pointer;text-decoration:underline dotted;' title='Clique para marcar como Enviada'>⏳ Pendente</span>`;
        } else {
            statusHtml = `<span style='color:gray;' title='Sem envio'>-</span>`;
        }
        // Edição rápida do contato principal
        let contatoHtml = '-';
        if (cob.cliente_contact_name && cob.cliente_contact_name.trim() !== '-') {
            contatoHtml = `<span class="contato-principal" data-cliente="${cob.cliente_id}">${cob.cliente_contact_name}</span>`;
        } else {
            contatoHtml = `<input type="text" class="input-contato-principal" data-cliente="${cob.cliente_id}" placeholder="Preencher nome..." style="min-width:80px;max-width:120px;padding:2px 6px;border:1px solid #a259e6;border-radius:4px;" />`;
        }
        
        // Coluna de Monitoramento
        const celularCliente = cob.cliente_celular || '';
        const monitoramentoHtml = `
            <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 4px; font-size: 12px;">
                    <input type="checkbox" class="checkbox-monitoramento" data-cliente-id="${cob.cliente_id}" style="width: 14px; height: 14px;">
                    <span>Monitorar</span>
                </label>
                <button class="btn-validar-cliente" 
                        data-cliente-id="${cob.cliente_id}" 
                        data-cliente-nome="${cob.cliente_nome || ''}" 
                        data-cliente-celular="${celularCliente}"
                        style="background: #10b981; color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer;"
                        ${!celularCliente ? 'disabled' : ''}
                        title="${!celularCliente ? 'Cliente sem celular' : 'Enviar mensagem de validação'}">
                    Validar
                </button>
            </div>
        `;
        
        tr.innerHTML = `
            <td class="px-3 py-2 text-center">${inicio + idx + 1}</td>
            <td class="px-2 py-2 text-left" style="min-width:100px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${cob.cliente_nome || '-'}</td>
            <td class="px-2 py-2 text-left">${contatoHtml}</td>
            <td class="px-2 py-2 text-right" style="min-width:90px;">R$ ${parseFloat(cob.valor).toFixed(2)}</td>
            <td class="px-3 py-2 text-center">${formatarDataBR(cob.vencimento)}</td>
            <td class="px-3 py-2 text-center">${traduzirStatus(cob.status)}</td>
            <td class="px-2 py-2 text-center ultima-interacao-cell" data-cliente="${cob.cliente_id}" data-cobranca="${cob.id}" data-valor="${cob.ultima_interacao || ''}" title="Clique para editar" style="cursor:pointer;">${formatarDataHoraBR(cob.ultima_interacao)}</td>
            <td class="px-2 py-2 text-center status-envio-cell" data-cliente="${cob.cliente_id}" data-cobranca="${cob.id}" data-status="${cob.whatsapp_status || ''}" title="Clique para corrigir status" style="cursor:pointer;">${statusHtml}</td>
            <td class="px-2 py-2 text-center">${monitoramentoHtml}</td>
            <td class="px-3 py-2 text-center" style="min-width:90px; white-space:nowrap;"><span style='display:flex;align-items:center;gap:6px;justify-content:center;'>${iconeBoleto}${iconeCliente}${iconeWhats}</span></td>
        `;
        tbody.appendChild(tr);
    });
    // Adiciona evento para inputs de contato principal
    document.querySelectorAll('.input-contato-principal').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const nome = this.value.trim();
                const clienteId = this.getAttribute('data-cliente');
                if (!nome) return;
                this.disabled = true;
                fetch(getBasePath() + '/api/atualizar_contato_cliente.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cliente_id: clienteId, contact_name: nome })
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        this.outerHTML = `<span class='contato-principal' data-cliente='${clienteId}'>${nome}</span>`;
                        // Atualizar a tabela sem recarregar a página inteira
                        carregarCobrancas(false);
                    } else {
                        alert('Erro ao salvar: ' + (resp.error || ''));
                        this.disabled = false;
                    }
                })
                .catch(() => {
                    alert('Erro ao salvar contato.');
                    this.disabled = false;
                });
            }
        });
    });
    // Edição inline da Última Interação
    document.querySelectorAll('.ultima-interacao-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            if (cell.querySelector('input')) return; // já está editando
            const clienteId = cell.getAttribute('data-cliente');
            const cobrancaId = cell.getAttribute('data-cobranca');
            let valorAtual = cell.getAttribute('data-valor');
            let valorInput = '';
            // Força o input mesmo se valorAtual for vazio ou '-'
            if (valorAtual && valorAtual !== '-') {
                // Converter para formato yyyy-MM-ddTHH:mm
                const dt = new Date(valorAtual.replace(/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})/, '$3-$2-$1T$4:$5'));
                valorInput = dt.toISOString().slice(0,16);
            } else {
                valorInput = new Date().toISOString().slice(0,16);
            }
            const input = document.createElement('input');
            input.type = 'datetime-local';
            input.value = valorInput;
            input.style = 'width:160px;padding:2px 6px;border:1px solid #a259e6;border-radius:4px;';
            cell.innerHTML = '';
            cell.appendChild(input);
            input.focus();
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    salvarInteracaoManual(clienteId, input.value, cell);
                } else if (e.key === 'Escape') {
                    cell.innerHTML = formatarDataHoraBR(valorAtual);
                }
            });
            input.addEventListener('blur', function() {
                cell.innerHTML = formatarDataHoraBR(valorAtual);
            });
        });
    });
    // Edição manual do Status Envio
    document.querySelectorAll('.status-envio-cell').forEach(cell => {
        cell.addEventListener('click', function(e) {
            if (cell.querySelector('select')) return; // já está editando
            const clienteId = cell.getAttribute('data-cliente');
            const cobrancaId = cell.getAttribute('data-cobranca');
            const statusAtual = cell.getAttribute('data-status') || '';
            // Cria select para escolher status
            const select = document.createElement('select');
            select.innerHTML = `
                <option value="">-</option>
                <option value="enviado">Enviado</option>
                <option value="pendente">Pendente</option>
                <option value="erro">Erro</option>
            `;
            select.value = statusAtual;
            // Botão de confirmação
            const btnSalvar = document.createElement('button');
            btnSalvar.textContent = 'Salvar';
            btnSalvar.style = 'margin-left:8px;padding:2px 10px;border-radius:4px;background:#7c3aed;color:#fff;border:none;cursor:pointer;';
            cell.innerHTML = '';
            cell.appendChild(select);
            cell.appendChild(btnSalvar);
            select.focus();
            let statusSalvo = false;
            function salvarStatusManual() {
                const novoStatus = select.value;
                cell.innerHTML = '<span style="color:#a259e6;">Salvando...</span>';
                fetch('/painel/api/corrigir_status_manual.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'cliente_id='+encodeURIComponent(clienteId)+'&cobranca_id='+encodeURIComponent(cobrancaId)+'&status='+encodeURIComponent(novoStatus)
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        let html = '';
                        if (novoStatus === 'enviado') {
                            html = "<span style='color:green;font-weight:bold;'>✔️ Enviado</span>";
                        } else if (novoStatus === 'erro') {
                            html = "<span style='color:red;font-weight:bold;'>❌ Erro</span>";
                        } else if (novoStatus === 'pendente') {
                            html = "<span style='color:orange;font-weight:bold;'>⏳ Pendente</span>";
                        } else {
                            html = "<span style='color:gray;'>-</span>";
                        }
                        cell.innerHTML = html;
                        cell.setAttribute('data-status', novoStatus);
                        statusSalvo = true;
                        // Atualiza a célula de Última Interação na mesma linha com valor real do backend
                        const row = cell.closest('tr');
                        const ultimaInteracaoCell = row ? row.querySelector('.ultima-interacao-cell') : null;
                        if (ultimaInteracaoCell) {
                            fetch(getBasePath() + '/api/cobrancas.php?id=' + encodeURIComponent(cobrancaId))
                              .then(r => r.json())
                              .then(data => {
                                  const cob = Array.isArray(data) ? data.find(c => String(c.id) === String(cobrancaId)) : null;
                                  if (cob && ultimaInteracaoCell) {
                                      ultimaInteracaoCell.textContent = formatarDataHoraBR(cob.ultima_interacao);
                                  }
                              });
                        }
                    } else {
                        cell.innerHTML = '<span style="color:red;">Erro!</span>';
                        alert('Erro ao salvar: ' + (resp.error || ''));
                    }
                })
                .catch(() => {
                    cell.innerHTML = '<span style="color:red;">Erro!</span>';
                    alert('Erro ao salvar status.');
                });
            }
            select.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    salvarStatusManual();
                }
            });
            btnSalvar.addEventListener('click', function() {
                salvarStatusManual();
            });
            // Salvar automaticamente ao mudar o select
            select.addEventListener('change', function() {
                salvarStatusManual();
            });
            select.addEventListener('blur', function() {
                setTimeout(() => {
                    if (!cell.contains(document.activeElement) && !statusSalvo) {
                        // Volta ao valor anterior se não salvar
                        let html = '';
                        if (statusAtual === 'enviado') {
                            html = "<span style='color:green;font-weight:bold;'>✔️ Enviado</span>";
                        } else if (statusAtual === 'erro') {
                            html = "<span style='color:red;font-weight:bold;'>❌ Erro</span>";
                        } else if (statusAtual === 'pendente') {
                            html = "<span style='color:orange;font-weight:bold;'>⏳ Pendente</span>";
                        } else {
                            html = "<span style='color:gray;'>-</span>";
                        }
                        cell.innerHTML = html;
                    }
                }, 200);
            });
            btnSalvar.addEventListener('blur', function() {
                setTimeout(() => {
                    if (!cell.contains(document.activeElement) && !statusSalvo) {
                        let html = '';
                        if (statusAtual === 'enviado') {
                            html = "<span style='color:green;font-weight:bold;'>✔️ Enviado</span>";
                        } else if (statusAtual === 'erro') {
                            html = "<span style='color:red;font-weight:bold;'>❌ Erro</span>";
                        } else if (statusAtual === 'pendente') {
                            html = "<span style='color:orange;font-weight:bold;'>⏳ Pendente</span>";
                        } else {
                            html = "<span style='color:gray;'>-</span>";
                        }
                        cell.innerHTML = html;
                    }
                }, 200);
            });
        });
    });
    atualizarTotaisCobrancas(cobrancasFiltradas);
    atualizarTotalFaturasInfo(cobrancasFiltradas);
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

function atualizarTotalFaturasInfo(data) {
    const el = document.getElementById('total-faturas-info');
    if (!el) return;
    const total = data.length;
    const valor = data.reduce((soma, cob) => soma + parseFloat(cob.valor), 0);
    el.textContent = total > 0 ? `Exibindo ${total} fatura${total > 1 ? 's' : ''} (R$ ${valor.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2})})` : '';
}

function carregarCobrancas(resetPagina = true) {
    const filtros = getFiltros();
    const params = new URLSearchParams();
    if (filtros.status) params.append('status', filtros.status);
    if (filtros.data_vencimento_inicio) params.append('data_vencimento_inicio', filtros.data_vencimento_inicio);
    if (filtros.data_vencimento_fim) params.append('data_vencimento_fim', filtros.data_vencimento_fim);
    fetch(getBasePath() + '/api/cobrancas.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            cobrancasFiltradas = aplicarFiltroClienteNome(data, filtros.cliente_nome);
            if (resetPagina) paginaAtual = 1;
            renderizarTabelaCobrancas();
        })
        .catch(() => {
            const tbody = document.getElementById('invoices-tbody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Erro ao carregar cobranças.</td></tr>';
            cobrancasFiltradas = [];
            atualizarTotaisCobrancas([]);
            atualizarTotalFaturasInfo([]);
            renderizarPaginacao();
        });
}

// Canal padrão para financeiro (envio de faturas)
const CANAL_PADRAO_FINANCEIRO = 1; // Altere para o canal_id desejado

// Adiciona modal para envio de WhatsApp
if (!document.getElementById('modal-whatsapp-envio')) {
  const modal = document.createElement('div');
  modal.id = 'modal-whatsapp-envio';
  modal.style = 'display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;';
  modal.innerHTML = `
    <div style="background:#fff;padding:32px 28px;border-radius:12px;min-width:420px;max-width:99vw;max-height:96vh;overflow:auto;position:relative;">
      <button id="close-modal-whatsapp-envio" style="position:absolute;top:12px;right:18px;font-size:1.4rem;background:none;border:none;cursor:pointer;">&times;</button>
      <h3 class="text-lg font-bold mb-3">Enviar WhatsApp</h3>
      <div class="mb-2"><label for="select-canal-whatsapp" style="font-weight:500;">Escolha o número para envio:</label><select id="select-canal-whatsapp" class="w-full border rounded px-2 py-1 mb-2" style="min-width:180px;"></select></div>
      <div class="mb-2"><textarea id="mensagem-whatsapp" rows="8" class="w-full border rounded px-2 py-1" style="resize:vertical;min-height:120px;font-size:1.1em;"></textarea></div>
      <button id="btn-enviar-whatsapp" class="bg-green-600 hover:bg-green-800 text-white px-4 py-2 rounded font-semibold w-full" style="font-size:1.15em;">Enviar</button>
      <div id="status-envio-whatsapp" class="mt-3 text-sm"></div>
    </div>
  `;
  document.body.appendChild(modal);
}

function montarMensagemPadraoWhatsApp(cob) {
  const nome = cob.cliente_contact_name || cob.cliente_nome || 'cliente';
  const link = cob.url_fatura || '';
  const vencimento = formatarDataBR(cob.vencimento);
  const hoje = new Date();
  const dataVenc = cob.vencimento ? new Date(cob.vencimento.replace(/-/g, '/')) : null;
  let msg = '';
  if ((cob.status || '').toUpperCase() === 'PENDING' && dataVenc && dataVenc.toDateString() === hoje.toDateString()) {
    msg = `Olá ${nome}! Lembrete: sua fatura vence hoje. Para acessar o boleto ou pagar via Pix, clique no link: ${link}`;
  } else {
    switch ((cob.status || '').toUpperCase()) {
      case 'PENDING':
        msg = `Olá ${nome}! Sua fatura com vencimento em ${vencimento} está aguardando pagamento. Para acessar o boleto ou pagar via Pix, clique no link: ${link}`;
        break;
      case 'OVERDUE':
        msg = `Olá ${nome}! Sua fatura com vencimento em ${vencimento} está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: ${link}`;
        break;
      case 'RECEIVED':
        msg = `Olá ${nome}! Confirmamos o recebimento do seu pagamento referente à fatura com vencimento em ${vencimento}. Obrigado!`;
        break;
      default:
        msg = `Olá ${nome}! Segue o link da sua fatura: ${link}`;
    }
  }
  msg += '\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.';
  return msg;
}

function abrirModalWhatsapp(cliente_id, canal_id, mensagemPadrao, cobranca_id) {
  const modal = document.getElementById('modal-whatsapp-envio');
  const closeBtn = document.getElementById('close-modal-whatsapp-envio');
  const textarea = document.getElementById('mensagem-whatsapp');
  const btnEnviar = document.getElementById('btn-enviar-whatsapp');
  const statusDiv = document.getElementById('status-envio-whatsapp');
  const selectCanal = document.getElementById('select-canal-whatsapp');
  textarea.value = mensagemPadrao || '';
  statusDiv.textContent = '';
  modal.style.display = 'flex';
  closeBtn.onclick = () => { 
      modal.style.display = 'none';
      btnEnviar.disabled = false; // Garante reabilitação ao fechar manualmente
  };
  // Buscar canal padrão para Financeiro
  fetch(getBasePath() + '/api/canal_padrao_financeiro.php')
    .then(r => r.json())
    .then(jsonPadrao => {
      const canalPadraoId = jsonPadrao.canal_id;
      // Carregar canais
      fetch('/painel/api/listar_canais_whatsapp.php')
        .then(r => {
          return r.text().then(txt => {
            try {
              return JSON.parse(txt);
            } catch (e) {
              console.error('Erro ao parsear JSON dos canais:', e, txt);
              throw e;
            }
          });
        })
        .then(canais => {
          selectCanal.innerHTML = '';
          canais.forEach((canal, idx) => {
            const opt = document.createElement('option');
            opt.value = canal.id;
            let label = '';
            if (canal.nome_exibicao && canal.identificador) {
              label = canal.nome_exibicao + ' (' + canal.identificador + ')';
            } else if (canal.nome_exibicao) {
              label = canal.nome_exibicao;
            } else if (canal.identificador) {
              label = canal.identificador;
            } else {
              label = 'Canal';
            }
            opt.textContent = label;
            if (String(canal.id) === String(canalPadraoId)) opt.selected = true;
            selectCanal.appendChild(opt);
          });
          // Se nenhum canal ficou selecionado, seleciona o primeiro
          if (!selectCanal.value && selectCanal.options.length > 0) {
            selectCanal.selectedIndex = 0;
          }
        })
        .catch(err => {
          console.error('Erro no fetch dos canais WhatsApp:', err);
        });
    });
  // Remover eventuais listeners antigos e atribuir novo
  btnEnviar.onclick = null;
  btnEnviar.onclick = function() {
    btnEnviar.disabled = true;
    statusDiv.textContent = 'Enviando...';
    const canalSelecionado = selectCanal.value;
    fetch('enviar_mensagem_whatsapp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `cliente_id=${encodeURIComponent(cliente_id)}&canal_id=${encodeURIComponent(canalSelecionado)}&mensagem=${encodeURIComponent(textarea.value)}${cobranca_id ? `&cobranca_id=${encodeURIComponent(cobranca_id)}` : ''}`
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success === true) {
        statusDiv.textContent = 'Mensagem enviada com sucesso!';
        statusDiv.style.color = 'green';
        setTimeout(() => {
          modal.style.display = 'none';
          location.reload();
        }, 2000);
      } else {
        statusDiv.textContent = resp.error || 'Erro ao enviar mensagem.';
        statusDiv.style.color = 'red';
        btnEnviar.disabled = false;
      }
    })
    .catch(error => {
      statusDiv.textContent = 'Erro ao conectar ao servidor ou resposta inválida.';
      statusDiv.style.color = 'red';
      btnEnviar.disabled = false;
    });
  };
}

// Adicionar modal de erro reutilizável se não existir
if (!document.getElementById('modal-erro')) {
  const modalErro = document.createElement('div');
  modalErro.id = 'modal-erro';
  modalErro.style = 'display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:99999;align-items:center;justify-content:center;';
  modalErro.innerHTML = `
    <div class="modal" style="max-width:420px;min-width:280px;text-align:center;">
      <button id="close-modal-erro" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>
      <h3 class="text-lg font-bold mb-4" id="modal-erro-titulo">Erro</h3>
      <div id="modal-erro-msg" style="white-space:pre-line;"></div>
    </div>
  `;
  document.body.appendChild(modalErro);
  document.getElementById('close-modal-erro').onclick = function() {
    document.getElementById('modal-erro').style.display = 'none';
  };
}
function exibirErro(titulo, msg) {
  document.getElementById('modal-erro-titulo').textContent = titulo || 'Erro';
  document.getElementById('modal-erro-msg').textContent = msg || 'Ocorreu um erro inesperado.';
  document.getElementById('modal-erro').style.display = 'flex';
}

function salvarInteracaoManual(clienteId, dataHora, cell) {
    const cobrancaId = cell.getAttribute('data-cobranca');
    if (!clienteId || !dataHora || !cobrancaId) return;
    // Converter para formato Y-m-d H:i:s
    const dt = new Date(dataHora);
    const dataHoraFormatada = dt.getFullYear() + '-' + String(dt.getMonth()+1).padStart(2,'0') + '-' + String(dt.getDate()).padStart(2,'0') + ' ' + String(dt.getHours()).padStart(2,'0') + ':' + String(dt.getMinutes()).padStart(2,'0') + ':00';
    cell.innerHTML = '<span style="color:#a259e6;">Salvando...</span>';
    fetch('/painel/api/registrar_interacao_manual.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cliente_id='+encodeURIComponent(clienteId)+'&cobranca_id='+encodeURIComponent(cobrancaId)+'&data_hora='+encodeURIComponent(dataHoraFormatada)
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            cell.innerHTML = formatarDataHoraBR(dataHoraFormatada);
            carregarCobrancas();
        } else {
            cell.innerHTML = '<span style="color:red;">Erro!</span>';
            alert('Erro ao salvar: ' + (resp.error || ''));
        }
    })
    .catch(() => {
        cell.innerHTML = '<span style="color:red;">Erro!</span>';
        alert('Erro ao salvar interação.');
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
    document.addEventListener('click', function(e) {
      if (e.target.closest('.btn-whatsapp')) {
        e.preventDefault();
        const btn = e.target.closest('.btn-whatsapp');
        const cliente_id = btn.getAttribute('data-cliente');
        // Buscar dados da cobrança pelo cliente_id E pelo id da fatura (linha)
        const tr = btn.closest('tr');
        let cobranca_id = null;
        if (tr) {
          cobranca_id = tr.getAttribute('data-cobranca-id');
        }
        if (!cobranca_id) {
          // Busca pelo cliente_id e vencimento para garantir
          let vencimento = null;
          if (tr) {
            const tdVenc = tr.querySelector('td:nth-child(5)');
            if (tdVenc) vencimento = tdVenc.textContent.trim();
          }
          const cob = cobrancasFiltradas.find(c =>
            String(c.cliente_id) === String(cliente_id) &&
            (!vencimento || formatarDataBR(c.vencimento) === vencimento)
          );
          if (cob) cobranca_id = cob.id;
        }
        let canal_id = 1; // Ajuste conforme sua lógica de múltiplos canais
        // Buscar dados da cobrança pelo cliente_id
        const cob = cobrancasFiltradas.find(c => String(c.cliente_id) === String(cliente_id) && (!cobranca_id || String(c.id) === String(cobranca_id))) || {};
        let mensagemPadrao = montarMensagemPadraoWhatsApp(cob);
        abrirModalWhatsapp(cliente_id, canal_id, mensagemPadrao, cobranca_id);
      }
    });
    // Handler do botão de correção de status
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn-corrigir-status')) {
        const msgId = e.target.getAttribute('data-msg-id');
        if (!msgId) return;
        e.target.disabled = true;
        fetch('/painel/api/corrigir_status_mensagem.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `id=${encodeURIComponent(msgId)}&status=pendente`
        })
        .then(r => r.json())
        .then(resp => {
          if (resp.success) {
            showToast('Status corrigido para Pendente!', 'success');
            carregarCobrancas();
          } else {
            showToast('Erro ao corrigir status: ' + (resp.error || ''), 'error');
            e.target.disabled = false;
          }
        })
        .catch(() => {
          showToast('Erro ao conectar ao servidor.', 'error');
          e.target.disabled = false;
        });
      }
      // Handler para clicar no status "Enviado"
      if (e.target.classList.contains('status-enviado-clickable')) {
        const msgId = e.target.getAttribute('data-msg-id');
        if (msgId) {
          e.target.style.pointerEvents = 'none';
          fetch('/painel/api/corrigir_status_mensagem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(msgId)}&status=pendente`
          })
          .then(r => r.json())
          .then(resp => {
            if (resp.success) {
              showToast('Status corrigido para Pendente!', 'success');
              carregarCobrancas();
            } else {
              showToast('Erro ao corrigir status: ' + (resp.error || ''), 'error');
              e.target.style.pointerEvents = '';
            }
          })
          .catch(() => {
            showToast('Erro ao conectar ao servidor.', 'error');
            e.target.style.pointerEvents = '';
          });
        } else {
          // Sem ID: exibe modal de confirmação
          exibirModalConfirmacaoLiberarEnvio(e.target.getAttribute('data-cliente-id'), e.target.getAttribute('data-vencimento'));
        }
      }
      if (e.target.classList.contains('status-pendente-clickable')) {
        const msgId = e.target.getAttribute('data-msg-id');
        if (msgId) {
          if (!confirm('Deseja marcar esta mensagem como ENVIADA?')) return;
          e.target.style.pointerEvents = 'none';
          fetch('/painel/api/corrigir_status_mensagem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(msgId)}&status=enviado`
          })
          .then(r => r.json())
          .then(resp => {
            if (resp.success) {
              showToast('Status alterado para Enviada!', 'success');
              carregarCobrancas();
            } else {
              showToast('Erro ao alterar status: ' + (resp.error || ''), 'error');
              e.target.style.pointerEvents = '';
            }
          })
          .catch(() => {
            showToast('Erro ao conectar ao servidor.', 'error');
            e.target.style.pointerEvents = '';
          });
        }
      }
    });
    // Modal de cliente
    if (document.getElementById('modal-cliente-detalhes')) {
      document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-abrir-modal-cliente')) {
          e.preventDefault();
          const btn = e.target.closest('.btn-abrir-modal-cliente');
          const clienteId = btn.getAttribute('data-cliente-id');
          const modal = document.getElementById('modal-cliente-detalhes');
          const modalBody = document.getElementById('modal-cliente-detalhes-body');
          modal.style.display = 'flex';
          modalBody.innerHTML = '<div style="color:#7c3aed;font-weight:bold;">Carregando dados do cliente...</div>';
          fetch('cliente_modal.php?id=' + encodeURIComponent(clienteId))
            .then(r => r.text())
            .then(html => { 
              modalBody.innerHTML = html; 
              // Executar JavaScript das abas após o conteúdo ser carregado
              setTimeout(() => {
                // Função para trocar abas
                function trocarAba(abaClicada) {
                  // Remove classe active de todas as abas
                  modalBody.querySelectorAll('.painel-aba').forEach(aba => {
                    aba.classList.remove('active');
                  });
                  
                  // Esconde todos os conteúdos das abas
                  modalBody.querySelectorAll('.painel-tab').forEach(tab => {
                    tab.style.display = 'none';
                  });
                  
                  // Adiciona classe active na aba clicada
                  abaClicada.classList.add('active');
                  
                  // Mostra o conteúdo da aba correspondente
                  const tabName = abaClicada.getAttribute('data-tab');
                  const tabContent = modalBody.querySelector('.painel-tab-' + tabName);
                  if (tabContent) {
                    tabContent.style.display = 'block';
                  }
                }
                
                // Adiciona event listeners nas abas
                modalBody.querySelectorAll('.painel-aba').forEach(aba => {
                  aba.addEventListener('click', function() {
                    trocarAba(this);
                  });
                });
                
                // Garante que a primeira aba esteja ativa por padrão
                const primeiraAba = modalBody.querySelector('.painel-aba');
                if (primeiraAba) {
                  trocarAba(primeiraAba);
                }
              }, 100);
            })
            .catch(() => { modalBody.innerHTML = '<span style="color:#e11d48;">Erro ao carregar dados do cliente.</span>'; });
        }
        if (e.target.id === 'btn-fechar-modal-cliente') {
          document.getElementById('modal-cliente-detalhes').style.display = 'none';
        }
      });
    }
});

// Função showToast (caso não exista)
if (typeof showToast !== 'function') {
  window.showToast = function(msg, tipo) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style = `position:fixed;top:24px;right:24px;z-index:9999;padding:12px 22px;background:${tipo==='success'?'#bbf7d0':'#fee2e2'};color:${tipo==='success'?'#166534':'#b91c1c'};border-radius:8px;font-weight:500;box-shadow:0 2px 8px #0002;`;
    document.body.appendChild(toast);
    setTimeout(()=>{toast.remove();}, 2500);
  }
} 

// Modal de confirmação para liberar envio sem ID
if (!document.getElementById('modal-confirmar-liberar-envio')) {
  const modal = document.createElement('div');
  modal.id = 'modal-confirmar-liberar-envio';
  modal.style = 'display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:99999;align-items:center;justify-content:center;';
  modal.innerHTML = `
    <div style="background:#fff;padding:32px 28px;border-radius:12px;min-width:320px;max-width:99vw;max-height:96vh;overflow:auto;position:relative;">
      <button id="close-modal-confirmar-liberar-envio" style="position:absolute;top:12px;right:18px;font-size:1.4rem;background:none;border:none;cursor:pointer;">&times;</button>
      <h3 class="text-lg font-bold mb-3">Liberar envio?</h3>
      <div class="mb-2">Não foi possível identificar a mensagem para correção automática.<br>Deseja liberar o envio para este cliente hoje mesmo assim?</div>
      <button id="btn-confirmar-liberar-envio" class="bg-yellow-500 hover:bg-yellow-700 text-white px-4 py-2 rounded font-semibold w-full" style="font-size:1.1em;">Liberar envio</button>
    </div>
  `;
  document.body.appendChild(modal);
  document.getElementById('close-modal-confirmar-liberar-envio').onclick = function() {
    modal.style.display = 'none';
  };
}
function exibirModalConfirmacaoLiberarEnvio(clienteId, vencimento) {
  const modal = document.getElementById('modal-confirmar-liberar-envio');
  modal.style.display = 'flex';
  const btn = document.getElementById('btn-confirmar-liberar-envio');
  btn.onclick = function() {
    btn.disabled = true;
    fetch('/painel/api/liberar_envio_sem_id.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `cliente_id=${encodeURIComponent(clienteId)}&vencimento=${encodeURIComponent(vencimento)}`
    })
    .then(r => r.json().catch(() => ({ success: false, error: 'Resposta inválida do servidor.' })))
    .then(resp => {
      if (resp.success) {
        showToast('Envio liberado para este cliente hoje!', 'success');
        modal.style.display = 'none';
        carregarCobrancas();
      } else {
        showToast('Erro ao liberar envio: ' + (resp.error || ''), 'error');
        btn.disabled = false;
      }
    })
    .catch(() => {
      showToast('Erro ao conectar ao servidor.', 'error');
      btn.disabled = false;
    });
  };
} 