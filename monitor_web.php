<?php
/**
 * MONITOR WEBHOOK - VERSÃO WEB
 * 
 * Monitora logs do webhook em tempo real via navegador
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Webhook WhatsApp</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff00;
            margin: 20px;
            line-height: 1.4;
        }
        .header {
            background: #333;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status {
            background: #2a2a2a;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .log-container {
            background: #000;
            border: 1px solid #333;
            border-radius: 5px;
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            font-size: 12px;
        }
        .log-entry {
            margin-bottom: 5px;
            padding: 2px 0;
        }
        .log-time {
            color: #ffff00;
            font-weight: bold;
        }
        .log-content {
            color: #00ff00;
        }
        .new-message {
            background: #003300;
            border-left: 3px solid #00ff00;
            padding-left: 10px;
        }
        .controls {
            margin: 15px 0;
        }
        button {
            background: #333;
            color: #00ff00;
            border: 1px solid #00ff00;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #00ff00;
            color: #000;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .stat-box {
            background: #2a2a2a;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #ffff00;
        }
        .stat-label {
            font-size: 12px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Monitor Webhook WhatsApp</h1>
        <p>Monitoramento em tempo real das mensagens recebidas</p>
    </div>

    <div class="status">
        <strong>Status:</strong> <span id="status">Iniciando...</span>
        <br>
        <strong>Última atualização:</strong> <span id="lastUpdate">-</span>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-value" id="totalMessages">0</div>
            <div class="stat-label">Total de Mensagens</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" id="newMessages">0</div>
            <div class="stat-label">Novas Mensagens</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" id="logSize">0 KB</div>
            <div class="stat-label">Tamanho do Log</div>
        </div>
    </div>

    <div class="controls">
        <button onclick="startMonitoring()">▶️ Iniciar Monitoramento</button>
        <button onclick="stopMonitoring()">⏹️ Parar Monitoramento</button>
        <button onclick="clearLog()">🗑️ Limpar Log</button>
        <button onclick="testWebhook()">🧪 Testar Webhook</button>
    </div>

    <div class="log-container" id="logContainer">
        <div class="log-entry">
            <span class="log-time">[<?php echo date('H:i:s'); ?>]</span>
            <span class="log-content">Monitor iniciado. Aguardando mensagens...</span>
        </div>
    </div>

    <script>
        let monitoring = false;
        let initialLogSize = 0;
        let totalMessages = 0;
        let newMessages = 0;
        let monitoringInterval;

        function updateStatus(message) {
            document.getElementById('status').textContent = message;
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
        }

        function addLogEntry(message, isNew = false) {
            const container = document.getElementById('logContainer');
            const entry = document.createElement('div');
            entry.className = 'log-entry' + (isNew ? ' new-message' : '');
            
            const time = new Date().toLocaleTimeString();
            entry.innerHTML = `
                <span class="log-time">[${time}]</span>
                <span class="log-content">${message}</span>
            `;
            
            container.appendChild(entry);
            container.scrollTop = container.scrollHeight;
            
            if (isNew) {
                newMessages++;
                document.getElementById('newMessages').textContent = newMessages;
            }
        }

        function updateStats() {
            fetch('get_webhook_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalMessages').textContent = data.totalMessages;
                    document.getElementById('logSize').textContent = data.logSize + ' KB';
                })
                .catch(error => {
                    console.error('Erro ao atualizar estatísticas:', error);
                });
        }

        function checkNewMessages() {
            fetch('check_webhook_logs.php')
                .then(response => response.json())
                .then(data => {
                    if (data.newMessages && data.newMessages.length > 0) {
                        data.newMessages.forEach(msg => {
                            addLogEntry(msg, true);
                        });
                        updateStatus('Nova mensagem detectada!');
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar mensagens:', error);
                    updateStatus('Erro na verificação');
                });
        }

        function startMonitoring() {
            if (monitoring) return;
            
            monitoring = true;
            updateStatus('Monitoramento ativo');
            addLogEntry('Monitoramento iniciado');
            
            // Verificar a cada 2 segundos
            monitoringInterval = setInterval(() => {
                checkNewMessages();
                updateStats();
            }, 2000);
        }

        function stopMonitoring() {
            if (!monitoring) return;
            
            monitoring = false;
            clearInterval(monitoringInterval);
            updateStatus('Monitoramento parado');
            addLogEntry('Monitoramento parado');
        }

        function clearLog() {
            document.getElementById('logContainer').innerHTML = '';
            addLogEntry('Log limpo');
        }

        function testWebhook() {
            addLogEntry('Testando webhook...');
            
            const testData = {
                event: 'onmessage',
                data: {
                    from: '554796164699',
                    text: 'Teste via navegador às ' + new Date().toLocaleTimeString(),
                    type: 'text'
                }
            };

            fetch('api/webhook_whatsapp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(testData)
            })
            .then(response => response.json())
            .then(data => {
                addLogEntry('Teste webhook: ' + JSON.stringify(data));
            })
            .catch(error => {
                addLogEntry('Erro no teste: ' + error.message);
            });
        }

        // Iniciar automaticamente
        window.onload = function() {
            updateStats();
            startMonitoring();
        };
    </script>
</body>
</html> 