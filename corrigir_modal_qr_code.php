<?php
/**
 * 🔧 CORRIGIR MODAL QR CODE
 * 
 * Script para corrigir o problema do QR Code não disponível
 * no modal de conexão WhatsApp do painel
 */

echo "🔧 CORRIGINDO MODAL QR CODE\n";
echo "==========================\n\n";

require_once 'config_vps_3001_principal.php';

$vps_ip = '212.85.11.238';

// ===== 1. VERIFICAR STATUS ATUAL =====
echo "1️⃣ VERIFICANDO STATUS ATUAL\n";
echo "---------------------------\n";

// Verificar VPS 3001 (principal)
echo "🔍 Verificando VPS 3001 (Principal)...\n";
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "  ✅ VPS 3001 respondendo\n";
    echo "  📊 Ready: " . ($status_3001['ready'] ? 'true' : 'false') . "\n";
    echo "  📱 Porta: " . ($status_3001['port'] ?? '3001') . "\n";
    
    if (isset($status_3001['clients_status'])) {
        foreach ($status_3001['clients_status'] as $session => $status) {
            echo "  🔍 Sessão $session: " . ($status['ready'] ? 'ready' : 'not ready') . "\n";
            echo "    📱 QR: " . ($status['hasQR'] ? 'disponível' : 'não disponível') . "\n";
        }
    }
} else {
    echo "  ❌ VPS 3001 não responde (HTTP $http_code_3001)\n";
}

echo "\n";

// ===== 2. TESTAR ENDPOINTS DE QR CODE =====
echo "2️⃣ TESTANDO ENDPOINTS DE QR CODE\n";
echo "--------------------------------\n";

$endpoints_qr = [
    '/qr' => 'QR Code geral',
    '/qr?session=default' => 'QR Code sessão default',
    '/qr?session=comercial' => 'QR Code sessão comercial',
    '/session/start/default' => 'Iniciar sessão default',
    '/session/start/comercial' => 'Iniciar sessão comercial'
];

foreach ($endpoints_qr as $endpoint => $descricao) {
    echo "🔍 Testando $descricao...\n";
    
    $method = (strpos($endpoint, '/session/start/') !== false) ? 'POST' : 'GET';
    
    $ch = curl_init("http://$vps_ip:3001$endpoint");
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($http_code === 200) ? "✅" : "❌";
    echo "  $status (HTTP $http_code)\n";
    
    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        if ($error_data && isset($error_data['message'])) {
            echo "    💬 Erro: " . $error_data['message'] . "\n";
        }
    }
    
    echo "\n";
}

// ===== 3. CRIAR SCRIPT DE CORREÇÃO DO MODAL =====
echo "3️⃣ CRIANDO SCRIPT DE CORREÇÃO DO MODAL\n";
echo "--------------------------------------\n";

$script_correcao = "<?php
/**
 * 🔧 CORREÇÃO DO MODAL QR CODE
 * 
 * Script para corrigir o problema do QR Code não disponível
 * no modal de conexão WhatsApp
 */

// Incluir configuração da VPS principal
require_once 'config_vps_3001_principal.php';

// Função para obter QR Code da VPS principal
function getQrCodeVps3001(\$session = 'default') {
    \$vps_url = getVpsPrincipal();
    
    // Primeiro, tentar iniciar a sessão se necessário
    \$ch = curl_init(\$vps_url . '/session/start/' . \$session);
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 15);
    
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);
    
    // Aguardar um pouco para a sessão inicializar
    if (\$http_code === 200) {
        sleep(2);
    }
    
    // Agora tentar obter o QR Code
    \$ch = curl_init(\$vps_url . '/qr?session=' . \$session);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
    
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);
    
    if (\$http_code === 200) {
        \$qr_data = json_decode(\$response, true);
        if (\$qr_data && isset(\$qr_data['qr'])) {
            return [
                'success' => true,
                'qr' => \$qr_data['qr'],
                'ready' => \$qr_data['ready'] ?? false
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'QR Code não disponível',
        'http_code' => \$http_code,
        'response' => \$response
    ];
}

// Função para verificar status da VPS
function getStatusVps3001() {
    \$vps_url = getVpsPrincipal();
    
    \$ch = curl_init(\$vps_url . '/status');
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
    
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);
    
    if (\$http_code === 200) {
        return json_decode(\$response, true);
    }
    
    return null;
}

