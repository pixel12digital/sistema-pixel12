<?php
/**
 * 🔍 VERIFICAR MENSAGEM "teste as 11:07"
 * Verifica se a mensagem foi salva no banco de dados
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🔍 Verificando Mensagem 'teste as 11:07'</h2>";

// Buscar mensagem específica
$sql = "SELECT * FROM mensagens_comunicacao 
        WHERE mensagem LIKE '%teste%11:07%' 
        OR mensagem LIKE '%11:07%'
        OR data_hora LIKE '%11:07%'
        ORDER BY data_hora DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<h3>✅ Mensagem encontrada no banco!</h3>";
    echo "<p>Total de mensagens encontradas: " . $result->num_rows . "</p>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>ID:</strong> " . $row['id'] . "<br>";
        echo "<strong>Cliente ID:</strong> " . $row['cliente_id'] . "<br>";
        echo "<strong>Mensagem:</strong> " . htmlspecialchars($row['mensagem']) . "<br>";
        echo "<strong>Direção:</strong> " . $row['direcao'] . "<br>";
        echo "<strong>Data/Hora:</strong> " . $row['data_hora'] . "<br>";
        echo "<strong>Número WhatsApp:</strong> " . $row['numero_whatsapp'] . "<br>";
        echo "<strong>Canal ID:</strong> " . $row['canal_id'] . "<br>";
        echo "</div>";
    }
} else {
    echo "<h3>❌ Mensagem NÃO encontrada no banco</h3>";
    echo "<p>A mensagem 'teste as 11:07' não foi salva no banco de dados.</p>";
}

echo "<h3>📊 Verificando mensagens de hoje (11:07)</h3>";

// Buscar mensagens de hoje às 11:07
$sql = "SELECT * FROM mensagens_comunicacao 
        WHERE DATE(data_hora) = CURDATE() 
        AND TIME(data_hora) LIKE '%11:07%'
        ORDER BY data_hora DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<p>Mensagens encontradas às 11:07 hoje:</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 5px; margin: 5px 0;'>";
        echo "ID: " . $row['id'] . " | Mensagem: " . htmlspecialchars($row['mensagem']) . " | Direção: " . $row['direcao'] . " | Hora: " . $row['data_hora'];
        echo "</div>";
    }
} else {
    echo "<p>Nenhuma mensagem encontrada às 11:07 hoje.</p>";
}

echo "<h3>📋 Últimas mensagens do Charles (554796164699)</h3>";

// Buscar últimas mensagens do Charles
$sql = "SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp = '554796164699' 
        OR numero_whatsapp = '4796164699'
        ORDER BY data_hora DESC 
        LIMIT 10";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<p>Últimas mensagens do Charles:</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #eee; padding: 5px; margin: 5px 0;'>";
        echo "ID: " . $row['id'] . " | Mensagem: " . htmlspecialchars($row['mensagem']) . " | Direção: " . $row['direcao'] . " | Hora: " . $row['data_hora'] . " | Número: " . $row['numero_whatsapp'];
        echo "</div>";
    }
} else {
    echo "<p>Nenhuma mensagem encontrada para o Charles.</p>";
}

echo "<h3>🔍 Verificando logs do webhook</h3>";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    echo "<p>Log encontrado: $log_file</p>";
    $log_content = file_get_contents($log_file);
    
    // Buscar por "11:07" no log
    if (strpos($log_content, '11:07') !== false) {
        echo "<p>✅ Encontrada referência a '11:07' no log!</p>";
        
        // Mostrar linhas que contêm 11:07
        $lines = explode("\n", $log_content);
        foreach ($lines as $line) {
            if (strpos($line, '11:07') !== false) {
                echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px 0; font-family: monospace;'>";
                echo htmlspecialchars($line);
                echo "</div>";
            }
        }
    } else {
        echo "<p>❌ Nenhuma referência a '11:07' encontrada no log.</p>";
    }
} else {
    echo "<p>❌ Log não encontrado: $log_file</p>";
}

echo "<h3>📊 Resumo</h3>";

if ($result && $result->num_rows > 0) {
    echo "<p><strong>✅ A mensagem foi processada pelo sistema</strong></p>";
    echo "<p>Se não aparece no chat, pode ser um problema de:</p>";
    echo "<ul>";
    echo "<li>Cache do frontend não atualizado</li>";
    echo "<li>Problema na exibição do painel</li>";
    echo "<li>Filtro de conversas</li>";
    echo "</ul>";
} else {
    echo "<p><strong>❌ A mensagem NÃO foi processada pelo sistema</strong></p>";
    echo "<p>Isso indica que:</p>";
    echo "<ul>";
    echo "<li>O webhook não foi chamado</li>";
    echo "<li>Houve erro no processamento</li>";
    echo "<li>A mensagem não chegou ao VPS</li>";
    echo "</ul>";
}

echo "<h3>🔧 Próximos Passos</h3>";
echo "<p>1. Verifique se o webhook está sendo chamado</p>";
echo "<p>2. Teste enviando uma nova mensagem</p>";
echo "<p>3. Verifique os logs do VPS: <code>ssh root@212.85.11.238</code></p>";
?> 