/**
 * Relatórios Financeiros
 * Sistema de relatórios consolidados de faturas e monitoramento
 */

class RelatoriosFinanceiros {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.carregarDados();
        this.configurarFiltros();
    }

    bindEvents() {
        document.getElementById('btn-exportar-relatorio')?.addEventListener('click', () => this.exportarRelatorio());
        document.getElementById('btn-atualizar-dados')?.addEventListener('click', () => this.carregarDados());
        document.getElementById('btn-aplicar-periodo')?.addEventListener('click', () => this.aplicarFiltroPeriodo());
        document.getElementById('filter-periodo')?.addEventListener('change', (e) => this.togglePeriodoPersonalizado(e.target.value));
    }

    /**
     * Configura filtros de período
     */
    configurarFiltros() {
        const filterPeriodo = document.getElementById('filter-periodo');
        if (filterPeriodo) {
            this.togglePeriodoPersonalizado(filterPeriodo.value);
        }
    }

    /**
     * Toggle campos de período personalizado
     */
    togglePeriodoPersonalizado(valor) {
        const periodoPersonalizado = document.getElementById('periodo-personalizado');
        const periodoPersonalizadoFim = document.getElementById('periodo-personalizado-fim');
        
        if (valor === 'personalizado') {
            periodoPersonalizado.style.display = 'block';
            periodoPersonalizadoFim.style.display = 'block';
        } else {
            periodoPersonalizado.style.display = 'none';
            periodoPersonalizadoFim.style.display = 'none';
        }
    }

    /**
     * Carrega dados dos relatórios
     */
    async carregarDados() {
        try {
            const periodo = this.obterPeriodoSelecionado();
            const response = await fetch(`api/relatorios_financeiros.php?periodo=${periodo}`);
            const data = await response.json();

            if (data.success) {
                this.atualizarEstatisticas(data.estatisticas);
                this.atualizarGraficos(data.graficos);
                this.atualizarTopClientes(data.top_clientes);
                this.atualizarAnaliseMonitoramento(data.monitoramento);
            }
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            this.mostrarAlerta('Erro ao carregar dados dos relatórios', 'error');
        }
    }

    /**
     * Obtém período selecionado
     */
    obterPeriodoSelecionado() {
        const filterPeriodo = document.getElementById('filter-periodo');
        const dataInicio = document.getElementById('data-inicio');
        const dataFim = document.getElementById('data-fim');

        if (filterPeriodo.value === 'personalizado') {
            return `personalizado&inicio=${dataInicio.value}&fim=${dataFim.value}`;
        }

        return filterPeriodo.value;
    }

    /**
     * Aplica filtro de período
     */
    aplicarFiltroPeriodo() {
        this.carregarDados();
    }

    /**
     * Atualiza estatísticas
     */
    atualizarEstatisticas(estatisticas) {
        document.getElementById('total-faturas').textContent = estatisticas.total_faturas || 0;
        document.getElementById('total-vencidas').textContent = estatisticas.total_vencidas || 0;
        document.getElementById('total-recebidas').textContent = estatisticas.total_recebidas || 0;
        document.getElementById('total-monitorados').textContent = estatisticas.total_monitorados || 0;

        document.getElementById('valor-total').textContent = `R$ ${(estatisticas.valor_total || 0).toFixed(2).replace('.', ',')}`;
        document.getElementById('valor-vencido').textContent = `R$ ${(estatisticas.valor_vencido || 0).toFixed(2).replace('.', ',')}`;
        document.getElementById('valor-recebido').textContent = `R$ ${(estatisticas.valor_recebido || 0).toFixed(2).replace('.', ',')}`;
        document.getElementById('taxa-efetividade').textContent = `${(estatisticas.taxa_efetividade || 0).toFixed(1)}%`;
    }

    /**
     * Atualiza gráficos
     */
    atualizarGraficos(dados) {
        this.renderizarGraficoStatus(dados.status);
        this.renderizarGraficoEvolucao(dados.evolucao);
    }

    /**
     * Renderiza gráfico de status
     */
    renderizarGraficoStatus(dados) {
        const container = document.getElementById('grafico-status');
        if (!container || !dados) return;

        // Gráfico simples em HTML/CSS
        const html = `
            <div class="w-full h-full flex flex-col justify-center">
                <div class="grid grid-cols-2 gap-4">
                    ${dados.map(item => `
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded" style="background-color: ${item.cor}"></div>
                            <div class="text-sm">
                                <div class="font-medium">${item.status}</div>
                                <div class="text-gray-500">${item.quantidade} (${item.percentual}%)</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.innerHTML = html;
    }

    /**
     * Renderiza gráfico de evolução
     */
    renderizarGraficoEvolucao(dados) {
        const container = document.getElementById('grafico-evolucao');
        if (!container || !dados) return;

        // Gráfico de barras simples
        const maxValor = Math.max(...dados.map(item => item.valor));
        const html = `
            <div class="w-full h-full flex items-end justify-between gap-2">
                ${dados.map(item => {
                    const altura = (item.valor / maxValor) * 100;
                    return `
                        <div class="flex flex-col items-center flex-1">
                            <div class="bg-blue-500 rounded-t w-full" style="height: ${altura}%"></div>
                            <div class="text-xs text-gray-500 mt-1">${item.mes}</div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        container.innerHTML = html;
    }

    /**
     * Atualiza top clientes
     */
    atualizarTopClientes(clientes) {
        const tbody = document.getElementById('tabela-top-clientes');
        if (!tbody) return;

        if (clientes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                        Nenhum cliente encontrado
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = clientes.map(cliente => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-3 py-3">
                    <div class="font-medium text-gray-900">${cliente.nome}</div>
                    <div class="text-sm text-gray-500">ID: ${cliente.id}</div>
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm font-medium">${cliente.total_faturas}</div>
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm font-medium">R$ ${parseFloat(cliente.valor_total).toFixed(2).replace('.', ',')}</div>
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm font-medium text-red-600">R$ ${parseFloat(cliente.valor_vencido).toFixed(2).replace('.', ',')}</div>
                </td>
                <td class="px-3 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${
                        cliente.status === 'PENDING' ? 'bg-blue-100 text-blue-800' :
                        cliente.status === 'OVERDUE' ? 'bg-red-100 text-red-800' :
                        cliente.status === 'RECEIVED' ? 'bg-green-100 text-green-800' :
                        'bg-gray-100 text-gray-800'
                    }">
                        ${this.traduzirStatus(cliente.status)}
                    </span>
                </td>
                <td class="px-3 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${
                        cliente.monitorado ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'
                    }">
                        ${cliente.monitorado ? '✅ Monitorado' : '❌ Não Monitorado'}
                    </span>
                </td>
                <td class="px-3 py-3">
                    <div class="flex gap-2">
                        <button onclick="window.relatoriosFinanceiros.verCliente(${cliente.id})" 
                                class="text-blue-600 hover:text-blue-800 text-sm">
                            👁️ Ver
                        </button>
                        <button onclick="window.relatoriosFinanceiros.verFaturas(${cliente.id})" 
                                class="text-green-600 hover:text-green-800 text-sm">
                            📋 Faturas
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    /**
     * Atualiza análise de monitoramento
     */
    atualizarAnaliseMonitoramento(dados) {
        document.getElementById('clientes-monitorados').textContent = dados.clientes_monitorados || 0;
        document.getElementById('mensagens-enviadas').textContent = dados.mensagens_enviadas || 0;
        document.getElementById('taxa-resposta').textContent = `${(dados.taxa_resposta || 0).toFixed(1)}%`;
    }

    /**
     * Ver cliente específico
     */
    verCliente(clienteId) {
        window.open(`clientes.php?id=${clienteId}`, '_blank');
    }

    /**
     * Ver faturas do cliente
     */
    verFaturas(clienteId) {
        window.open(`faturas.php?cliente_id=${clienteId}`, '_blank');
    }

    /**
     * Exporta relatório
     */
    exportarRelatorio() {
        const periodo = this.obterPeriodoSelecionado();
        const url = `api/exportar_relatorio.php?periodo=${periodo}`;
        
        // Criar link temporário para download
        const link = document.createElement('a');
        link.href = url;
        link.download = `relatorio_financeiro_${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.mostrarAlerta('Relatório exportado com sucesso!', 'success');
    }

    /**
     * Traduz status
     */
    traduzirStatus(status) {
        const statusMap = {
            'PENDING': 'Aguardando',
            'OVERDUE': 'Vencida',
            'RECEIVED': 'Recebida',
            'CONFIRMED': 'Confirmada'
        };
        return statusMap[status] || status;
    }

    /**
     * Mostra alerta
     */
    mostrarAlerta(mensagem, tipo = 'info') {
        const alerta = document.createElement('div');
        alerta.className = `alerta alerta-${tipo}`;
        alerta.innerHTML = mensagem;
        alerta.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            word-wrap: break-word;
        `;

        const cores = {
            success: '#10b981',
            error: '#ef4444',
            info: '#3b82f6',
            warning: '#f59e0b'
        };
        alerta.style.background = cores[tipo] || cores.info;

        const icones = {
            success: '✅',
            error: '❌',
            info: 'ℹ️',
            warning: '⚠️'
        };
        alerta.innerHTML = `${icones[tipo] || icones.info} ${mensagem}`;

        document.body.appendChild(alerta);

        setTimeout(() => {
            alerta.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 300);
        }, 4000);
    }
}

// Inicializar quando DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.relatoriosFinanceiros = new RelatoriosFinanceiros();
}); 