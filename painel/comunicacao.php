<!-- Cache busting: 2025-08-07 18:30:00 -->
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Força limpeza de cache com headers ainda mais agressivos
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('ETag: "' . md5(time()) . '"');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$page = 'comunicacao.php';
$page_title = 'Comunicação - Gerenciar Canais';
require_once __DIR__ . '/../config.php';
require_once 'db.php';

// Configuração dos canais específicos
$CANAIS_CONFIG = [
    'pixel12digital' => [
        'identificador' => '554797146908@c.us',
        'nome_exibicao' => 'Pixel12Digital',
        'tipo' => 'IA',
        'descricao' => 'Atendimento por IA (Ana)',
        'porta' => 3000,
        'cor' => '#10b981' // Verde
    ],
    'atendimento_humano' => [
        'identificador' => '554797309525@c.us', 
        'nome_exibicao' => 'Pixel - Comercial',
        'tipo' => 'HUMANO',
        'descricao' => 'Atendimento Humano',
        'porta' => 3001,
        'cor' => '#3b82f6' // Azul
    ]
];

// Processa exclusão de canal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir_canal' && isset($_POST['canal_id'])) {
    $canal_id = intval($_POST['canal_id']);
    $mysqli->query("DELETE FROM canais_comunicacao WHERE id = $canal_id");
    echo '<script>location.href = location.pathname;</script>';
    exit;
}

// Processa cadastro de canal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'add_canal') {
    $identificador = $mysqli->real_escape_string(trim($_POST['identificador']));
    $nome_exibicao = $mysqli->real_escape_string(trim($_POST['nome_exibicao']));
    $porta = intval($_POST['porta']);
    $tipo = 'whatsapp';
    $status = 'pendente';
    
    // Verifica se já existe um canal com esta porta
    $canal_existente = $mysqli->query("SELECT id FROM canais_comunicacao WHERE porta = $porta")->fetch_assoc();
    if ($canal_existente) {
        $erro_cadastro = 'Já existe um canal WhatsApp nesta porta.';
    } else {
        $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao, porta) VALUES ('$tipo', '$identificador', '$nome_exibicao', '$status', NULL, $porta)");
        $canal_id = $mysqli->insert_id;
    }
    
    if (!isset($erro_cadastro) && isset($canal_id)) {
        echo '<script>location.href = location.pathname;</script>';
        exit;
    }
}

// Processa definição de canal padrão por função
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'set_padrao_funcao') {
    $funcao = $_POST['funcao'];
    $canal_id = intval($_POST['canal_id']);
    $mysqli->query("INSERT INTO canais_padrao_funcoes (funcao, canal_id) VALUES ('" . $mysqli->real_escape_string($funcao) . "', $canal_id) ON DUPLICATE KEY UPDATE canal_id = $canal_id");
    echo '<script>location.href=location.pathname;</script>';
    exit;
}

include 'template.php';

