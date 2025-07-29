/**
 * Sistema de Monitoramento Automático de Clientes - Versão 3.0
 * Pixel12 Digital - Financeiro
 * Sistema Inteligente com Validações
 */

class ClienteMonitoramento {
    constructor() {
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
        // Remover event listener e função enviarMensagemValidacao relacionadas ao botão 'Validar'
    }

    /**
     * Monta mensagem de validação personalizada
     */
    montarMensagemValidacao(clienteNome, clienteContactName) {
        const nome = clienteContactName || clienteNome || 'cliente';
        
        let mensagem = `Olá ${nome}! Este é nosso contato financeiro da Pixel12 Digital. `;
        mensagem += `Aqui você pode solicitar faturas, tirar dúvidas sobre pagamentos ou solicitar documentos. `;
        mensagem += `Se precisar de algo relacionado a projetos, comercial ou suporte, entre em contato através do (47) 99730-9525. `;
        mensagem += `\n\nEste canal é automatizado e está disponível 24/7 para suas consultas financeiras. 😊`;
        
        return mensagem;
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

        // Buscar nome do contato principal
        let clienteContactName = '';
        try {
            const response = await fetch(`api/dados_cliente.php?id=${clienteId}`);
            const data = await response.json();
            if (data.success && data.cliente) {
                clienteContactName = data.cliente.contact_name || '';
            }
        } catch (error) {
            console.error('Erro ao buscar dados do cliente:', error);
        }

        // Montar mensagem personalizada
        const mensagem = this.montarMensagemValidacao(clienteNome, clienteContactName);

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
                    mensagem: mensagem
                })
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarAlerta(`Mensagem enviada para ${clienteContactName || clienteNome}`, 'success');
                // NÃO marca automaticamente - usuário decide quando marcar
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
     * Alterna o status de monitoramento do cliente com validação inteligente
     */
    async toggleMonitoramento(checkbox) {
        const clienteId = checkbox.getAttribute('data-cliente-id');
        const isMonitorado = checkbox.checked;

        // Feedback visual imediato
        const label = checkbox.closest('label');
        const originalBackground = label.style.background;
        const originalColor = label.style.color;
        
        // Mudar cor do label
        label.style.background = isMonitorado ? '#bbf7d0' : '#fee2e2';
        label.style.color = isMonitorado ? '#166534' : '#b91c1c';
        label.style.transition = 'all 0.3s ease';

        try {
            // Se está marcando para monitorar, validar se há cobranças vencidas
            if (isMonitorado) {
                const podeMonitorar = await this.validarMonitoramento(clienteId);
                if (!podeMonitorar.pode) {
                    // Reverter checkbox
                    checkbox.checked = false;
                    this.mostrarAlerta(podeMonitorar.motivo, 'warning');
                    
                    // Restaurar cor original
                    label.style.background = originalBackground;
                    label.style.color = originalColor;
                    return;
                }
            }

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
        } finally {
            // Restaurar cor original após 2 segundos
            setTimeout(() => {
                label.style.background = originalBackground;
                label.style.color = originalColor;
            }, 2000);
        }
    }

    /**
     * Valida se o cliente pode ser monitorado
     */
    async validarMonitoramento(clienteId) {
        try {
            const response = await fetch(`api/validar_monitoramento.php?cliente_id=${clienteId}`);
            const data = await response.json();
            
            if (data.success) {
                return {
                    pode: data.pode_monitorar,
                    motivo: data.motivo || 'Cliente não possui cobranças vencidas'
                };
            } else {
                return {
                    pode: false,
                    motivo: data.error || 'Erro ao validar monitoramento'
                };
            }
        } catch (error) {
            console.error('Erro ao validar monitoramento:', error);
            return {
                pode: false,
                motivo: 'Erro de conexão ao validar'
            };
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

        // Se há avisos, mostrar como alertas informativos
        if (data.avisos && data.avisos.length > 0) {
            console.log('Avisos do monitoramento:', data.avisos);
            // Mostrar avisos como alertas informativos (não como erros)
            data.avisos.forEach(aviso => {
                this.mostrarAlerta(aviso, 'warning');
            });
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
                        checkbox.checked = cliente.monitorado === '1' || cliente.monitorado === 1;
                        
                        // Aplicar estilo visual
                        const label = checkbox.closest('label');
                        if (label) {
                            label.style.background = '#bbf7d0';
                            label.style.color = '#166534';
                        }
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
                // Se ainda há cobranças vencidas após verificação
                if (statusData.total_vencidas > 0) {
                    await this.agendarMensagemCobrancaVencida(cobranca, statusData.cobrancas_vencidas);
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
                // Mensagem agendada com sucesso
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
                // Mensagem enviada com sucesso
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

        // Cores por tipo
        const cores = {
            success: '#10b981',
            error: '#ef4444',
            info: '#3b82f6',
            warning: '#f59e0b'
        };
        alerta.style.background = cores[tipo] || cores.info;

        // Adicionar ícone
        const icones = {
            success: '✅',
            error: '❌',
            info: 'ℹ️',
            warning: '⚠️'
        };
        alerta.innerHTML = `${icones[tipo] || icones.info} ${mensagem}`;

        document.body.appendChild(alerta);

        // Remover após 4 segundos
        setTimeout(() => {
            alerta.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 300);
        }, 4000);

        // Adicionar CSS para animações
        if (!document.getElementById('alerta-animations')) {
            const style = document.createElement('style');
            style.id = 'alerta-animations';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
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
    }
}

function getChaveAtualCompleta() {
    return $('#chave-atual-display').attr('data-chave') || '';
}

// Inicializar sistema quando DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.clienteMonitoramento = new ClienteMonitoramento();
    
    // Aguardar um pouco mais para garantir que a tabela esteja carregada
    setTimeout(() => {
        window.clienteMonitoramento.carregarClientesMonitorados();
        window.clienteMonitoramento.iniciarMonitoramentoAutomatico();
    }, 2000); // Aumentado para 2 segundos
}); 

