<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔍 Teste da Consulta de Mensagens</h2>";
echo "<p><strong>Data/Hora do teste:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Testar com o cliente_id 4296 (Charles Dietrich)
$cliente_id = 4296;

echo "<h3>📊 Testando Consulta para Cliente ID: $cliente_id</h3>";

// Consulta principal (igual à do api/mensagens_cliente.php)
$sql_principal = "SELECT m.*, 
                       c.nome_exibicao as canal_nome,
                       c.porta as canal_porta,
                       c.identificador as canal_identificador,
                       CASE 
                           WHEN m.direcao = 'enviado' THEN 'Você'
                           WHEN m.direcao = 'recebido' THEN c.nome_exibicao
                           ELSE 'Sistema'
                       END as contato_interagiu
                FROM mensagens_comunicacao m
                LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                WHERE m.cliente_id = ?
                ORDER BY m.data_hora ASC";

$stmt = $mysqli->prepare($sql_principal);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h4>✅ Resultados da Consulta Principal:</h4>";

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal Nome</th><th>Canal ID</th><th>Contato</th>";
    echo "</tr>";
    
    $mensagens = [];
    while ($msg = $result->fetch_assoc()) {
        $mensagens[] = $msg;
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . (strlen($msg['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: " . ($msg['direcao'] == 'enviado' ? 'green' : 'blue') . ";'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['canal_nome']) . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['contato_interagiu']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><strong>Total de mensagens encontradas:</strong> " . count($mensagens) . "</p>";
    
    // Verificar mensagens específicas dos canais 3000 e 3001
    echo "<h4>🎯 Mensagens dos Canais 3000 e 3001:</h4>";
    $mensagens_especificas = array_filter($mensagens, function($msg) {
        return strpos($msg['mensagem'], 'Teste mensagem enviada de canal 3000') !== false ||
               strpos($msg['mensagem'], 'Teste mensagem enviada de canal 3001') !== false ||
               $msg['canal_id'] == 36 || $msg['canal_id'] == 37;
    });
    
    if (!empty($mensagens_especificas)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
        echo "<tr style='background-color: #2196F3; color: white;'>";
        echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal Nome</th><th>Canal ID</th>";
        echo "</tr>";
        
        foreach ($mensagens_especificas as $msg) {
            echo "<tr>";
            echo "<td>" . $msg['id'] . "</td>";
            echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 150)) . (strlen($msg['mensagem']) > 150 ? '...' : '') . "</td>";
            echo "<td style='font-weight: bold; color: " . ($msg['direcao'] == 'enviado' ? 'green' : 'blue') . ";'>" . $msg['direcao'] . "</td>";
            echo "<td>" . $msg['status'] . "</td>";
            echo "<td>" . $msg['data_hora'] . "</td>";
            echo "<td>" . htmlspecialchars($msg['canal_nome']) . "</td>";
            echo "<td>" . $msg['canal_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Nenhuma mensagem específica dos canais 3000/3001 encontrada!</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem encontrada para o cliente ID: $cliente_id</p>";
}

$stmt->close();

// Verificar se há problemas na tabela canais_comunicacao
echo "<h3>🔍 Verificando Tabela canais_comunicacao</h3>";
$sql_canais = "SELECT id, nome_exibicao, porta, identificador, status FROM canais_comunicacao ORDER BY id";
$result_canais = $mysqli->query($sql_canais);

if ($result_canais && $result_canais->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #FF9800; color: white;'>";
    echo "<th>ID</th><th>Nome Exibição</th><th>Porta</th><th>Identificador</th><th>Status</th>";
    echo "</tr>";
    
    while ($canal = $result_canais->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $canal['id'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['nome_exibicao']) . "</td>";
        echo "<td>" . $canal['porta'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['identificador']) . "</td>";
        echo "<td>" . $canal['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhum canal encontrado!</p>";
}

// Verificar mensagens mais recentes
echo "<h3>🕒 Mensagens Mais Recentes (Últimas 5)</h3>";
$sql_recentes = "SELECT m.id, m.mensagem, m.direcao, m.status, m.data_hora, m.canal_id, c.nome_exibicao as canal_nome
                 FROM mensagens_comunicacao m
                 LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                 WHERE m.cliente_id = $cliente_id
                 ORDER BY m.data_hora DESC
                 LIMIT 5";

$result_recentes = $mysqli->query($sql_recentes);

if ($result_recentes && $result_recentes->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #9C27B0; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Canal Nome</th>";
    echo "</tr>";
    
    while ($msg = $result_recentes->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . (strlen($msg['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: " . ($msg['direcao'] == 'enviado' ? 'green' : 'blue') . ";'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['canal_nome']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem recente encontrada!</p>";
}

$mysqli->close();
?> 