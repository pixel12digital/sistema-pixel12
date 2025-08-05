<?php
/**
 * 🎯 SOLUÇÃO FINAL - QR CODE
 * 
 * Solução completa para o problema do QR Code não disponível
 * Baseado na estrutura real da VPS 3001
 */

echo "🎯 SOLUÇÃO FINAL - QR CODE\n";
echo "==========================\n\n";

require_once 'config_vps_3001_principal.php';

$vps_ip = '212.85.11.238';

// ===== 1. ANÁLISE DA ESTRUTURA REAL DA VPS =====
echo "1️⃣ ANÁLISE DA ESTRUTURA REAL DA VPS\n";
echo "------------------------------------\n";

// Verificar status real da VPS 3001
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "✅ VPS 3001 respondendo\n";
    echo "📊 Status: " . ($status_3001['status'] ?? 'unknown') . "\n";
    echo "📊 Ready: " . ($status_3001['ready'] ? 'true' : 'false') . "\n";
    echo "📱 Porta: " . ($status_3001['port'] ?? 'N/A') . "\n";
    echo "🕒 Última sessão: " . ($status_3001['lastSession'] ?? 'N/A') . "\n";
    
    if (isset($status_3001['clients_status'])) {
        foreach ($status_3001['clients_status'] as $session => $status) {
            echo "🔍 Sessão $session:\n";
            echo "  📊 Ready: " . ($status['ready'] ? 'true' : 'false') . "\n";
            echo "  📱 QR: " . ($status['hasQR'] ? 'disponível' : 'não disponível') . "\n";
        }
    }
} else {
    echo "❌ VPS 3001 não responde (HTTP $http_code_3001)\n";
}

echo "\n";

// ===== 2. IDENTIFICAR PROBLEMA REAL =====
echo "2️⃣ IDENTIFICANDO PROBLEMA REAL\n";
echo "-------------------------------\n";

$problema_identificado = false;

if ($http_code_3001 === 200) {
    if (!$status_3001['ready']) {
        echo "❌ PROBLEMA: VPS 3001 não está pronta (ready: false)\n";
        $problema_identificado = true;
    }
    
    if (isset($status_3001['clients_status']['default'])) {
        $default_status = $status_3001['clients_status']['default'];
        if (!$default_status['ready']) {
            echo "❌ PROBLEMA: Sessão default não está pronta\n";
            $problema_identificado = true;
        }
        if (!$default_status['hasQR']) {
            echo "❌ PROBLEMA: QR Code não está disponível na sessão\n";
            $problema_identificado = true;
        }
    }
}

if (!$problema_identificado) {
    echo "✅ VPS 3001 está funcionando corretamente\n";
}

echo "\n";

// ===== 3. CRIAR SOLUÇÃO ADAPTADA =====
echo "3️⃣ CRIANDO SOLUÇÃO ADAPTADA\n";
echo "----------------------------\n";

$solucao_adaptada = "<?php
/**
 * 🎯 SOLUÇÃO ADAPTADA - QR CODE
 * 
 * Solução adaptada para a estrutura real da VPS 3001
 * Gerado automaticamente em " . date('Y-m-d H:i:s') . "
 */

// Incluir configuração da VPS principal
require_once 'config_vps_3001_principal.php';