// Sincronizar chave com banco
$(document).on('click', '#btn-sincronizar-chave', function() {
    var chaveAtual = getChaveAtualCompleta();
    var $status = $('#status-sincronizar-chave');
    $status.text('Sincronizando...');
    $.post('api/sincronizar_asaas_key.php', { chave: chaveAtual }, function(resp) {
        if (resp && resp.success) {
            $status.text(resp.mensagem).css('color', resp.status === 'atualizada' ? 'green' : 'blue');
        } else {
            $status.text(resp && resp.error ? resp.error : 'Erro ao sincronizar').css('color', 'red');
        }
    }, 'json').fail(function() {
        $status.text('Erro de comunicação com o servidor').css('color', 'red');
    });
}); 

// Forçar sincronização da chave com banco
$(document).on('click', '#btn-forcar-chave', function() {
    var chaveAtual = getChaveAtualCompleta();
    var $status = $('#status-sincronizar-chave');
    $status.text('Forçando sincronização...');
    $.post('api/sincronizar_asaas_key.php', { chave: chaveAtual, forcar: 1 }, function(resp) {
        if (resp && resp.success) {
            $status.text('Chave forçada no banco com sucesso!').css('color', 'green');
        } else {
            $status.text(resp && resp.error ? resp.error : 'Erro ao forçar sincronização').css('color', 'red');
        }
    }, 'json').fail(function() {
        $status.text('Erro de comunicação com o servidor').css('color', 'red');
    });
}); 

$(document).on('click', '#btn-testar-chave-atual', function() {
    var chaveAtual = getChaveAtualCompleta();
    var $status = $('#status-sincronizar-chave');
    $status.text('Testando e sincronizando...');
    // Primeiro, testar a chave (pode ser via endpoint de teste, se existir)
    $.post('api/test_asaas_key.php', { chave: chaveAtual }, function(resp) {
        if (resp && resp.success) {
            // Se válida, sincronizar no banco
            $.post('api/sincronizar_asaas_key.php', { chave: chaveAtual, forcar: 1 }, function(resp2) {
                if (resp2 && resp2.success) {
                    $status.text('Chave testada e salva no banco com sucesso!').css('color', 'green');
                } else {
                    $status.text(resp2 && resp2.error ? resp2.error : 'Erro ao salvar no banco').css('color', 'red');
                }
            }, 'json').fail(function() {
                $status.text('Erro ao salvar chave no banco').css('color', 'red');
            });
        } else {
            $status.text(resp && resp.error ? resp.error : 'Chave inválida').css('color', 'red');
        }
    }, 'json').fail(function() {
        $status.text('Erro ao testar chave').css('color', 'red');
    });
}); 