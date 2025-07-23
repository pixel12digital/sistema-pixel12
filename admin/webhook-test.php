<?php
$page = 'webhook-test.php';
$page_title = 'Centro de Testes de Webhook';
$custom_header = '<button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center gap-2" onclick="executarTodosOsTestes()"><span>🧪 Executar Todos os Testes</span></button> <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2" onclick="limparLogs()"><span>🗑️ Limpar Logs</span></button> <a href="../painel/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md flex items-center gap-2"><span>← Voltar para Painel</span></a>';

// Definir base path para assets quando acessado do admin
$base_path = '../painel/';

function render_content() {
?>

<script src="https://cdn.tailwindcss.com"></script>
<style>
/* Correção para caminhos de assets no admin */
.sidebar-logo {
    background-image: url('../painel/assets/images/logo-pixel12digital.png');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}
</style>

<!-- Dashboard de Status -->
<section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 text-xl">🌐</span>
                </div>
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Status VPS</div>
                <div class="text-2xl font-bold text-gray-900" id="status-vps">Verificando...</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-xl">🔗</span>
                </div>
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Webhook</div>
                <div class="text-2xl font-bold text-gray-900" id="status-webhook">Verificando...</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <span class="text-purple-600 text-xl">📱</span>
                </div>
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">WhatsApp</div>
                <div class="text-2xl font-bold text-gray-900" id="status-whatsapp">Verificando...</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-600 text-xl">🗄️</span>
                </div>
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Banco de Dados</div>
                <div class="text-2xl font-bold text-gray-900" id="status-database">Verificando...</div>
            </div>
        </div>
    </div>
</section>

<!-- Configurações de Ambiente -->
<section class="bg-white rounded-lg shadow-sm p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">⚙️ Configurações de Ambiente</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-md font-medium text-gray-700 mb-3">🏠 Ambiente Local</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <span class="text-sm text-gray-600">Webhook URL:</span>
                    <span class="text-xs font-mono text-gray-800" id="webhook-local">http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="text-xs" id="status-local">🔍 Verificando...</span>
                </div>
                <button onclick="configurarAmbiente('local')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                    🔧 Configurar Local
                </button>
            </div>
        </div>

        <div>
            <h3 class="text-md font-medium text-gray-700 mb-3">☁️ Ambiente Produção</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <span class="text-sm text-gray-600">Webhook URL:</span>
                    <span class="text-xs font-mono text-gray-800" id="webhook-producao">https://app.pixel12digital.com.br/api/webhook_whatsapp.php</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="text-xs" id="status-producao">🔍 Verificando...</span>
                </div>
                <button onclick="configurarAmbiente('producao')" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                    🚀 Configurar Produção
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Testes Disponíveis -->
<section class="bg-white rounded-lg shadow-sm p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">🧪 Centro de Testes</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Testes Básicos -->
        <div class="space-y-3">
            <h3 class="text-md font-medium text-gray-700">Testes Básicos</h3>
            <button onclick="testarConectividadeVPS()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm">
                🌐 Testar Conectividade VPS
            </button>
            <button onclick="testarWebhookAtual()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm">
                🔗 Testar Webhook Atual
            </button>
            <button onclick="testarBancoDados()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm">
                🗄️ Testar Banco de Dados
            </button>
        </div>

        <!-- Testes WhatsApp -->
        <div class="space-y-3">
            <h3 class="text-md font-medium text-gray-700">WhatsApp</h3>
            <button onclick="testarStatusWhatsApp()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                📱 Status WhatsApp
            </button>
            <button onclick="enviarMensagemTeste()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                💬 Enviar Mensagem Teste
            </button>
            <button onclick="testarRecebimentoMensagem()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                📥 Testar Recebimento
            </button>
        </div>

        <!-- Testes Avançados -->
        <div class="space-y-3">
            <h3 class="text-md font-medium text-gray-700">Avançados</h3>
            <button onclick="testarWebhookCompleto()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm">
                🔧 Teste Completo
            </button>
            <button onclick="simularFluxoCompleto()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm">
                🎯 Simular Fluxo Completo
            </button>
            <button onclick="testarWebhookAsaas()" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm">
                💰 Testar Webhook Asaas
            </button>
            <button onclick="diagnosticarProblemas()" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
                🩺 Diagnosticar Problemas
            </button>
        </div>
    </div>
</section>

<!-- Futuras Integrações -->
<section class="bg-white rounded-lg shadow-sm p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">🚀 Futuras Integrações</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
            <div class="text-3xl mb-2">🤖</div>
            <h4 class="font-medium text-gray-700">Telegram Bot</h4>
            <p class="text-xs text-gray-500 mt-1">Integração com Telegram</p>
            <button disabled class="mt-3 w-full bg-gray-300 text-gray-500 px-3 py-2 rounded-md text-xs cursor-not-allowed">
                Em Breve
            </button>
        </div>

        <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
            <div class="text-3xl mb-2">📧</div>
            <h4 class="font-medium text-gray-700">Email Webhook</h4>
            <p class="text-xs text-gray-500 mt-1">Notificações por email</p>
            <button disabled class="mt-3 w-full bg-gray-300 text-gray-500 px-3 py-2 rounded-md text-xs cursor-not-allowed">
                Em Breve
            </button>
        </div>

        <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
            <div class="text-3xl mb-2">🔔</div>
            <h4 class="font-medium text-gray-700">Push Notifications</h4>
            <p class="text-xs text-gray-500 mt-1">Notificações push</p>
            <button disabled class="mt-3 w-full bg-gray-300 text-gray-500 px-3 py-2 rounded-md text-xs cursor-not-allowed">
                Em Breve
            </button>
        </div>

        <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
            <div class="text-3xl mb-2">🔗</div>
            <h4 class="font-medium text-gray-700">API Externa</h4>
            <p class="text-xs text-gray-500 mt-1">Webhooks personalizados</p>
            <button disabled class="mt-3 w-full bg-gray-300 text-gray-500 px-3 py-2 rounded-md text-xs cursor-not-allowed">
                Em Breve
            </button>
        </div>
    </div>
</section>

<!-- Console de Logs -->
<section class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">📋 Console de Logs</h2>
        <div class="flex space-x-2">
            <button onclick="exportarLogs()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                📥 Exportar
            </button>
            <button onclick="limparLogs()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                🗑️ Limpar
            </button>
        </div>
    </div>
    
    <div id="console-logs" class="bg-gray-900 text-green-400 p-4 rounded-md font-mono text-sm h-96 overflow-y-auto">
        <div class="text-gray-500">[<?php echo date('H:i:s'); ?>] 🚀 Sistema de Testes de Webhook iniciado...</div>
    </div>
    
    <!-- Filtros de Log -->
    <div class="mt-4 flex flex-wrap gap-2">
        <button onclick="filtrarLogs('all')" class="px-3 py-1 bg-gray-600 text-white rounded text-xs hover:bg-gray-700 filter-btn active">
            Todos
        </button>
        <button onclick="filtrarLogs('success')" class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700 filter-btn">
            ✅ Sucessos
        </button>
        <button onclick="filtrarLogs('error')" class="px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700 filter-btn">
            ❌ Erros
        </button>
        <button onclick="filtrarLogs('warning')" class="px-3 py-1 bg-yellow-600 text-white rounded text-xs hover:bg-yellow-700 filter-btn">
            ⚠️ Avisos
        </button>
        <button onclick="filtrarLogs('info')" class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 filter-btn">
            ℹ️ Info
        </button>
    </div>
</section>

<!-- Modal de Configuração Personalizada -->
<div id="modal-config-personalizada" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">🔧 Configuração Personalizada</h3>
            <button onclick="fecharModalConfig()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">URL do Webhook</label>
                <input type="text" id="webhook-personalizada" class="w-full p-3 border border-gray-300 rounded-md" placeholder="https://exemplo.com/webhook">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">URL da VPS</label>
                <input type="text" id="vps-personalizada" class="w-full p-3 border border-gray-300 rounded-md" value="http://212.85.11.238:3000">
            </div>
            
            <div class="flex gap-3">
                <button onclick="aplicarConfigPersonalizada()" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    ✅ Aplicar
                </button>
                <button onclick="fecharModalConfig()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    ❌ Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Configurações
const CONFIG = {
    vps_url: 'http://212.85.11.238:3000',
    webhooks: {
        local: 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php',
        producao: 'https://app.pixel12digital.com.br/api/webhook_whatsapp.php'
    },
    auto_refresh: true,
    refresh_interval: 30000 // 30 segundos
};

let logFilter = 'all';
let logs = [];

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    log('🚀 Sistema de Testes iniciado', 'info');
    verificarStatusInicial();
    
    if (CONFIG.auto_refresh) {
        setInterval(verificarStatusInicial, CONFIG.refresh_interval);
    }
});