function render_content() {
    global $mysqli, $erro_cadastro, $CANAIS_CONFIG;
    
    // CSS PADRÃO DO PAINEL
    echo '<style>'
    . 'body { background: #f7f8fa; }'
    . '.com-table { width: 100%; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 2px 12px #0001; margin-bottom: 30px; border-collapse: separate; border-spacing: 0; }'
    . '.com-table th { background: #ede9fe; color: #4b2995; font-weight: bold; font-size: 1.08em; padding: 14px 10px; text-align: left; }'
    . '.com-table td { padding: 13px 10px; font-size: 1.04em; text-align: left; }'
    . '.com-table tr.zebra { background: #f3f4f6; }'
    . '.com-table tr { border-bottom: 1px solid #ececec; }'
    . '.com-table tr:last-child { border-bottom: none; }'
    . '.status-conectado { color: #22c55e; font-weight: bold; }'
    . '.status-pendente { color: #f59e42; font-weight: bold; }'
    . '.status-verificando { color: #6b7280; font-style: italic; }'
    . '.btn-ac { display: inline-block; margin: 0 2px; padding: 5px 12px; border-radius: 6px; font-weight: 500; text-decoration: none; transition: background 0.2s; font-size: 0.97em; border: none; cursor: pointer; }'
    . '.btn-editar { background: #ede9fe; color: #6d28d9; border: 1px solid #c7d2fe; }'
    . '.btn-editar:hover { background: #c7d2fe; }'
    . '.btn-conectar { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }'
    . '.btn-conectar:hover { background: #bbf7d0; }'
    . '.btn-excluir { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }'
    . '.btn-excluir:hover { background: #fecaca; }'
    . '.btn-desconectar { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }'
    . '.btn-desconectar:hover { background: #fecaca; }'
    . '.com-table th, .com-table td { vertical-align: middle; }'
    . '.com-table thead { position: sticky; top: 0; z-index: 1; }'
    . '.modal { background: #fff; border-radius: 14px; box-shadow: 0 8px 32px #0003; padding: 36px 28px; min-width: 320px; max-width: 95vw; position: relative; }'
    . '.modal h3 { font-size: 1.25em; margin-bottom: 18px; }'
    . '.modal button { top: 14px; right: 18px; }'
    . '@media (max-width: 700px) { .com-table th, .com-table td { padding: 8px 2px; font-size: 0.95em; } .modal { padding: 18px 6px; } }'
    . '.canal-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid; }'
    . '.canal-card.ia { border-left-color: #10b981; }'
    . '.canal-card.humano { border-left-color: #3b82f6; }'
    . '.canal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }'
    . '.canal-title { font-size: 1.2em; font-weight: bold; margin: 0; }'
    . '.canal-type { padding: 4px 8px; border-radius: 4px; color: white; font-size: 0.8em; font-weight: bold; }'
    . '.canal-type.ia { background: #10b981; }'
    . '.canal-type.humano { background: #3b82f6; }'
    . '.canal-status { display: flex; align-items: center; gap: 10px; }'
    . '.status-indicator { width: 12px; height: 12px; border-radius: 50%; }'
    . '.status-indicator.online { background: #22c55e; }'
    . '.status-indicator.offline { background: #ef4444; }'
    . '.status-indicator.checking { background: #f59e0b; animation: pulse 2s infinite; }'
    . '@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }'
    . '.qr-container { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 15px 0; }'
    . '.qr-code { margin: 0 auto; }'
    . '</style>';
    
    echo '<link rel="stylesheet" href="assets/style.css">';
    echo '<h1 class="text-2xl font-bold mb-6">Central de Comunicação - WhatsApp</h1>';
    
    // Status do Render.com
    echo '<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">';
    echo '<h3 class="text-lg font-semibold text-blue-800 mb-2">🌐 Status do Serviço Render.com</h3>';
    echo '<div id="render-status" class="flex items-center gap-2">';
    echo '<div class="status-indicator checking"></div>';
    echo '<span class="text-blue-700">Verificando status do serviço...</span>';
    echo '</div>';
    echo '<div class="mt-2 text-sm text-blue-600">';
    echo 'URL: <a href="https://whatsapp-web-js-qy62.onrender.com" target="_blank" class="underline">https://whatsapp-web-js-qy62.onrender.com</a>';
    echo '</div>';
    echo '</div>';

    // Canais configurados
    echo '<div class="mb-6">';
    echo '<h2 class="text-lg font-semibold mb-4">📱 Canais WhatsApp Configurados</h2>';
    
    foreach ($CANAIS_CONFIG as $key => $canal) {
        $status_class = 'checking';
        $status_text = 'Verificando...';
        
        echo '<div class="canal-card ' . ($canal['tipo'] === 'IA' ? 'ia' : 'humano') . '">';
        echo '<div class="canal-header">';
        echo '<div>';
        echo '<h3 class="canal-title">' . htmlspecialchars($canal['nome_exibicao']) . '</h3>';
        echo '<p class="text-sm text-gray-600">' . htmlspecialchars($canal['descricao']) . '</p>';
        echo '</div>';
        echo '<div class="canal-status">';
        echo '<div class="status-indicator ' . $status_class . '"></div>';
        echo '<span class="text-sm font-medium">' . $status_text . '</span>';
        echo '<span class="canal-type ' . strtolower($canal['tipo']) . '">' . $canal['tipo'] . '</span>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">';
        echo '<div><strong>Número:</strong> ' . htmlspecialchars($canal['identificador']) . '</div>';
        echo '<div><strong>Porta:</strong> ' . $canal['porta'] . '</div>';
        echo '<div><strong>Status:</strong> <span class="canal-status-text" data-canal="' . $key . '">Verificando...</span></div>';
        echo '</div>';
        
        echo '<div class="mt-4 flex gap-2">';
        echo '<button onclick="conectarCanal(\'' . $key . '\')" class="btn-ac btn-conectar">🔗 Conectar</button>';
        echo '<button onclick="verificarQR(\'' . $key . '\')" class="btn-ac btn-editar">📱 Ver QR Code</button>';
        echo '<button onclick="verificarStatus(\'' . $key . '\')" class="btn-ac btn-editar">🔄 Atualizar Status</button>';
        echo '</div>';
        
        // Área para QR Code
        echo '<div id="qr-area-' . $key . '" class="qr-container" style="display: none;">';
        echo '<h4 class="font-semibold mb-2">📱 QR Code para ' . htmlspecialchars($canal['nome_exibicao']) . '</h4>';
        echo '<div id="qr-code-' . $key . '" class="qr-code"></div>';
        echo '<p class="text-sm text-gray-600 mt-2">Escaneie este QR Code com seu WhatsApp</p>';
        echo '</div>';
        
        echo '</div>';
    }
    echo '</div>';

    // Tabela de canais (legado)
    echo '<div class="mb-6">';
    echo '<h2 class="text-lg font-semibold mb-4">📊 Canais Registrados (Legado)</h2>';
    
    $res = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY data_conexao DESC, id DESC");
    echo '<div class="overflow-x-auto"><table class="com-table">';
    echo '<thead class="bg-gray-100"><tr>';
    echo '<th class="px-4 py-2">Tipo</th>';
    echo '<th class="px-4 py-2">Identificador</th>';
    echo '<th class="px-4 py-2">Nome de Exibição</th>';
    echo '<th class="px-4 py-2">Status</th>';
    echo '<th class="px-4 py-2">Última Sessão</th>';
    echo '<th class="px-4 py-2">Porta</th>';
    echo '<th class="px-4 py-2" style="text-align:center;">Ações</th>';
    echo '</tr></thead><tbody>';
    
    if ($res && $res->num_rows > 0) {
        $i = 0;
        while ($row = $res->fetch_assoc()) {
            $zebra = ($i++ % 2 == 0) ? ' style="background:#f3f4f6;"' : '';
            echo '<tr' . $zebra . '>';
            echo '<td class="px-4 py-2">' . htmlspecialchars(ucfirst($row['tipo'])) . '</td>';
            echo '<td class="px-4 py-2">' . htmlspecialchars($row['identificador']) . '</td>';
            echo '<td class="px-4 py-2">' . htmlspecialchars($row['nome_exibicao']) . '</td>';
            echo '<td class="px-4 py-2 canal-status-area status-verificando" data-canal-id="' . $row['id'] . '" data-porta="' . $row['porta'] . '"><span class="status-text">Verificando...</span></td>';
            echo '<td class="px-4 py-2 canal-data-conexao" data-canal-id="' . $row['id'] . '">-</td>';
            echo '<td class="px-4 py-2">' . ($row['porta'] ? htmlspecialchars($row['porta']) : '-') . '</td>';
            $acoes = '';
            $acoes .= '<div class="acoes-btn-group" style="display:flex;gap:8px;align-items:center;justify-content:center;">';
            $acoes .= '<div class="acoes-btn-area" data-canal-id="' . $row['id'] . '"></div>';
            $acoes .= '<a href="#" class="btn-ac btn-excluir btn-excluir-canal" data-canal-id="' . $row['id'] . '">Excluir</a>';
            $acoes .= '</div>';
            echo '<td class="px-4 py-2" style="text-align:center;">' . $acoes . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" class="text-center text-gray-400 py-4">Nenhum canal cadastrado ainda.</td></tr>';
    }
    
    echo '</tbody></table></div>';
    echo '</div>';

    // Debug Console
    echo '<div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px; margin: 25px 0;">';
    echo '<h3 style="color: #374151; margin-bottom: 15px;">🐛 Debug Console</h3>';
    echo '<div id="debug-console" style="background: rgba(0,0,0,0.8); color: #10b981; padding: 20px; border-radius: 8px; font-family: \'Courier New\', monospace; font-size: 0.9em; max-height: 300px; overflow-y: auto; border: 1px solid #374151;">';
    echo '[' . date('H:i:s') . '] ✅ Sistema PHP carregado com sucesso!<br>';
    echo '</div>';
    echo '<div style="text-align: center; margin-top: 15px;">';
    echo '<button onclick="document.getElementById(\'debug-console\').innerHTML = \'\';" style="background: #ef4444; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; margin: 5px;">🗑️ Limpar Console</button>';
    echo '<button onclick="verificarStatusRender()" style="background: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; margin: 5px;">🌐 Verificar Render.com</button>';
    echo '<button onclick="testarConexaoWhatsApp()" style="background: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; margin: 5px;">📱 Testar WhatsApp</button>';
    echo '</div>';
    echo '</div>';
}

// ===== JAVASCRIPT CONSOLIDADO NO FINAL =====
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Configuração
const API_URL = window.location.origin + '/loja-virtual-revenda/painel/api/whatsapp_render.php';
const CANAIS_CONFIG = <?php echo json_encode($CANAIS_CONFIG); ?>;

// Debug function
function debug(message, type = 'info') {
    const console = document.getElementById('debug-console');
    if (!console) return;
    
    const timestamp = new Date().toLocaleTimeString();
    const color = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6';
    console.innerHTML += `[${timestamp}] <span style="color: ${color};">${message}</span><br>`;
    console.scrollTop = console.scrollHeight;
}

// Função para fazer requisições para a API
async function makeApiRequest(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    
    // Adicionar dados extras
    Object.keys(data).forEach(key => {
        formData.append(key, data[key]);
    });
    
    debug(`🔗 Fazendo requisição para: ${API_URL}`, 'info');
    debug(`📋 Ação: ${action}`, 'info');
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        debug(`📡 Response status: ${response.status}`, 'info');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const text = await response.text();
        debug(`📄 Resposta bruta: ${text.substring(0, 200)}...`, 'info');
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            throw new Error(`Resposta não é JSON válido: ${text.substring(0, 100)}`);
        }
        
        debug(`✅ Resposta processada: ${JSON.stringify(result)}`, 'success');
        return result;
    } catch (error) {
        debug(`❌ Erro na requisição: ${error.message}`, 'error');
        throw new Error(`Erro na requisição: ${error.message}`);
    }
}

