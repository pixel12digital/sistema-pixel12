<!DOCTYPE html>
<html>
<head>
    <title>🔍 Descobrir Endpoints VPS WhatsApp</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%); color: white; }
        .container { max-width: 800px; margin: 0 auto; background: rgba(255,255,255,0.1); padding: 30px; border-radius: 15px; }
        .endpoint-card { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 15px 0; border-left: 4px solid #10b981; }
        .success { border-left-color: #10b981; background: rgba(16, 185, 129, 0.1); }
        .error { border-left-color: #ef4444; background: rgba(239, 68, 68, 0.1); }
        .warning { border-left-color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
        .btn { background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 10px; font-weight: bold; }
        .btn-blue { background: #3b82f6; }
        .code { background: rgba(0,0,0,0.5); padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .result { white-space: pre-wrap; font-family: monospace; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Descobrir Endpoints da API WhatsApp</h1>
        <p>Baseado no relatório, sabemos que <strong>/status</strong> e <strong>/sessions</strong> funcionam, mas <strong>/qr</strong> retorna 404. Vamos descobrir os endpoints corretos:</p>

        <div class="endpoint-card">
            <h3>📋 Status do Diagnóstico Anterior</h3>
            <div class="code">
✅ /status → HTTP 200 (Funcionando)
✅ /sessions → HTTP 200 (Funcionando)  
❌ /qr → HTTP 404 (Não encontrado)
⚠️ Latência: 1.144 segundos
            </div>
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <button class="btn" onclick="descobrirEndpoints()">🚀 Descobrir Todos os Endpoints</button>
            <button class="btn btn-blue" onclick="testarEndpointsQR()">🔳 Testar Apenas Endpoints QR</button>
        </div>

        <div id="results"></div>

        <div class="endpoint-card warning">
            <h3>💡 Endpoints Comuns para Testar</h3>
            <div class="code">
Endpoints QR Possíveis:
• /qr
• /generate-qr
• /session/qr
• /whatsapp/qr  
• /session/default/qr
• /api/qr
• /qrcode

Endpoints de Informação:
• /status
• /sessions
• /info
• /health
• /api
• /docs
            </div>
        </div>
    </div>

    <script>
        const VPS_URL = 'http://212.85.11.238:3000';
        const AJAX_PROXY = 'ajax_whatsapp.php';

        function log(message, type = 'info') {
            const results = document.getElementById('results');
            const div = document.createElement('div');
            div.className = `endpoint-card ${type}`;
            div.innerHTML = `<strong>[${new Date().toLocaleTimeString()}]</strong><br>${message}`;
            results.appendChild(div);
            results.scrollTop = results.scrollHeight;
        }

        async function testarEndpoint(endpoint) {
            try {
                const response = await fetch(`${AJAX_PROXY}?test_endpoint=${encodeURIComponent(endpoint)}&_=${Date.now()}`);
                const data = await response.text();
                return {
                    success: response.ok,
                    status: response.status,
                    data: data
                };
            } catch (error) {
                return {
                    success: false,
                    error: error.message
                };
            }
        }

        async function descobrirEndpoints() {
            log('🚀 Iniciando descoberta completa de endpoints...', 'info');
            
            const endpoints = [
                // Endpoints já testados (confirmação)
                '/status',
                '/sessions',
                '/qr',
                
                // Endpoints QR alternativos
                '/generate-qr',
                '/session/qr',
                '/whatsapp/qr',
                '/session/default/qr',
                '/api/qr',
                '/qrcode',
                '/get-qr',
                '/qr-code',
                
                // Endpoints informativos
                '/info',
                '/health',
                '/api',
                '/docs',
                '/help',
                '/',
                
                // Endpoints de sessão
                '/session',
                '/session/default',
                '/session/list',
                '/sessions/default',
                
                // Endpoints WhatsApp específicos
                '/whatsapp',
                '/whatsapp/status',
                '/whatsapp/session',
                '/send',
                '/message'
            ];

            let funcionando = [];
            let naoFuncionando = [];

            for (const endpoint of endpoints) {
                log(`🔍 Testando: ${endpoint}`, 'info');
                
                const result = await testarViaCurl(endpoint);
                
                if (result.success && result.status === 200) {
                    funcionando.push({
                        endpoint: endpoint,
                        status: result.status,
                        preview: result.data?.substring(0, 100) || ''
                    });
                    log(`✅ ${endpoint} → HTTP ${result.status}`, 'success');
                } else {
                    naoFuncionando.push({
                        endpoint: endpoint,
                        status: result.status || 'ERROR',
                        error: result.error || 'HTTP Error'
                    });
                    log(`❌ ${endpoint} → HTTP ${result.status || 'ERROR'}`, 'error');
                }
                
                // Pequena pausa para não sobrecarregar
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            // Resumo final
            log(`\n📊 RESUMO FINAL:\n\n✅ ENDPOINTS FUNCIONANDO (${funcionando.length}):\n${funcionando.map(e => `• ${e.endpoint} (HTTP ${e.status})`).join('\n')}\n\n❌ ENDPOINTS NÃO FUNCIONANDO (${naoFuncionando.length}):\n${naoFuncionando.map(e => `• ${e.endpoint} (${e.status})`).join('\n')}`, 'info');
        }

        async function testarEndpointsQR() {
            log('🔳 Testando apenas endpoints relacionados ao QR Code...', 'info');
            
            const qrEndpoints = [
                '/qr',
                '/generate-qr',
                '/session/qr',
                '/whatsapp/qr',
                '/session/default/qr',
                '/api/qr',
                '/qrcode',
                '/get-qr',
                '/qr-code'
            ];

            for (const endpoint of qrEndpoints) {
                const result = await testarViaCurl(endpoint);
                
                if (result.success && result.status === 200) {
                    log(`✅ QR ENCONTRADO! ${endpoint} → HTTP ${result.status}\nResposta: ${result.data?.substring(0, 200)}...`, 'success');
                } else {
                    log(`❌ ${endpoint} → HTTP ${result.status || 'ERROR'}`, 'error');
                }
                
                await new Promise(resolve => setTimeout(resolve, 300));
            }
        }

        async function testarViaCurl(endpoint) {
            try {
                // Usar o proxy PHP para fazer a requisição
                const formData = new FormData();
                formData.append('action', 'raw_request');
                formData.append('endpoint', endpoint);
                
                const response = await fetch(AJAX_PROXY + '?_=' + Date.now(), {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.text();
                    return {
                        success: true,
                        status: response.status,
                        data: data
                    };
                } else {
                    return {
                        success: false,
                        status: response.status,
                        error: `HTTP ${response.status}`
                    };
                }
            } catch (error) {
                return {
                    success: false,
                    error: error.message
                };
            }
        }

        // Auto-executar teste rápido ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            log('📋 Sistema de descoberta de endpoints carregado. Use os botões acima para iniciar os testes.', 'info');
        });
    </script>
</body>
</html> 