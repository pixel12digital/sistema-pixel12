<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>📡 Verificação de Canais de Comunicação</h1>";

// Buscar todos os canais
$sql = "SELECT * FROM canais_comunicacao ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Tipo</th><th>Status</th><th>Identificador</th></tr>";
    
    while ($canal = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$canal['id']}</td>";
        echo "<td>{$canal['nome_exibicao']}</td>";
        echo "<td>{$canal['tipo']}</td>";
        echo "<td>{$canal['status']}</td>";
        echo "<td>{$canal['identificador']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhum canal encontrado!</p>";
}

// Verificar qual canal é usado para WhatsApp
echo "<h3>🔍 Canal WhatsApp</h3>";
$sql_whatsapp = "SELECT * FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result_whatsapp = $mysqli->query($sql_whatsapp);

if ($result_whatsapp && $result_whatsapp->num_rows > 0) {
    while ($canal = $result_whatsapp->fetch_assoc()) {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<p><strong>Canal WhatsApp:</strong> {$canal['nome_exibicao']} (ID: {$canal['id']})</p>";
        echo "<p><strong>Status:</strong> {$canal['status']}</p>";
        echo "<p><strong>Identificador:</strong> {$canal['identificador']}</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Nenhum canal WhatsApp encontrado</p>";
}

// Verificar mensagens existentes para ver qual canal_id é usado
echo "<h3>📊 Mensagens Existentes</h3>";
$sql_mensagens = "SELECT canal_id, COUNT(*) as total FROM mensagens_comunicacao GROUP BY canal_id ORDER BY canal_id";
$result_mensagens = $mysqli->query($sql_mensagens);

if ($result_mensagens && $result_mensagens->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Canal ID</th><th>Total Mensagens</th></tr>";
    
    while ($msg = $result_mensagens->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$msg['canal_id']}</td>";
        echo "<td>{$msg['total']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma mensagem encontrada</p>";
}

echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 