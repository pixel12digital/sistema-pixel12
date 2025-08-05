<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔍 Verificação de Mensagens - Canais 3000 e 3001</h2>";
echo "<p><strong>Data/Hora da verificação:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar mensagens específicas mencionadas
echo "<h3>📨 Mensagens Encontradas dos Canais 3000 e 3001</h3>";

$sql_especificas = "SELECT 
    m.id,
    m.canal_id,
    m.canal_nome,
    m.cliente_id,
    m.mensagem,
    m.tipo,
    m.data_hora,
    m.direcao,
    m.status,
    m.numero_whatsapp,
    m.whatsapp_message_id,
    m.motivo_erro
FROM mensagens_comunicacao m 
WHERE (m.mensagem LIKE '%Teste mensagem enviada de canal 3001%'
   OR m.mensagem LIKE '%Teste mensagem enviada de canal 3000%'
   OR m.mensagem LIKE '%554797309525%'
   OR m.mensagem LIKE '%554797146908%'
   OR m.canal_id IN (36, 37))
   AND m.data_hora >= '2025-08-04'
ORDER BY m.data_hora DESC";

$result_especificas = $mysqli->query($sql_especificas);

if ($result_especificas && $result_especificas->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Canal ID</th><th>Canal Nome</th><th>Cliente ID</th><th>Mensagem</th><th>Tipo</th><th>Data/Hora</th><th>Direção</th><th>Status</th><th>Número WhatsApp</th><th>Message ID</th><th>Erro</th>";
    echo "</tr>";
    
    while ($row = $result_especificas->fetch_assoc()) {
        $bg_color = ($row['direcao'] == 'enviado') ? '#e8f5e8' : '#f0f8ff';
        echo "<tr style='background-color: $bg_color;'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['canal_nome']) . "</td>";
        echo "<td>" . $row['cliente_id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($row['mensagem'], 0, 200)) . (strlen($row['mensagem']) > 200 ? '...' : '') . "</td>";
        echo "<td>" . $row['tipo'] . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "<td style='font-weight: bold; color: " . ($row['direcao'] == 'enviado' ? 'green' : 'blue') . ";'>" . $row['direcao'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['numero_whatsapp'] . "</td>";
        echo "<td>" . $row['whatsapp_message_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['motivo_erro']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><strong>Total de mensagens encontradas:</strong> " . $result_especificas->num_rows . "</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Nenhuma mensagem específica encontrada</p>";
}

// Verificar mensagens mais recentes
echo "<h3>🕒 Mensagens Mais Recentes (Últimas 10)</h3>";
$sql_recentes = "SELECT 
    m.id,
    m.canal_id,
    m.canal_nome,
    m.cliente_id,
    m.mensagem,
    m.tipo,
    m.data_hora,
    m.direcao,
    m.status,
    m.numero_whatsapp
FROM mensagens_comunicacao m 
WHERE m.data_hora >= '2025-08-05'
ORDER BY m.data_hora DESC 
LIMIT 10";

$result_recentes = $mysqli->query($sql_recentes);

if ($result_recentes && $result_recentes->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #2196F3; color: white;'>";
    echo "<th>ID</th><th>Canal ID</th><th>Canal Nome</th><th>Cliente ID</th><th>Mensagem</th><th>Tipo</th><th>Data/Hora</th><th>Direção</th><th>Status</th><th>Número WhatsApp</th>";
    echo "</tr>";
    
    while ($row = $result_recentes->fetch_assoc()) {
        $bg_color = ($row['direcao'] == 'enviado') ? '#e8f5e8' : '#f0f8ff';
        echo "<tr style='background-color: $bg_color;'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['canal_nome']) . "</td>";
        echo "<td>" . $row['cliente_id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($row['mensagem'], 0, 150)) . (strlen($row['mensagem']) > 150 ? '...' : '') . "</td>";
        echo "<td>" . $row['tipo'] . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "<td style='font-weight: bold; color: " . ($row['direcao'] == 'enviado' ? 'green' : 'blue') . ";'>" . $row['direcao'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['numero_whatsapp'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Nenhuma mensagem recente encontrada</p>";
}

// Resumo estatístico
echo "<h3>📊 Resumo Estatístico</h3>";
$sql_stats = "SELECT 
    COUNT(*) as total_mensagens,
    COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas,
    COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
    COUNT(CASE WHEN data_hora >= CURDATE() THEN 1 END) as hoje,
    COUNT(CASE WHEN data_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as ultima_semana
FROM mensagens_comunicacao";

$result_stats = $mysqli->query($sql_stats);
if ($result_stats && $row = $result_stats->fetch_assoc()) {
    echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Total de mensagens:</strong> " . $row['total_mensagens'] . "</p>";
    echo "<p><strong>Mensagens enviadas:</strong> " . $row['enviadas'] . "</p>";
    echo "<p><strong>Mensagens recebidas:</strong> " . $row['recebidas'] . "</p>";
    echo "<p><strong>Mensagens hoje:</strong> " . $row['hoje'] . "</p>";
    echo "<p><strong>Mensagens última semana:</strong> " . $row['ultima_semana'] . "</p>";
    echo "</div>";
}

$mysqli->close();
?> 