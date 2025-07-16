<?php
require_once '../config.php';
require_once '../db.php';

// DEBUG: Função para logging
function debugLog($message, $data = null) {
    $timestamp = date('H:i:s');
    $logMessage = "[DEBUG $timestamp] $message";
    error_log($logMessage);
    if ($data) {
        error_log("[DEBUG $timestamp] Data: " . json_encode($data));
    }
}

debugLog('formulario_envio.php iniciado');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

debugLog('Cliente ID recebido:', $cliente_id);

if (!$cliente_id) {
    debugLog('ERRO: Cliente ID inválido');
    echo '<div class="text-center text-gray-400">Cliente não encontrado.</div>';
    exit;
}

$cliente = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id")->fetch_assoc();

debugLog('Cliente encontrado:', $cliente ? 'sim' : 'não');

if (!$cliente) {
    debugLog('ERRO: Cliente não encontrado no banco');
    echo '<div class="text-center text-gray-400">Cliente não encontrado.</div>';
    exit;
}

debugLog('Gerando formulário de envio');

echo '<form class="flex flex-col gap-2" method="POST" action="chat_enviar.php" enctype="multipart/form-data" id="form-chat-enviar">';
echo '<input type="hidden" name="cliente_id" value="' . intval($cliente_id) . '">';

// Adiciona select de canais WhatsApp conectados
debugLog('Buscando canais WhatsApp conectados');
$canais_whatsapp = [];
$resCanais = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE status = 'conectado' AND tipo = 'whatsapp'");

debugLog('Query canais executada:', $resCanais ? $resCanais->num_rows . ' canais' : 'erro na query');

if ($resCanais) {
    while ($row = $resCanais->fetch_assoc()) {
        $canais_whatsapp[$row['id']] = $row['nome_exibicao'];
    }
    debugLog('Canais carregados:', $canais_whatsapp);
} else {
    debugLog('ERRO na query de canais:', $mysqli->error);
}

if (count($canais_whatsapp) > 0) {
    debugLog('Renderizando select de canais');
    echo '<select name="canal_id" required style="margin-bottom:8px;">';
    foreach ($canais_whatsapp as $id => $nome) {
        echo '<option value="' . $id . '">' . htmlspecialchars($nome) . '</option>';
    }
    echo '</select>';
} else {
    debugLog('Nenhum canal WhatsApp conectado');
    echo '<div style="color:#a00;margin-bottom:8px;">Nenhum canal WhatsApp conectado.</div>';
}

echo '<div class="flex gap-2">';
echo '<input type="text" name="mensagem" placeholder="Digite sua mensagem..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">';
echo '<button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700" style="min-width:90px;">Enviar</button>';
echo '</div>';
echo '<input type="file" name="anexo" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt" class="px-2 py-2 border border-gray-300 rounded-lg text-sm">';
echo '</form>';

debugLog('formulario_envio.php finalizado');
?> 