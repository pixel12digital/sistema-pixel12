<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>Localizar e Alterar Mensagem Agendada #4</h1>";
echo "<h2>Teste para hoje às 17:35</h2>";

// Buscar a mensagem #4
$sql = "SELECT ma.*, c.nome as cliente_nome, c.celular 
        FROM mensagens_agendadas ma 
        JOIN clientes c ON ma.cliente_id = c.id 
        WHERE ma.id = 4";

$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "<p style='color: red;'>❌ Mensagem #4 não encontrada!</p>";
    
    // Mostrar todas as mensagens agendadas
    echo "<h3>Todas as mensagens agendadas:</h3>";
    $sql_todas = "SELECT ma.*, c.nome as cliente_nome 
                  FROM mensagens_agendadas ma 
                  JOIN clientes c ON ma.cliente_id = c.id 
                  ORDER BY ma.id ASC";
    $result_todas = $mysqli->query($sql_todas);
    
    if ($result_todas && $result_todas->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Tipo</th><th>Prioridade</th><th>Data Agendada</th><th>Status</th></tr>";
        
        while ($row = $result_todas->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['cliente_nome']}</td>";
            echo "<td>{$row['tipo']}</td>";
            echo "<td>{$row['prioridade']}</td>";
            echo "<td>{$row['data_agendada']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    exit;
}

$mensagem = $result->fetch_assoc();

echo "<h3>Mensagem #4 Encontrada:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
echo "<p><strong>ID:</strong> {$mensagem['id']}</p>";
echo "<p><strong>Cliente:</strong> {$mensagem['cliente_nome']}</p>";
echo "<p><strong>Celular:</strong> {$mensagem['celular']}</p>";
echo "<p><strong>Tipo:</strong> {$mensagem['tipo']}</p>";
echo "<p><strong>Prioridade:</strong> {$mensagem['prioridade']}</p>";
echo "<p><strong>Data Agendada Atual:</strong> {$mensagem['data_agendada']}</p>";
echo "<p><strong>Status:</strong> {$mensagem['status']}</p>";
echo "</div>";

echo "<h3>Conteúdo da Mensagem:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
echo htmlspecialchars($mensagem['mensagem']);
echo "</pre>";

// Alterar para hoje às 17:35
$nova_data = date('Y-m-d') . ' 17:35:00';

echo "<h3>Alterando agendamento...</h3>";

$sql_update = "UPDATE mensagens_agendadas 
               SET data_agendada = '$nova_data', 
                   data_atualizacao = NOW() 
               WHERE id = 4";

if ($mysqli->query($sql_update)) {
    echo "<p style='color: green;'>✅ Agendamento alterado com sucesso!</p>";
    echo "<p><strong>Nova data:</strong> $nova_data</p>";
    
    // Verificar se foi alterado
    $sql_check = "SELECT data_agendada FROM mensagens_agendadas WHERE id = 4";
    $result_check = $mysqli->query($sql_check);
    $check = $result_check->fetch_assoc();
    
    echo "<p><strong>Data confirmada no banco:</strong> {$check['data_agendada']}</p>";
    
    // Log da alteração
    $log_data = date('Y-m-d H:i:s') . " - Mensagem #4 alterada para $nova_data (teste)\n";
    file_put_contents('logs/alteracao_mensagem_teste.log', $log_data, FILE_APPEND);
    
} else {
    echo "<p style='color: red;'>❌ Erro ao alterar agendamento: " . $mysqli->error . "</p>";
}

echo "<h3>Teste de Envio Manual</h3>";

// Testar envio manual da mensagem
echo "<p>Deseja testar o envio manual da mensagem agora?</p>";

$numero_limpo = preg_replace('/\D/', '', $mensagem['celular']);
$numero_formatado = '55' . $numero_limpo . '@c.us';

$payload = json_encode([
    'sessionName' => 'default',
    'number' => $numero_formatado,
    'message' => $mensagem['mensagem']
]);

echo "<p><strong>Número formatado:</strong> $numero_formatado</p>";
echo "<p><strong>Payload:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($payload);
echo "</pre>";

// Fazer o envio de teste
$ch = curl_init("http://212.85.11.238:3000/send/text");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h4>Resultado do Envio de Teste:</h4>";
echo "<ul>";
echo "<li><strong>HTTP Code:</strong> $http_code</li>";
echo "<li><strong>Resposta:</strong> " . htmlspecialchars($response) . "</li>";
if ($error) {
    echo "<li style='color: red;'><strong>Erro cURL:</strong> $error</li>";
}
echo "</ul>";

$data_response = json_decode($response, true);
if ($http_code === 200 && $data_response && isset($data_response['success']) && $data_response['success']) {
    echo "<p style='color: green;'>✅ <strong>Mensagem de teste enviada com sucesso!</strong></p>";
    
    // Marcar como enviada no banco
    $sql_marcar = "UPDATE mensagens_agendadas 
                   SET status = 'enviada', 
                       data_atualizacao = NOW() 
                   WHERE id = 4";
    $mysqli->query($sql_marcar);
    
    echo "<p style='color: green;'>✅ Status alterado para 'enviada' no banco</p>";
} else {
    echo "<p style='color: red;'>❌ Falha no envio da mensagem de teste</p>";
}

echo "<h3>Resumo do Teste</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Teste Concluído</h4>";
echo "<p><strong>Mensagem #4:</strong> {$mensagem['cliente_nome']}</p>";
echo "<p><strong>Agendamento alterado para:</strong> $nova_data</p>";
echo "<p><strong>Envio de teste:</strong> " . ($http_code === 200 ? 'SUCESSO' : 'FALHA') . "</p>";
echo "<p><strong>Status no banco:</strong> Atualizado</p>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Teste concluído em:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 