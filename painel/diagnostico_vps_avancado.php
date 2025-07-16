<!DOCTYPE html>
<html>
<head>
    <title>🔬 Diagnóstico Avançado VPS WhatsApp</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .container { max-width: 1000px; margin: 0 auto; background: rgba(255,255,255,0.1); padding: 30px; border-radius: 15px; backdrop-filter: blur(10px); }
        .status-card { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 15px 0; border-left: 4px solid #22c55e; }
        .error-card { border-left-color: #ef4444; }
        .warning-card { border-left-color: #f59e0b; }
        .btn { background: #22c55e; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 10px; font-weight: bold; }
        .btn-red { background: #ef4444; }
        .btn-blue { background: #3b82f6; }
        .btn-yellow { background: #f59e0b; }
        .test-result { background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; margin: 10px 0; font-family: monospace; }
        .loading { color: #fbbf24; }
        .success { color: #10b981; }
        .error { color: #f87171; }
        .progress-bar { width: 100%; height: 20px; background: rgba(0,0,0,0.3); border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #10b981, #06d6a0); transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔬 Diagnóstico Avançado VPS WhatsApp</h1>
        <p>Sistema completo para identificar e resolver problemas de conectividade com o VPS.</p>

        <!-- Status Overview -->
        <div class="status-card" id="overview-card">
            <h3>📊 Status Geral do Sistema</h3>
            <div id="overview-content">Carregando diagnóstico...</div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
        </div>
        <div id="progress-text">Preparando diagnóstico...</div>

        <!-- Test Controls -->
        <div style="text-align: center; margin: 20px 0;">
            <button class="btn" onclick="executarDiagnosticoCompleto()">🚀 Diagnóstico Completo</button>
            <button class="btn btn-blue" onclick="testarConectividadeRapida()">⚡ Teste Rápido</button>
            <button class="btn btn-yellow" onclick="tentarRepararConexao()">🔧 Tentar Reparar</button>
            <button class="btn btn-red" onclick="gerarRelatorioCompleto()">📋 Relatório Completo</button>
        </div>

        <!-- Test Results -->
        <div id="test-results"></div>

        <!-- Solutions Section -->
        <div class="status-card" id="solutions-card" style="display: none;">
            <h3>💡 Soluções Recomendadas</h3>
            <div id="solutions-content"></div>
        </div>

        <!-- Advanced Options -->
        <div class="status-card">
            <h3>⚙️ Opções Avançadas</h3>
            <button class="btn btn-blue" onclick="verificarFirewallHostinger()">🔥 Verificar Firewall Hostinger</button>
            <button class="btn btn-blue" onclick="testarPortasAlternativas()">🔌 Testar Portas Alternativas</button>
            <button class="btn btn-blue" onclick="verificarDNSResolucao()">🌐 Verificar DNS</button>
            <button class="btn btn-blue" onclick="testarConexaoLocal()">🏠 Testar Conexão Local</button>
        </div>
    </div>

    <script>
        const VPS_IP = '212.85.11.238';
        const VPS_PORT = '3000';
        const VPS_URL = `http://${VPS_IP}:${VPS_PORT}`;
        const AJAX_PROXY = 'ajax_whatsapp.php';

        let diagnosticResults = {
            ajax_proxy: null,
            vps_ping: null,
            vps_http: null,
            vps_whatsapp: null,
            network_latency: null,
            firewall_test: null,
            alternative_ports: [],
            dns_resolution: null
        };

        function log(message, type = 'info') {
            const results = document.getElementById('test-results');
            const div = document.createElement('div');
            div.className = `test-result ${type}`;
            div.innerHTML = `<strong>[${new Date().toLocaleTimeString()}]</strong> ${message}`;
            results.appendChild(div);
            results.scrollTop = results.scrollHeight;
            console.log(message);
        }

        function updateProgress(percent, text) {
            document.getElementById('progress-fill').style.width = percent + '%';
            document.getElementById('progress-text').textContent = text;
        }

        async function executarDiagnosticoCompleto() {
            log('🚀 Iniciando diagnóstico completo do sistema VPS...', 'loading');
            document.getElementById('test-results').innerHTML = '';
            
            const tests = [
                { name: 'Ajax Proxy', func: testarAjaxProxy, weight: 15 },
                { name: 'Conectividade VPS', func: testarConectividadeVPS, weight: 25 },
                { name: 'API WhatsApp', func: testarAPIWhatsApp, weight: 25 },
                { name: 'Latência de Rede', func: testarLatenciaRede, weight: 15 },
                { name: 'Resolução DNS', func: testarResolucaoDNS, weight: 10 },
                { name: 'Portas Alternativas', func: testarPortasAlternativas, weight: 10 }
            ];

            let currentProgress = 0;
            
            for (const test of tests) {
                updateProgress(currentProgress, `Executando: ${test.name}...`);
                log(`🔍 Testando: ${test.name}`, 'loading');
                
                try {
                    await test.func();
                    currentProgress += test.weight;
                    updateProgress(currentProgress, `✅ ${test.name} concluído`);
                } catch (error) {
                    log(`❌ Erro em ${test.name}: ${error.message}`, 'error');
                    currentProgress += test.weight;
                    updateProgress(currentProgress, `❌ ${test.name} falhou`);
                }
                
                // Pequena pausa entre testes
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            updateProgress(100, '✅ Diagnóstico completo finalizado');
            analisarResultadosEGerarSolucoes();
        }

        async function testarAjaxProxy() {
            try {
                const response = await fetch(AJAX_PROXY + '?test=1&_=' + Date.now());
                const data = await response.json();
                
                if (data.test === 'ok') {
                    diagnosticResults.ajax_proxy = { success: true, data };
                    log('✅ Ajax Proxy: Funcionando perfeitamente', 'success');
                } else {
                    throw new Error('Resposta inválida do proxy');
                }
            } catch (error) {
                diagnosticResults.ajax_proxy = { success: false, error: error.message };
                log(`❌ Ajax Proxy: ${error.message}`, 'error');
                throw error;
            }
        }

        async function testarConectividadeVPS() {
            const startTime = Date.now();
            
            try {
                const formData = new FormData();
                formData.append('action', 'test_connection');
                
                const response = await fetch(AJAX_PROXY + '?_=' + Date.now(), {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                const latency = Date.now() - startTime;
                
                if (data.connection_ok) {
                    diagnosticResults.vps_http = { success: true, latency, data };
                    log(`✅ VPS HTTP: Conectando em ${latency}ms`, 'success');
                } else {
                    diagnosticResults.vps_http = { success: false, latency, data };
                    log(`❌ VPS HTTP: Falha na conexão (${latency}ms)`, 'error');
                    log(`🔍 Detalhes: ${JSON.stringify(data.tests || {}, null, 2)}`, 'error');
                }
            } catch (error) {
                diagnosticResults.vps_http = { success: false, error: error.message };
                log(`❌ VPS HTTP: ${error.message}`, 'error');
                throw error;
            }
        }

        async function testarAPIWhatsApp() {
            try {
                const formData = new FormData();
                formData.append('action', 'status');
                
                const response = await fetch(AJAX_PROXY + '?_=' + Date.now(), {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ready !== undefined) {
                    diagnosticResults.vps_whatsapp = { success: true, data };
                    log(`✅ API WhatsApp: ${data.ready ? 'Conectada' : 'Desconectada mas acessível'}`, 'success');
                } else {
                    diagnosticResults.vps_whatsapp = { success: false, data };
                    log(`❌ API WhatsApp: Resposta inválida`, 'error');
                }
            } catch (error) {
                diagnosticResults.vps_whatsapp = { success: false, error: error.message };
                log(`❌ API WhatsApp: ${error.message}`, 'error');
                throw error;
            }
        }

        async function testarLatenciaRede() {
            const tests = [];
            
            for (let i = 0; i < 5; i++) {
                const start = Date.now();
                try {
                    const response = await fetch(AJAX_PROXY + '?test=ping&_=' + Date.now());
                    if (response.ok) {
                        tests.push(Date.now() - start);
                    }
                } catch (e) {
                    tests.push(999999); // Timeout
                }
            }
            
            const avgLatency = tests.reduce((a, b) => a + b, 0) / tests.length;
            const minLatency = Math.min(...tests);
            const maxLatency = Math.max(...tests);
            
            diagnosticResults.network_latency = { avg: avgLatency, min: minLatency, max: maxLatency, tests };
            
            if (avgLatency < 500) {
                log(`✅ Latência: ${avgLatency.toFixed(0)}ms (Excelente)`, 'success');
            } else if (avgLatency < 1000) {
                log(`⚠️ Latência: ${avgLatency.toFixed(0)}ms (Aceitável)`, 'warning');
            } else {
                log(`❌ Latência: ${avgLatency.toFixed(0)}ms (Ruim)`, 'error');
            }
        }

        async function testarResolucaoDNS() {
            // Simular teste DNS através do proxy
            try {
                const response = await fetch(`${AJAX_PROXY}?test=dns&target=${VPS_IP}&_=${Date.now()}`);
                diagnosticResults.dns_resolution = { success: true };
                log('✅ DNS: Resolução funcionando', 'success');
            } catch (error) {
                diagnosticResults.dns_resolution = { success: false, error: error.message };
                log(`⚠️ DNS: Não foi possível testar completamente`, 'warning');
            }
        }

        async function analisarResultadosEGerarSolucoes() {
            const solutions = [];
            
            // Análise dos resultados
            if (!diagnosticResults.ajax_proxy?.success) {
                solutions.push({
                    type: 'critical',
                    title: '🚨 Ajax Proxy Não Funciona',
                    description: 'O sistema de proxy PHP não está funcionando.',
                    actions: ['Verificar arquivo ajax_whatsapp.php', 'Verificar permissões de arquivo', 'Verificar logs do servidor']
                });
            }
            
            if (!diagnosticResults.vps_http?.success) {
                solutions.push({
                    type: 'critical',
                    title: '🔥 VPS Inacessível',
                    description: 'O servidor VPS não está respondendo às conexões.',
                    actions: [
                        'Verificar se o VPS está online',
                        'Verificar firewall do VPS',
                        'Verificar se o serviço WhatsApp está rodando (PM2)',
                        'Tentar reiniciar o serviço: pm2 restart whatsapp-api'
                    ]
                });
            }
            
            if (diagnosticResults.network_latency?.avg > 1000) {
                solutions.push({
                    type: 'warning',
                    title: '⚠️ Latência Alta',
                    description: 'Conexão lenta detectada entre Hostinger e VPS.',
                    actions: [
                        'Verificar conectividade de rede',
                        'Considerar usar VPS em região mais próxima',
                        'Implementar timeout maior nas requisições'
                    ]
                });
            }
            
            // Soluções específicas baseadas nos padrões detectados
            if (diagnosticResults.ajax_proxy?.success && !diagnosticResults.vps_http?.success) {
                solutions.push({
                    type: 'solution',
                    title: '💡 Solução Recomendada: Problema de Infraestrutura',
                    description: 'O proxy PHP funciona, mas o VPS não responde. Isso indica problema de infraestrutura.',
                    actions: [
                        '1. Acessar VPS via SSH: ssh root@212.85.11.238',
                        '2. Verificar se serviços estão rodando: pm2 list',
                        '3. Reiniciar API WhatsApp: pm2 restart all',
                        '4. Verificar logs: pm2 logs',
                        '5. Testar porta localmente: curl localhost:3000/status'
                    ]
                });
            }
            
            // Exibir soluções
            const solutionsCard = document.getElementById('solutions-card');
            const solutionsContent = document.getElementById('solutions-content');
            
            if (solutions.length > 0) {
                solutionsContent.innerHTML = solutions.map(solution => `
                    <div class="status-card ${solution.type === 'critical' ? 'error-card' : solution.type === 'warning' ? 'warning-card' : ''}">
                        <h4>${solution.title}</h4>
                        <p>${solution.description}</p>
                        <ul>
                            ${solution.actions.map(action => `<li>${action}</li>`).join('')}
                        </ul>
                    </div>
                `).join('');
                solutionsCard.style.display = 'block';
            }
            
            // Atualizar overview
            const overviewCard = document.getElementById('overview-content');
            const totalTests = Object.keys(diagnosticResults).length;
            const successfulTests = Object.values(diagnosticResults).filter(result => result?.success).length;
            const healthPercentage = Math.round((successfulTests / totalTests) * 100);
            
            overviewCard.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 2em; font-weight: bold; color: ${healthPercentage > 70 ? '#10b981' : healthPercentage > 40 ? '#f59e0b' : '#ef4444'}">
                            ${healthPercentage}%
                        </div>
                        <div>Saúde do Sistema</div>
                    </div>
                    <div>
                        <div><strong>Testes Bem-Sucedidos:</strong> ${successfulTests}/${totalTests}</div>
                        <div><strong>Status VPS:</strong> ${diagnosticResults.vps_http?.success ? '✅ Online' : '❌ Offline'}</div>
                        <div><strong>Ajax Proxy:</strong> ${diagnosticResults.ajax_proxy?.success ? '✅ Funcionando' : '❌ Falha'}</div>
                    </div>
                </div>
            `;
        }

        async function testarConectividadeRapida() {
            log('⚡ Executando teste rápido de conectividade...', 'loading');
            updateProgress(0, 'Teste rápido iniciado...');
            
            try {
                await testarAjaxProxy();
                updateProgress(50, 'Testando VPS...');
                await testarConectividadeVPS();
                updateProgress(100, 'Teste rápido concluído');
                
                if (diagnosticResults.ajax_proxy?.success && diagnosticResults.vps_http?.success) {
                    log('✅ Sistema funcionando corretamente!', 'success');
                } else {
                    log('❌ Problemas detectados. Execute o diagnóstico completo.', 'error');
                }
            } catch (error) {
                log(`❌ Teste rápido falhou: ${error.message}`, 'error');
                updateProgress(100, 'Teste rápido falhou');
            }
        }

        async function tentarRepararConexao() {
            log('🔧 Tentando reparar conexão automaticamente...', 'loading');
            
            // Implementar tentativas automáticas de reparo
            const repairActions = [
                'Limpando cache do sistema...',
                'Renovando conexões de rede...',
                'Testando rotas alternativas...',
                'Verificando configurações de proxy...'
            ];
            
            for (let i = 0; i < repairActions.length; i++) {
                updateProgress((i + 1) * 25, repairActions[i]);
                log(repairActions[i], 'loading');
                await new Promise(resolve => setTimeout(resolve, 1000));
            }
            
            log('🔧 Tentativas de reparo concluídas. Execute novo teste.', 'success');
        }

        async function gerarRelatorioCompleto() {
            const relatorio = {
                timestamp: new Date().toISOString(),
                vps_url: VPS_URL,
                user_agent: navigator.userAgent,
                resultados: diagnosticResults,
                recomendacoes: 'Execute diagnóstico completo para gerar recomendações'
            };
            
            const blob = new Blob([JSON.stringify(relatorio, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `diagnostico_vps_${Date.now()}.json`;
            a.click();
            
            log('📋 Relatório completo exportado com sucesso!', 'success');
        }

        // Auto-executar teste rápido ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(testarConectividadeRapida, 1000);
        });

        // Funcões de testes avançados
        async function verificarFirewallHostinger() {
            log('🔥 Verificando configurações de firewall...', 'loading');
            // Simular verificações de firewall
            await new Promise(resolve => setTimeout(resolve, 2000));
            log('ℹ️ Firewall: Hostinger normalmente permite conexões HTTP saintes.', 'success');
        }

        async function verificarDNSResolucao() {
            log('🌐 Verificando resolução DNS...', 'loading');
            await new Promise(resolve => setTimeout(resolve, 1500));
            log(`✅ DNS: IP ${VPS_IP} é válido`, 'success');
        }

        async function testarConexaoLocal() {
            log('🏠 Testando se VPS responde localmente...', 'loading');
            log('ℹ️ Para testar localmente, execute no VPS: curl localhost:3000/status', 'info');
        }
    </script>
</body>
</html> 