<?php
/**
 * Integração da Chave da API do Asaas
 * Incluir este arquivo na sua interface para gerenciamento automático
 */

// Verificar se já foi incluído
if (defined('ASAAS_INTEGRATION_LOADED')) {
    return;
}
define('ASAAS_INTEGRATION_LOADED', true);

// Função para obter status da chave
function obterStatusChaveAsaas() {
    $verificador = new VerificadorAutomaticoChave();
    return $verificador->obterStatusAtual();
}

// Função para obter alertas
function obterAlertasChaveAsaas() {
    $verificador = new VerificadorAutomaticoChave();
    return $verificador->obterAlertas();
}

// Função para verificar se há problemas
function verificarProblemasChaveAsaas() {
    $status = obterStatusChaveAsaas();
    $alertas = obterAlertasChaveAsaas();
    
    return [
        'tem_problemas' => !$status['valida'] || $alertas !== null,
        'status' => $status,
        'alertas' => $alertas
    ];
}

// Função para gerar HTML do status
function gerarHtmlStatusChaveAsaas() {
    $problemas = verificarProblemasChaveAsaas();
    $status = $problemas['status'];
    
    if (!$status) {
        return '<div class="status-chave-asaas status-desconhecido">
                    <span class="indicador">❓</span>
                    <span class="texto">Status desconhecido</span>
                </div>';
    }
    
    $classe = $status['valida'] ? 'status-valido' : 'status-invalido';
    $indicador = $status['valida'] ? '✅' : '❌';
    $texto = $status['valida'] ? 'Chave Válida' : 'Chave Inválida';
    
    return "<div class='status-chave-asaas {$classe}'>
                <span class='indicador'>{$indicador}</span>
                <span class='texto'>{$texto}</span>
                <span class='tipo'>{$status['tipo_chave']}</span>
            </div>";
}

// Função para gerar HTML de alerta
function gerarHtmlAlertaChaveAsaas() {
    $problemas = verificarProblemasChaveAsaas();
    
    if (!$problemas['tem_problemas']) {
        return '';
    }
    
    $html = '<div class="alerta-chave-asaas">';
    
    if ($problemas['alertas']) {
        $alerta = $problemas['alertas'];
        $html .= "<div class='alerta-item alerta-alto'>
                    <div class='alerta-header'>
                        <span class='alerta-icono'>🚨</span>
                        <span class='alerta-titulo'>Chave da API Inválida</span>
                        <span class='alerta-tempo'>" . date('d/m H:i', strtotime($alerta['timestamp'])) . "</span>
                    </div>
                    <div class='alerta-mensagem'>{$alerta['mensagem']}</div>
                    <div class='alerta-acoes'>
                        <button onclick='abrirModalConfiguracaoAsaas()' class='btn-alerta'>Configurar API</button>
                        <button onclick='verificarChaveAsaas()' class='btn-alerta btn-secundario'>Verificar Agora</button>
                    </div>
                  </div>";
    }
    
    $html .= '</div>';
    return $html;
}

