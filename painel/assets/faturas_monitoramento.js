/**
 * Sistema de Monitoramento Automático de Clientes - Versão 2.0
 * Pixel12 Digital - Financeiro
 */

class ClienteMonitoramento {
    constructor() {
        this.mensagemValidacao = "Olá! Este é nosso contato financeiro da Pixel12 Digital. Se precisar consultar faturas, tirar dúvidas sobre pagamentos ou solicitar documentos, estamos à disposição. 😊";
        this.init();
    }

    init() {
        this.bindEvents();
        this.carregarClientesMonitorados();
    }

    bindEvents() {
        // Event listener para checkbox de monitoramento
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('checkbox-monitoramento')) {
                this.toggleMonitoramento(e.target);
            }
        });

        // Event listener para botão de validação
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-validar-cliente')) {
                this.enviarMensagemValidacao(e.target);
            }
        });
    }

    /**
     * Envia mensagem de validação para o cliente
     */
    async enviarMensagemValidacao(btn) {
        const clienteId = btn.getAttribute('data-cliente-id');
        const clienteNome = btn.getAttribute('data-cliente-nome');
        const clienteCelular = btn.getAttribute('data-cliente-celular');

        if (!clienteCelular) {
            this.mostrarAlerta('Cliente sem número de celular cadastrado', 'error');
            return;
        }

        // Desabilitar botão
        btn.disabled = true;
        btn.innerHTML = 'Enviando...';

        try {
            const response = await fetch('api/enviar_mensagem_validacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    cliente_nome: clienteNome,
                    cliente_celular: clienteCelular,
                    mensagem: this.mensagemValidacao
                })
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarAlerta(`Mensagem enviada para ${clienteNome}`, 'success');
                // Marcar checkbox como monitorado
                const checkbox = document.querySelector(`input[data-cliente-id="${clienteId}"].checkbox-monitoramento`);
                if (checkbox) {
                    checkbox.checked = true;
                    this.salvarStatusMonitoramento(clienteId, true);
                }
            } else {
                this.mostrarAlerta(`Erro ao enviar mensagem: ${data.error}`, 'error');
            }
        } catch (error) {
            this.mostrarAlerta('Erro de conexão', 'error');
        } finally {
            // Reabilitar botão
            btn.disabled = false;
            btn.innerHTML = 'Validar';
        }
    }

    /**
     * Alterna o status de monitoramento do cliente
     */
    async toggleMonitoramento(checkbox) {
        const clienteId = checkbox.getAttribute('data-cliente-id');
        const isMonitorado = checkbox.checked;

        try {
            await this.salvarStatusMonitoramento(clienteId, isMonitorado);
            
            if (isMonitorado) {
                this.mostrarAlerta('Cliente adicionado ao monitoramento automático', 'success');
            } else {
                this.mostrarAlerta('Cliente removido do monitoramento automático', 'info');
            }
        } catch (error) {
            // Reverter checkbox em caso de erro
            checkbox.checked = !isMonitorado;
            this.mostrarAlerta('Erro ao salvar status de monitoramento', 'error');
        }
    }

    /**
     * Salva o status de monitoramento no banco
     */
    async salvarStatusMonitoramento(clienteId, isMonitorado) {
        const response = await fetch('api/salvar_monitoramento_cliente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cliente_id: clienteId,
                monitorado: isMonitorado
            })
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error);
        }

        return data;
    }

    /**
     * Carrega clientes já monitorados
     */
    async carregarClientesMonitorados() {
        try {
            const response = await fetch('api/listar_clientes_monitorados.php');
            const data = await response.json();

            if (data.success) {
                data.clientes.forEach(cliente => {
                    const checkbox = document.querySelector(`input[data-cliente-id="${cliente.id}"].checkbox-monitoramento`);
                    if (checkbox) {
                        checkbox.checked = cliente.monitorado === '1';
                    }
                });
            }
        } catch (error) {
            console.error('Erro ao carregar clientes monitorados:', error);
        }
    }

    /**
     * Verifica cobranças vencidas com status real do Asaas
     */
    async verificarCobrancasVencidas() {
        try {
            // Primeiro, buscar clientes monitorados com cobranças vencidas
            const response = await fetch('api/verificar_cobrancas_vencidas.php');
            const data = await response.json();

            if (data.success && data.cobrancas.length > 0) {
                console.log(`Encontradas ${data.cobrancas.length} cobranças vencidas para clientes monitorados`);
                
                for (const cobranca of data.cobrancas) {
                    // Verificar status real no Asaas antes de agendar mensagem
                    await this.verificarEAgendarMensagem(cobranca);
                    // Aguardar 1 segundo entre verificações
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
            }
        } catch (error) {
            console.error('Erro ao verificar cobranças vencidas:', error);
        }
    }

    /**
     * Verifica status real no Asaas e agenda mensagem se necessário
     */
    async verificarEAgendarMensagem(cobranca) {
        try {
            // Verificar status real no Asaas
            const statusResponse = await fetch('api/verificar_status_asaas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cliente_id: cobranca.cliente_id
                })
            });

            const statusData = await statusResponse.json();

            if (statusData.success) {
                // Se houve atualizações de status
                if (statusData.total_atualizadas > 0) {
                    console.log(`Status atualizado para cliente ${cobranca.cliente_nome}: ${statusData.total_atualizadas} cobranças`);
                }

                // Se ainda há cobranças vencidas após verificação
                if (statusData.total_vencidas > 0) {
                    await this.agendarMensagemCobrancaVencida(cobranca, statusData.cobrancas_vencidas);
                } else {
                    console.log(`Cliente ${cobranca.cliente_nome} não possui mais cobranças vencidas`);
                }
            }
        } catch (error) {
            console.error(`Erro ao verificar status Asaas para ${cobranca.cliente_nome}:`, error);
        }
    }

    /**
     * Agenda mensagem de cobrança vencida
     */
    async agendarMensagemCobrancaVencida(cobranca, faturasVencidas) {
        // Montar mensagem com todas as faturas vencidas
        const mensagem = this.montarMensagemCobrancaVencida(cobranca, faturasVencidas);

        try {
            const response = await fetch('api/agendar_envio_mensagens.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cliente_id: cobranca.cliente_id,
                    mensagem: mensagem,
                    tipo: 'cobranca_vencida',
                    prioridade: this.calcularPrioridade(faturasVencidas)
                })
            });

            const data = await response.json();
            if (data.success) {
                console.log(`Mensagem agendada para ${cobranca.cliente_nome} - Horário: ${data.horario_envio}`);
            } else {
                console.error(`Erro ao agendar mensagem para ${cobranca.cliente_nome}:`, data.error);
            }
        } catch (error) {
            console.error(`Erro ao agendar mensagem para ${cobranca.cliente_nome}:`, error);
        }
    }

    /**
     * Monta mensagem de cobrança vencida com todas as faturas
     */
    montarMensagemCobrancaVencida(cobranca, faturasVencidas) {
        let mensagem = `Olá ${cobranca.cliente_nome}! \n\n`;
        mensagem += `⚠️ Você possui faturas em aberto:\n\n`;
        
        let valorTotal = 0;
        faturasVencidas.forEach(fatura => {
            const valor = parseFloat(fatura.valor);
            valorTotal += valor;
            const valorFormatado = valor.toFixed(2).replace('.', ',');
            const vencimentoFormatado = new Date(fatura.vencimento).toLocaleDateString('pt-BR');
            
            mensagem += `• Fatura #${fatura.id} - R$ ${valorFormatado} - Venceu em ${vencimentoFormatado}\n`;
        });
        
        mensagem += `\n💰 Valor total em aberto: R$ ${valorTotal.toFixed(2).replace('.', ',')}\n`;
        mensagem += `🔗 Link para pagamento: ${faturasVencidas[0].url_fatura}\n\n`;
        mensagem += `Para consultar todas as suas faturas, responda "faturas" ou "consulta".\n\n`;
        mensagem += `Atenciosamente,\nEquipe Financeira Pixel12 Digital`;

        return mensagem;
    }

    /**
     * Calcula prioridade baseada nas faturas vencidas
     */
    calcularPrioridade(faturasVencidas) {
        const diasVencimento = faturasVencidas.map(fatura => {
            const vencimento = new Date(fatura.vencimento);
            const hoje = new Date();
            return Math.floor((hoje - vencimento) / (1000 * 60 * 60 * 24));
        });

        const maxDiasVencido = Math.max(...diasVencimento);
        const valorTotal = faturasVencidas.reduce((total, fatura) => total + parseFloat(fatura.valor), 0);

        // Prioridade alta: mais de 30 dias vencido ou valor alto
        if (maxDiasVencido > 30 || valorTotal > 1000) {
            return 'alta';
        }
        // Prioridade baixa: menos de 7 dias vencido e valor baixo
        else if (maxDiasVencido < 7 && valorTotal < 100) {
            return 'baixa';
        }
        // Prioridade normal: demais casos
        else {
            return 'normal';
        }
    }

    /**
     * Processa resposta do cliente
     */
    async processarRespostaCliente(clienteId, mensagem) {
        const mensagemLower = mensagem.toLowerCase();

        if (mensagemLower.includes('fatura') || mensagemLower.includes('consulta') || mensagemLower.includes('faturas')) {
            await this.enviarFaturasCliente(clienteId);
        } else if (mensagemLower.includes('pagar') || mensagemLower.includes('pagamento')) {
            await this.enviarLinksPagamento(clienteId);
        } else {
            await this.enviarMensagemPadrao(clienteId);
        }
    }

    /**
     * Envia faturas do cliente
     */
    async enviarFaturasCliente(clienteId) {
        try {
            const response = await fetch(`api/buscar_faturas_cliente.php?cliente_id=${clienteId}`);
            const data = await response.json();

            if (data.success && data.faturas.length > 0) {
                let mensagem = `📋 Suas faturas:\n\n`;
                
                data.faturas.forEach(fatura => {
                    const status = this.traduzirStatus(fatura.status);
                    const valor = parseFloat(fatura.valor).toFixed(2).replace('.', ',');
                    mensagem += `Fatura #${fatura.id}\n`;
                    mensagem += `Valor: R$ ${valor}\n`;
                    mensagem += `Vencimento: ${fatura.vencimento_formatado}\n`;
                    mensagem += `Status: ${status}\n`;
                    if (fatura.url_fatura) {
                        mensagem += `Link: ${fatura.url_fatura}\n`;
                    }
                    mensagem += `\n`;
                });

                await this.enviarMensagemAutomatica(clienteId, mensagem);
            } else {
                await this.enviarMensagemAutomatica(clienteId, 'Você não possui faturas cadastradas no momento.');
            }
        } catch (error) {
            console.error('Erro ao buscar faturas do cliente:', error);
        }
    }

    /**
     * Envia links de pagamento
     */
    async enviarLinksPagamento(clienteId) {
        try {
            const response = await fetch(`api/buscar_faturas_pendentes.php?cliente_id=${clienteId}`);
            const data = await response.json();

            if (data.success && data.faturas.length > 0) {
                let mensagem = `💳 Links para pagamento:\n\n`;
                
                data.faturas.forEach(fatura => {
                    const valor = parseFloat(fatura.valor).toFixed(2).replace('.', ',');
                    mensagem += `Fatura #${fatura.id} - R$ ${valor}\n`;
                    mensagem += `${fatura.url_fatura}\n\n`;
                });

                await this.enviarMensagemAutomatica(clienteId, mensagem);
            } else {
                await this.enviarMensagemAutomatica(clienteId, 'Você não possui faturas pendentes no momento.');
            }
        } catch (error) {
            console.error('Erro ao buscar links de pagamento:', error);
        }
    }

    /**
     * Envia mensagem padrão
     */
    async enviarMensagemPadrao(clienteId) {
        const mensagem = `Olá! Como posso ajudá-lo?

Para consultar suas faturas, digite "faturas" ou "consulta"
Para links de pagamento, digite "pagar" ou "pagamento"
Para abrir um ticket de atendimento, digite "atendente"

Atenciosamente,
Equipe Financeira Pixel12 Digital`;

        await this.enviarMensagemAutomatica(clienteId, mensagem);
    }

    /**
     * Envia mensagem automática
     */
    async enviarMensagemAutomatica(clienteId, mensagem) {
        try {
            const response = await fetch('api/enviar_mensagem_automatica.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    mensagem: mensagem,
                    tipo: 'resposta_automatica'
                })
            });

            const data = await response.json();
            if (data.success) {
                console.log(`Mensagem automática enviada para cliente ${clienteId}`);
            }
        } catch (error) {
            console.error('Erro ao enviar mensagem automática:', error);
        }
    }

    /**
     * Traduz status da fatura
     */
    traduzirStatus(status) {
        const statusMap = {
            'PENDING': 'Aguardando pagamento',
            'OVERDUE': 'Vencida',
            'RECEIVED': 'Paga',
            'CONFIRMED': 'Confirmada',
            'CANCELLED': 'Cancelada'
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
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;

        // Cores por tipo
        const cores = {
            success: '#10b981',
            error: '#ef4444',
            info: '#3b82f6',
            warning: '#f59e0b'
        };
        alerta.style.background = cores[tipo] || cores.info;

        document.body.appendChild(alerta);

        // Remover após 5 segundos
        setTimeout(() => {
            alerta.remove();
        }, 5000);
    }

    /**
     * Inicia monitoramento automático
     */
    iniciarMonitoramentoAutomatico() {
        // Verificar cobranças vencidas a cada 2 horas (reduzido para ser mais eficiente)
        setInterval(() => {
            this.verificarCobrancasVencidas();
        }, 2 * 60 * 60 * 1000);

        // Primeira verificação após 5 minutos
        setTimeout(() => {
            this.verificarCobrancasVencidas();
        }, 5 * 60 * 1000);

        console.log('Monitoramento automático iniciado - Versão 2.0');
    }
}

// Inicializar sistema quando DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.clienteMonitoramento = new ClienteMonitoramento();
    
    // Iniciar monitoramento automático se houver clientes monitorados
    setTimeout(() => {
        window.clienteMonitoramento.iniciarMonitoramentoAutomatico();
    }, 5000);
}); 