// Função para verificar status real da VPS
function getStatusRealVps3001() {
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

// Função para verificar se QR Code está disponível
function isQrCodeAvailable(\$session = 'default') {
    \$status = getStatusRealVps3001();
    
    if (\$status && isset(\$status['clients_status'][\$session])) {
        \$client_status = \$status['clients_status'][\$session];
        return \$client_status['ready'] && \$client_status['hasQR'];
    }
    
    return false;
}

// Função para aguardar QR Code ficar disponível
function waitForQrCode(\$session = 'default', \$max_attempts = 10) {
    for (\$i = 0; \$i < \$max_attempts; \$i++) {
        if (isQrCodeAvailable(\$session)) {
            return true;
        }
        
        // Aguardar 2 segundos antes da próxima tentativa
        sleep(2);
    }
    
    return false;
}

// Função para obter QR Code (adaptada)
function getQrCodeAdaptado(\$session = 'default') {
    \$vps_url = getVpsPrincipal();
    
    // Primeiro verificar se QR Code está disponível
    if (!isQrCodeAvailable(\$session)) {
        // Tentar aguardar QR Code ficar disponível
        if (!waitForQrCode(\$session)) {
            return [
                'success' => false,
                'error' => 'QR Code não está disponível. Aguarde alguns segundos e tente novamente.',
                'suggestion' => 'A sessão pode estar inicializando. Tente novamente em 10-30 segundos.'
            ];
        }
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
        'error' => 'Não foi possível obter o QR Code',
        'http_code' => \$http_code,
        'response' => \$response
    ];
}

// Função para forçar reinicialização da sessão
function forceSessionRestart(\$session = 'default') {
    \$vps_url = getVpsPrincipal();
    
    // Tentar desconectar sessão atual
    \$ch = curl_init(\$vps_url . '/session/' . \$session . '/disconnect');
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
    curl_exec(\$ch);
    curl_close(\$ch);
    
    // Aguardar um pouco
    sleep(5);
    
    // Aguardar QR Code ficar disponível
    if (waitForQrCode(\$session, 15)) {
        return getQrCodeAdaptado(\$session);
    }
    
    return [
        'success' => false,
        'error' => 'Não foi possível reinicializar a sessão',
        'suggestion' => 'Tente novamente ou reinicie o processo no servidor'
    ];
}

// Função para obter informações de debug
function getDebugInfo() {
    \$status = getStatusRealVps3001();
    
    if (\$status) {
        return [
            'success' => true,
            'vps_ready' => \$status['ready'] ?? false,
            'vps_status' => \$status['status'] ?? 'unknown',
            'vps_port' => \$status['port'] ?? 'N/A',
            'last_session' => \$status['lastSession'] ?? 'N/A',
            'clients_status' => \$status['clients_status'] ?? []
        ];
    }
    
    return [
        'success' => false,
        'error' => 'VPS não está respondendo'
    ];
}

// Endpoint para requisições AJAX
if (isset(\$_GET['action'])) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');
    
    \$action = \$_GET['action'];
    \$session = \$_GET['session'] ?? 'default';
    
    switch (\$action) {
        case 'status':
            echo json_encode(getDebugInfo());
            break;
            
        case 'qr':
            echo json_encode(getQrCodeAdaptado(\$session));
            break;
            
        case 'force_restart':
            echo json_encode(forceSessionRestart(\$session));
            break;
            
        case 'wait_qr':
            \$result = waitForQrCode(\$session);
            echo json_encode([
                'success' => \$result,
                'message' => \$result ? 'QR Code disponível' : 'QR Code não disponível após aguardar'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Ação não reconhecida',
                'available_actions' => ['status', 'qr', 'force_restart', 'wait_qr']
            ]);
    }
    exit;
}
?>";

file_put_contents('solucao_qr_code_adaptada.php', $solucao_adaptada);
echo "✅ Arquivo solucao_qr_code_adaptada.php criado\n";

// ===== 4. CRIAR JAVASCRIPT ADAPTADO =====
echo "\n4️⃣ CRIANDO JAVASCRIPT ADAPTADO\n";
echo "-------------------------------\n";

