<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>Teste do Monitoramento Corrigido</h1>";

// Buscar um cliente com cobranças vencidas para teste
$sql = "SELECT c.id, c.nome, c.celular, c.email, 
               COUNT(cob.id) as total_cobrancas,
               SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 ELSE 0 END) as cobrancas_vencidas
        FROM clientes c
        LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
        GROUP BY c.id, c.nome, c.celular, c.email
        HAVING cobrancas_vencidas > 0
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "<p style='color: red;'>Nenhum cliente com cobranças vencidas encontrado para teste.</p>";
    exit;
}

$cliente = $result->fetch_assoc();

echo "<h2>Cliente de Teste</h2>";
echo "<p><strong>ID:</strong> {$cliente['id']}</p>";
echo "<p><strong>Nome:</strong> {$cliente['nome']}</p>";
echo "<p><strong>Celular:</strong> {$cliente['celular']}</p>";
echo "<p><strong>Email:</strong> {$cliente['email']}</p>";
echo "<p><strong>Total de Cobranças:</strong> {$cliente['total_cobrancas']}</p>";
echo "<p><strong>Cobranças Vencidas:</strong> {$cliente['cobrancas_vencidas']}</p>";

echo "<h2>Teste de Monitoramento</h2>";

// Simular requisição POST
$input = [
    'cliente_id' => $cliente['id'],
    'monitorado' => 1
];

echo "<h3>1. Testando ativação do monitoramento...</h3>";

// Fazer a requisição para a API
$ch = curl_init('http://localhost:8080/loja-virtual-revenda/painel/api/salvar_monitoramento_cliente.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
if ($error) {
    echo "<p style='color: red;'><strong>Erro cURL:</strong> $error</p>";
}

echo "<p><strong>Resposta:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($response);
echo "</pre>";

// Decodificar resposta
$data = json_decode($response, true);

if ($data) {
    if ($data['success']) {
        echo "<p style='color: green;'>✅ Monitoramento ativado com sucesso!</p>";
        
        if (isset($data['avisos']) && !empty($data['avisos'])) {
            echo "<h3>Avisos:</h3>";
            echo "<ul>";
            foreach ($data['avisos'] as $aviso) {
                echo "<li style='color: orange;'>⚠️ $aviso</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ Erro: " . ($data['error'] ?? 'Erro desconhecido') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Erro ao decodificar resposta JSON</p>";
}

echo "<h3>2. Verificando status no banco...</h3>";

// Verificar se foi salvo no banco
$sql_check = "SELECT monitorado, data_atualizacao FROM clientes_monitoramento WHERE cliente_id = {$cliente['id']}";
$result_check = $mysqli->query($sql_check);

if ($result_check && $result_check->num_rows > 0) {
    $monitoramento = $result_check->fetch_assoc();
    echo "<p style='color: green;'>✅ Status no banco: " . ($monitoramento['monitorado'] ? 'Monitorado' : 'Não monitorado') . "</p>";
    echo "<p><strong>Última atualização:</strong> {$monitoramento['data_atualizacao']}</p>";
} else {
    echo "<p style='color: red;'>❌ Cliente não encontrado na tabela de monitoramento</p>";
}

echo "<h3>3. Verificando mensagens agendadas...</h3>";

// Verificar se foram agendadas mensagens
$sql_mensagens = "SELECT COUNT(*) as total FROM mensagens_agendadas WHERE cliente_id = {$cliente['id']} AND tipo = 'cobranca_vencida'";
$result_mensagens = $mysqli->query($sql_mensagens);

if ($result_mensagens) {
    $mensagens = $result_mensagens->fetch_assoc();
    echo "<p><strong>Mensagens agendadas:</strong> {$mensagens['total']}</p>";
    
    if ($mensagens['total'] > 0) {
        echo "<p style='color: green;'>✅ Mensagens foram agendadas automaticamente</p>";
        
        // Mostrar detalhes das mensagens
        $sql_detalhes = "SELECT mensagem, prioridade, data_agendada, status FROM mensagens_agendadas 
                        WHERE cliente_id = {$cliente['id']} AND tipo = 'cobranca_vencida' 
                        ORDER BY data_agendada ASC";
        $result_detalhes = $mysqli->query($sql_detalhes);
        
        if ($result_detalhes && $result_detalhes->num_rows > 0) {
            echo "<h4>Detalhes das mensagens:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Prioridade</th><th>Data Agendada</th><th>Status</th><th>Mensagem</th></tr>";
            
            while ($msg = $result_detalhes->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$msg['prioridade']}</td>";
                echo "<td>{$msg['data_agendada']}</td>";
                echo "<td>{$msg['status']}</td>";
                echo "<td>" . substr($msg['mensagem'], 0, 100) . "...</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhuma mensagem foi agendada</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Erro ao verificar mensagens agendadas</p>";
}

echo "<h3>4. Logs recentes...</h3>";

// Mostrar logs recentes
$log_file = __DIR__ . '/logs/monitoramento_clientes.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $linhas = explode("\n", $log_content);
    $ultimas_linhas = array_slice($linhas, -10); // Últimas 10 linhas
    
    echo "<h4>Últimas 10 linhas do log:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    foreach ($ultimas_linhas as $linha) {
        if (trim($linha)) {
            echo htmlspecialchars($linha) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Arquivo de log não encontrado</p>";
}

echo "<hr>";
echo "<p><strong>Teste concluído em:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 