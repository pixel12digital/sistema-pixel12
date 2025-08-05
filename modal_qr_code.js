/**
 * 🔄 JAVASCRIPT PARA MODAL QR CODE
 * 
 * Script JavaScript para corrigir o modal de conexão WhatsApp
 */

// Configuração da VPS principal
const VPS_PRINCIPAL_URL = 'http://212.85.11.238:3001';
const AJAX_ENDPOINT = 'ajax_modal_qr.php';

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

// Função para atualizar QR Code
async function atualizarQrCode(session = 'default') {
    console.log('Atualizando QR Code para sessão:', session);
    
    const result = await fazerRequisicaoAjax('qr', { session });
    
    if (result.success && result.qr) {
        // Gerar QR Code visual
        const qrCodeElement = document.getElementById('qr-code-area');
        if (qrCodeElement) {
            qrCodeElement.innerHTML = \`
                <div style='text-align: center;'>
                    <div id='qrcode'></div>
                    <p style='margin-top: 10px; color: #666;'>Escaneie o QR Code com seu WhatsApp</p>
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
                    </div>
                \`;
            }
        }
        
        return { success: true, qr: result.qr };
    } else {
        // Mostrar erro
        const qrCodeElement = document.getElementById('qr-code-area');
        if (qrCodeElement) {
            qrCodeElement.innerHTML = \`
                <div style='text-align: center; color: #f59e0b;'>
                    <p>⚠️ QR Code não disponível</p>
                    <p style='font-size: 12px;'>${result.error || 'Tente novamente em alguns segundos'}</p>
                </div>
            \`;
        }
        
        return result;
    }
}

// Função para forçar novo QR Code
async function forcarNovoQrCode(session = 'default') {
    console.log('Forçando novo QR Code para sessão:', session);
    
    const qrCodeElement = document.getElementById('qr-code-area');
    if (qrCodeElement) {
        qrCodeElement.innerHTML = '<div style="text-align: center;"><p>🔄 Gerando novo QR Code...</p></div>';
    }
    
    const result = await fazerRequisicaoAjax('force_new', { session });
    
    if (result.success && result.qr) {
        return await atualizarQrCode(session);
    } else {
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
                <p><strong>Status:</strong> ${status.status || 'unknown'}</p>
                <p><strong>Ready:</strong> ${status.ready ? 'true' : 'false'}</p>
                <p><strong>Porta:</strong> ${status.port || 'N/A'}</p>
            \`;
        }
        
        return status;
    } else {
        console.error('Erro ao verificar status da VPS:', result.error);
        return null;
    }
}

// Função para inicializar o modal
function inicializarModalQrCode() {
    console.log('Inicializando modal QR Code');
    
    // Verificar status da VPS
    verificarStatusVps();
    
    // Tentar obter QR Code inicial
    setTimeout(() => {
        atualizarQrCode('default');
    }, 1000);
    
    // Configurar botões
    const btnAtualizar = document.getElementById('btn-atualizar-qr');
    const btnForcarNovo = document.getElementById('btn-forcar-novo-qr');
    
    if (btnAtualizar) {
        btnAtualizar.addEventListener('click', () => {
            atualizarQrCode('default');
        });
    }
    
    if (btnForcarNovo) {
        btnForcarNovo.addEventListener('click', () => {
            forcarNovoQrCode('default');
        });
    }
}

// Auto-inicialização quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarModalQrCode);
} else {
    inicializarModalQrCode();
}

// Exportar funções para uso global
window.QrCodeModal = {
    atualizar: atualizarQrCode,
    forcarNovo: forcarNovoQrCode,
    verificarStatus: verificarStatusVps,
    inicializar: inicializarModalQrCode
};