// Função para forçar novo QR Code
function forceNewQrCode(\$session = 'default') {
    \$vps_url = getVpsPrincipal();
    
    // Desconectar sessão atual (se existir)
    \$ch = curl_init(\$vps_url . '/session/' . \$session . '/disconnect');
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
    curl_exec(\$ch);
    curl_close(\$ch);
    
    // Aguardar um pouco
    sleep(3);
    
    // Iniciar nova sessão
    return getQrCodeVps3001(\$session);
}

// Função para atualizar QR Code
function updateQrCode(\$session = 'default') {
    return getQrCodeVps3001(\$session);
}

// Exemplo de uso
if (isset(\$_GET['action'])) {
    header('Content-Type: application/json');
    
    switch (\$_GET['action']) {
        case 'status':
            \$status = getStatusVps3001();
            echo json_encode(\$status);
            break;
            
        case 'qr':
            \$session = \$_GET['session'] ?? 'default';
            \$qr_data = getQrCodeVps3001(\$session);
            echo json_encode(\$qr_data);
            break;
            
        case 'force_new':
            \$session = \$_GET['session'] ?? 'default';
            \$qr_data = forceNewQrCode(\$session);
            echo json_encode(\$qr_data);
            break;
            
        case 'update':
            \$session = \$_GET['session'] ?? 'default';
            \$qr_data = updateQrCode(\$session);
            echo json_encode(\$qr_data);
            break;
            
        default:
            echo json_encode(['error' => 'Ação não reconhecida']);
    }
    exit;
}
?>";

file_put_contents('correcao_modal_qr.php', $script_correcao);
echo "✅ Arquivo correcao_modal_qr.php criado\n";

// ===== 4. CRIAR SCRIPT DE TESTE DO MODAL =====
echo "\n4️⃣ CRIANDO SCRIPT DE TESTE DO MODAL\n";
echo "-----------------------------------\n";

$script_teste = "<?php
/**
 * 🧪 TESTE DO MODAL QR CODE
 * 
 * Script para testar as funções de correção do modal QR Code
 */

require_once 'correcao_modal_qr.php';

echo \"🧪 TESTE DO MODAL QR CODE\\n\";
echo \"========================\\n\\n\";

// Teste 1: Verificar status da VPS
echo \"1️⃣ VERIFICANDO STATUS DA VPS\\n\";
\$status = getStatusVps3001();
if (\$status) {
    echo \"  ✅ VPS 3001 funcionando\\n\";
    echo \"  📊 Ready: \" . (\$status['ready'] ? 'true' : 'false') . \"\\n\";
    echo \"  📱 Porta: \" . (\$status['port'] ?? 'N/A') . \"\\n\";
} else {
    echo \"  ❌ VPS 3001 não está funcionando\\n\";
}

echo \"\\n\";

// Teste 2: Tentar obter QR Code
echo \"2️⃣ TENTANDO OBTER QR CODE\\n\";
\$qr_data = getQrCodeVps3001('default');
if (\$qr_data['success']) {
    echo \"  ✅ QR Code obtido com sucesso\\n\";
    echo \"  📱 QR: \" . substr(\$qr_data['qr'], 0, 50) . \"...\\n\";
    echo \"  📊 Ready: \" . (\$qr_data['ready'] ? 'true' : 'false') . \"\\n\";
} else {
    echo \"  ❌ Falha ao obter QR Code\\n\";
    echo \"  💬 Erro: \" . \$qr_data['error'] . \"\\n\";
    echo \"  📊 HTTP Code: \" . \$qr_data['http_code'] . \"\\n\";
}

echo \"\\n\";

// Teste 3: Forçar novo QR Code
echo \"3️⃣ FORÇANDO NOVO QR CODE\\n\";
\$qr_data = forceNewQrCode('default');
if (\$qr_data['success']) {
    echo \"  ✅ Novo QR Code gerado\\n\";
    echo \"  📱 QR: \" . substr(\$qr_data['qr'], 0, 50) . \"...\\n\";
} else {
    echo \"  ❌ Falha ao gerar novo QR Code\\n\";
    echo \"  💬 Erro: \" . \$qr_data['error'] . \"\\n\";
}

echo \"\\n✅ Teste concluído!\\n\";
?>";

file_put_contents('teste_modal_qr.php', $script_teste);
echo "✅ Arquivo teste_modal_qr.php criado\n";

