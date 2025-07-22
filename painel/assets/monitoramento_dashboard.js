/**
 * Dashboard de Monitoramento Inteligente
 * Sistema para controlar e acompanhar monitoramento de clientes
 */

class MonitoramentoDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.carregarDados();
        this.atualizarStatus();
    }

    bindEvents() {
        // Botões principais
        document.getElementById('btn-executar-monitoramento')?.addEventListener('click', () => this.executarMonitoramento());
        document.getElementById('btn-configuracoes')?.addEventListener('click', () => this.abrirConfiguracoes());
        document.getElementById('btn-aplicar-filtros')?.addEventListener('click', () => this.aplicarFiltros());
        document.getElementById('btn-limpar-filtros')?.addEventListener('click', () => this.limparFiltros());
        document.getElementById('btn-exportar')?.addEventListener('click', () => this.exportarDados());
        document.getElementById('btn-ir-faturas')?.addEventListener('click', () => this.irParaFaturas());
        document.getElementById('btn-limpar-filtro-cliente')?.addEventListener('click', () => this.limparFiltroCliente());

        // Modal de configurações
        document.getElementById('btn-fechar-config')?.addEventListener('click', () => this.fecharConfiguracoes());
        document.getElementById('btn-salvar-config')?.addEventListener('click', () => this.salvarConfiguracoes());
        document.getElementById('btn-cancelar-config')?.addEventListener('click', () => this.fecharConfiguracoes());

        // Modal de logs
        document.getElementById('btn-fechar-logs')?.addEventListener('click', () => this.fecharLogs());
        document.getElementById('btn-atualizar-logs')?.addEventListener('click', () => this.atualizarLogs());
        document.getElementById('btn-limpar-logs')?.addEventListener('click', () => this.limparLogs());
        
        // Verificar parâmetros da URL
        this.verificarParametrosURL();
    }

    /**
     * Carrega dados do dashboard
     */
    async carregarDados() {
        try {
            const response = await fetch('api/dashboard_monitoramento.php');
            const data = await response.json();

            if (data.success) {
                this.atualizarEstatisticas(data.estatisticas);
                this.renderizarTabela(data.clientes);
            }
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            this.mostrarAlerta('Erro ao carregar dados do dashboard', 'error');
        }
    }

    /**
     * Atualiza estatísticas do dashboard
     */
    atualizarEstatisticas(estatisticas) {
        document.getElementById('total-monitorados').textContent = estatisticas.total_monitorados || 0;
        document.getElementById('total-vencidas').textContent = estatisticas.total_vencidas || 0;
        document.getElementById('total-mensagens').textContent = estatisticas.total_mensagens || 0;
        
        document.getElementById('proxima-verificacao').textContent = estatisticas.proxima_verificacao || 'N/A';
        document.getElementById('ultima-execucao').textContent = estatisticas.ultima_execucao || 'N/A';
    }

    /**
     * Renderiza tabela de clientes monitorados
     */
    renderizarTabela(clientes) {
        const tbody = document.getElementById('tabela-monitoramento');
        if (!tbody) return;

        if (clientes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-3 py-4 text-center text-gray-500">
                        Nenhum cliente em monitoramento encontrado
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
                    <div class="text-sm">${cliente.celular || 'N/A'}</div>
                    <div class="text-xs text-gray-500">${cliente.contact_name || ''}</div>
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm font-medium">${cliente.quantidade_cobrancas}</div>
                    <div class="text-xs text-gray-500">cobranças</div>
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm font-medium">R$ ${parseFloat(cliente.valor_total).toFixed(2).replace('.', ',')}</div>
                    ${cliente.valor_vencido > 0 ? `
                        <div class="text-xs text-red-600">R$ ${parseFloat(cliente.valor_vencido).toFixed(2).replace('.', ',')} vencido</div>
                    ` : ''}
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm">
                        ${cliente.dias_vencido > 0 ? `
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${
                                cliente.dias_vencido <= 7 ? 'bg-yellow-100 text-yellow-800' :
                                cliente.dias_vencido <= 15 ? 'bg-orange-100 text-orange-800' :
                                'bg-red-100 text-red-800'
                            }">
                                ${cliente.dias_vencido} dias
                            </span>
                        ` : `
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Em dia
                            </span>
                        `}
                    </div>
                </td>
                <td class="px-3 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${
                        cliente.cobrancas_vencidas > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                    }">
                        ${cliente.cobrancas_vencidas > 0 ? `${cliente.cobrancas_vencidas} vencida(s)` : 'Em dia'}
                    </span>
                </td>
                <td class="px-3 py-3">
                    <div class="text-sm text-gray-500">
                        ${cliente.ultima_mensagem || 'Nunca'}
                    </div>
                </td>
                <td class="px-3 py-3">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               data-cliente-id="${cliente.id}" 
                               ${cliente.monitorado ? 'checked' : ''} 
                               ${cliente.cobrancas_vencidas === 0 ? 'disabled' : ''}
                               onchange="window.monitoramentoDashboard.toggleMonitoramento(this)"
                               class="rounded border-gray-300 ${cliente.cobrancas_vencidas === 0 ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="ml-2 text-sm ${cliente.monitorado ? 'text-green-600' : 'text-gray-400'}">
                            ${cliente.monitorado ? 'Ativo' : (cliente.cobrancas_vencidas === 0 ? 'Todas pagas' : 'Inativo')}
                        </span>
                    </div>
                </td>
                <td class="px-3 py-3">
                    <div class="flex gap-2">
                        <button onclick="window.monitoramentoDashboard.verDetalhes(${cliente.id})" 
                                class="text-blue-600 hover:text-blue-800 text-sm">
                            👁️ Ver
                        </button>
                        ${cliente.cobrancas_vencidas > 0 ? `
                            <button onclick="window.monitoramentoDashboard.enviarMensagem(${cliente.id})" 
                                    class="text-green-600 hover:text-green-800 text-sm">
                                💬 Enviar
                            </button>
                        ` : ''}
                        <button onclick="window.monitoramentoDashboard.removerMonitoramento(${cliente.id})" 
                                class="text-red-600 hover:text-red-800 text-sm">
                            🗑️ Remover
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    /**
     * Executa monitoramento manual
     */
    async executarMonitoramento() {
        const btn = document.getElementById('btn-executar-monitoramento');
        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Executando...</span>';

        try {
            const response = await fetch('api/executar_monitoramento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(`Monitoramento executado! ${data.mensagens_enviadas} mensagens enviadas`, 'success');
                this.carregarDados(); // Recarregar dados
            } else {
                this.mostrarAlerta(`Erro: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Erro ao executar monitoramento:', error);
            this.mostrarAlerta('Erro ao executar monitoramento', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>🔄 Executar Monitoramento</span>';
        }
    }

    /**
     * Alterna monitoramento de cliente
     */
    async toggleMonitoramento(checkbox) {
        const clienteId = checkbox.getAttribute('data-cliente-id');
        const isMonitorado = checkbox.checked;

        // Se está tentando ativar monitoramento, verificar se pode
        if (isMonitorado) {
            try {
                const response = await fetch(`api/validar_monitoramento.php?cliente_id=${clienteId}`);
                const data = await response.json();
                
                if (!data.success) {
                    this.mostrarAlerta(data.error || 'Erro ao validar monitoramento', 'error');
                    checkbox.checked = false;
                    return;
                }
                
                if (!data.pode_monitorar) {
                    this.mostrarAlerta(data.motivo, 'warning');
                    checkbox.checked = false;
                    return;
                }
            } catch (error) {
                console.error('Erro ao validar monitoramento:', error);
                this.mostrarAlerta('Erro ao validar monitoramento', 'error');
                checkbox.checked = false;
                return;
            }
        }

        try {
            const response = await fetch('api/salvar_monitoramento_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    monitorado: isMonitorado
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(
                    isMonitorado ? 'Cliente adicionado ao monitoramento' : 'Cliente removido do monitoramento',
                    'success'
                );
                this.carregarDados(); // Recarregar dados
            } else {
                // Reverter checkbox em caso de erro
                checkbox.checked = !isMonitorado;
                this.mostrarAlerta(data.error || 'Erro ao salvar status de monitoramento', 'error');
            }
        } catch (error) {
            console.error('Erro ao alternar monitoramento:', error);
            checkbox.checked = !isMonitorado;
            this.mostrarAlerta('Erro de conexão', 'error');
        }
    }

    /**
     * Aplica filtros na tabela
     */
    aplicarFiltros() {
        const filtros = {
            status: document.getElementById('filter-status').value,
            dias_vencidos: document.getElementById('filter-dias-vencidos').value,
            valor: document.getElementById('filter-valor').value
        };

        // Implementar lógica de filtros
        console.log('Aplicando filtros:', filtros);
        this.carregarDados(); // Recarregar com filtros
    }

    /**
     * Limpa filtros
     */
    limparFiltros() {
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-dias-vencidos').value = '';
        document.getElementById('filter-valor').value = '';
        this.carregarDados();
    }

    /**
     * Exporta dados
     */
    exportarDados() {
        // Implementar exportação para CSV/Excel
        this.mostrarAlerta('Funcionalidade de exportação em desenvolvimento', 'info');
    }

    /**
     * Abre modal de configurações
     */
    abrirConfiguracoes() {
        document.getElementById('modal-configuracoes').style.display = 'flex';
        this.carregarConfiguracoes();
    }

    /**
     * Fecha modal de configurações
     */
    fecharConfiguracoes() {
        document.getElementById('modal-configuracoes').style.display = 'none';
    }

    /**
     * Carrega configurações atuais
     */
    async carregarConfiguracoes() {
        try {
            const response = await fetch('api/configuracoes_monitoramento.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('intervalo-verificacao').value = data.config.intervalo_verificacao || 60;
                document.getElementById('dias-minimos').value = data.config.dias_minimos || 1;
                document.getElementById('limite-mensagens').value = data.config.limite_mensagens || 1;
                document.getElementById('monitorar-apenas-vencidas').checked = data.config.monitorar_apenas_vencidas !== false;
                document.getElementById('verificar-status-asaas').checked = data.config.verificar_status_asaas !== false;
            }
        } catch (error) {
            console.error('Erro ao carregar configurações:', error);
        }
    }

    /**
     * Salva configurações
     */
    async salvarConfiguracoes() {
        const config = {
            intervalo_verificacao: parseInt(document.getElementById('intervalo-verificacao').value),
            dias_minimos: parseInt(document.getElementById('dias-minimos').value),
            limite_mensagens: parseInt(document.getElementById('limite-mensagens').value),
            monitorar_apenas_vencidas: document.getElementById('monitorar-apenas-vencidas').checked,
            verificar_status_asaas: document.getElementById('verificar-status-asaas').checked
        };

        try {
            const response = await fetch('api/salvar_configuracoes_monitoramento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(config)
            });

            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta('Configurações salvas com sucesso!', 'success');
                this.fecharConfiguracoes();
            } else {
                this.mostrarAlerta('Erro ao salvar configurações', 'error');
            }
        } catch (error) {
            console.error('Erro ao salvar configurações:', error);
            this.mostrarAlerta('Erro de conexão', 'error');
        }
    }

    /**
     * Atualiza status do sistema
     */
    async atualizarStatus() {
        try {
            const response = await fetch('api/status_monitoramento.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('proxima-verificacao').textContent = data.proxima_verificacao;
                document.getElementById('ultima-execucao').textContent = data.ultima_execucao;
            }
        } catch (error) {
            console.error('Erro ao atualizar status:', error);
        }
    }

    /**
     * Ver detalhes do cliente
     */
    verDetalhes(clienteId) {
        // Implementar modal de detalhes
        this.mostrarAlerta('Funcionalidade de detalhes em desenvolvimento', 'info');
    }

    /**
     * Enviar mensagem para cliente
     */
    async enviarMensagem(clienteId) {
        try {
            const response = await fetch('api/enviar_mensagem_monitoramento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cliente_id: clienteId })
            });

            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta('Mensagem enviada com sucesso!', 'success');
            } else {
                this.mostrarAlerta(`Erro: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
            this.mostrarAlerta('Erro de conexão', 'error');
        }
    }

    /**
     * Remove monitoramento de cliente
     */
    async removerMonitoramento(clienteId) {
        if (!confirm('Tem certeza que deseja remover este cliente do monitoramento?')) {
            return;
        }

        try {
            const response = await fetch('api/remover_monitoramento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cliente_id: clienteId })
            });

            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta('Cliente removido do monitoramento', 'success');
                this.carregarDados();
            } else {
                this.mostrarAlerta(`Erro: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Erro ao remover monitoramento:', error);
            this.mostrarAlerta('Erro de conexão', 'error');
        }
    }

    /**
     * Traduz status da cobrança
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

    /**
     * Verifica parâmetros da URL
     */
    verificarParametrosURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const clienteId = urlParams.get('cliente_id');
        
        if (clienteId) {
            this.focarClienteEspecifico(clienteId);
        }
    }

    /**
     * Foca em um cliente específico
     */
    async focarClienteEspecifico(clienteId) {
        try {
            const response = await fetch(`api/dados_cliente.php?id=${clienteId}`);
            const data = await response.json();
            
            if (data.success && data.cliente) {
                this.mostrarFiltroCliente(data.cliente);
                this.aplicarFiltroCliente(clienteId);
            }
        } catch (error) {
            console.error('Erro ao buscar dados do cliente:', error);
        }
    }

    /**
     * Mostra filtro de cliente específico
     */
    mostrarFiltroCliente(cliente) {
        const filtroElement = document.getElementById('filtro-cliente-especifico');
        const nomeElement = document.getElementById('nome-cliente-filtro');
        
        if (filtroElement && nomeElement) {
            nomeElement.textContent = cliente.nome;
            filtroElement.style.display = 'block';
        }
    }

    /**
     * Aplica filtro de cliente específico
     */
    aplicarFiltroCliente(clienteId) {
        // Marcar o cliente na tabela
        setTimeout(() => {
            const linhas = document.querySelectorAll('#tabela-monitoramento tr');
            linhas.forEach(linha => {
                const checkbox = linha.querySelector(`input[data-cliente-id="${clienteId}"]`);
                if (checkbox) {
                    linha.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    linha.style.backgroundColor = '#fef3c7';
                    linha.style.border = '2px solid #f59e0b';
                    
                    // Remover destaque após 3 segundos
                    setTimeout(() => {
                        linha.style.backgroundColor = '';
                        linha.style.border = '';
                    }, 3000);
                }
            });
        }, 1000);
    }

    /**
     * Limpa filtro de cliente específico
     */
    limparFiltroCliente() {
        const filtroElement = document.getElementById('filtro-cliente-especifico');
        if (filtroElement) {
            filtroElement.style.display = 'none';
        }
        
        // Remover parâmetro da URL
        const url = new URL(window.location);
        url.searchParams.delete('cliente_id');
        window.history.replaceState({}, '', url);
        
        // Recarregar dados
        this.carregarDados();
    }

    /**
     * Vai para a página de faturas
     */
    irParaFaturas() {
        window.location.href = 'faturas.php';
    }
}

// Inicializar quando DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.monitoramentoDashboard = new MonitoramentoDashboard();
}); 