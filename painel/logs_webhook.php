<?php
// Página para visualizar logs de debug do webhook e recebimento de mensagens
// Caminhos dos arquivos de log
$logs = [
    'Webhook API' => '../debug_webhook.log',
    'Recebimento' => '../receber_mensagem.log',
    'Chat Enviar' => '../debug_chat_enviar.log',
];

?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logs de Debug - Webhook WhatsApp</title>
    <style>
        body { font-family: monospace; background: #f8fafc; color: #222; margin: 0; padding: 0; }
        h1 { background: #6366f1; color: #fff; margin: 0; padding: 1rem; }
        .log-container { padding: 2rem; }
        .log-block { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; margin-bottom: 2rem; padding: 1rem; }
        .log-title { font-size: 1.1rem; font-weight: bold; margin-bottom: 0.5rem; color: #6366f1; }
        pre { background: #f1f5f9; border-radius: 6px; padding: 1rem; overflow-x: auto; font-size: 0.95rem; }
    </style>
</head>
<body>
    <h1>Logs de Debug - Webhook WhatsApp</h1>
    <div class="log-container">
        <?php foreach ($logs as $title => $file): ?>
            <div class="log-block">
                <div class="log-title"><?= htmlspecialchars($title) ?> (<?= htmlspecialchars($file) ?>)</div>
                <pre><?php
                if (file_exists($file)) {
                    echo htmlspecialchars(file_get_contents($file));
                } else {
                    echo "Arquivo de log não encontrado.";
                }
                ?></pre>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html> 