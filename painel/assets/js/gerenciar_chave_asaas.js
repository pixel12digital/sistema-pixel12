/**
 * Gerenciador de Chave da API do Asaas
 * Integração com interface web para atualização automática
 */

class GerenciadorChaveAsaas {
    constructor() {
        this.apiUrl = 'api/atualizar_chave_asaas.php';
        this.init();
    }

    init() {
        // Verificar status da chave atual ao carregar
        this.verificarStatusChave();
        
        // Adicionar listeners para botões (se existirem)
        this.adicionarEventListeners();
    }

    async verificarStatusChave() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.atualizarInterfaceStatus(data.data);
            } else {
                console.error('Erro ao verificar status:', data.message);
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
        }
    }

    async testarNovaChave(novaChave) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nova_chave: novaChave,
                    apenas_teste: true
                })
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erro ao testar chave:', error);
            return { success: false, message: 'Erro de conexão' };
        }
    }

    async aplicarNovaChave(novaChave) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nova_chave: novaChave
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.mostrarSucesso('Chave atualizada com sucesso!');
                this.verificarStatusChave(); // Atualizar interface
                return true;
            } else {
                this.mostrarErro(data.message);
                return false;
            }
        } catch (error) {
            console.error('Erro ao aplicar chave:', error);
            this.mostrarErro('Erro de conexão');
            return false;
        }
    }

    atualizarInterfaceStatus(data) {
        // Atualizar elementos da interface com o status atual
        const elementos = {
            'chave-atual': data.chave_atual,
            'tipo-chave': data.tipo_chave,
            'status-chave': data.valida ? 'Válida' : 'Inválida',
            'ultima-verificacao': data.ultima_verificacao
        };

        // Atualizar cada elemento se existir
        Object.keys(elementos).forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = elementos[id];
                
                // Adicionar classes CSS baseadas no status
                if (id === 'status-chave') {
                    elemento.className = data.valida ? 'status-valido' : 'status-invalido';
                }
            }
        });

        // Atualizar indicador visual
        this.atualizarIndicadorVisual(data.valida);
    }

    atualizarIndicadorVisual(valida) {
        const indicador = document.getElementById('indicador-chave');
        if (indicador) {
            indicador.className = valida ? 'indicador-verde' : 'indicador-vermelho';
            indicador.innerHTML = valida ? '✅' : '❌';
        }
    }

    async processarNovaChave() {
        const inputChave = document.getElementById('nova-chave-input');
        if (!inputChave) return;

        const novaChave = inputChave.value.trim();
        
        if (!novaChave) {
            this.mostrarErro('Por favor, insira uma chave válida');
            return;
        }

        // Validar formato da chave
        if (!novaChave.startsWith('$aact_')) {
            this.mostrarErro('A chave deve começar com $aact_');
            return;
        }

        // Mostrar loading
        this.mostrarLoading('Testando nova chave...');

        // Testar a chave primeiro
        const teste = await this.testarNovaChave(novaChave);
        
        if (!teste.success) {
            this.ocultarLoading();
            this.mostrarErro('Chave inválida: ' + teste.message);
            return;
        }

        // Se o teste passou, aplicar a chave
        this.mostrarLoading('Aplicando nova chave...');
        
        const sucesso = await this.aplicarNovaChave(novaChave);
        
        this.ocultarLoading();
        
        if (sucesso) {
            // Limpar campo de entrada
            inputChave.value = '';
            
            // Recarregar página após 2 segundos para aplicar mudanças
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    }

    adicionarEventListeners() {
        // Botão para testar nova chave
        const btnTestar = document.getElementById('btn-testar-chave');
        if (btnTestar) {
            btnTestar.addEventListener('click', () => this.processarNovaChave());
        }

        // Botão para aplicar nova chave
        const btnAplicar = document.getElementById('btn-aplicar-chave');
        if (btnAplicar) {
            btnAplicar.addEventListener('click', () => this.processarNovaChave());
        }

        // Input para nova chave (Enter key)
        const inputChave = document.getElementById('nova-chave-input');
        if (inputChave) {
            inputChave.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.processarNovaChave();
                }
            });
        }
    }

    mostrarSucesso(mensagem) {
        this.mostrarNotificacao(mensagem, 'success');
    }

    mostrarErro(mensagem) {
        this.mostrarNotificacao(mensagem, 'error');
    }

    mostrarLoading(mensagem) {
        this.mostrarNotificacao(mensagem, 'loading');
    }

    ocultarLoading() {
        const loading = document.querySelector('.notificacao.loading');
        if (loading) {
            loading.remove();
        }
    }

    mostrarNotificacao(mensagem, tipo) {
        // Remover notificações anteriores
        const notificacoes = document.querySelectorAll('.notificacao');
        notificacoes.forEach(n => n.remove());

        // Criar nova notificação
        const notificacao = document.createElement('div');
        notificacao.className = `notificacao ${tipo}`;
        notificacao.innerHTML = `
            <div class="notificacao-conteudo">
                <span class="notificacao-mensagem">${mensagem}</span>
                ${tipo === 'loading' ? '<div class="spinner"></div>' : ''}
            </div>
        `;

        // Adicionar ao body
        document.body.appendChild(notificacao);

        // Auto-remover após 5 segundos (exceto loading)
        if (tipo !== 'loading') {
            setTimeout(() => {
                notificacao.remove();
            }, 5000);
        }
    }
}

// CSS para notificações
const style = document.createElement('style');
style.textContent = `
    .notificacao {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .notificacao.success {
        background: #10b981;
    }

    .notificacao.error {
        background: #ef4444;
    }

    .notificacao.loading {
        background: #3b82f6;
    }

    .notificacao-conteudo {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .status-valido {
        color: #10b981;
        font-weight: bold;
    }

    .status-invalido {
        color: #ef4444;
        font-weight: bold;
    }

    .indicador-verde {
        color: #10b981;
        font-size: 1.2em;
    }

    .indicador-vermelho {
        color: #ef4444;
        font-size: 1.2em;
    }
`;
document.head.appendChild(style);

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.gerenciadorChaveAsaas = new GerenciadorChaveAsaas();
});

// Exportar para uso global
window.GerenciadorChaveAsaas = GerenciadorChaveAsaas; 