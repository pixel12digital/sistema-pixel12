/**
 * Monitoramento Otimizado da Chave da API do Asaas
 * Sistema inteligente que reduz requisições desnecessárias
 */

class MonitoramentoOtimizadoAsaas {
    constructor() {
        this.cache = new Map();
        this.ultimaVerificacao = null;
        this.intervaloVerificacao = 300000; // 5 minutos
        this.intervaloCache = 60000; // 1 minuto
        this.verificacaoAtiva = false;
        this.callbacks = [];
        
        this.inicializar();
    }
    
    inicializar() {
        // Verificar status inicial
        this.verificarStatusInicial();
        
        // Configurar verificações periódicas otimizadas
        this.configurarVerificacoesPeriodicas();
        
        // Configurar cache inteligente
        this.configurarCacheInteligente();
        
        console.log('🔍 Monitoramento otimizado da API Asaas inicializado');
    }
    
    async verificarStatusInicial() {
        try {
            const status = await this.obterStatusComCache();
            this.atualizarInterface(status);
            
            // Se há alertas, mostrar imediatamente
            if (status && !status.valida) {
                this.mostrarAlerta(status);
            }
        } catch (error) {
            console.error('Erro na verificação inicial:', error);
        }
    }
    
    configurarVerificacoesPeriodicas() {
        // Verificação principal a cada 5 minutos
        setInterval(async () => {
            if (!this.verificacaoAtiva) {
                await this.verificarStatusPeriodico();
            }
        }, this.intervaloVerificacao);
        
        // Cache inteligente a cada 1 minuto
        setInterval(() => {
            this.atualizarCacheLocal();
        }, this.intervaloCache);
    }
    
    configurarCacheInteligente() {
        // Limpar cache antigo a cada 10 minutos
        setInterval(() => {
            this.limparCacheAntigo();
        }, 600000);
    }
    
    async verificarStatusPeriodico() {
        if (this.verificacaoAtiva) return;
        
        this.verificacaoAtiva = true;
        
        try {
            // Verificar se deve fazer nova verificação
            const deveVerificar = await this.deveFazerVerificacao();
            
            if (deveVerificar) {
                const status = await this.verificarChaveReal();
                this.atualizarInterface(status);
                
                if (status && !status.valida) {
                    this.mostrarAlerta(status);
                }
            } else {
                // Usar cache se não precisa verificar
                const status = await this.obterStatusComCache();
                this.atualizarInterface(status);
            }
        } catch (error) {
            console.error('Erro na verificação periódica:', error);
        } finally {
            this.verificacaoAtiva = false;
        }
    }
    
    async deveFazerVerificacao() {
        try {
            const response = await fetch('verificador_automatico_chave_otimizado.php?action=estatisticas');
            const data = await response.json();
            
            return data.deve_verificar;
        } catch (error) {
            console.error('Erro ao verificar se deve fazer verificação:', error);
            return true; // Em caso de erro, fazer verificação
        }
    }
    
    async obterStatusComCache() {
        const cacheKey = 'status_asaas';
        const agora = Date.now();
        
        // Verificar cache local
        if (this.cache.has(cacheKey)) {
            const cacheData = this.cache.get(cacheKey);
            const tempoCache = agora - cacheData.timestamp;
            
            // Se o cache é recente (menos de 1 minuto), usar ele
            if (tempoCache < this.intervaloCache) {
                return cacheData.data;
            }
        }
        
        // Buscar do servidor
        try {
            const response = await fetch('verificador_automatico_chave_otimizado.php?action=status');
            const data = await response.json();
            
            // Salvar no cache
            this.cache.set(cacheKey, {
                data: data,
                timestamp: agora
            });
            
            return data;
        } catch (error) {
            console.error('Erro ao obter status:', error);
            return null;
        }
    }
    
    async verificarChaveReal() {
        try {
            const response = await fetch('verificador_automatico_chave_otimizado.php?action=verificar');
            const data = await response.json();
            
            // Atualizar cache
            this.cache.set('status_asaas', {
                data: data,
                timestamp: Date.now()
            });
            
            return data;
        } catch (error) {
            console.error('Erro ao verificar chave:', error);
            return null;
        }
    }
    
    atualizarInterface(status) {
        if (!status) return;
        
        const container = document.getElementById('status-chave-asaas-container');
        if (container) {
            container.innerHTML = this.gerarHtmlStatus(status);
        }
        
        // Executar callbacks registrados
        this.callbacks.forEach(callback => {
            try {
                callback(status);
            } catch (error) {
                console.error('Erro em callback:', error);
            }
        });
    }
    