// Verificar status do Render.com
async function verificarStatusRender() {
    debug('🌐 Verificando status do Render.com...', 'info');
    
    try {
        const result = await makeApiRequest('health');
        
        if (result.success) {
            debug('✅ Render.com está online!', 'success');
            const statusElement = document.getElementById('render-status');
            if (statusElement) {
                statusElement.innerHTML = `
                    <div class="status-indicator online"></div>
                    <span class="text-green-700">Online</span>
                `;
            }
        } else {
            throw new Error(result.error || 'Serviço não respondeu corretamente');
        }
    } catch (error) {
        debug('❌ Render.com offline: ' + error.message, 'error');
        const statusElement = document.getElementById('render-status');
        if (statusElement) {
            statusElement.innerHTML = `
                <div class="status-indicator offline"></div>
                <span class="text-red-700">Offline</span>
            `;
        }
    }
}

// Conectar canal
async function conectarCanal(canalKey) {
    const canal = CANAIS_CONFIG[canalKey];
    debug(`🔗 Conectando canal ${canal.nome_exibicao} (porta ${canal.porta})...`, 'info');
    
    try {
        const result = await makeApiRequest('connect', {
            canal: canalKey,
            porta: canal.porta
        });
        
        if (result.success) {
            debug(`✅ Canal ${canal.nome_exibicao} (porta ${canal.porta}) conectado!`, 'success');
            verificarStatus(canalKey);
        } else {
            throw new Error(result.error || 'Erro desconhecido');
        }
    } catch (error) {
        debug(`❌ Erro ao conectar canal ${canal.nome_exibicao} (porta ${canal.porta}): ${error.message}`, 'error');
    }
}