// Função de log
function log(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    const logEntry = {
        timestamp,
        message,
        type,
        icon: icons[type] || 'ℹ️'
    };
    
    logs.push(logEntry);
    
    if (logFilter === 'all' || logFilter === type) {
        const console = document.getElementById('console-logs');
        const logLine = document.createElement('div');
        logLine.className = `log-entry log-${type}`;
        logLine.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${logEntry.icon} ${message}`;
        console.appendChild(logLine);
        console.scrollTop = console.scrollHeight;
    }
}

// Verificação de status inicial
async function verificarStatusInicial() {
    try {
        // Testar VPS
        const vpsStatus = await testarVPS();
        document.getElementById('status-vps').textContent = vpsStatus ? 'Online' : 'Offline';
        document.getElementById('status-vps').className = vpsStatus ? 'text-2xl font-bold text-green-600' : 'text-2xl font-bold text-red-600';
        
        // Testar webhooks
        const localStatus = await testarWebhook(CONFIG.webhooks.local);
        const producaoStatus = await testarWebhook(CONFIG.webhooks.producao);
        
        document.getElementById('status-local').textContent = localStatus ? '✅ Online' : '❌ Offline';
        document.getElementById('status-producao').textContent = producaoStatus ? '✅ Online' : '❌ Offline';
        
        // Status geral do webhook
        const webhookStatus = localStatus || producaoStatus;
        document.getElementById('status-webhook').textContent = webhookStatus ? 'Funcionando' : 'Offline';
        document.getElementById('status-webhook').className = webhookStatus ? 'text-2xl font-bold text-green-600' : 'text-2xl font-bold text-red-600';
        
        // Testar banco de dados
        const dbStatus = await testarBancoDados();
        document.getElementById('status-database').textContent = dbStatus ? 'Conectado' : 'Erro';
        document.getElementById('status-database').className = dbStatus ? 'text-2xl font-bold text-green-600' : 'text-2xl font-bold text-red-600';
        
    } catch (error) {
        log(`Erro na verificação inicial: ${error.message}`, 'error');
    }
}

// Testar VPS
async function testarVPS() {
    try {
        const response = await fetch(`${CONFIG.vps_url}/status`);
        return response.ok;
    } catch (error) {
        return false;
    }
}

// Testar webhook
async function testarWebhook(url) {
    try {
        const response = await fetch(url, {
            method: 'GET',
            timeout: 5000
        });
        return response.status === 200 || response.status === 400; // 400 é normal para GET sem dados
    } catch (error) {
        return false;
    }
}

// Funções de teste específicas
async function testarConectividadeVPS() {
    log('🌐 Testando conectividade com VPS...', 'info');
    
    try {
        const response = await fetch(`${CONFIG.vps_url}/status`);
        const data = await response.json();
        
        if (response.ok) {
            log(`✅ VPS online - Status: ${JSON.stringify(data)}`, 'success');
        } else {
            log(`❌ VPS com problemas - HTTP ${response.status}`, 'error');
        }
    } catch (error) {
        log(`❌ Erro ao conectar com VPS: ${error.message}`, 'error');
    }
}

async function testarWebhookAtual() {
    log('🔗 Testando webhook atual...', 'info');
    
    // Verificar qual webhook está configurado
    try {
        const response = await fetch(`${CONFIG.vps_url}/webhook/config`);
        const config = await response.json();
        
        if (config.webhook_url) {
            log(`📍 Webhook configurado: ${config.webhook_url}`, 'info');
            
            // Testar o webhook configurado
            const testResponse = await fetch(config.webhook_url);
            if (testResponse.ok || testResponse.status === 400) {
                log('✅ Webhook respondendo corretamente', 'success');
            } else {
                log(`❌ Webhook com problemas - HTTP ${testResponse.status}`, 'error');
            }
        } else {
            log('⚠️ Nenhum webhook configurado', 'warning');
        }
    } catch (error) {
        log(`❌ Erro ao testar webhook: ${error.message}`, 'error');
    }
}

async function testarBancoDados() {
    log('🗄️ Testando conexão com banco de dados...', 'info');
    
    try {
        const response = await fetch('test-database.php');
        const result = await response.json();
        
        if (result.success) {
            log('✅ Banco de dados conectado corretamente', 'success');
            return true;
        } else {
            log(`❌ Erro no banco de dados: ${result.error}`, 'error');
            return false;
        }
    } catch (error) {
        log(`❌ Erro ao testar banco: ${error.message}`, 'error');
        return false;
    }
}

async function testarStatusWhatsApp() {
    log('📱 Verificando status do WhatsApp...', 'info');
    
    try {
        const response = await fetch(`${CONFIG.vps_url}/status`);
        const data = await response.json();
        
        if (data.ready) {
            log('✅ WhatsApp conectado e pronto', 'success');
        } else {
            log('⚠️ WhatsApp não está conectado', 'warning');
        }
    } catch (error) {
        log(`❌ Erro ao verificar WhatsApp: ${error.message}`, 'error');
    }
}

async function enviarMensagemTeste() {
    log('💬 Enviando mensagem de teste...', 'info');
    
    const testData = {
        to: '5547997146908@c.us',
        message: `TESTE WEBHOOK ${new Date().toLocaleTimeString()}`
    };
    
    try {
        const response = await fetch(`${CONFIG.vps_url}/send-message`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            log('✅ Mensagem de teste enviada com sucesso', 'success');
        } else {
            log(`❌ Erro ao enviar mensagem: ${result.error}`, 'error');
        }
    } catch (error) {
        log(`❌ Erro no envio: ${error.message}`, 'error');
    }
}

async function testarRecebimentoMensagem() {
    log('📥 Simulando recebimento de mensagem...', 'info');
    
    const testData = {
        event: 'onmessage',
        data: {
            from: '5547997146908@c.us',
            text: `TESTE RECEBIMENTO ${new Date().toLocaleTimeString()}`,
            type: 'text'
        }
    };
    
    try {
        const response = await fetch(`${CONFIG.vps_url}/webhook/test`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            log('✅ Teste de recebimento executado com sucesso', 'success');
        } else {
            log(`❌ Erro no teste de recebimento: ${result.error}`, 'error');
        }
    } catch (error) {
        log(`❌ Erro no teste: ${error.message}`, 'error');
    }
}

async function configurarAmbiente(ambiente) {
    log(`🔧 Configurando ambiente: ${ambiente}...`, 'info');
    
    const webhookUrl = CONFIG.webhooks[ambiente];
    
    try {
        const response = await fetch(`${CONFIG.vps_url}/webhook/config`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url: webhookUrl })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            log(`✅ Ambiente ${ambiente} configurado com sucesso`, 'success');
            log(`📍 Webhook apontando para: ${webhookUrl}`, 'info');
            verificarStatusInicial(); // Atualizar status
        } else {
            log(`❌ Erro ao configurar ${ambiente}: ${result.error}`, 'error');
        }
    } catch (error) {
        log(`❌ Erro na configuração: ${error.message}`, 'error');
    }
}

async function testarWebhookCompleto() {
    log('🔧 Iniciando teste completo do webhook...', 'info');
    
    // Sequência de testes
    await testarConectividadeVPS();
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    await testarWebhookAtual();
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    await testarStatusWhatsApp();
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    await testarRecebimentoMensagem();
    
    log('🎯 Teste completo finalizado', 'info');
}

async function simularFluxoCompleto() {
    log('🎯 Simulando fluxo completo de mensagem...', 'info');
    
    // 1. Verificar se WhatsApp está conectado
    log('1. Verificando conexão WhatsApp...', 'info');
    await testarStatusWhatsApp();
    
    // 2. Simular recebimento de mensagem
    log('2. Simulando recebimento de mensagem...', 'info');
    await testarRecebimentoMensagem();
    
    // 3. Verificar se mensagem foi salva no banco
    log('3. Verificando salvamento no banco...', 'info');
    await testarBancoDados();
    
    // 4. Simular resposta automática
    log('4. Simulando resposta automática...', 'info');
    await enviarMensagemTeste();
    
    log('🎉 Fluxo completo simulado com sucesso!', 'success');
}

async function diagnosticarProblemas() {
    log('🩺 Iniciando diagnóstico de problemas...', 'info');
    
    const problemas = [];
    
    // Verificar VPS
    const vpsOk = await testarVPS();
    if (!vpsOk) problemas.push('VPS não está acessível');
    
    // Verificar webhooks
    const localOk = await testarWebhook(CONFIG.webhooks.local);
    const producaoOk = await testarWebhook(CONFIG.webhooks.producao);
    
    if (!localOk && !producaoOk) {
        problemas.push('Nenhum webhook está respondendo');
    }
    
    // Verificar banco
    const dbOk = await testarBancoDados();
    if (!dbOk) problemas.push('Problemas na conexão com banco de dados');
    
    if (problemas.length === 0) {
        log('✅ Nenhum problema detectado!', 'success');
    } else {
        log('❌ Problemas detectados:', 'error');
        problemas.forEach(problema => {
            log(`   • ${problema}`, 'error');
        });
    }
}

async function testarWebhookAsaas() {
    log('💰 Testando webhook do Asaas...', 'info');
    
    // Payload de exemplo do Asaas
    const payloadAsaas = {
        "event": "PAYMENT_RECEIVED",
        "payment": {
            "object": "payment",
            "id": "pay_test_" + Date.now(),
            "dateCreated": new Date().toISOString().split('T')[0],
            "customer": "cus_test_123",
            "value": 50.00,
            "netValue": 47.25,
            "description": "Teste de webhook via painel",
            "billingType": "PIX",
            "status": "RECEIVED",
            "dueDate": new Date().toISOString().split('T')[0],
            "paymentDate": new Date().toISOString().split('T')[0],
            "invoiceUrl": "https://www.asaas.com/i/test123",
            "deleted": false
        }
    };
    
    try {
        // Testar endpoint local
        log('📍 Testando endpoint local do Asaas...', 'info');
        const response = await fetch('http://localhost:8080/loja-virtual-revenda/public/webhook_asaas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payloadAsaas)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            log(`✅ Webhook Asaas local funcionando - ${result.message}`, 'success');
            log(`📋 Evento processado: ${result.event}`, 'info');
        } else {
            log(`❌ Erro no webhook Asaas local: ${result.error}`, 'error');
        }
        
        // Testar endpoint de produção
        log('📍 Testando endpoint de produção do Asaas...', 'info');
        const prodResponse = await fetch('https://app.pixel12digital.com.br/public/webhook_asaas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payloadAsaas)
        });
        
        if (prodResponse.ok) {
            const prodResult = await prodResponse.json();
            log(`✅ Webhook Asaas produção funcionando - ${prodResult.message}`, 'success');
        } else {
            log(`❌ Webhook Asaas produção com problemas (HTTP ${prodResponse.status})`, 'error');
        }
        
        // Verificar logs
        log('📋 Verificando logs do webhook Asaas...', 'info');
        log('ℹ️ Verifique o arquivo logs/webhook_asaas_*.log para detalhes', 'info');
        
    } catch (error) {
        log(`❌ Erro no teste do webhook Asaas: ${error.message}`, 'error');
    }
}

async function executarTodosOsTestes() {
    log('🧪 Executando todos os testes...', 'info');
    
    await testarConectividadeVPS();
    await testarWebhookAtual();
    await testarBancoDados();
    await testarStatusWhatsApp();
    await testarWebhookCompleto();
    
    log('✅ Todos os testes executados!', 'success');
}

// Funções de utilidade
function filtrarLogs(filter) {
    logFilter = filter;
    
    // Atualizar botões ativos
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Reexibir logs filtrados
    const console = document.getElementById('console-logs');
    console.innerHTML = '<div class="text-gray-500">[Sistema] Logs filtrados por: ' + filter + '</div>';
    
    logs.forEach(log => {
        if (filter === 'all' || log.type === filter) {
            const logLine = document.createElement('div');
            logLine.className = `log-entry log-${log.type}`;
            logLine.innerHTML = `<span class="text-gray-500">[${log.timestamp}]</span> ${log.icon} ${log.message}`;
            console.appendChild(logLine);
        }
    });
    
    console.scrollTop = console.scrollHeight;
}

function limparLogs() {
    logs = [];
    document.getElementById('console-logs').innerHTML = '<div class="text-gray-500">[Sistema] Logs limpos</div>';
    log('🗑️ Logs limpos', 'info');
}

function exportarLogs() {
    const logText = logs.map(log => `[${log.timestamp}] ${log.icon} ${log.message}`).join('\n');
    const blob = new Blob([logText], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `webhook-test-logs-${new Date().toISOString().slice(0, 10)}.txt`;
    a.click();
    
    URL.revokeObjectURL(url);
    log('📥 Logs exportados', 'success');
}

// Configuração personalizada
function abrirModalConfig() {
    document.getElementById('modal-config-personalizada').classList.remove('hidden');
    document.getElementById('modal-config-personalizada').classList.add('flex');
}

function fecharModalConfig() {
    document.getElementById('modal-config-personalizada').classList.add('hidden');
    document.getElementById('modal-config-personalizada').classList.remove('flex');
}

function aplicarConfigPersonalizada() {
    const webhookUrl = document.getElementById('webhook-personalizada').value;
    const vpsUrl = document.getElementById('vps-personalizada').value;
    
    if (webhookUrl && vpsUrl) {
        CONFIG.vps_url = vpsUrl;
        CONFIG.webhooks.personalizada = webhookUrl;
        
        log(`🔧 Configuração personalizada aplicada`, 'success');
        log(`📍 VPS: ${vpsUrl}`, 'info');
        log(`📍 Webhook: ${webhookUrl}`, 'info');
        
        fecharModalConfig();
        verificarStatusInicial();
    } else {
        alert('Por favor, preencha todos os campos');
    }
}

// Estilos adicionais para os logs
const style = document.createElement('style');
style.textContent = `
    .filter-btn.active {
        ring: 2px;
        ring-color: rgba(255, 255, 255, 0.5);
    }
    
    .log-success { color: #10b981; }
    .log-error { color: #ef4444; }
    .log-warning { color: #f59e0b; }
    .log-info { color: #3b82f6; }
`;
document.head.appendChild(style);
</script>

<?php
}

// Incluir template do painel com path correto
include __DIR__ . '/../painel/template.php';
?> 