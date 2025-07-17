<?php
/**
 * Script para reiniciar o servidor WhatsApp na VPS
 * Aplica as mudanças e reinicia o serviço
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔄 Reiniciar Servidor VPS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .step { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .log-area { background: #000; color: #0f0; padding: 15px; border-radius: 5px; font-family: monospace; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Reiniciar Servidor WhatsApp VPS</h1>
        
        <div class="step info">
            <h3>📋 Informações</h3>
            <p><strong>VPS:</strong> 212.85.11.238:3000</p>
            <p><strong>Arquivo:</strong> whatsapp-api-server.js</p>
            <p><strong>Status:</strong> Aguardando reinicialização</p>
        </div>

        <div class="step">
            <h3>🔧 Passo 1: Verificar Status Atual</h3>
            <button class="btn-primary" onclick="verificarStatusAtual()">Verificar Status</button>
            <div id="status-atual"></div>
        </div>

        <div class="step">
            <h3>📤 Passo 2: Enviar Arquivo Atualizado</h3>
            <button class="btn-warning" onclick="enviarArquivoAtualizado()">Enviar Arquivo</button>
            <div id="envio-arquivo"></div>
        </div>

        <div class="step">
            <h3>🔄 Passo 3: Reiniciar Servidor</h3>
            <button class="btn-danger" onclick="reiniciarServidor()">Reiniciar Servidor</button>
            <div id="reiniciar-servidor"></div>
        </div>

        <div class="step">
            <h3>✅ Passo 4: Verificar Novo Status</h3>
            <button class="btn-success" onclick="verificarNovoStatus()">Verificar Novo Status</button>
            <div id="novo-status"></div>
        </div>

        <div class="step">
            <h3>🧪 Passe 5: Testar QR Code</h3>
            <button class="btn-primary" onclick="testarQRCode()">Testar QR Code</button>
            <div id="teste-qr"></div>
        </div>

        <div class="step">
            <h3>📋 Log de Execução</h3>
            <div id="log-execucao" class="log-area">[<?php echo date('H:i:s'); ?>] Sistema carregado. Aguardando comandos...</div>
        </div>
    </div>

    <script>
        const VPS_URL = 'http://212.85.11.238:3000';
        
        function log(message, type = 'info') {
            const logArea = document.getElementById('log-execucao');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? '#ff6b6b' : type === 'success' ? '#51cf66' : type === 'warning' ? '#ffd43b' : '#74c0fc';
            
            logArea.innerHTML += `<div style="color: ${color};">[${timestamp}] ${message}</div>`;
            logArea.scrollTop = logArea.scrollHeight;
        }

        async function verificarStatusAtual() {
            const resultado = document.getElementById('status-atual');
            resultado.innerHTML = '<div class="info">🔍 Verificando status atual...</div>';
            
            log('Verificando status atual da VPS...', 'info');
            
            try {
                const response = await fetch(`${VPS_URL}/status?check=${Date.now()}`, {
                    method: 'GET',
                    cache: 'no-cache'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultado.innerHTML = `
                        <div class="success">
                            <h4>✅ VPS Online</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                    log('VPS está online e respondendo', 'success');
                } else {
                    resultado.innerHTML = `
                        <div class="error">
                            <h4>❌ VPS Offline</h4>
                            <p>HTTP ${response.status}: ${response.statusText}</p>
                        </div>
                    `;
                    log(`VPS offline: HTTP ${response.status}`, 'error');
                }
            } catch (error) {
                resultado.innerHTML = `
                    <div class="error">
                        <h4>❌ Erro de Conectividade</h4>
                        <p>${error.message}</p>
                    </div>
                `;
                log(`Erro de conectividade: ${error.message}`, 'error');
            }
        }

        async function enviarArquivoAtualizado() {
            const resultado = document.getElementById('envio-arquivo');
            resultado.innerHTML = '<div class="info">📤 Enviando arquivo atualizado...</div>';
            
            log('Iniciando envio do arquivo atualizado...', 'info');
            
            try {
                // Simular envio do arquivo (em produção, isso seria feito via SSH/SCP)
                const response = await fetch('enviar_arquivo_vps.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'upload_file',
                        filename: 'whatsapp-api-server.js',
                        content: '// Arquivo atualizado com endpoints QR'
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultado.innerHTML = `
                        <div class="success">
                            <h4>✅ Arquivo Enviado</h4>
                            <p>${data.message}</p>
                        </div>
                    `;
                    log('Arquivo enviado com sucesso', 'success');
                } else {
                    resultado.innerHTML = `
                        <div class="warning">
                            <h4>⚠️ Envio Manual Necessário</h4>
                            <p>Execute manualmente na VPS:</p>
                            <pre>nano whatsapp-api-server.js</pre>
                            <p>E cole o conteúdo atualizado</p>
                        </div>
                    `;
                    log('Envio automático falhou - necessário manual', 'warning');
                }
            } catch (error) {
                resultado.innerHTML = `
                    <div class="warning">
                        <h4>⚠️ Envio Manual Necessário</h4>
                        <p>Erro: ${error.message}</p>
                        <p>Execute manualmente na VPS:</p>
                        <pre>nano whatsapp-api-server.js</pre>
                    </div>
                `;
                log(`Erro no envio: ${error.message}`, 'error');
            }
        }

        async function reiniciarServidor() {
            const resultado = document.getElementById('reiniciar-servidor');
            resultado.innerHTML = '<div class="info">🔄 Reiniciando servidor...</div>';
            
            log('Iniciando reinicialização do servidor...', 'info');
            
            try {
                // Simular reinicialização (em produção, isso seria feito via SSH)
                const response = await fetch('reiniciar_vps.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'restart_pm2',
                        service: 'whatsapp-api'
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultado.innerHTML = `
                        <div class="success">
                            <h4>✅ Servidor Reiniciado</h4>
                            <p>${data.message}</p>
                        </div>
                    `;
                    log('Servidor reiniciado com sucesso', 'success');
                    
                    // Aguardar alguns segundos para o servidor inicializar
                    setTimeout(() => {
                        log('Aguardando inicialização do servidor...', 'info');
                    }, 5000);
                } else {
                    resultado.innerHTML = `
                        <div class="warning">
                            <h4>⚠️ Reinicialização Manual Necessária</h4>
                            <p>Execute na VPS:</p>
                            <pre>pm2 restart whatsapp-api</pre>
                            <p>Ou:</p>
                            <pre>pm2 restart all</pre>
                        </div>
                    `;
                    log('Reinicialização automática falhou - necessário manual', 'warning');
                }
            } catch (error) {
                resultado.innerHTML = `
                    <div class="warning">
                        <h4>⚠️ Reinicialização Manual Necessária</h4>
                        <p>Erro: ${error.message}</p>
                        <p>Execute na VPS:</p>
                        <pre>pm2 restart whatsapp-api</pre>
                    </div>
                `;
                log(`Erro na reinicialização: ${error.message}`, 'error');
            }
        }

        async function verificarNovoStatus() {
            const resultado = document.getElementById('novo-status');
            resultado.innerHTML = '<div class="info">🔍 Verificando novo status...</div>';
            
            log('Verificando status após reinicialização...', 'info');
            
            // Aguardar um pouco para o servidor inicializar
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            try {
                const response = await fetch(`${VPS_URL}/status?new_check=${Date.now()}`, {
                    method: 'GET',
                    cache: 'no-cache'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultado.innerHTML = `
                        <div class="success">
                            <h4>✅ Servidor Reiniciado com Sucesso</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                    log('Servidor reiniciado e funcionando', 'success');
                } else {
                    resultado.innerHTML = `
                        <div class="error">
                            <h4>❌ Servidor Ainda Offline</h4>
                            <p>HTTP ${response.status}: ${response.statusText}</p>
                            <p>Aguarde mais alguns segundos e tente novamente</p>
                        </div>
                    `;
                    log(`Servidor ainda offline: HTTP ${response.status}`, 'error');
                }
            } catch (error) {
                resultado.innerHTML = `
                    <div class="error">
                        <h4>❌ Erro de Conectividade</h4>
                        <p>${error.message}</p>
                        <p>Verifique se o servidor foi reiniciado corretamente</p>
                    </div>
                `;
                log(`Erro de conectividade: ${error.message}`, 'error');
            }
        }

        async function testarQRCode() {
            const resultado = document.getElementById('teste-qr');
            resultado.innerHTML = '<div class="info">🧪 Testando QR Code...</div>';
            
            log('Testando novo endpoint de QR Code...', 'info');
            
            try {
                const response = await fetch(`${VPS_URL}/qr?test=${Date.now()}`, {
                    method: 'GET',
                    cache: 'no-cache'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    resultado.innerHTML = `
                        <div class="success">
                            <h4>✅ Endpoint QR Funcionando</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                    log('Endpoint QR funcionando corretamente', 'success');
                } else {
                    resultado.innerHTML = `
                        <div class="error">
                            <h4>❌ Endpoint QR com Problema</h4>
                            <p>HTTP ${response.status}: ${response.statusText}</p>
                        </div>
                    `;
                    log(`Endpoint QR com problema: HTTP ${response.status}`, 'error');
                }
            } catch (error) {
                resultado.innerHTML = `
                    <div class="error">
                        <h4>❌ Erro no Teste QR</h4>
                        <p>${error.message}</p>
                    </div>
                `;
                log(`Erro no teste QR: ${error.message}`, 'error');
            }
        }

        // Auto-iniciar verificação
        window.onload = function() {
            setTimeout(verificarStatusAtual, 1000);
        };
    </script>
</body>
</html> 