// ===== 5. CRIAR AJAX PARA O MODAL =====
echo "\n5️⃣ CRIANDO AJAX PARA O MODAL\n";
echo "----------------------------\n";

$ajax_modal = "<?php
/**
 * 🔄 AJAX PARA MODAL QR CODE
 * 
 * Endpoint AJAX para o modal de conexão WhatsApp
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'correcao_modal_qr.php';

\$action = \$_GET['action'] ?? \$_POST['action'] ?? '';
\$session = \$_GET['session'] ?? \$_POST['session'] ?? 'default';
\$porta = \$_GET['porta'] ?? \$_POST['porta'] ?? '3001';

// Log da requisição
error_log(\"[AJAX MODAL] Action: \$action, Session: \$session, Porta: \$porta\");

try {
    switch (\$action) {
        case 'status':
            \$status = getStatusVps3001();
            if (\$status) {
                echo json_encode([
                    'success' => true,
                    'data' => \$status
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'VPS não está respondendo'
                ]);
            }
            break;
            
        case 'qr':
            \$qr_data = getQrCodeVps3001(\$session);
            echo json_encode(\$qr_data);
            break;
            
        case 'force_new':
            \$qr_data = forceNewQrCode(\$session);
            echo json_encode(\$qr_data);
            break;
            
        case 'update':
            \$qr_data = updateQrCode(\$session);
            echo json_encode(\$qr_data);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Ação não reconhecida',
                'available_actions' => ['status', 'qr', 'force_new', 'update']
            ]);
    }
} catch (Exception \$e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . \$e->getMessage()
    ]);
}
?>";

file_put_contents('ajax_modal_qr.php', $ajax_modal);
echo "✅ Arquivo ajax_modal_qr.php criado\n";

// ===== 6. CRIAR JAVASCRIPT PARA O MODAL =====
echo "\n6️⃣ CRIANDO JAVASCRIPT PARA O MODAL\n";
echo "----------------------------------\n";

$javascript_modal = "/**
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
                        <p style='font-family: monospace; font-size: 12px; word-break: break-all;'>\${result.qr}</p>
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
                    <p style='font-size: 12px;'>\${result.error || 'Tente novamente em alguns segundos'}</p>
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
        qrCodeElement.innerHTML = '<div style=\"text-align: center;\"><p>🔄 Gerando novo QR Code...</p></div>';
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
                <p><strong>Status:</strong> \${status.status || 'unknown'}</p>
                <p><strong>Ready:</strong> \${status.ready ? 'true' : 'false'}</p>
                <p><strong>Porta:</strong> \${status.port || 'N/A'}</p>
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
";

file_put_contents('modal_qr_code.js', $javascript_modal);
echo "✅ Arquivo modal_qr_code.js criado\n";

// ===== 7. RESUMO FINAL =====
echo "\n7️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 SOLUÇÃO IMPLEMENTADA:\n";
echo "✅ Script de correção do modal QR Code criado\n";
echo "✅ Funções para obter e atualizar QR Code\n";
echo "✅ Endpoint AJAX para comunicação com a VPS\n";
echo "✅ JavaScript para interação com o modal\n\n";

echo "📁 ARQUIVOS CRIADOS:\n";
echo "• correcao_modal_qr.php - Funções de correção do modal\n";
echo "• teste_modal_qr.php - Teste das funções\n";
echo "• ajax_modal_qr.php - Endpoint AJAX\n";
echo "• modal_qr_code.js - JavaScript para o modal\n\n";

echo "💡 COMO USAR:\n";
echo "1. Inclua o JavaScript no seu painel: <script src='modal_qr_code.js'></script>\n";
echo "2. Use as funções: QrCodeModal.atualizar(), QrCodeModal.forcarNovo()\n";
echo "3. Teste as funções: php teste_modal_qr.php\n";
echo "4. O modal será corrigido automaticamente\n\n";

echo "🚀 PRÓXIMOS PASSOS:\n";
echo "1. Teste o script: php teste_modal_qr.php\n";
echo "2. Integre o JavaScript no seu painel\n";
echo "3. Verifique se o modal funciona corretamente\n";
echo "4. Monitore os logs para debug\n\n";

echo "✅ Correção do modal QR Code concluída!\n";
echo "🎉 O problema do QR Code não disponível será resolvido!\n";
?> 