<?php
require_once 'config.php';
require_once 'painel/db.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>Verificação de Mensagens - Canais 3000 e 3001</h2>";
echo "<p>Data/Hora da verificação: " . date('Y-m-d H:i:s') . "</p>";

// Verificar mensagens do canal 3000
echo "<h3>Canal 3000 (Pixel12Digital)</h3>";
$sql_3000 = "SELECT 
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
WHERE m.canal_id = 3000 
   OR m.canal_nome LIKE '%3000%'
   OR m.numero_whatsapp LIKE '%554797146908%'
ORDER BY m.data_hora DESC 
LIMIT 20";

$result_3000 = $mysqli->query($sql_3000);

if ($result_3000 && $result_3000->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Canal ID</th><th>Canal Nome</th><th>Cliente ID</th><th>Mensagem</th><th>Tipo</th><th>Data/Hora</th><th>Direção</th><th>Status</th><th>Número WhatsApp</th><th>Message ID</th><th>Erro</th>";
    echo "</tr>";
    
    while ($row = $result_3000->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['canal_nome']) . "</td>";
        echo "<td>" . $row['cliente_id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['mensagem'], 0, 100)) . (strlen($row['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td>" . $row['tipo'] . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "<td>" . $row['direcao'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['numero_whatsapp'] . "</td>";
        echo "<td>" . $row['whatsapp_message_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['motivo_erro']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Nenhuma mensagem encontrada para o canal 3000</p>";
}

// Verificar mensagens do canal 3001
echo "<h3>Canal 3001 (Pixel - Comercial)</h3>";
$sql_3001 = "SELECT 
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
WHERE m.canal_id = 3001 
   OR m.canal_nome LIKE '%3001%'
   OR m.numero_whatsapp LIKE '%554797309525%'
ORDER BY m.data_hora DESC 
LIMIT 20";

$result_3001 = $mysqli->query($sql_3001);

if ($result_3001 && $result_3001->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Canal ID</th><th>Canal Nome</th><th>Cliente ID</th><th>Mensagem</th><th>Tipo</th><th>Data/Hora</th><th>Direção</th><th>Status</th><th>Número WhatsApp</th><th>Message ID</th><th>Erro</th>";
    echo "</tr>";
    
    while ($row = $result_3001->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['canal_nome']) . "</td>";
        echo "<td>" . $row['cliente_id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['mensagem'], 0, 100)) . (strlen($row['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td>" . $row['tipo'] . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "<td>" . $row['direcao'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['numero_whatsapp'] . "</td>";
        echo "<td>" . $row['whatsapp_message_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['motivo_erro']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Nenhuma mensagem encontrada para o canal 3001</p>";
}

// Verificar mensagens específicas mencionadas
echo "<h3>Mensagens Específicas Mencionadas</h3>";
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
WHERE m.mensagem LIKE '%Teste mensagem enviada de canal 3001%'
   OR m.mensagem LIKE '%Teste mensagem enviada de canal 3000%'
   OR m.mensagem LIKE '%554797309525%'
   OR m.mensagem LIKE '%554797146908%'
ORDER BY m.data_hora DESC 
LIMIT 10";

$result_especificas = $mysqli->query($sql_especificas);

if ($result_especificas && $result_especificas->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e0f0e0;'>";
    echo "<th>ID</th><th>Canal ID</th><th>Canal Nome</th><th>Cliente ID</th><th>Mensagem</th><th>Tipo</th><th>Data/Hora</th><th>Direção</th><th>Status</th><th>Número WhatsApp</th><th>Message ID</th><th>Erro</th>";
    echo "</tr>";
    
    while ($row = $result_especificas->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['canal_nome']) . "</td>";
        echo "<td>" . $row['cliente_id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['mensagem'], 0, 150)) . (strlen($row['mensagem']) > 150 ? '...' : '') . "</td>";
        echo "<td>" . $row['tipo'] . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "<td>" . $row['direcao'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['numero_whatsapp'] . "</td>";
        echo "<td>" . $row['whatsapp_message_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['motivo_erro']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Nenhuma mensagem específica encontrada</p>";
}

// Verificar estrutura da tabela
echo "<h3>Estrutura da Tabela mensagens_comunicacao</h3>";
$sql_structure = "DESCRIBE mensagens_comunicacao";
$result_structure = $mysqli->query($sql_structure);

if ($result_structure) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    while ($row = $result_structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$mysqli->close();
?> 