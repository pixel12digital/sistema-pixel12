/**
 * 🎯 JAVASCRIPT ADAPTADO - QR CODE
 * 
 * JavaScript adaptado para a estrutura real da VPS 3001
 */

// Configuração
const AJAX_ENDPOINT = 'solucao_qr_code_adaptada.php';

// Função para fazer requisição AJAX
async function fazerRequisicaoAjax(action, params = {}) {
    const url = new URL(AJAX_ENDPOINT, window.location.origin);
    url.searchParams.set('action', action);
    
    for (const [key, value] of Object.entries(params)) {
        url.searchParams.set(key, value);
    }
    
    try {
        const response = await fetch(url.toString());
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro na requisição AJAX:', error);
        return { success: false, error: error.message };
    }
}

// Função para atualizar QR Code com retry
async function atualizarQrCodeComRetry(session = 'default', maxRetries = 3) {
    console.log('Tentando obter QR Code para sessão:', session);
    
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        console.log(\`Tentativa ${attempt}/${maxRetries}\`);
        
        const result = await fazerRequisicaoAjax('qr', { session });
        
        if (result.success && result.qr) {
            // QR Code obtido com sucesso
            const qrCodeElement = document.getElementById('qr-code-area');
            if (qrCodeElement) {
                qrCodeElement.innerHTML = \`
                    <div style='text-align: center;'>
                        <div id='qrcode'></div>
                        <p style='margin-top: 10px; color: #666;'>Escaneie o QR Code com seu WhatsApp</p>
                        <p style='font-size: 12px; color: #999;'>Sessão: ${session}</p>
                    </div>
                \`;
                
                // Usar biblioteca QR Code (se disponível)
                if (typeof QRCode !== 'undefined') {
                    new QRCode(document.getElementById('qrcode'), result.qr);
                } else {
                    // Fallback: mostrar QR Code como texto
                    qrCodeElement.innerHTML = \`
                        <div style='text-align: center;'>
                            <p style='color: #666;'>QR Code disponível</p>
                            <p style='font-family: monospace; font-size: 12px; word-break: break-all;'>${result.qr}</p>
                            <p style='font-size: 12px; color: #999;'>Sessão: ${session}</p>
                        </div>
                    \`;
                }
            }
            
            return { success: true, qr: result.qr };
        } else {
            // Mostrar progresso
            const qrCodeElement = document.getElementById('qr-code-area');
            if (qrCodeElement) {
                qrCodeElement.innerHTML = \`
                    <div style='text-align: center; color: #f59e0b;'>
                        <p>⚠️ QR Code não disponível</p>
                        <p style='font-size: 12px;'>Tentativa ${attempt}/${maxRetries}</p>
                        <p style='font-size: 12px;'>${result.error || 'Aguarde...'}</p>
                        <p style='font-size: 12px;'>${result.suggestion || ''}</p>
                    </div>
                \`;
            }
            
            // Aguardar antes da próxima tentativa
            if (attempt < maxRetries) {
                await new Promise(resolve => setTimeout(resolve, 3000));
            }
        }
    }
    
    // Todas as tentativas falharam
    const qrCodeElement = document.getElementById('qr-code-area');
    if (qrCodeElement) {
        qrCodeElement.innerHTML = \`
            <div style='text-align: center; color: #ef4444;'>
                <p>❌ QR Code não disponível</p>
                <p style='font-size: 12px;'>Todas as tentativas falharam</p>
                <p style='font-size: 12px;'>Tente forçar uma nova sessão</p>
            </div>
        \`;
    }
    
    return { success: false, error: 'Todas as tentativas falharam' };
}

// Função para forçar reinicialização da sessão
async function forcarReinicializacao(session = 'default') {
    console.log('Forçando reinicialização da sessão:', session);
    
    const qrCodeElement = document.getElementById('qr-code-area');
    if (qrCodeElement) {
        qrCodeElement.innerHTML = '<div style="text-align: center;"><p>🔄 Reinicializando sessão...</p></div>';
    }
    
    const result = await fazerRequisicaoAjax('force_restart', { session });
    
    if (result.success && result.qr) {
        return await atualizarQrCodeComRetry(session);
    } else {
        const qrCodeElement = document.getElementById('qr-code-area');
        if (qrCodeElement) {
            qrCodeElement.innerHTML = \`
                <div style='text-align: center; color: #ef4444;'>
                    <p>❌ Falha na reinicialização</p>
                    <p style='font-size: 12px;'>${result.error || 'Erro desconhecido'}</p>
                    <p style='font-size: 12px;'>${result.suggestion || ''}</p>
                </div>
            \`;
        }
        
        return result;
    }
}

// Função para verificar status da VPS
async function verificarStatusVps() {
    const result = await fazerRequisicaoAjax('status');
    
    if (result.success && result.data) {
        const status = result.data;
        console.log('Status da VPS:', status);
        
        // Atualizar informações de debug
        const debugElement = document.querySelector('.debug-info');
        if (debugElement) {
            debugElement.innerHTML = \`
                <p><strong>Status:</strong> ${status.vps_status || 'unknown'}</p>
                <p><strong>Ready:</strong> ${status.vps_ready ? 'true' : 'false'}</p>
                <p><strong>Porta:</strong> ${status.vps_port || 'N/A'}</p>
                <p><strong>Última Sessão:</strong> ${status.last_session || 'N/A'}</p>
            \`;
        }
        
        return status;
    } else {
        console.error('Erro ao verificar status da VPS:', result.error);
        return null;
    }
}

// Função para inicializar o modal
function inicializarModalQrCodeAdaptado() {
    console.log('Inicializando modal QR Code adaptado');
    
    // Verificar status da VPS
    verificarStatusVps();
    
    // Tentar obter QR Code inicial com retry
    setTimeout(() => {
        atualizarQrCodeComRetry('default');
    }, 1000);
    
    // Configurar botões
    const btnAtualizar = document.getElementById('btn-atualizar-qr');
    const btnForcarNovo = document.getElementById('btn-forcar-novo-qr');
    
    if (btnAtualizar) {
        btnAtualizar.addEventListener('click', () => {
            atualizarQrCodeComRetry('default');
        });
    }
    
    if (btnForcarNovo) {
        btnForcarNovo.addEventListener('click', () => {
            forcarReinicializacao('default');
        });
    }
}

// Auto-inicialização
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarModalQrCodeAdaptado);
} else {
    inicializarModalQrCodeAdaptado();
}

// Exportar funções para uso global
window.QrCodeModalAdaptado = {
    atualizar: atualizarQrCodeComRetry,
    forcarReinicializacao: forcarReinicializacao,
    verificarStatus: verificarStatusVps,
    inicializar: inicializarModalQrCodeAdaptado
};
