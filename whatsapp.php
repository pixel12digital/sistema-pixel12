<?php
// Cabeçalhos anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <title>🚀 WhatsApp Connect - Solução DEFINITIVA</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #25D366, #128C7E); 
            color: white; margin: 0; padding: 20px; min-height: 100vh;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .hero { 
            background: rgba(0,0,0,0.2); padding: 30px; 
            border-radius: 15px; text-align: center; margin-bottom: 30px;
            backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);
        }
        .status-box { 
            background: rgba(255,255,255,0.1); padding: 20px; 
            border-radius: 10px; margin: 20px 0; border-left: 4px solid #22c55e;
            transition: all 0.3s ease;
        }
        .error { border-left-color: #ef4444; background: rgba(239,68,68,0.2); }
        .warning { border-left-color: #f59e0b; background: rgba(245,158,11,0.2); }
        .success { border-left-color: #22c55e; background: rgba(34,197,94,0.2); }
        .btn { 
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white; padding: 15px 30px; border: none; 
            border-radius: 8px; cursor: pointer; font-size: 1.1em; 
            margin: 10px; transition: all 0.3s; font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn:hover { 
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .btn-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .btn-blue:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .btn-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-red:hover { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .debug-area { 
            background: rgba(0,0,0,0.4); padding: 20px; 
            border-radius: 8px; font-family: 'Courier New', monospace; 
            font-size: 0.9em; max-height: 300px; overflow-y: auto; 
            margin: 15px 0; border: 1px solid rgba(255,255,255,0.2);
        }
        .input-group { margin: 15px 0; }
        .input-group input { 
            padding: 12px; border-radius: 6px; border: none; 
            width: 250px; margin-right: 10px; font-size: 1em;
        }
        .qr-section {
            background: white; color: black; padding: 30px; 
            border-radius: 15px; text-align: center; margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .modal { 
            display: none; position: fixed; top: 0; left: 0; 
            width: 100%; height: 100%; background: rgba(0,0,0,0.85); 
            z-index: 10000; backdrop-filter: blur(5px);
        }
        .modal-content { 
            background: white; color: black; margin: 3% auto; 
            padding: 30px; border-radius: 20px; max-width: 650px; 
            position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .close { 
            position: absolute; top: 15px; right: 20px; 
            font-size: 28px; cursor: pointer; color: #666; transition: color 0.3s;
        }
        .close:hover { color: #ef4444; }
        .config-info { 
            background: rgba(0,0,0,0.3); padding: 15px; 
            border-radius: 8px; margin: 15px 0; font-family: monospace;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>🚀 WhatsApp Connect</h1>
            <p style="font-size: 1.3em; margin: 10px 0;">Solução DEFINITIVA | Sistema Diagnóstico Completo</p>
            <div class="config-info">
                🌐 <strong>VPS:</strong> 212.85.11.238:3000<br>
                ⏰ <strong>Carregado:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
                🚀 <strong>Servidor:</strong> <?php echo $_SERVER['SERVER_NAME']; ?><br>
                📱 <strong>Build:</strong> v2.0.DEFINITIVO
            </div>
        </div>

        <!-- Status VPS -->
        <div id="vps-status" class="status-box">
            <h3>🔧 Status VPS Direct</h3>
            <div id="vps-info">⏳ Verificando conectividade...</div>
        </div>

        <!-- Status API Robô -->
        <div id="api-status" class="status-box">
            <h3>🤖 Status API Robô</h3>
            <div id="api-info">⏳ Aguardando teste VPS...</div>
        </div>

        <!-- Status WhatsApp -->
        <div id="whatsapp-status" class="status-box">
            <h3>📱 Status WhatsApp</h3>
            <div id="whatsapp-info">⏳ Aguardando API...</div>
        </div>

        <!-- Botões Principais -->
        <div style="text-align: center; margin: 40px 0;">
            <button class="btn" onclick="executarDiagnosticoCompleto()">
                🧪 Diagnóstico Completo
            </button>
            <button class="btn btn-blue" onclick="conectarWhatsApp()">
                📱 Conectar WhatsApp
            </button>
            <button class="btn btn-blue" onclick="abrirChatOriginal()">
                💬 Abrir Chat Original
            </button>
        </div>

        <!-- Testes Manuais -->
        <div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px; margin: 25px 0;">
            <h3>🔧 Testes Manuais Avançados</h3>
            <div class="input-group">
                <input type="text" id="endpoint-input" placeholder="/status" value="/status">
                <button class="btn btn-blue" onclick="testarEndpoint()">🔍 Testar Endpoint</button>
            </div>
            <div class="input-group">
                <button class="btn" onclick="testarEndpoint('/status')">GET /status</button>
                <button class="btn" onclick="testarEndpoint('/qr')">GET /qr</button>
                <button class="btn" onclick="testarEndpoint('/sessions')">GET /sessions</button>
                <button class="btn" onclick="testarEndpoint('/ready')">GET /ready</button>
            </div>
        </div>

        <!-- Debug Console -->
        <div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px;">
            <h3>🐛 Console de Debug Avançado</h3>
            <div id="debug-console" class="debug-area">[<?php echo date('H:i:s'); ?>] Sistema PHP carregado com sucesso!<br></div>
            <div style="text-align: center; margin-top: 15px;">
                <button class="btn btn-red" onclick="limparDebug()">🗑️ Limpar Console</button>
                <button class="btn btn-blue" onclick="salvarLog()">💾 Salvar Log</button>
            </div>
        </div>

        <!-- Modal QR Code -->
        <div id="modal-qr" class="modal">
            <div class="modal-content">
                <span class="close" onclick="fecharModal()">&times;</span>
                <h3 style="text-align: center; color: #25D366;">📱 Conectar WhatsApp</h3>
                <div id="qr-display" class="qr-section">
                    <div class="pulse">⏳ Preparando QR Code...</div>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn" onclick="atualizarQR()">🔄 Novo QR</button>
                    <button class="btn btn-blue" onclick="testarConexaoAtiva()">🔍 Testar Conexão</button>
                    <button class="btn btn-red" onclick="fecharModal()">❌ Fechar</button>
                </div>
                <div id="status-conexao" style="text-align: center; margin: 15px 0; font-weight: bold;"></div>
            </div>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <script>
        // =================== CONFIGURAÇÃO HARDCODED ===================
        const VPS_URL = 'http://212.85.11.238:3000';
        const CURRENT_TIME = new Date().toISOString();
        const SERVER_NAME = '<?php echo $_SERVER['SERVER_NAME']; ?>';
        
        console.log('🚀 WhatsApp Definitivo System Started');
        console.log('🌐 VPS URL:', VPS_URL);
        console.log('⏰ Load Time:', CURRENT_TIME);
        console.log('🖥️ Server:', SERVER_NAME);
        
        // =================== VARIÁVEIS GLOBAIS ===================
        let qrCodeInstance = null;
        let monitorTimer = null;
        let tentativasReconexao = 0;
        let logCompleto = [];
        
        // =================== FUNÇÕES DE DEBUG ===================
        
        function debug(message, type = 'info') {
            const console_el = document.getElementById('debug-console');
            const timestamp = new Date().toLocaleTimeString();
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '🔍';
            const color = type === 'error' ? 'color: #ff6b6b;' : type === 'success' ? 'color: #51cf66;' : type === 'warning' ? 'color: #ffd43b;' : 'color: #74c0fc;';
            
            const logEntry = `[${timestamp}] ${icon} ${message}`;
            console_el.innerHTML += `<div style="${color}">${logEntry}</div>`;
            console_el.scrollTop = console_el.scrollHeight;
            
            // Salvar no log completo
            logCompleto.push({timestamp, type, message});
            
            // Console do navegador
            console.log(logEntry);
        }
        
        function limparDebug() {
            document.getElementById('debug-console').innerHTML = '';
            logCompleto = [];
        }
        
        function salvarLog() {
            const logText = logCompleto.map(entry => 
                `[${entry.timestamp}] ${entry.type.toUpperCase()}: ${entry.message}`
            ).join('\n');
            
            const blob = new Blob([logText], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `whatsapp-debug-${new Date().getTime()}.log`;
            a.click();
            URL.revokeObjectURL(url);
        }
        
        // =================== FUNÇÕES PRINCIPAIS ===================
        
        function executarDiagnosticoCompleto() {
            debug('🧪 Iniciando diagnóstico completo do sistema...', 'info');
            limparStatus();
            
            // Sequência de testes
            setTimeout(() => testarVPS(), 500);
        }
        
        function limparStatus() {
            document.getElementById('vps-status').className = 'status-box';
            document.getElementById('api-status').className = 'status-box';
            document.getElementById('whatsapp-status').className = 'status-box';
            
            document.getElementById('vps-info').innerHTML = '⏳ Preparando teste...';
            document.getElementById('api-info').innerHTML = '⏳ Aguardando...';
            document.getElementById('whatsapp-info').innerHTML = '⏳ Aguardando...';
        }
        
        function testarVPS() {
            const statusBox = document.getElementById('vps-status');
            const infoBox = document.getElementById('vps-info');
            
            statusBox.className = 'status-box';
            infoBox.innerHTML = '<div class="pulse">⏳ Testando conectividade VPS...</div>';
            debug('📡 Iniciando teste de conectividade VPS...', 'info');
            
            const startTime = Date.now();
            const testUrl = VPS_URL + '/status?diagnostic=' + Math.random();
            
            fetch(testUrl, {
                method: 'GET',
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                const responseTime = Date.now() - startTime;
                debug(`📡 VPS respondeu: HTTP ${response.status} em ${responseTime}ms`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json().then(data => ({data, responseTime, status: response.status}));
            })
            .then(({data, responseTime, status}) => {
                statusBox.className = 'status-box success';
                infoBox.innerHTML = `
                    <div style="color: #22c55e; font-weight: bold; font-size: 1.2em;">✅ VPS ONLINE</div>
                    <div>📡 URL: ${VPS_URL}</div>
                    <div>⚡ Latência: ${responseTime}ms</div>
                    <div>📊 Status HTTP: ${status}</div>
                    <div>🔍 Dados: ${JSON.stringify(data).substring(0, 150)}...</div>
                    <div>🕒 Teste realizado: ${new Date().toLocaleTimeString()}</div>
                `;
                
                debug('✅ VPS funcionando perfeitamente!', 'success');
                debug(`📊 Dados recebidos: ${JSON.stringify(data)}`, 'info');
                
                // Próximo teste
                setTimeout(() => testarAPI(), 1500);
            })
            .catch(error => {
                statusBox.className = 'status-box error';
                infoBox.innerHTML = `
                    <div style="color: #ef4444; font-weight: bold; font-size: 1.2em;">❌ VPS OFFLINE</div>
                    <div>🚨 Erro: ${error.message}</div>
                    <div>🔧 URL testada: ${testUrl}</div>
                    <div>🕒 Falha em: ${new Date().toLocaleTimeString()}</div>
                `;
                
                debug(`❌ Erro na VPS: ${error.message}`, 'error');
                debug(`🔧 URL que falhou: ${testUrl}`, 'error');
            });
        }
        
        function testarAPI() {
            const statusBox = document.getElementById('api-status');
            const infoBox = document.getElementById('api-info');
            
            statusBox.className = 'status-box';
            infoBox.innerHTML = '<div class="pulse">🤖 Testando endpoints da API...</div>';
            debug('🤖 Iniciando teste completo da API do robô...', 'info');
            
            const endpoints = [
                { path: '/status', name: 'Status Geral' },
                { path: '/sessions', name: 'Sessões Ativas' },
                { path: '/qr', name: 'QR Code' },
                { path: '/ready', name: 'Prontidão' }
            ];
            
            let sucessos = 0;
            let total = endpoints.length;
            let resultados = [];
            
            endpoints.forEach((endpoint, index) => {
                const url = VPS_URL + endpoint.path + '?api_test=' + Math.random();
                debug(`🔍 Testando ${endpoint.name}: ${endpoint.path}`, 'info');
                
                fetch(url, {cache: 'no-cache'})
                    .then(response => {
                        const resultado = {
                            endpoint: endpoint.path,
                            nome: endpoint.name,
                            status: response.status,
                            ok: response.ok
                        };
                        
                        debug(`📡 ${endpoint.name}: HTTP ${response.status}`, response.ok ? 'success' : 'error');
                        
                        if (response.ok) {
                            sucessos++;
                            return response.json().then(data => {
                                resultado.data = data;
                                return resultado;
                            });
                        } else {
                            throw new Error(`HTTP ${response.status}`);
                        }
                    })
                    .then(resultado => {
                        resultados.push(resultado);
                        debug(`✅ ${resultado.nome} OK: ${JSON.stringify(resultado.data).substring(0, 100)}...`, 'success');
                        
                        // Verificar se todos foram testados
                        if (resultados.length + (total - sucessos) >= total) {
                            atualizarStatusAPI(sucessos, total, resultados);
                            if (sucessos > 0) {
                                setTimeout(() => testarWhatsApp(), 1500);
                            }
                        }
                    })
                    .catch(error => {
                        debug(`❌ ${endpoint.name} falhou: ${error.message}`, 'error');
                        
                        // Verificar se todos foram testados
                        if (resultados.length + (total - sucessos) >= total) {
                            atualizarStatusAPI(sucessos, total, resultados);
                        }
                    });
            });
        }
        
        function atualizarStatusAPI(sucessos, total, resultados) {
            const statusBox = document.getElementById('api-status');
            const infoBox = document.getElementById('api-info');
            
            if (sucessos === total) {
                statusBox.className = 'status-box success';
                infoBox.innerHTML = `
                    <div style="color: #22c55e; font-weight: bold; font-size: 1.2em;">✅ API ROBÔ 100% FUNCIONAL</div>
                    <div>🤖 Endpoints testados: ${total}</div>
                    <div>✅ Sucessos: ${sucessos}</div>
                    <div>🎯 Status: Sistema completamente operacional</div>
                    <div>📋 Endpoints: ${resultados.map(r => r.endpoint).join(', ')}</div>
                `;
                debug('🎉 API do robô funcionando 100%!', 'success');
            } else if (sucessos > 0) {
                statusBox.className = 'status-box warning';
                infoBox.innerHTML = `
                    <div style="color: #f59e0b; font-weight: bold; font-size: 1.2em;">⚠️ API PARCIALMENTE FUNCIONAL</div>
                    <div>🤖 Sucessos: ${sucessos}/${total}</div>
                    <div>🔧 Alguns endpoints com problema</div>
                    <div>✅ Funcionando: ${resultados.map(r => r.endpoint).join(', ')}</div>
                `;
                debug('⚠️ API do robô funcionando parcialmente', 'warning');
            } else {
                statusBox.className = 'status-box error';
                infoBox.innerHTML = `
                    <div style="color: #ef4444; font-weight: bold; font-size: 1.2em;">❌ API ROBÔ COMPLETAMENTE OFFLINE</div>
                    <div>🚨 Nenhum endpoint respondeu corretamente</div>
                    <div>🔧 Verifique configuração da API</div>
                `;
                debug('❌ API do robô completamente offline', 'error');
            }
        }
        
        function testarWhatsApp() {
            const statusBox = document.getElementById('whatsapp-status');
            const infoBox = document.getElementById('whatsapp-info');
            
            statusBox.className = 'status-box';
            infoBox.innerHTML = '<div class="pulse">📱 Verificando status WhatsApp...</div>';
            debug('📱 Verificando status de conexão do WhatsApp...', 'info');
            
            const checkUrl = VPS_URL + '/status?whatsapp_detailed=' + Math.random();
            
            fetch(checkUrl, {
                method: 'GET',
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(data => {
                debug(`📱 Status WhatsApp recebido: ${JSON.stringify(data)}`, 'info');
                
                if (data.ready) {
                    statusBox.className = 'status-box success';
                    infoBox.innerHTML = `
                        <div style="color: #22c55e; font-weight: bold; font-size: 1.2em;">✅ WHATSAPP CONECTADO</div>
                        <div>📱 Número: ${data.number || 'Disponível'}</div>
                        <div>🔗 Status: Pronto para envio/recebimento</div>
                        <div>⏰ Última sessão: ${data.lastSession ? new Date(data.lastSession).toLocaleString() : 'Ativa agora'}</div>
                        <div>🎯 Sistema: 100% Operacional para mensagens</div>
                    `;
                    debug('🎉 WhatsApp conectado e operacional!', 'success');
                } else {
                    statusBox.className = 'status-box warning';
                    infoBox.innerHTML = `
                        <div style="color: #f59e0b; font-weight: bold; font-size: 1.2em;">🔴 WHATSAPP DESCONECTADO</div>
                        <div>📱 Ação necessária: Escanear QR Code</div>
                        <div>🔧 Clique em "Conectar WhatsApp" para iniciar</div>
                        <div>⚡ VPS e API funcionando normalmente</div>
                        <div>🎯 Pronto para conexão via QR</div>
                    `;
                    debug('⚠️ WhatsApp desconectado - QR Code necessário', 'warning');
                }
            })
            .catch(error => {
                statusBox.className = 'status-box error';
                infoBox.innerHTML = `
                    <div style="color: #ef4444; font-weight: bold; font-size: 1.2em;">❌ ERRO WHATSAPP</div>
                    <div>🚨 ${error.message}</div>
                `;
                debug(`❌ Erro ao verificar WhatsApp: ${error.message}`, 'error');
            });
        }
        
        function testarEndpoint(endpoint = null) {
            if (!endpoint) {
                endpoint = document.getElementById('endpoint-input').value;
            }
            
            if (!endpoint.startsWith('/')) {
                endpoint = '/' + endpoint;
            }
            
            const url = VPS_URL + endpoint + '?manual_test=' + Math.random();
            debug(`🔧 Teste manual iniciado: ${endpoint}`, 'info');
            
            fetch(url, {cache: 'no-cache'})
                .then(response => {
                    debug(`📡 ${endpoint}: HTTP ${response.status}`, response.ok ? 'success' : 'error');
                    return response.json().then(data => ({data, status: response.status}));
                })
                .then(({data, status}) => {
                    debug(`✅ ${endpoint} resposta completa:`, 'success');
                    debug(JSON.stringify(data, null, 2), 'info');
                })
                .catch(error => {
                    debug(`❌ ${endpoint} falhou: ${error.message}`, 'error');
                });
        }
        
        function conectarWhatsApp() {
            debug('📱 Iniciando processo de conexão WhatsApp...', 'info');
            document.getElementById('modal-qr').style.display = 'block';
            document.getElementById('qr-display').innerHTML = '<div class="pulse">⏳ Preparando QR Code...</div>';
            document.getElementById('status-conexao').innerHTML = '';
            
            carregarQRCode();
            
            // Monitorar conexão a cada 3 segundos
            monitorTimer = setInterval(monitorarConexaoWhatsApp, 3000);
        }
        
        function carregarQRCode() {
            const qrArea = document.getElementById('qr-display');
            debug('📱 Solicitando QR Code da VPS...', 'info');
            
            const qrUrl = VPS_URL + '/qr?generate=' + Date.now();
            
            fetch(qrUrl, {
                method: 'GET',
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(data => {
                debug(`📱 QR Code recebido: ${JSON.stringify(data)}`, 'info');
                
                qrArea.innerHTML = '';
                
                if (data.qr) {
                    // Criar QR Code
                    qrCodeInstance = new QRCode(qrArea, {
                        text: data.qr,
                        width: 320,
                        height: 320,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    
                    document.getElementById('status-conexao').innerHTML = 
                        '<div style="color: #3b82f6; font-size: 1.1em;">📱 Escaneie o QR Code com seu WhatsApp</div>';
                    
                    debug('✅ QR Code exibido com sucesso', 'success');
                } else if (data.ready) {
                    qrArea.innerHTML = `
                        <div style="color: green; font-size: 2em; padding: 60px;">
                            ✅ JÁ CONECTADO!<br>
                            <small style="font-size: 0.5em;">WhatsApp pronto para uso</small>
                        </div>
                    `;
                    document.getElementById('status-conexao').innerHTML = 
                        '<div style="color: green; font-size: 1.2em;">🎉 WhatsApp já está conectado!</div>';
                    debug('🎉 WhatsApp já conectado!', 'success');
                } else {
                    qrArea.innerHTML = '<div style="color: red; padding: 60px; font-size: 1.2em;">❌ QR Code indisponível</div>';
                    document.getElementById('status-conexao').innerHTML = 
                        '<div style="color: red;">❌ Erro ao gerar QR Code</div>';
                    debug('❌ QR Code não disponível', 'error');
                }
            })
            .catch(error => {
                debug(`❌ Erro ao carregar QR: ${error.message}`, 'error');
                qrArea.innerHTML = `
                    <div style="color: red; padding: 60px;">
                        ❌ Erro ao carregar QR<br>
                        <small>${error.message}</small>
                    </div>
                `;
            });
        }
        
        function monitorarConexaoWhatsApp() {
            fetch(VPS_URL + '/status?connection_monitor=' + Date.now(), {cache: 'no-cache'})
                .then(response => response.json())
                .then(data => {
                    if (data.ready) {
                        debug('🎉 WhatsApp conectado com sucesso!', 'success');
                        
                        // Parar monitoramento
                        if (monitorTimer) {
                            clearInterval(monitorTimer);
                            monitorTimer = null;
                        }
                        
                        // Mostrar sucesso
                        document.getElementById('qr-display').innerHTML = `
                            <div style="color: green; font-size: 2.2em; padding: 60px; text-align: center;">
                                🎉 CONECTADO COM SUCESSO!<br>
                                <small style="font-size: 0.4em;">WhatsApp pronto para uso</small>
                            </div>
                        `;
                        
                        document.getElementById('status-conexao').innerHTML = 
                            '<div style="color: green; font-size: 1.3em;">✅ Conexão estabelecida com sucesso!</div>';
                        
                        // Fechar modal automaticamente após 4 segundos
                        setTimeout(() => {
                            fecharModal();
                            executarDiagnosticoCompleto(); // Atualizar status
                        }, 4000);
                    }
                })
                .catch(error => {
                    tentativasReconexao++;
                    if (tentativasReconexao > 10) {
                        debug(`⚠️ Monitor: Muitas tentativas falharam (${tentativasReconexao})`, 'warning');
                    }
                });
        }
        
        function testarConexaoAtiva() {
            debug('🔍 Testando conexão ativa...', 'info');
            fetch(VPS_URL + '/status?active_test=' + Date.now(), {cache: 'no-cache'})
                .then(response => response.json())
                .then(data => {
                    debug(`🔍 Teste ativo: ${JSON.stringify(data)}`, 'info');
                    document.getElementById('status-conexao').innerHTML = 
                        `<div style="color: #3b82f6;">Status: ${data.ready ? 'Conectado' : 'Aguardando QR'}</div>`;
                })
                .catch(error => {
                    debug(`❌ Teste ativo falhou: ${error.message}`, 'error');
                });
        }
        
        function atualizarQR() {
            debug('🔄 Atualizando QR Code...', 'info');
            carregarQRCode();
        }
        
        function fecharModal() {
            document.getElementById('modal-qr').style.display = 'none';
            
            if (monitorTimer) {
                clearInterval(monitorTimer);
                monitorTimer = null;
            }
            
            qrCodeInstance = null;
            tentativasReconexao = 0;
            debug('❌ Modal QR Code fechado', 'info');
        }
        
        function abrirChatOriginal() {
            debug('💬 Redirecionando para chat original...', 'info');
            window.open('painel/comunicacao.php', '_blank');
        }
        
        // =================== EVENT LISTENERS ===================
        
        // Fechar modal clicando fora
        window.onclick = function(event) {
            if (event.target == document.getElementById('modal-qr')) {
                fecharModal();
            }
        }
        
        // Auto-verificação a cada 3 minutos
        setInterval(() => {
            debug('🔄 Auto-verificação do sistema...', 'info');
            testarWhatsApp();
        }, 180000);
        
        // =================== INICIALIZAÇÃO ===================
        
        // Auto-start após carregar
        document.addEventListener('DOMContentLoaded', function() {
            debug('📱 DOM carregado, iniciando diagnóstico automático...', 'info');
            setTimeout(executarDiagnosticoCompleto, 1000);
        });
        
        debug('🎯 Sistema WhatsApp Definitivo carregado!', 'success');
        debug('📱 Versão: 2.0.DEFINITIVO', 'info');
        debug('🌐 VPS: 212.85.11.238:3000', 'info');
        debug('⚡ Status: Pronto para diagnóstico completo', 'success');
    </script>
</body>
</html> 