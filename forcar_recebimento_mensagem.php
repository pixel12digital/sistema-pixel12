<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$mysqli->set_charset('utf8mb4');

// Forçar recebimento de mensagem
$cliente_id = 4296;
$numero = '554796164699';
$mensagem = 'Teste mensagem recebida de canal 3001 554797309525 17:45 - FORÇADA';
$canal_id = 37; // Canal 3001
$canal_nome = 'Pixel - Comercial';

// Inserir mensagem recebida
$sql = "INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, direcao, data_hora, status, numero_whatsapp, canal_id, canal_nome) 
         VALUES (?, ?, 'text', 'recebido', NOW(), 'nao_lido', ?, ?, ?)";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('issss', $cliente_id, $mensagem, $numero, $canal_id, $canal_nome);

if ($stmt->execute()) {
    $mensagem_id = $mysqli->insert_id;
    echo "✅ Mensagem recebida forçada criada - ID: $mensagem_id<br>";
    
    // Limpar cache
    $cache_file = __DIR__ . '/cache/' . md5("mensagens_{$cliente_id}") . '.cache';
    if (file_exists($cache_file)) {
        unlink($cache_file);
        echo "✅ Cache limpo<br>";
    }
} else {
    echo "❌ Erro ao criar mensagem forçada: " . $stmt->error . "<br>";
}

$stmt->close();
$mysqli->close();

echo "<p><strong>🎯 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Acesse o chat: <a href='painel/chat.php?cliente_id=$cliente_id' target='_blank'>Chat do Cliente</a></li>";
echo "<li>Recarregue a página (F5)</li>";
echo "<li>Verifique se a mensagem forçada aparece</li>";
echo "</ol>";
?>