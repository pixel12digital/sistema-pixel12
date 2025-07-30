<?php
$page = 'testar_captura_mensagens.php';
$page_title = 'Testar Captura de Mensagens';
$custom_header = '';

function render_content() {
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">🔍 Testar Captura de Mensagens</h1>
            <p class="text-gray-600 mb-6">
                Esta página permite testar a captura de mensagens que foram enviadas pelo sistema de monitoramento 
                mas não estão registradas no banco de dados do chat.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-2">📊 Status Atual</h3>
                    <div id="status-atual" class="text-sm text-blue-600">
                        Carregando...
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 mb-2">✅ Última Execução</h3>
                    <div id="ultima-execucao" class="text-sm text-green-600">
                        Carregando...
                    </div>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-4 mb-6">
                <button id="btn-capturar-mensagens" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    🔄 Capturar Mensagens
                </button>
                
                <button id="btn-verificar-perdidas" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    🔍 Verificar Mensagens Perdidas
                </button>
                
                <button id="btn-limpar-cache" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    🗑️ Limpar Cache
                </button>
                
                <button id="btn-ir-chat" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    💬 Ir para Chat
                </button>
            </div>
            
            <div id="resultado" class="hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">📋 Resultado</h3>
                    <div id="resultado-conteudo" class="text-sm text-gray-600">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">📝 Logs de Execução</h2>
            <div id="logs" class="bg-gray-100 p-4 rounded-lg font-mono text-sm max-h-96 overflow-y-auto">
                <div class="text-gray-500">Aguardando execução...</div>
            </div>
        </div>
    </div>
</div>

<script>
class TestadorCapturaMensagens {
    constructor() {
        this.bindEvents();
        this.carregarStatus();
    }
    
    bindEvents() {
        document.getElementById('btn-capturar-mensagens').addEventListener('click', () => this.capturarMensagens());
        document.getElementById('btn-verificar-perdidas').addEventListener('click', () => this.verificarMensagensPerdidas());
        document.getElementById('btn-limpar-cache').addEventListener('click', () => this.limparCache());
        document.getElementById('btn-ir-chat').addEventListener('click', () => this.irParaChat());
    }
    
    async carregarStatus() {
        try {
            const response = await fetch('api/status_monitoramento.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('status-atual').innerHTML = `
                    <div>✅ Sistema ativo</div>
                    <div>📱 WhatsApp: ${data.whatsapp_status || 'Desconhecido'}</div>
                    <div>👥 Clientes monitorados: ${data.clientes_monitorados || 0}</div>
                `;
            } else {
                document.getElementById('status-atual').innerHTML = `
                    <div>❌ Sistema inativo</div>
                    <div>${data.error || 'Erro desconhecido'}</div>
                `;
            }
            
            // Buscar última execução
            const responseExecucao = await fetch('api/ultima_execucao_monitoramento.php');
            const dataExecucao = await responseExecucao.json();
            
            if (dataExecucao.success) {
                document.getElementById('ultima-execucao').innerHTML = `
                    <div>🕐 ${dataExecucao.ultima_execucao || 'Nunca'}</div>
                    <div>📊 Mensagens capturadas: ${dataExecucao.mensagens_capturadas || 0}</div>
                `;
            } else {
                document.getElementById('ultima-execucao').innerHTML = `
                    <div>❌ Erro ao carregar</div>
                `;
            }
            
        } catch (error) {
            console.error('Erro ao carregar status:', error);
            document.getElementById('status-atual').innerHTML = '<div>❌ Erro de conexão</div>';
        }
    }
    
    async capturarMensagens() {
        this.adicionarLog('🔄 Iniciando captura de mensagens...');
        
        try {
            const response = await fetch('capturar_mensagens_monitoramento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'acao=capturar'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarResultado({
                    titulo: '✅ Captura Concluída',
                    mensagem: `Mensagens capturadas: ${data.mensagens_capturadas}<br>
                              Mensagens já existentes: ${data.mensagens_ja_existentes}<br>
                              Clientes processados: ${data.clientes_processados}`
                });
                
                this.adicionarLog(`✅ Captura concluída: ${data.mensagens_capturadas} novas mensagens`);
            } else {
                this.mostrarResultado({
                    titulo: '❌ Erro na Captura',
                    mensagem: data.error || 'Erro desconhecido'
                });
                
                this.adicionarLog(`❌ Erro na captura: ${data.error}`);
            }
            
        } catch (error) {
            console.error('Erro ao capturar mensagens:', error);
            this.adicionarLog(`❌ Erro de conexão: ${error.message}`);
            this.mostrarResultado({
                titulo: '❌ Erro de Conexão',
                mensagem: error.message
            });
        }
    }
    
    async verificarMensagensPerdidas() {
        this.adicionarLog('🔍 Verificando mensagens perdidas...');
        
        try {
            const response = await fetch('capturar_mensagens_monitoramento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'acao=verificar'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarResultado({
                    titulo: '🔍 Verificação Concluída',
                    mensagem: `Clientes sem mensagem hoje: ${data.clientes_sem_mensagem}<br>
                              Total de clientes verificados: ${data.clientes ? data.clientes.length : 0}`
                });
                
                this.adicionarLog(`🔍 Verificação concluída: ${data.clientes_sem_mensagem} clientes sem mensagem`);
                
                if (data.clientes && data.clientes.length > 0) {
                    this.adicionarLog('📋 Clientes sem mensagem:');
                    data.clientes.forEach(cliente => {
                        this.adicionarLog(`   - ${cliente.nome} (ID: ${cliente.id})`);
                    });
                }
            } else {
                this.mostrarResultado({
                    titulo: '❌ Erro na Verificação',
                    mensagem: data.error || 'Erro desconhecido'
                });
                
                this.adicionarLog(`❌ Erro na verificação: ${data.error}`);
            }
            
        } catch (error) {
            console.error('Erro ao verificar mensagens perdidas:', error);
            this.adicionarLog(`❌ Erro de conexão: ${error.message}`);
            this.mostrarResultado({
                titulo: '❌ Erro de Conexão',
                mensagem: error.message
            });
        }
    }
    
    async limparCache() {
        this.adicionarLog('🗑️ Limpando cache...');
        
        try {
            const response = await fetch('api/invalidar_cache.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'limpar_tudo' })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.adicionarLog('✅ Cache limpo com sucesso');
                this.mostrarResultado({
                    titulo: '✅ Cache Limpo',
                    mensagem: 'Cache do sistema foi limpo com sucesso'
                });
            } else {
                this.adicionarLog(`❌ Erro ao limpar cache: ${data.error}`);
                this.mostrarResultado({
                    titulo: '❌ Erro ao Limpar Cache',
                    mensagem: data.error || 'Erro desconhecido'
                });
            }
            
        } catch (error) {
            console.error('Erro ao limpar cache:', error);
            this.adicionarLog(`❌ Erro de conexão: ${error.message}`);
            this.mostrarResultado({
                titulo: '❌ Erro de Conexão',
                mensagem: error.message
            });
        }
    }
    
    irParaChat() {
        window.location.href = 'chat.php';
    }
    
    mostrarResultado(resultado) {
        const resultadoDiv = document.getElementById('resultado');
        const conteudoDiv = document.getElementById('resultado-conteudo');
        
        conteudoDiv.innerHTML = `
            <div class="font-semibold mb-2">${resultado.titulo}</div>
            <div>${resultado.mensagem}</div>
        `;
        
        resultadoDiv.classList.remove('hidden');
        
        // Esconder após 10 segundos
        setTimeout(() => {
            resultadoDiv.classList.add('hidden');
        }, 10000);
    }
    
    adicionarLog(mensagem) {
        const logsDiv = document.getElementById('logs');
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = 'mb-1';
        logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${mensagem}`;
        
        logsDiv.appendChild(logEntry);
        logsDiv.scrollTop = logsDiv.scrollHeight;
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    new TestadorCapturaMensagens();
});
</script>

<?php
}

include 'template.php';
?> 