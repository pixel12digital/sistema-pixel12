<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$mysqli->set_charset('utf8mb4');

// Forçar recebimento da mensagem específica
$cliente_id = 4296;
$numero = '554796164699';
$mensagem = 'Teste de mensagem enviada para canal 3000 554797146908 - 18:04';
$canal_id = 36; // Canal 3000
$canal_nome = 'Pixel12Digital';

// Verificar se já existe
$sql_check = "SELECT id FROM mensagens_comunicacao WHERE mensagem = ? AND direcao = 'recebido'";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param('s', $mensagem);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "✅ Mensagem já existe no banco!<br>";
    $msg_existente = $result_check->fetch_assoc();
    echo "ID: {$msg_existente['id']}<br>";
} else {
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
}

$stmt_check->close();
$mysqli->close();

echo "<p><strong>🎯 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Acesse o chat: <a href='painel/chat.php?cliente_id=4296' target='_blank'>Chat do Cliente</a></li>";
echo "<li>Recarregue a página (F5)</li>";
echo "<li>Verifique se a mensagem específica aparece</li>";
echo "</ol>";
?>