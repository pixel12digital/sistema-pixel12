/**
 * Sistema de Gerenciamento de Faturas
 * Carrega e gerencia as cobranças do sistema
 */

// Função para detectar o caminho base dinamicamente
function getBasePath() {
    const currentPath = window.location.pathname;
    if (currentPath.includes('loja-virtual-revenda')) {
        return '/loja-virtual-revenda';
    }
    return ''; // Para produção (raiz do domínio)
}

class InvoicesManager {
    constructor() {
        this.cobrancas = [];
        this.filtros = {};
        this.paginaAtual = 1;
        this.porPagina = 20;
        this.carregando = false;
        
        this.inicializar();
    }
    
    inicializar() {
        console.log('📊 Invoices Manager inicializado');
        this.carregarCobrancas();
        this.configurarEventos();
    }
    
    configurarEventos() {
        // Botão aplicar filtros
        const btnAplicarFiltros = document.getElementById('btn-aplicar-filtros');
        if (btnAplicarFiltros) {
            btnAplicarFiltros.addEventListener('click', () => {
                this.aplicarFiltros();
            });
        }
        
        // Filtros de data
        const filtrosData = document.querySelectorAll('input[type="date"]');
        filtrosData.forEach(input => {
            input.addEventListener('change', () => {
                this.aplicarFiltros();
            });
        });
        
        // Filtro de status
        const filtroStatus = document.getElementById('filtro-status');
        if (filtroStatus) {
            filtroStatus.addEventListener('change', () => {
                this.aplicarFiltros();
            });
        }
        
        // Busca de cliente
        const buscaCliente = document.getElementById('busca-cliente');
        if (buscaCliente) {
            let timeout;
            buscaCliente.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.aplicarFiltros();
                }, 500);
            });
        }
    }
    
    aplicarFiltros() {
        this.filtros = {};
        
        // Status
        const filtroStatus = document.getElementById('filtro-status');
        if (filtroStatus && filtroStatus.value !== 'todos') {
            this.filtros.status = filtroStatus.value;
        }
        
        // Datas de vencimento
        const vencimentoInicio = document.getElementById('vencimento-inicio');
        const vencimentoFim = document.getElementById('vencimento-fim');
        if (vencimentoInicio && vencimentoInicio.value) {
            this.filtros.data_vencimento_inicio = vencimentoInicio.value;
        }
        if (vencimentoFim && vencimentoFim.value) {
            this.filtros.data_vencimento_fim = vencimentoFim.value;
        }
        
        // Busca de cliente
        const buscaCliente = document.getElementById('busca-cliente');
        if (buscaCliente && buscaCliente.value.trim()) {
            this.filtros.cliente = buscaCliente.value.trim();
        }
        
        this.paginaAtual = 1;
        this.carregarCobrancas();
    }
    
    async carregarCobrancas() {
        if (this.carregando) return;
        
        this.carregando = true;
        this.mostrarCarregando();
        
        try {
            const params = new URLSearchParams(this.filtros);
            params.append('pagina', this.paginaAtual);
            params.append('por_pagina', this.porPagina);
            
            const response = await fetch(`${getBasePath()}/api/cobrancas.php?${params}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (Array.isArray(data)) {
                this.cobrancas = data;
                this.renderizarCobrancas();
                this.atualizarEstatisticas();
            } else {
                throw new Error('Resposta inválida do servidor');
            }
            
        } catch (error) {
            console.error('Erro ao carregar cobranças:', error);
            this.mostrarErro('Erro ao carregar cobranças: ' + error.message);
        } finally {
            this.carregando = false;
            this.ocultarCarregando();
        }
    }
    
    mostrarCarregando() {
        const tabela = document.getElementById('tabela-cobrancas');
        if (tabela) {
            tabela.innerHTML = '<tr><td colspan="10" class="text-center py-4">⏳ Carregando cobranças...</td></tr>';
        }
    }
    
    ocultarCarregando() {
        // Removido automaticamente quando renderizarCobrancas é chamado
    }
    
    mostrarErro(mensagem) {
        const tabela = document.getElementById('tabela-cobrancas');
        if (tabela) {
            tabela.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-red-600">❌ ${mensagem}</td></tr>`;
        }
    }
    
    renderizarCobrancas() {
        const tabela = document.getElementById('tabela-cobrancas');
        if (!tabela) return;
        
        if (this.cobrancas.length === 0) {
            tabela.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-gray-500">Nenhuma cobrança encontrada</td></tr>';
            return;
        }
        
        const html = this.cobrancas.map(cobranca => this.gerarLinhaCobranca(cobranca)).join('');
        tabela.innerHTML = html;
    }
    
    gerarLinhaCobranca(cobranca) {
        const statusClass = this.getStatusClass(cobranca.status);
        const valor = parseFloat(cobranca.valor).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
        
        const vencimento = new Date(cobranca.vencimento).toLocaleDateString('pt-BR');
        const ultimaInteracao = cobranca.ultima_interacao ? 
            new Date(cobranca.ultima_interacao).toLocaleDateString('pt-BR') : 
            'Nunca';
        
        // Traduzir status para português
        const statusTraduzido = this.traduzirStatus(cobranca.status);
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2">${cobranca.id}</td>
                <td class="px-4 py-2">${cobranca.cliente_nome || 'Cliente não informado'}</td>
                <td class="px-4 py-2">${cobranca.cliente_contact_name || 'Preencher nome.'}</td>
                <td class="px-4 py-2 font-semibold">${valor}</td>
                <td class="px-4 py-2">${vencimento}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                        ${statusTraduzido}
                    </span>
                </td>
                <td class="px-4 py-2 text-sm text-gray-600">${ultimaInteracao}</td>
                <td class="px-4 py-2">
                    ${this.getWhatsAppStatus(cobranca.whatsapp_status, cobranca.whatsapp_motivo_erro)}
                </td>
                <td class="px-4 py-2">
                    <button class="text-blue-600 hover:text-blue-800 text-sm">
                        📊 Monitorar
                    </button>
                </td>
                <td class="px-4 py-2">
                    <div class="flex space-x-2">
                        <button class="text-green-600 hover:text-green-800" title="Editar">
                            ✏️
                        </button>
                        <button class="text-red-600 hover:text-red-800" title="Excluir">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    getStatusClass(status) {
        const classes = {
            'PENDING': 'bg-yellow-100 text-yellow-800',
            'CONFIRMED': 'bg-green-100 text-green-800',
            'OVERDUE': 'bg-red-100 text-red-800',
            'CANCELLED': 'bg-gray-100 text-gray-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    traduzirStatus(status) {
        const traducoes = {
            'PENDING': 'Pendente',
            'CONFIRMED': 'Confirmada',
            'OVERDUE': 'Vencida',
            'CANCELLED': 'Cancelada'
        };
        return traducoes[status] || status;
    }
    
    getWhatsAppStatus(status, motivoErro) {
        if (!status) return '<span class="text-gray-500">Não enviado</span>';
        
        if (status === 'enviado') {
            return '<span class="text-green-600">✅ Enviado</span>';
        } else if (status === 'erro') {
            return `<span class="text-red-600" title="${motivoErro || 'Erro desconhecido'}">❌ Erro</span>`;
        } else {
            return `<span class="text-yellow-600">⏳ ${status}</span>`;
        }
    }
    
    atualizarEstatisticas() {
        const emAberto = this.cobrancas.filter(c => c.status === 'PENDING').length;
        const pendentes = this.cobrancas.filter(c => c.status === 'CONFIRMED').length;
        const vencidas = this.cobrancas.filter(c => c.status === 'OVERDUE').length;
        
        const valorEmAberto = this.cobrancas
            .filter(c => c.status === 'PENDING')
            .reduce((total, c) => total + parseFloat(c.valor), 0);
        
        const valorPendentes = this.cobrancas
            .filter(c => c.status === 'CONFIRMED')
            .reduce((total, c) => total + parseFloat(c.valor), 0);
        
        const valorVencidas = this.cobrancas
            .filter(c => c.status === 'OVERDUE')
            .reduce((total, c) => total + parseFloat(c.valor), 0);
        
        // Atualizar elementos da interface
        this.atualizarElemento('em-aberto-valor', valorEmAberto.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }));
        
        this.atualizarElemento('pendentes-valor', `${pendentes} (${valorPendentes.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        })})`);
        
        this.atualizarElemento('vencidas-valor', `${vencidas} (${valorVencidas.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        })})`);
    }
    
    atualizarElemento(id, valor) {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.textContent = valor;
        }
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Inicializando Invoices Manager...');
    window.invoicesManager = new InvoicesManager();
}); 