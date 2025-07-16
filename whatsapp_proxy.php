<?php
// Cabeçalhos anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Configuração VPS
$VPS_URL = 'http://212.85.11.238:3000';

// Função para fazer requisições via servidor (contorna CORS)
function fazerRequisicaoVPS($endpoint, $metodo = 'GET') {
    global $VPS_URL;
    
    $url = $VPS_URL . $endpoint;
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: WhatsApp-Proxy/1.0',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'data' => $response ? json_decode($response, true) : null,
        'error' => $error,
        'raw_response' => $response
    ];
}

// Verificar status completo no carregamento da página
$statusGeral = fazerRequisicaoVPS('/status');
$statusQR = fazerRequisicaoVPS('/qr');
$statusSessions = fazerRequisicaoVPS('/sessions');

// Detectar se está conectado
$whatsappConectado = $statusGeral['success'] && 
                    isset($statusGeral['data']['clients_status']['default']['status']) && 
                    $statusGeral['data']['clients_status']['default']['status'] === 'ready';

$qrDisponivel = $statusQR['success'] && 
                isset($statusQR['data']['qr']) && 
                !empty($statusQR['data']['qr']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <title>🚀 WhatsApp Proxy - CORS FREE</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #25D366, #128C7E); 
            color: white; margin: 0; padding: 20px; min-height: 100vh;
        }
        .container { max-width: 900px; margin: 0 auto; }
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
            text-decoration: none; display: inline-block;
        }
        .btn:hover { 
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .btn-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .btn-blue:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .btn-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-red:hover { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .btn-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .btn-orange:hover { background: linear-gradient(135deg, #d97706, #b45309); }
        .qr-container {
            background: white; color: black; padding: 30px; 
            border-radius: 15px; text-align: center; margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .debug-area { 
            background: rgba(0,0,0,0.4); padding: 20px; 
            border-radius: 8px; font-family: 'Courier New', monospace; 
            font-size: 0.9em; max-height: 400px; overflow-y: auto; 
            margin: 15px 0; border: 1px solid rgba(255,255,255,0.2);
        }
        .code-block {
            background: rgba(0,0,0,0.3); padding: 15px; 
            border-radius: 8px; font-family: monospace; 
            overflow-x: auto; margin: 10px 0;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
        .connection-status {
            font-size: 1.3em; padding: 15px; margin: 15px 0;
            border-radius: 10px; text-align: center; font-weight: bold;
        }
        .online { background: rgba(34, 197, 94, 0.3); border: 2px solid #22c55e; }
        .offline { background: rgba(239, 68, 68, 0.3); border: 2px solid #ef4444; }
        .qr-ready { background: rgba(245, 158, 11, 0.3); border: 2px solid #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>🚀 WhatsApp Proxy</h1>
            <p style="font-size: 1.3em; margin: 10px 0;">CORS-FREE | Server-Side Requests</p>
            <div style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; margin: 15px 0;">
                🌐 <strong>VPS:</strong> <?php echo $VPS_URL; ?><br>
                ⏰ <strong>Verificado:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
                🔧 <strong>Método:</strong> Proxy PHP Server-Side<br>
                📱 <strong>Build:</strong> v3.0.PROXY-FREE
            </div>
        </div>

        <!-- Status de Conexão Principal -->
        <div class="connection-status <?php 
            if ($whatsappConectado) {
                echo 'online">🎉 WHATSAPP CONECTADO E FUNCIONANDO!';
            } elseif ($statusGeral['success'] && $qrDisponivel) {
                echo 'qr-ready">📱 VPS ONLINE - QR CODE DISPONÍVEL';
            } elseif ($statusGeral['success']) {
                echo 'qr-ready">🔧 VPS ONLINE - PREPARANDO WHATSAPP';
            } else {
                echo 'offline">❌ VPS OFFLINE';
            }
        ?>
        </div>

        <!-- Diagnóstico Detalhado -->
        <div class="status-box <?php echo $statusGeral['success'] ? 'success' : 'error'; ?>">
            <h3>🔧 Status Detalhado da VPS</h3>
            <div class="code-block">
                <strong>🌐 Endpoint /status:</strong><br>
                HTTP Code: <?php echo $statusGeral['http_code']; ?><br>
                Success: <?php echo $statusGeral['success'] ? 'SIM' : 'NÃO'; ?><br>
                <?php if ($statusGeral['error']): ?>
                Error: <?php echo htmlspecialchars($statusGeral['error']); ?><br>
                <?php endif; ?>
                <?php if ($statusGeral['data']): ?>
                <br><strong>📊 Dados da API:</strong><br>
                <pre><?php echo json_encode($statusGeral['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($statusGeral['success']): ?>
        <!-- Status WhatsApp -->
        <div class="status-box <?php echo $whatsappConectado ? 'success' : 'warning'; ?>">
            <h3>📱 Status WhatsApp</h3>
            <?php if ($whatsappConectado): ?>
                <div style="color: #22c55e; font-weight: bold; font-size: 1.2em;">✅ CONECTADO E PRONTO!</div>
                <div>📱 WhatsApp está conectado e funcionando</div>
                <div>🎯 Sistema operacional para envio/recebimento</div>
            <?php else: ?>
                <div style="color: #f59e0b; font-weight: bold; font-size: 1.2em;">📱 AGUARDANDO CONEXÃO</div>
                <div>🔧 WhatsApp precisa ser conectado via QR Code</div>
                <?php if ($qrDisponivel): ?>
                <div>✅ QR Code disponível e pronto para escaneamento</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- QR Code (se disponível) -->
        <?php if ($qrDisponivel && !$whatsappConectado): ?>
        <div class="qr-container">
            <h3 style="color: #25D366; margin-bottom: 20px;">📱 Escaneie o QR Code</h3>
            <div id="qrcode" style="margin: 20px auto; max-width: 320px;"></div>
            <p style="color: #666; margin-top: 20px;">
                1. Abra o WhatsApp no seu celular<br>
                2. Toque em ⋮ (3 pontos) > Aparelhos conectados<br>
                3. Toque em "Conectar um aparelho"<br>
                4. Escaneie este QR Code
            </p>
            <div style="margin-top: 20px;">
                <a href="?refresh=1" class="btn">🔄 Atualizar QR</a>
                <a href="?test_connection=1" class="btn btn-blue">🔍 Testar Conexão</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Botões de Ação -->
        <div style="text-align: center; margin: 40px 0;">
            <a href="?" class="btn">🔄 Atualizar Status</a>
            <a href="verificar_vps.php" class="btn btn-orange" target="_blank">🔍 Diagnóstico VPS</a>
            <a href="painel/comunicacao.php" class="btn btn-blue" target="_blank">💬 Abrir Chat</a>
        </div>

        <!-- Informações Técnicas -->
        <div class="status-box">
            <h3>🔧 Informações Técnicas</h3>
            <div class="code-block">
                <strong>🌐 Método de Requisição:</strong> PHP cURL (Server-Side)<br>
                <strong>🚫 CORS:</strong> Problema contornado com proxy PHP<br>
                <strong>🔒 Security:</strong> Requisições feitas pelo servidor<br>
                <strong>📡 Latência:</strong> Conexão direta servidor-para-servidor<br>
                <strong>🛡️ Firewall:</strong> Bypass completo de limitações do navegador
            </div>
        </div>

        <?php else: ?>
        <!-- VPS Offline - Soluções -->
        <div class="status-box error">
            <h3>🛠️ VPS Offline - Soluções</h3>
            <p>A VPS não está respondendo. Possíveis soluções:</p>
            <div style="text-align: center; margin: 20px 0;">
                <a href="verificar_vps.php" class="btn btn-orange" target="_blank">🔍 Diagnóstico Completo</a>
                <a href="?local_test=1" class="btn btn-blue">🏠 Testar Localhost</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Debug Log -->
        <div class="status-box">
            <h3>🐛 Debug Log</h3>
            <div class="debug-area">
                [<?php echo date('H:i:s'); ?>] ✅ Sistema Proxy carregado<br>
                [<?php echo date('H:i:s'); ?>] 🌐 VPS URL: <?php echo $VPS_URL; ?><br>
                [<?php echo date('H:i:s'); ?>] 📡 Status Geral: <?php echo $statusGeral['success'] ? 'SUCCESS' : 'FAILED'; ?><br>
                [<?php echo date('H:i:s'); ?>] 📱 WhatsApp: <?php echo $whatsappConectado ? 'CONECTADO' : 'DESCONECTADO'; ?><br>
                [<?php echo date('H:i:s'); ?>] 🔳 QR Code: <?php echo $qrDisponivel ? 'DISPONÍVEL' : 'INDISPONÍVEL'; ?><br>
                [<?php echo date('H:i:s'); ?>] 🚀 Sistema: CORS-FREE funcionando!
            </div>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <script>
        // Gerar QR Code se disponível
        <?php if ($qrDisponivel && !$whatsappConectado): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const qrData = <?php echo json_encode($statusQR['data']['qr']); ?>;
            const qrContainer = document.getElementById('qrcode');
            
            if (qrData && qrContainer) {
                new QRCode(qrContainer, {
                    text: qrData,
                    width: 300,
                    height: 300,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                console.log('✅ QR Code gerado com sucesso');
            }
        });
        
        // Auto-refresh a cada 30 segundos para verificar conexão
        setTimeout(function() {
            console.log('🔄 Auto-refresh para verificar conexão...');
            window.location.reload();
        }, 30000);
        <?php endif; ?>
        
        console.log('🚀 WhatsApp Proxy System Loaded');
        console.log('🌐 VPS Status:', <?php echo $statusGeral['success'] ? 'true' : 'false'; ?>);
        console.log('📱 WhatsApp Connected:', <?php echo $whatsappConectado ? 'true' : 'false'; ?>);
        console.log('🔳 QR Available:', <?php echo $qrDisponivel ? 'true' : 'false'; ?>);
    </script>
</body>
</html> 