// CSS para integração
$css = "
<style>
.status-chave-asaas {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

.status-chave-asaas.status-valido {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-chave-asaas.status-invalido {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.status-chave-asaas.status-desconhecido {
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.status-chave-asaas .indicador {
    font-size: 16px;
}

.status-chave-asaas .tipo {
    font-size: 12px;
    opacity: 0.8;
    margin-left: auto;
}

.alerta-chave-asaas {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
}

.alerta-item {
    background: white;
    border: 1px solid #fecaca;
    border-left: 4px solid #dc2626;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.alerta-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.alerta-icono {
    font-size: 18px;
}

.alerta-titulo {
    font-weight: 600;
    color: #dc2626;
    flex: 1;
}

.alerta-tempo {
    font-size: 12px;
    color: #6b7280;
}

.alerta-mensagem {
    color: #374151;
    margin-bottom: 12px;
    line-height: 1.4;
}

.alerta-acoes {
    display: flex;
    gap: 8px;
}

.btn-alerta {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-alerta:first-child {
    background: #dc2626;
    color: white;
}

.btn-alerta:first-child:hover {
    background: #b91c1c;
}

.btn-alerta.btn-secundario {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-alerta.btn-secundario:hover {
    background: #e5e7eb;
}

.modal-configuracao-asaas {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10001;
}

.modal-configuracao-asaas.ativo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-conteudo {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-titulo {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
}

.modal-fechar {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-fechar:hover {
    color: #374151;
}

.form-grupo {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #374151;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    font-family: monospace;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: white;
}

.form-acoes {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-primario {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    flex: 1;
}

.btn-primario:hover {
    background: #2563eb;
}

.btn-secundario {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}

.btn-secundario:hover {
    background: #e5e7eb;
}
</style>";

echo $css;
?>

<script>
// Funções JavaScript para integração
function abrirModalConfiguracaoAsaas() {
    const modal = document.getElementById('modal-configuracao-asaas');
    if (modal) {
        modal.classList.add('ativo');
    }
}

function fecharModalConfiguracaoAsaas() {
    const modal = document.getElementById('modal-configuracao-asaas');
    if (modal) {
        modal.classList.remove('ativo');
    }
}

async function verificarChaveAsaas() {
    try {
        const response = await fetch('verificador_automatico_chave.php?action=verificar');
        const data = await response.json();
        
        if (data.valida) {
            mostrarNotificacao('✅ Chave válida!', 'success');
        } else {
            mostrarNotificacao('❌ Chave inválida - HTTP ' + data.http_code, 'error');
        }
        
        // Recarregar status na interface
        atualizarStatusChaveAsaas();
        
    } catch (error) {
        mostrarNotificacao('Erro ao verificar chave', 'error');
    }
}

async function atualizarStatusChaveAsaas() {
    try {
        const response = await fetch('verificador_automatico_chave.php?action=status');
        const data = await response.json();
        
        const container = document.getElementById('status-chave-asaas-container');
        if (container && data) {
            container.innerHTML = gerarHtmlStatusChaveAsaas();
        }
        
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
    }
}

function mostrarNotificacao(mensagem, tipo) {
    const notificacao = document.createElement('div');
    notificacao.className = `notificacao ${tipo}`;
    notificacao.innerHTML = `
        <div class="notificacao-conteudo">
            <span class="notificacao-mensagem">${mensagem}</span>
        </div>
    `;
    
    document.body.appendChild(notificacao);
    
    setTimeout(() => {
        notificacao.remove();
    }, 5000);
}

// Verificar status periodicamente (a cada 5 minutos)
setInterval(atualizarStatusChaveAsaas, 300000);

// Verificar status ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    atualizarStatusChaveAsaas();
    
    // Adicionar alertas se houver problemas
    const alertasContainer = document.getElementById('alertas-chave-asaas-container');
    if (alertasContainer) {
        const alertasHtml = '<?php echo addslashes(gerarHtmlAlertaChaveAsaas()); ?>';
        if (alertasHtml) {
            alertasContainer.innerHTML = alertasHtml;
        }
    }
});
</script>

<!-- Modal de Configuração -->
<div id="modal-configuracao-asaas" class="modal-configuracao-asaas">
    <div class="modal-conteudo">
        <div class="modal-header">
            <h2 class="modal-titulo">🔑 Configuração da API do Asaas</h2>
            <button class="modal-fechar" onclick="fecharModalConfiguracaoAsaas()">&times;</button>
        </div>
        
        <div class="form-grupo">
            <label class="form-label">Nova Chave da API:</label>
            <input type="text" id="nova-chave-input" class="form-input" 
                   placeholder="$aact_test_... ou $aact_prod_...">
        </div>
        
        <div class="form-grupo">
            <label class="form-label">Tipo de Chave:</label>
            <select id="tipo-chave-select" class="form-select">
                <option value="test">Teste (Sandbox) - Recomendado para desenvolvimento</option>
                <option value="prod">Produção - Para ambiente real</option>
            </select>
        </div>
        
        <div class="form-acoes">
            <button class="btn-secundario" onclick="fecharModalConfiguracaoAsaas()">Cancelar</button>
            <button class="btn-primario" onclick="processarNovaChaveAsaas()">Aplicar Nova Chave</button>
        </div>
    </div>
</div>

<script>
async function processarNovaChaveAsaas() {
    const novaChave = document.getElementById('nova-chave-input').value.trim();
    
    if (!novaChave) {
        mostrarNotificacao('Por favor, insira uma chave válida', 'error');
        return;
    }
    
    if (!novaChave.startsWith('$aact_')) {
        mostrarNotificacao('A chave deve começar com $aact_', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/atualizar_chave_asaas.php', {
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
            mostrarNotificacao('✅ Chave atualizada com sucesso!', 'success');
            fecharModalConfiguracaoAsaas();
            
            // Recarregar página após 2 segundos
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            mostrarNotificacao('❌ ' + data.message, 'error');
        }
        
    } catch (error) {
        mostrarNotificacao('Erro de conexão', 'error');
    }
}
</script> 