<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - Gerenciamento de Canais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-online { color: #28a745; }
        .status-offline { color: #dc3545; }
        .card-canal { transition: transform 0.2s; }
        .card-canal:hover { transform: translateY(-2px); }
        .qr-code { max-width: 200px; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="#">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#config">
                                <i class="fas fa-cog"></i> Configuração
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#logs">
                                <i class="fas fa-list"></i> Logs
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fab fa-whatsapp text-success"></i> 
                        Gerenciamento de Canais WhatsApp
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshStatus()">
                                <i class="fas fa-sync-alt"></i> Atualizar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="row">
                    <?php foreach ($canais as $porta => $canal): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card card-canal h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fab fa-whatsapp text-success"></i> 
                                    <?= htmlspecialchars($canal['nome']) ?>
                                </h5>
                                <span class="badge <?= $canal['status'] === 'online' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($canal['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <p><strong>Porta:</strong> <?= $porta ?></p>
                                        <p><strong>Sessão:</strong> <?= htmlspecialchars($canal['session']) ?></p>
                                        <p><strong>Número:</strong> <?= htmlspecialchars($canal['numero']) ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p><strong>URL:</strong> <small><?= htmlspecialchars($canal['url']) ?></small></p>
                                        <p><strong>Última verificação:</strong> <small><?= $canal['ultima_verificacao'] ?></small></p>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-primary" onclick="getQRCode(<?= $porta ?>, '<?= $canal['session'] ?>')">
                                        <i class="fas fa-qrcode"></i> QR Code
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="testConnection(<?= $porta ?>)">
                                        <i class="fas fa-wifi"></i> Testar
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="configureWebhook(<?= $porta ?>)">
                                        <i class="fas fa-link"></i> Webhook
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- QR Code Modal -->
                <div class="modal fade" id="qrModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">QR Code - Canal <span id="qrCanalPorta"></span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div id="qrCodeContent"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Send Message Modal -->
                <div class="modal fade" id="sendMessageModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Enviar Mensagem</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="sendMessageForm">
                                    <div class="mb-3">
                                        <label for="messageNumber" class="form-label">Número</label>
                                        <input type="text" class="form-control" id="messageNumber" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="messageText" class="form-label">Mensagem</label>
                                        <textarea class="form-control" id="messageText" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="messagePorta" class="form-label">Canal</label>
                                        <select class="form-control" id="messagePorta">
                                            <option value="3000">Canal 3000 - Financeiro</option>
                                            <option value="3001">Canal 3001 - Comercial</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" onclick="sendMessage()">Enviar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funções JavaScript
        function refreshStatus() {
            location.reload();
        }

        function getQRCode(porta, session) {
            fetch(`/loja-virtual-revenda/whatsapp/qr?porta=${porta}&session=${session}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('qrCanalPorta').textContent = porta;
                        document.getElementById('qrCodeContent').innerHTML = 
                            `<img src="data:image/png;base64,${data.qrcode}" class="qr-code" alt="QR Code">`;
                        new bootstrap.Modal(document.getElementById('qrModal')).show();
                    } else {
                        alert('Erro ao obter QR Code: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao obter QR Code');
                });
        }

        function testConnection(porta) {
            fetch(`/loja-virtual-revenda/whatsapp/test?porta=${porta}`)
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao testar conexão');
                });
        }

        function configureWebhook(porta) {
            const webhookUrl = prompt('Digite a URL do webhook:');
            if (webhookUrl) {
                fetch('/loja-virtual-revenda/whatsapp/webhook', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        porta: porta,
                        webhook_url: webhookUrl
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao configurar webhook');
                });
            }
        }

        function sendMessage() {
            const number = document.getElementById('messageNumber').value;
            const message = document.getElementById('messageText').value;
            const porta = document.getElementById('messagePorta').value;

            if (!number || !message) {
                alert('Preencha todos os campos');
                return;
            }

            fetch('/loja-virtual-revenda/whatsapp/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    number: number,
                    message: message,
                    porta: porta
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Mensagem enviada com sucesso!');
                    bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
                    document.getElementById('sendMessageForm').reset();
                } else {
                    alert('Erro ao enviar mensagem: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar mensagem');
            });
        }

        // Auto-refresh a cada 30 segundos
        setInterval(refreshStatus, 30000);
    </script>
</body>
</html> 