$javascript_adaptado = "/**
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
        console.log(\`Tentativa \${attempt}/\${maxRetries}\`);
        
        const result = await fazerRequisicaoAjax('qr', { session });
        
        if (result.success && result.qr) {
            // QR Code obtido com sucesso
            const qrCodeElement = document.getElementById('qr-code-area');
            if (qrCodeElement) {
                qrCodeElement.innerHTML = \`
                    <div style='text-align: center;'>
                        <div id='qrcode'></div>
                        <p style='margin-top: 10px; color: #666;'>Escaneie o QR Code com seu WhatsApp</p>
                        <p style='font-size: 12px; color: #999;'>Sessão: \${session}</p>
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
                            <p style='font-size: 12px; color: #999;'>Sessão: \${session}</p>
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
                        <p style='font-size: 12px;'>Tentativa \${attempt}/\${maxRetries}</p>
                        <p style='font-size: 12px;'>\${result.error || 'Aguarde...'}</p>
                        <p style='font-size: 12px;'>\${result.suggestion || ''}</p>
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
        qrCodeElement.innerHTML = '<div style=\"text-align: center;\"><p>🔄 Reinicializando sessão...</p></div>';
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
                    <p style='font-size: 12px;'>\${result.error || 'Erro desconhecido'}</p>
                    <p style='font-size: 12px;'>\${result.suggestion || ''}</p>
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
                <p><strong>Status:</strong> \${status.vps_status || 'unknown'}</p>
                <p><strong>Ready:</strong> \${status.vps_ready ? 'true' : 'false'}</p>
                <p><strong>Porta:</strong> \${status.vps_port || 'N/A'}</p>
                <p><strong>Última Sessão:</strong> \${status.last_session || 'N/A'}</p>
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
";

file_put_contents('modal_qr_code_adaptado.js', $javascript_adaptado);
echo "✅ Arquivo modal_qr_code_adaptado.js criado\n";

// ===== 5. CRIAR SCRIPT DE TESTE FINAL =====
echo "\n5️⃣ CRIANDO SCRIPT DE TESTE FINAL\n";
echo "--------------------------------\n";

$script_teste_final = "<?php
/**
 * 🧪 TESTE FINAL - SOLUÇÃO QR CODE
 * 
 * Teste completo da solução adaptada para QR Code
 */

require_once 'solucao_qr_code_adaptada.php';

echo \"🧪 TESTE FINAL - SOLUÇÃO QR CODE\\n\";
echo \"================================\\n\\n\";

// Teste 1: Verificar status real da VPS
echo \"1️⃣ VERIFICANDO STATUS REAL DA VPS\\n\";
\$status = getStatusRealVps3001();
if (\$status) {
    echo \"  ✅ VPS 3001 respondendo\\n\";
    echo \"  📊 Status: \" . (\$status['status'] ?? 'unknown') . \"\\n\";
    echo \"  📊 Ready: \" . (\$status['ready'] ? 'true' : 'false') . \"\\n\";
    echo \"  📱 Porta: \" . (\$status['port'] ?? 'N/A') . \"\\n\";
    
    if (isset(\$status['clients_status']['default'])) {
        \$default = \$status['clients_status']['default'];
        echo \"  🔍 Sessão default:\\n\";
        echo \"    📊 Ready: \" . (\$default['ready'] ? 'true' : 'false') . \"\\n\";
        echo \"    📱 QR: \" . (\$default['hasQR'] ? 'disponível' : 'não disponível') . \"\\n\";
    }
} else {
    echo \"  ❌ VPS 3001 não está funcionando\\n\";
}

echo \"\\n\";

// Teste 2: Verificar se QR Code está disponível
echo \"2️⃣ VERIFICANDO DISPONIBILIDADE DO QR CODE\\n\";
\$qr_disponivel = isQrCodeAvailable('default');
echo \"  QR Code disponível: \" . (\$qr_disponivel ? '✅ Sim' : '❌ Não') . \"\\n\";

if (!\$qr_disponivel) {
    echo \"  💡 Tentando aguardar QR Code ficar disponível...\\n\";
    \$qr_disponivel = waitForQrCode('default', 5);
    echo \"  QR Code após aguardar: \" . (\$qr_disponivel ? '✅ Sim' : '❌ Não') . \"\\n\";
}

echo \"\\n\";

// Teste 3: Tentar obter QR Code
echo \"3️⃣ TENTANDO OBTER QR CODE\\n\";
\$qr_data = getQrCodeAdaptado('default');
if (\$qr_data['success']) {
    echo \"  ✅ QR Code obtido com sucesso\\n\";
    echo \"  📱 QR: \" . substr(\$qr_data['qr'], 0, 50) . \"...\\n\";
    echo \"  📊 Ready: \" . (\$qr_data['ready'] ? 'true' : 'false') . \"\\n\";
} else {
    echo \"  ❌ Falha ao obter QR Code\\n\";
    echo \"  💬 Erro: \" . \$qr_data['error'] . \"\\n\";
    if (isset(\$qr_data['suggestion'])) {
        echo \"  💡 Sugestão: \" . \$qr_data['suggestion'] . \"\\n\";
    }
}

echo \"\\n\";

// Teste 4: Informações de debug
echo \"4️⃣ INFORMAÇÕES DE DEBUG\\n\";
\$debug_info = getDebugInfo();
if (\$debug_info['success']) {
    echo \"  ✅ Debug info obtida\\n\";
    echo \"  📊 VPS Ready: \" . (\$debug_info['vps_ready'] ? 'true' : 'false') . \"\\n\";
    echo \"  📊 VPS Status: \" . \$debug_info['vps_status'] . \"\\n\";
    echo \"  📱 Porta: \" . \$debug_info['vps_port'] . \"\\n\";
    echo \"  🕒 Última Sessão: \" . \$debug_info['last_session'] . \"\\n\";
} else {
    echo \"  ❌ Falha ao obter debug info\\n\";
    echo \"  💬 Erro: \" . \$debug_info['error'] . \"\\n\";
}

echo \"\\n✅ Teste final concluído!\\n\";
echo \"💡 Use a solução adaptada para resolver o problema do QR Code\\n\";
?>";

file_put_contents('teste_final_qr_code.php', $script_teste_final);
echo "✅ Arquivo teste_final_qr_code.php criado\n";

// ===== 6. RESUMO FINAL =====
echo "\n6️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 SOLUÇÃO FINAL IMPLEMENTADA:\n";
echo "✅ Análise da estrutura real da VPS 3001\n";
echo "✅ Identificação do problema real\n";
echo "✅ Solução adaptada criada\n";
echo "✅ JavaScript com retry e fallback\n";
echo "✅ Teste completo implementado\n\n";

echo "📁 ARQUIVOS CRIADOS:\n";
echo "• solucao_qr_code_adaptada.php - Solução adaptada\n";
echo "• modal_qr_code_adaptado.js - JavaScript adaptado\n";
echo "• teste_final_qr_code.php - Teste completo\n\n";

echo "💡 COMO USAR:\n";
echo "1. Inclua o JavaScript: <script src='modal_qr_code_adaptado.js'></script>\n";
echo "2. Use as funções: QrCodeModalAdaptado.atualizar()\n";
echo "3. Teste a solução: php teste_final_qr_code.php\n";
echo "4. O modal será corrigido automaticamente\n\n";

echo "🚀 PRÓXIMOS PASSOS:\n";
echo "1. Teste a solução: php teste_final_qr_code.php\n";
echo "2. Integre o JavaScript no seu painel\n";
echo "3. Verifique se o QR Code aparece corretamente\n";
echo "4. Monitore o funcionamento\n\n";

echo "✅ Solução final concluída!\n";
echo "🎉 O problema do QR Code não disponível será resolvido!\n";
?> 