    gerarHtmlStatus(status) {
        const icone = status.valida ? '✅' : '❌';
        const classe = status.valida ? 'status-valido' : 'status-invalido';
        const texto = status.valida ? 'Chave Válida' : 'Chave Inválida';
        const cache = status.cache ? ' (Cache)' : '';
        
        return `
            <div class="status-chave-asaas ${classe}">
                <div class="status-header">
                    <span class="status-icone">${icone}</span>
                    <span class="status-texto">${texto}${cache}</span>
                </div>
                <div class="status-detalhes">
                    <small>Última verificação: ${status.timestamp}</small>
                    ${status.http_code ? `<br><small>HTTP: ${status.http_code}</small>` : ''}
                    ${status.response_time ? `<br><small>Tempo: ${status.response_time}ms</small>` : ''}
                </div>
            </div>
        `;
    }
    
    mostrarAlerta(status) {
        const alertaContainer = document.getElementById('alertas-chave-asaas-container');
        if (alertaContainer) {
            alertaContainer.innerHTML = `
                <div class="alerta-chave-asaas">
                    <div class="alerta-header">
                        <span class="alerta-icone">⚠️</span>
                        <span class="alerta-titulo">Chave da API Inválida</span>
                    </div>
                    <div class="alerta-mensagem">
                        A chave da API do Asaas está inválida. 
                        <a href="#" onclick="abrirModalConfiguracaoAsaas()">Clique aqui para atualizar</a>
                    </div>
                    <div class="alerta-detalhes">
                        <small>HTTP Code: ${status.http_code || 'N/A'}</small>
                        <br><small>Última verificação: ${status.timestamp}</small>
                    </div>
                </div>
            `;
        }
    }
    
    atualizarCacheLocal() {
        // Atualizar cache local com dados mais recentes se necessário
        const cacheKey = 'status_asaas';
        if (this.cache.has(cacheKey)) {
            const cacheData = this.cache.get(cacheKey);
            const tempoCache = Date.now() - cacheData.timestamp;
            
            // Se o cache é muito antigo, limpar
            if (tempoCache > 300000) { // 5 minutos
                this.cache.delete(cacheKey);
            }
        }
    }
    
    limparCacheAntigo() {
        const agora = Date.now();
        const tempoLimite = 600000; // 10 minutos
        
        for (const [key, value] of this.cache.entries()) {
            if (agora - value.timestamp > tempoLimite) {
                this.cache.delete(key);
            }
        }
    }
    
    // Métodos públicos para integração
    onStatusChange(callback) {
        this.callbacks.push(callback);
    }
    
    async forcarVerificacao() {
        return await this.verificarChaveReal();
    }
    
    obterCache() {
        return this.cache;
    }
    
    limparCache() {
        this.cache.clear();
    }
}

// Inicializar monitoramento quando o DOM estiver pronto
let monitoramentoAsaas;

document.addEventListener('DOMContentLoaded', () => {
    monitoramentoAsaas = new MonitoramentoOtimizadoAsaas();
    
    // Expor para uso global
    window.monitoramentoAsaas = monitoramentoAsaas;
});

// Funções de integração com o sistema existente
function verificarChaveAsaas() {
    if (monitoramentoAsaas) {
        return monitoramentoAsaas.forcarVerificacao();
    }
}

function atualizarStatusChaveAsaas() {
    if (monitoramentoAsaas) {
        return monitoramentoAsaas.obterStatusComCache();
    }
}

// CSS para os elementos de status
const cssMonitoramento = `
<style>
.status-chave-asaas {
    padding: 12px;
    border-radius: 6px;
    margin: 8px 0;
    border: 1px solid #e5e7eb;
}

.status-valido {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.status-invalido {
    background: #fef2f2;
    border-color: #fecaca;
}

.status-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.status-icone {
    font-size: 16px;
}

.status-texto {
    font-weight: 600;
    font-size: 14px;
}

.status-detalhes {
    font-size: 12px;
    color: #6b7280;
}

.alerta-chave-asaas {
    padding: 12px;
    border-radius: 6px;
    background: #fef3c7;
    border: 1px solid #fde68a;
    margin: 8px 0;
}

.alerta-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.alerta-icone {
    font-size: 16px;
}

.alerta-titulo {
    font-weight: 600;
    color: #92400e;
}

.alerta-mensagem {
    color: #92400e;
    margin-bottom: 8px;
}

.alerta-mensagem a {
    color: #d97706;
    text-decoration: underline;
}

.alerta-detalhes {
    font-size: 12px;
    color: #92400e;
}
</style>
`;

// Injetar CSS
document.addEventListener('DOMContentLoaded', () => {
    const style = document.createElement('style');
    style.textContent = cssMonitoramento;
    document.head.appendChild(style);
}); 