<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/painel/db.php';

echo "<h2>🔍 ESTRUTURA DA TABELA MENSAGENS_AGENDADAS</h2>";

try {
    // Verificar se a tabela existe
    $sql_check = "SHOW TABLES LIKE 'mensagens_agendadas'";
    $result_check = $mysqli->query($sql_check);
    
    if ($result_check->num_rows === 0) {
        echo "<p style='color: red;'>❌ Tabela 'mensagens_agendadas' não existe!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Tabela 'mensagens_agendadas' existe!</p>";
    
    // Mostrar estrutura da tabela
    $sql_structure = "DESCRIBE mensagens_agendadas";
    $result_structure = $mysqli->query($sql_structure);
    
    if ($result_structure) {
        echo "<h3>📋 ESTRUTURA DA TABELA:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
        echo "</tr>";
        
        while ($row = $result_structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Mostrar dados da tabela
    $sql_data = "SELECT * FROM mensagens_agendadas LIMIT 5";
    $result_data = $mysqli->query($sql_data);
    
    if ($result_data && $result_data->num_rows > 0) {
        echo "<h3>📊 DADOS DA TABELA (Primeiros 5 registros):</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        
        // Cabeçalho
        $first_row = $result_data->fetch_assoc();
        echo "<tr style='background: #f0f0f0;'>";
        foreach ($first_row as $key => $value) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        // Primeira linha
        echo "<tr>";
        foreach ($first_row as $value) {
            echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
        }
        echo "</tr>";
        
        // Resto das linhas
        while ($row = $result_data->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum dado encontrado na tabela.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='http://localhost:8080/painel/monitoramento.php'>← Voltar ao Monitoramento</a></p>";
?> 