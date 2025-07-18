/**
 * Monitoramento Simples da Chave da API do Asaas
 * Versão simplificada para debug
 */

class MonitoramentoSimplesAsaas {
    constructor() {
        this.inicializar();
    }
    
    inicializar() {
        console.log('🔍 Monitoramento simples inicializado');
        this.verificarStatusInicial();
    }
    
    async verificarStatusInicial() {
        console.log('Verificando status inicial...');
        
        try {
            const response = await fetch('verificador_automatico_chave_otimizado.php?action=status');
            console.log('Resposta recebida:', response);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Dados recebidos:', data);
            
            this.atualizarInterface(data);
            
        } catch (error) {
            console.error('Erro na verificação inicial:', error);
            this.mostrarErro(error.message);
        }
    }
    
    atualizarInterface(status) {
        console.log('Atualizando interface com:', status);
        
        const container = document.getElementById('status-chave-asaas-container');
        if (!container) {
            console.error('Container não encontrado');
            return;
        }
        
        if (!status) {
            container.innerHTML = '<div class="status-chave-asaas status-invalido">❌ Erro ao carregar status</div>';
            return;
        }
        
        const icone = status.valida ? '✅' : '❌';
        const classe = status.valida ? 'status-valido' : 'status-invalido';
        const texto = status.valida ? 'Chave Válida' : 'Chave Inválida';
        
        container.innerHTML = `
            <div class="status-chave-asaas ${classe}">
                <div class="status-header">
                    <span class="status-icone">${icone}</span>
                    <span class="status-texto">${texto}</span>
                </div>
                <div class="status-detalhes">
                    <small>Última verificação: ${status.timestamp}</small>
                    ${status.http_code ? `<br><small>HTTP: ${status.http_code}</small>` : ''}
                    ${status.response_time ? `<br><small>Tempo: ${status.response_time}ms</small>` : ''}
                </div>
            </div>
        `;
        
        console.log('Interface atualizada');
    }
    
    mostrarErro(mensagem) {
        const container = document.getElementById('status-chave-asaas-container');
        if (container) {
            container.innerHTML = `
                <div class="status-chave-asaas status-invalido">
                    <div class="status-header">
                        <span class="status-icone">❌</span>
                        <span class="status-texto">Erro: ${mensagem}</span>
                    </div>
                </div>
            `;
        }
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando monitoramento...');
    window.monitoramentoSimples = new MonitoramentoSimplesAsaas();
}); 