// Verificar QR Code
async function verificarQR(canalKey) {
    const canal = CANAIS_CONFIG[canalKey];
    debug(`📱 Verificando QR Code para ${canal.nome_exibicao} (porta ${canal.porta})...`, 'info');
    
    try {
        // Tentar obter QR Code com retry
        let result = null;
        let attempts = 0;
        const maxAttempts = 3;
        
        while (attempts < maxAttempts) {
            attempts++;
            debug(`🔄 Tentativa ${attempts}/${maxAttempts} de obter QR Code para porta ${canal.porta}...`, 'info');
            
            result = await makeApiRequest('qr', { porta: canal.porta });
            
            if (result.success && result.qr && result.qr !== 'QR code não disponível') {
                debug(`✅ QR Code encontrado na tentativa ${attempts} para porta ${canal.porta}!`, 'success');
                break;
            } else {
                debug(`⚠️ Tentativa ${attempts}: QR Code não disponível para porta ${canal.porta}`, 'warning');
                
                if (attempts < maxAttempts) {
                    debug(`⏳ Aguardando 2 segundos antes da próxima tentativa...`, 'info');
                    await new Promise(resolve => setTimeout(resolve, 2000));
                }
            }
        }
        
        if (result.success && result.qr && result.qr !== 'QR code não disponível') {
            debug(`✅ QR Code encontrado para ${canal.nome_exibicao} (porta ${canal.porta})!`, 'success');
            
            // Exibir QR Code
            const qrArea = document.getElementById(`qr-area-${canalKey}`);
            const qrCode = document.getElementById(`qr-code-${canalKey}`);
            
            if (qrArea && qrCode) {
                qrArea.style.display = 'block';
                qrCode.innerHTML = '';
                
                // Gerar QR Code
                new QRCode(qrCode, {
                    text: result.qr,
                    width: 200,
                    height: 200,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                debug(`🎯 QR Code gerado para ${canal.nome_exibicao}`, 'success');
            } else {
                debug(`⚠️ Elementos QR não encontrados para ${canalKey}`, 'warning');
            }
        } else {
            debug(`❌ QR Code não disponível para ${canal.nome_exibicao} após ${maxAttempts} tentativas`, 'error');
            
            const qrArea = document.getElementById(`qr-area-${canalKey}`);
            if (qrArea) {
                qrArea.style.display = 'block';
                qrArea.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 20px;">❌ QR Code não disponível. Tente novamente em alguns segundos.</div>';
            }
        }
    } catch (error) {
        debug(`❌ Erro ao verificar QR Code para ${canal.nome_exibicao}: ${error.message}`, 'error');
        
        const qrArea = document.getElementById(`qr-area-${canalKey}`);
        if (qrArea) {
            qrArea.style.display = 'block';
            qrArea.innerHTML = `<div style="color: #ef4444; text-align: center; padding: 20px;">❌ Erro: ${error.message}</div>`;
        }
    }
}

// Verificar status do canal
async function verificarStatus(canalKey) {
    const canal = CANAIS_CONFIG[canalKey];
    debug(`🔄 Verificando status de ${canal.nome_exibicao} (porta ${canal.porta})...`, 'info');
    
    try {
        const result = await makeApiRequest('status', { porta: canal.porta });
        
        const statusElement = document.querySelector(`[data-canal="${canalKey}"]`);
        if (statusElement) {
            if (result.success && result.connected) {
                statusElement.textContent = 'Conectado';
                statusElement.className = 'text-green-600 font-bold';
            } else {
                statusElement.textContent = 'Desconectado';
                statusElement.className = 'text-red-600 font-bold';
            }
        }
        
        debug(`✅ Status de ${canal.nome_exibicao} (porta ${canal.porta}): ${result.success && result.connected ? 'Conectado' : 'Desconectado'}`, 'success');
    } catch (error) {
        debug(`❌ Erro ao verificar status de ${canal.nome_exibicao}: ${error.message}`, 'error');
    }
}

// Testar conexão WhatsApp
async function testarConexaoWhatsApp() {
    debug('📱 Testando conexão WhatsApp...', 'info');
    
    try {
        const result = await makeApiRequest('test');
        
        if (result.success) {
            debug('✅ Conexão WhatsApp OK!', 'success');
        } else {
            debug('⚠️ Conexão WhatsApp com problemas: ' + (result.error || 'Erro desconhecido'), 'warning');
        }
    } catch (error) {
        debug('❌ Erro ao testar conexão WhatsApp: ' + error.message, 'error');
    }
}

// Enviar mensagem de teste
async function enviarMensagemTeste(canalKey) {
    const canal = CANAIS_CONFIG[canalKey];
    debug(`📤 Enviando mensagem de teste para ${canal.nome_exibicao}...`, 'info');
    
    try {
        const result = await makeApiRequest('send_message', {
            to: canal.identificador,
            message: 'Teste de conexão - ' + new Date().toLocaleString()
        });
        
        if (result.success) {
            debug(`✅ Mensagem enviada com sucesso para ${canal.nome_exibicao}!`, 'success');
        } else {
            throw new Error(result.error || 'Erro desconhecido');
        }
    } catch (error) {
        debug(`❌ Erro ao enviar mensagem: ${error.message}`, 'error');
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    debug('🚀 Sistema inicializado!', 'success');
    debug(`🔗 API URL: ${API_URL}`, 'info');
    verificarStatusRender();
    
    // Verificar status dos canais a cada 30 segundos
    setInterval(() => {
        if (CANAIS_CONFIG && typeof CANAIS_CONFIG === 'object') {
            Object.keys(CANAIS_CONFIG).forEach(canalKey => {
                verificarStatus(canalKey);
            });
        }
    }, 30000);
    
    // Adicionar botões de teste para cada canal
    if (CANAIS_CONFIG && typeof CANAIS_CONFIG === 'object') {
        Object.keys(CANAIS_CONFIG).forEach(canalKey => {
            const canal = CANAIS_CONFIG[canalKey];
            const card = document.querySelector(`.canal-card.${canal.tipo.toLowerCase()}`);
            if (card) {
                const buttonGroup = card.querySelector('.flex.gap-2');
                if (buttonGroup) {
                    const testButton = document.createElement('button');
                    testButton.className = 'btn-ac btn-editar';
                    testButton.onclick = () => enviarMensagemTeste(canalKey);
                    testButton.textContent = '📤 Testar Mensagem';
                    buttonGroup.appendChild(testButton);
                }
            }
        });
    }
});
</script>
