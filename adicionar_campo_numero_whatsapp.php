<?php
/**
 * ADICIONAR CAMPO NUMERO_WHATSAPP
 * 
 * Script para adicionar o campo numero_whatsapp na tabela mensagens_comunicacao
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 ADICIONANDO CAMPO NUMERO_WHATSAPP\n";
echo "====================================\n\n";

// Verificar se o campo já existe
$result = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'numero_whatsapp'");

if ($result && $result->num_rows > 0) {
    echo "✅ Campo 'numero_whatsapp' já existe na tabela mensagens_comunicacao\n";
} else {
    echo "📝 Campo 'numero_whatsapp' não encontrado. Adicionando...\n";
    
    // Adicionar o campo
    $sql = "ALTER TABLE mensagens_comunicacao ADD COLUMN numero_whatsapp VARCHAR(20) NULL AFTER status";
    
    if ($mysqli->query($sql)) {
        echo "✅ Campo 'numero_whatsapp' adicionado com sucesso!\n";
    } else {
        echo "❌ Erro ao adicionar campo: " . $mysqli->error . "\n";
    }
}

// Verificar estrutura atual da tabela
echo "\n📋 ESTRUTURA ATUAL DA TABELA:\n";
echo "==============================\n";

$result = $mysqli->query("DESCRIBE mensagens_comunicacao");
while ($row = $result->fetch_assoc()) {
    echo "   {$row['Field']} | {$row['Type']} | Null: {$row['Null']} | Default: " . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\n✅ Verificação concluída!\n";
?> 