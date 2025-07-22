/**
 * Integração de Monitoramento na Página de Faturas
 * Adiciona indicadores visuais e funcionalidades de monitoramento
 */

class FaturasMonitoramentoIntegracao {
    constructor() {
        this.dadosMonitoramento = {};
        this.init();
    }

    init() {
        this.carregarDadosMonitoramento();
        this.atualizarEstatisticasMonitoramento();
        
        // Atualizar a cada 30 segundos
        setInterval(() => {
            this.carregarDadosMonitoramento();
        }, 30000);
    }

    /**
     * Carrega dados de monitoramento
     */
    async carregarDadosMonitoramento() {
        try {
            const response = await fetch('api/buscar_dados_monitoramento_faturas.php');
            const data = await response.json();

            if (data.success) {
                this.dadosMonitoramento = data.dados_monitoramento;
                this.atualizarEstatisticasMonitoramento();
                this.atualizarIndicadoresTabela();
            }
        } catch (error) {
            console.error('Erro ao carregar dados de monitoramento:', error);
        }
    }

    /**
     * Atualiza estatísticas de monitoramento no card
     */
    atualizarEstatisticasMonitoramento() {
        const statsElement = document.getElementById('monitoring-stats');
        const detailsElement = document.getElementById('monitoring-details');
        
        if (!statsElement || !detailsElement) return;

        const totalMonitorados = Object.keys(this.dadosMonitoramento).length;
        const valorTotalVencido = Object.values(this.dadosMonitoramento)
            .reduce((total, cliente) => total + cliente.valor_vencido, 0);

        statsElement.textContent = `${totalMonitorados} clientes`;
        detailsElement.textContent = `R$ ${valorTotalVencido.toFixed(2).replace('.', ',')} vencidos`;

        // Adicionar link para página de monitoramento
        const cardElement = statsElement.closest('.summary-monitoring');
        if (cardElement && !cardElement.hasAttribute('data-click-handler')) {
            cardElement.style.cursor = 'pointer';
            cardElement.setAttribute('data-click-handler', 'true');
            cardElement.addEventListener('click', () => {
                window.location.href = 'monitoramento.php';
            });
            
            // Adicionar tooltip
            cardElement.title = 'Clique para ir para a página de Monitoramento';
        }
    }

    /**
     * Atualiza indicadores na tabela de faturas
     */
    atualizarIndicadoresTabela() {
        // Aguardar a tabela ser carregada
        setTimeout(() => {
            const tbody = document.getElementById('invoices-tbody');
            if (!tbody) return;

            const linhas = tbody.querySelectorAll('tr');
            linhas.forEach(linha => {
                const clienteId = this.extrairClienteId(linha);
                if (clienteId && this.dadosMonitoramento[clienteId]) {
                    this.adicionarIndicadorMonitoramento(linha, clienteId);
                }
            });
        }, 1000);
    }

    /**
     * Extrai ID do cliente da linha da tabela
     */
    extrairClienteId(linha) {
        // Tentar diferentes métodos para encontrar o ID do cliente
        const celulaCliente = linha.querySelector('td:nth-child(2)');
        if (!celulaCliente) return null;

        // Verificar se há um data attribute
        const dataClienteId = linha.getAttribute('data-cliente-id');
        if (dataClienteId) return dataClienteId;

        // Tentar extrair do texto ou estrutura da célula
        const linkCliente = celulaCliente.querySelector('a');
        if (linkCliente) {
            const href = linkCliente.getAttribute('href');
            const match = href.match(/cliente_id=(\d+)/);
            if (match) return match[1];
        }

        return null;
    }

    /**
     * Adiciona indicador de monitoramento na linha
     */
    adicionarIndicadorMonitoramento(linha, clienteId) {
        const dadosCliente = this.dadosMonitoramento[clienteId];
        if (!dadosCliente) return;

        // Verificar se já tem indicador
        if (linha.querySelector('.indicador-monitoramento')) return;

        // Adicionar indicador na coluna de monitoramento
        const colunaMonitoramento = linha.querySelector('td:nth-child(9)');
        if (!colunaMonitoramento) return;

        const indicador = this.criarIndicadorMonitoramento(dadosCliente, clienteId);
        colunaMonitoramento.innerHTML = indicador;
    }

    /**
     * Cria o HTML do indicador de monitoramento
     */
    criarIndicadorMonitoramento(dadosCliente, clienteId) {
        const { valor_vencido, cobrancas_vencidas, ultima_mensagem } = dadosCliente;
        
        let statusClass = 'bg-green-100 text-green-800';
        let statusIcon = '✅';
        let statusText = 'Monitorado';
        let podeMonitorar = true;
        
        if (cobrancas_vencidas > 0) {
            statusClass = 'bg-red-100 text-red-800';
            statusIcon = '⚠️';
            statusText = `${cobrancas_vencidas} vencida(s)`;
        } else {
            // Verificar se todas as cobranças foram pagas
            statusClass = 'bg-gray-100 text-gray-800';
            statusIcon = '✅';
            statusText = 'Todas pagas';
            podeMonitorar = false;
        }

        return `
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                        ${statusIcon} ${statusText}
                    </span>
                </div>
                ${valor_vencido > 0 ? `
                    <div class="text-xs text-red-600 font-medium">
                        R$ ${valor_vencido.toFixed(2).replace('.', ',')} vencido
                    </div>
                ` : ''}
                ${ultima_mensagem ? `
                    <div class="text-xs text-gray-500">
                        Última: ${ultima_mensagem}
                    </div>
                ` : ''}
                <div class="flex gap-1 mt-1">
                    <button onclick="window.faturasMonitoramentoIntegracao.verNoMonitoramento(${clienteId})" 
                            class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded">
                        📊 Ver
                    </button>
                    ${podeMonitorar ? `
                        <button onclick="window.faturasMonitoramentoIntegracao.toggleMonitoramento(${clienteId})" 
                                class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded">
                            🔄 Toggle
                        </button>
                    ` : `
                        <button disabled 
                                class="text-xs bg-gray-300 text-gray-500 px-2 py-1 rounded cursor-not-allowed" 
                                title="Todas as cobranças foram pagas">
                            ✅ Pagas
                        </button>
                    `}
                </div>
            </div>
        `;
    }

    /**
     * Vai para a página de monitoramento focando no cliente
     */
    verNoMonitoramento(clienteId) {
        window.location.href = `monitoramento.php?cliente_id=${clienteId}`;
    }

    /**
     * Alterna monitoramento do cliente
     */
    async toggleMonitoramento(clienteId) {
        const dadosCliente = this.dadosMonitoramento[clienteId];
        if (!dadosCliente) return;

        const isMonitorado = dadosCliente.monitorado;
        
        try {
            const response = await fetch('api/salvar_monitoramento_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    monitorado: !isMonitorado
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Atualizar dados locais
                this.dadosMonitoramento[clienteId].monitorado = !isMonitorado;
                
                // Recarregar dados
                this.carregarDadosMonitoramento();
                
                this.mostrarAlerta(
                    !isMonitorado ? 'Cliente adicionado ao monitoramento' : 'Cliente removido do monitoramento',
                    'success'
                );
            } else {
                this.mostrarAlerta('Erro ao alterar monitoramento', 'error');
            }
        } catch (error) {
            console.error('Erro ao alternar monitoramento:', error);
            this.mostrarAlerta('Erro de conexão', 'error');
        }
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
    window.faturasMonitoramentoIntegracao = new FaturasMonitoramentoIntegracao();
}); 