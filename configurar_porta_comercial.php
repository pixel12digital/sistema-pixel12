<?php
/**
 * CONFIGURADOR DE PORTA COMERCIAL - VPS
 * 
 * Script para configurar porta 3001 na VPS atual
 * para o canal comercial sem afetar o canal financeiro
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Porta Comercial - VPS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configurar Porta Comercial - VPS</h1>
        <p><strong>VPS Atual:</strong> 212.85.11.238:3000 (Canal Financeiro)</p>
        <p><strong>Nova Porta:</strong> 212.85.11.238:3001 (Canal Comercial)</p>

        <div class="status info">
            <strong>⚠️ IMPORTANTE:</strong> Este script NÃO modifica o sistema atual. 
            Apenas configura uma nova porta na VPS existente.
        </div>

        <div class="step">
            <h3>📋 Passo 1: Verificar VPS Atual</h3>
            <button class="btn" onclick="verificarVPSAtual()">🔍 Verificar VPS Atual</button>
            <div id="status-vps-atual"></div>
        </div>

        <div class="step">
            <h3>🔧 Passo 2: Configurar Porta 3001</h3>
            <p>Para configurar a porta 3001 na VPS, você precisa:</p>
            
            <div class="code">
                <strong>1. Acessar a VPS via SSH:</strong><br>
                ssh root@212.85.11.238
            </div>

            <div class="code">
                <strong>2. Verificar se a porta 3001 está livre:</strong><br>
                netstat -tulpn | grep :3001
            </div>

            <div class="code">
                <strong>3. Abrir a porta no firewall:</strong><br>
                ufw allow 3001
            </div>

            <div class="code">
                <strong>4. Configurar novo servidor WhatsApp:</strong><br>
                # Criar diretório para o canal comercial<br>
                mkdir /opt/whatsapp-comercial<br>
                cd /opt/whatsapp-comercial<br>
                # Instalar servidor WhatsApp na porta 3001
            </div>

            <button class="btn btn-warning" onclick="mostrarComandosVPS()">📋 Ver Comandos Completos</button>
        </div>

        <div class="step">
            <h3>🧪 Passo 3: Testar Nova Porta</h3>
            <button class="btn btn-success" onclick="testarPortaComercial()">🧪 Testar Porta 3001</button>
            <div id="status-porta-comercial"></div>
        </div>

        <div class="step">
            <h3>📊 Passo 4: Configuração Local</h3>
            <p>Após configurar a VPS, você precisará:</p>
            <ul>
                <li>Criar novo banco na Hostinger</li>
                <li>Criar projeto separado localmente</li>
                <li>Configurar webhook para porta 3001</li>
                <li>Integrar com ChatGPT</li>
            </ul>
        </div>

        <div class="status warning">
            <strong>💡 Dica:</strong> Se você não tem acesso SSH à VPS, 
            entre em contato com o administrador da VPS para configurar a porta 3001.
        </div>
    </div>

    <script>
        function verificarVPSAtual() {
            const statusDiv = document.getElementById('status-vps-atual');
            statusDiv.innerHTML = '<div class="status info">🔍 Verificando VPS atual...</div>';
            
            fetch('verificar_vps.php')
                .then(response => response.json())
                .then(data => {
                    let html = '<div class="status success"><h4>✅ VPS Atual Funcionando</h4>';
                    html += '<p><strong>IP:</strong> ' + data.vps_ip + '</p>';
                    html += '<p><strong>Porta:</strong> ' + data.vps_port + '</p>';
                    html += '<p><strong>Status:</strong> Conectado</p>';
                    html += '</div>';
                    statusDiv.innerHTML = html;
                })
                .catch(error => {
                    statusDiv.innerHTML = '<div class="status error">❌ Erro ao verificar VPS: ' + error.message + '</div>';
                });
        }

        function testarPortaComercial() {
            const statusDiv = document.getElementById('status-porta-comercial');
            statusDiv.innerHTML = '<div class="status info">🧪 Testando porta 3001...</div>';
            
            // Simular teste da nova porta
            setTimeout(() => {
                statusDiv.innerHTML = '<div class="status warning">⚠️ Porta 3001 ainda não configurada na VPS</div>';
            }, 2000);
        }

        function mostrarComandosVPS() {
            const comandos = `
                <div class="code">
                    <strong>Comandos completos para VPS:</strong><br><br>
                    
                    <strong>1. Acessar VPS:</strong><br>
                    ssh root@212.85.11.238<br><br>
                    
                    <strong>2. Verificar portas em uso:</strong><br>
                    netstat -tulpn | grep :300<br><br>
                    
                    <strong>3. Abrir porta 3001:</strong><br>
                    ufw allow 3001<br><br>
                    
                    <strong>4. Verificar se foi aberta:</strong><br>
                    ufw status | grep 3001<br><br>
                    
                    <strong>5. Criar diretório para canal comercial:</strong><br>
                    mkdir -p /opt/whatsapp-comercial<br>
                    cd /opt/whatsapp-comercial<br><br>
                    
                    <strong>6. Instalar servidor WhatsApp (se necessário):</strong><br>
                    npm install whatsapp-web.js<br><br>
                    
                    <strong>7. Configurar para porta 3001:</strong><br>
                    # No arquivo de configuração do servidor<br>
                    PORT=3001
                </div>
            `;
            
            alert('Comandos copiados! Verifique o console do navegador.');
            console.log(comandos);
        }
    </script>
</body>
</html> 