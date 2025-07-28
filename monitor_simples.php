<?php
/**
 * MONITOR SIMPLES - WEBHOOK WHATSAPP
 * 
 * Versão simplificada que funciona diretamente no navegador
 */

header('Content-Type: text/html; charset=utf-8');

// Verificar se é uma requisição AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    switch ($_GET['action']) {
        case 'stats':
            getStats();
            break;
        case 'logs':
            getLogs();
            break;
        case 'test':
            testWebhook();
            break;
        default:
            echo json_encode(['error' => 'Ação inválida']);
    }
    exit;
}

function getStats() {
    try {
        require_once 'config.php';
        require_once 'painel/db.php';
        
        // Contar mensagens de hoje
        $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
        $result = $mysqli->query($sql);
        $total = $result ? $result->fetch_assoc()['total'] : 0;
        
        // Tamanho do log
        $log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
        $log_size = file_exists($log_file) ? round(filesize($log_file) / 1024, 2) : 0;
        
        echo json_encode([
            'success' => true,
            'totalMessages' => $total,
            'logSize' => $log_size,
            'timestamp' => date('H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getLogs() {
    $log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
    $new_messages = [];
    
    if (file_exists($log_file)) {
        $logs = file($log_file);
        $recent_logs = array_slice($logs, -10); // Últimas 10 linhas
        
        foreach ($recent_logs as $log) {
            $hora = substr($log, 0, 19);
            $conteudo = substr($log, 20);
            if (strlen($conteudo) > 0) {
                $new_messages[] = "[$hora] " . substr($conteudo, 0, 100) . "...";
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'newMessages' => $new_messages,
        'timestamp' => date('H:i:s')
    ]);
}

function testWebhook() {
    $test_data = [
        'event' => 'onmessage',
        'data' => [
            'from' => '554796164699',
            'text' => 'Teste via monitor às ' . date('H:i:s'),
            'type' => 'text'
        ]
    ];
    
    $ch = curl_init('https://pixel12digital.com.br/app/api/webhook_whatsapp.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo json_encode([
        'success' => true,
        'httpCode' => $http_code,
        'response' => $response,
        'timestamp' => date('H:i:s')
    ]);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Webhook WhatsApp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            height: 300px;
            overflow-y: auto;
            font-size: 12px;
            font-family: 'Courier New', monospace;
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
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
            font-size: 20px;
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
            fetch('?action=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalMessages').textContent = data.totalMessages;
                        document.getElementById('logSize').textContent = data.logSize + ' KB';
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar estatísticas:', error);
                });
        }

        function checkNewMessages() {
            fetch('?action=logs')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.newMessages && data.newMessages.length > 0) {
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
            
            // Verificar a cada 3 segundos
            monitoringInterval = setInterval(() => {
                checkNewMessages();
                updateStats();
            }, 3000);
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
            
            fetch('?action=test')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addLogEntry(`Teste webhook: HTTP ${data.httpCode} - ${data.response}`);
                    } else {
                        addLogEntry('Erro no teste: ' + data.error);
                    }
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