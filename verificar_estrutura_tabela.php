<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔍 Verificação da Estrutura da Tabela</h1>";

// Verificar estrutura da tabela canais_comunicacao
$result = $mysqli->query("DESCRIBE canais_comunicacao");

echo "<h2>📋 Estrutura da tabela canais_comunicacao:</h2>";
echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<thead>";
echo "<tr style='background: #f8fafc;'>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Campo</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Tipo</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Null</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Key</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Default</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600;'>{$row['Field']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$row['Type']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$row['Null']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$row['Key']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$row['Default']}</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";

// Verificar dados dos canais
echo "<h2>📊 Dados dos Canais:</h2>";
$canais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE tipo = 'whatsapp'");

echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<thead>";
echo "<tr style='background: #f8fafc;'>";

if ($canais->num_rows > 0) {
    $first_row = $canais->fetch_assoc();
    $canais->data_seek(0); // Reset pointer
    
    foreach ($first_row as $key => $value) {
        echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>$key</th>";
    }
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($canal = $canais->fetch_assoc()) {
        echo "<tr>";
        foreach ($canal as $value) {
            echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</tbody>";
} else {
    echo "<tr><td colspan='5' style='padding: 0.5rem; text-align: center;'>Nenhum canal encontrado</td></tr>";
}

echo "</table>";
echo "</div>";

echo "<p style='color: green; font-weight: bold;'>✅ Estrutura da tabela verificada!</p>";
?> 