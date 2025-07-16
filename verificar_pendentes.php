<?php
require_once 'painel/config.php';
require_once 'painel/db.php';

echo "=== MENSAGENS PENDENTES ===\n";
$result = $mysqli->query('SELECT * FROM mensagens_pendentes ORDER BY id DESC LIMIT 5');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Número: {$row['numero']} | Mensagem: {$row['mensagem']}\n";
}

echo "\n=== MOVENDO MENSAGENS PARA CLIENTE 156 ===\n";

// Mover mensagens pendentes do número 554796164699 para o cliente 156
$sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
        SELECT 36, 156, mensagem, tipo, data_hora, 'recebido', 'recebido' 
        FROM mensagens_pendentes 
        WHERE numero = '554796164699'";

if ($mysqli->query($sql)) {
    $afetadas = $mysqli->affected_rows;
    echo "SUCESSO: $afetadas mensagens movidas para cliente 156\n";
    
    // Limpar mensagens pendentes movidas
    $mysqli->query("DELETE FROM mensagens_pendentes WHERE numero = '554796164699'");
    echo "Mensagens pendentes limpas\n";
} else {
    echo "ERRO: " . $mysqli->error . "\n";
}

echo "\n=== VERIFICANDO MENSAGENS DO CLIENTE 156 ===\n";
$result = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE cliente_id = 156 AND direcao = 'recebido' ORDER BY id DESC LIMIT 3");
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Mensagem: {$row['mensagem']} | Data: {$row['data_hora']}\n";
}
?> 