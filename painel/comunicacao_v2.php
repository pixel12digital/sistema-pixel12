<?php
// VERSÃO V2 - ANTI-CACHE MÁXIMO
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('ETag: "' . md5(microtime(true)) . '"');

$page = 'comunicacao_v2.php';
$page_title = 'WhatsApp - Sistema Limpo';
require_once 'config.php';
require_once 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate, max-age=0">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="-1">
    <title>WhatsApp - Sistema V2</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f8fa; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #7c3aed; color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .info-box { background: #e0f2fe; border-left: 4px solid #0288d1; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { background: #e8f5e8; border-left: 4px solid #4caf50; }
        .error { background: #ffebee; border-left: 4px solid #f44336; }
        .btn { background: #7c3aed; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #6d28d9; }
        .btn-success { background: #22c55e; } .btn-success:hover { background: #16a34a; }
        .qr-area { border: 2px solid #ddd; padding: 20px; border-radius: 10px; text-align: center; min-height: 200px; background: white; }
        .table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; margin: 20px 0; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: bold; }
        .status-conectado { color: #22c55e; font-weight: bold; }
        .status-desconectado { color: #f59e0b; font-weight: bold; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 10px; max-width: 500px; position: relative; }
        .close { position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Sistema WhatsApp - Versão Limpa V2</h1>
            <p>Sistema completamente novo sem cache - URL da VPS: <strong><?= WHATSAPP_ROBOT_URL ?></strong></p>
        </div>

        <div id="status-info" class="info-box">
            ⏳ Verificando status da VPS...
        </div>

        <div id="canais-section">
            <h2>📋 Canais WhatsApp</h2>
            
            <?php
            $canais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id DESC");
            if ($canais && $canais->num_rows > 0) {
                echo '<table class="table">';
                echo '<thead><tr><th>ID</th><th>Nome</th><th>Porta</th><th>Status</th><th>Ações</th></tr></thead><tbody>';
                
                while ($canal = $canais->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $canal['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($canal['nome_exibicao']) . '</td>';
                    echo '<td>' . $canal['porta'] . '</td>';
                    echo '<td><span id="status-' . $canal['id'] . '" class="status-desconectado">Verificando...</span></td>';
                    echo '<td>';
                    echo '<button class="btn btn-conectar" data-porta="' . $canal['porta'] . '" onclick="conectarWhatsApp(' . $canal['porta'] . ')">Conectar</button>';
                    echo '<button class="btn" onclick="verificarStatus(' . $canal['porta'] . ')">Status</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<div class="info-box">Nenhum canal cadastrado.</div>';
            }
            ?>
        </div>

        <!-- Modal para QR Code -->
        <div id="qr-modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="fecharModal()">&times;</span>
                <h3>📱 Conectar WhatsApp</h3>
                <div id="qr-area" class="qr-area">
                    Carregando QR Code...
                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <button class="btn" onclick="atualizarQR()">🔄 Atualizar QR</button>
                    <button class="btn" onclick="fecharModal()">❌ Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // ===== CONFIGURAÇÃO HARDCODED DA VPS (SEM CACHE) =====
        const VPS_URL = 'http://212.85.11.238:3000';
        const TIMESTAMP = Date.now();
        
        console.log('🚀 === SISTEMA V2 CARREGADO ===');
        console.log('📡 URL VPS:', VPS_URL);
        console.log('⏰ Timestamp:', TIMESTAMP);
        console.log('🧪 Versão sem cache ativa');

        let qrCodeObj = null;
        let statusInterval = null;
        let currentPorta = null;

        // Verificar status inicial da VPS
        function verificarVPS() {
            const statusDiv = document.getElementById('status-info');
            
            fetch(VPS_URL + '/status?_=' + Date.now())
                .then(r => r.json())
                .then(data => {
                    if (data.ready) {
                        statusDiv.className = 'info-box success';
                        statusDiv.innerHTML = '✅ VPS Online e Funcionando! API WhatsApp respondendo normalmente.';
                    } else {
                        statusDiv.className = 'info-box';
                        statusDiv.innerHTML = '🔄 VPS Online - WhatsApp aguardando conexão (QR Code necessário).';
                    }
                })
                .catch(err => {
                    statusDiv.className = 'info-box error';
                    statusDiv.innerHTML = '❌ Erro de conectividade com VPS: ' + err.message;
                });
        }

        function verificarStatus(porta) {
            const statusSpan = document.getElementById('status-' + porta);
            if (!statusSpan) return;
            
            statusSpan.textContent = 'Verificando...';
            
            fetch(VPS_URL + '/status?_=' + Date.now())
                .then(r => r.json())
                .then(data => {
                    if (data.ready) {
                        statusSpan.textContent = 'Conectado';
                        statusSpan.className = 'status-conectado';
                    } else {
                        statusSpan.textContent = 'Desconectado';
                        statusSpan.className = 'status-desconectado';
                    }
                })
                .catch(err => {
                    statusSpan.textContent = 'Erro';
                    statusSpan.className = 'status-desconectado';
                });
        }

        function conectarWhatsApp(porta) {
            currentPorta = porta;
            document.getElementById('qr-modal').style.display = 'block';
            document.getElementById('qr-area').innerHTML = 'Carregando QR Code...';
            
            carregarQR();
            
            // Verificar status a cada 5 segundos
            statusInterval = setInterval(() => {
                verificarConexao();
            }, 5000);
        }

        function carregarQR() {
            const qrArea = document.getElementById('qr-area');
            
            fetch(VPS_URL + '/qr?_=' + Date.now())
                .then(r => r.json())
                .then(data => {
                    qrArea.innerHTML = '';
                    
                    if (data.qr) {
                        qrCodeObj = new QRCode(qrArea, {
                            text: data.qr,
                            width: 256,
                            height: 256
                        });
                    } else {
                        qrArea.innerHTML = '<p>QR Code indisponível. Aguarde...</p>';
                    }
                })
                .catch(err => {
                    qrArea.innerHTML = '<p style="color: red;">Erro ao carregar QR: ' + err.message + '</p>';
                });
        }

        function verificarConexao() {
            fetch(VPS_URL + '/status?_=' + Date.now())
                .then(r => r.json())
                .then(data => {
                    if (data.ready) {
                        // Conectado!
                        clearInterval(statusInterval);
                        document.getElementById('qr-area').innerHTML = '<div style="color: green; font-size: 18px; font-weight: bold;">✅ WhatsApp Conectado com Sucesso!</div>';
                        setTimeout(() => {
                            fecharModal();
                            if (currentPorta) verificarStatus(currentPorta);
                        }, 2000);
                    }
                })
                .catch(err => {
                    console.log('Verificação de conexão falhou:', err);
                });
        }

        function atualizarQR() {
            carregarQR();
        }

        function fecharModal() {
            document.getElementById('qr-modal').style.display = 'none';
            if (statusInterval) {
                clearInterval(statusInterval);
                statusInterval = null;
            }
        }

        // Verificar VPS ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            verificarVPS();
            
            // Verificar status de todos os canais
            document.querySelectorAll('.btn-conectar').forEach(btn => {
                const porta = btn.getAttribute('data-porta');
                verificarStatus(porta);
            });
        });

        // Fechar modal se clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('qr-modal');
            if (event.target == modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html> 