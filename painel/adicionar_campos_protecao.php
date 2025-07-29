<?php
/**
 * Script para adicionar campos de proteção à tabela clientes
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

echo "🔧 ADICIONANDO CAMPOS DE PROTEÇÃO À TABELA CLIENTES\n\n";

// Campos de proteção para dados editados manualmente
$camposProtecao = [
    'telefone_editado_manual' => 'TINYINT(1) DEFAULT 0',
    'celular_editado_manual' => 'TINYINT(1) DEFAULT 0',
    'email_editado_manual' => 'TINYINT(1) DEFAULT 0',
    'nome_editado_manual' => 'TINYINT(1) DEFAULT 0',
    'endereco_editado_manual' => 'TINYINT(1) DEFAULT 0',
    'data_ultima_edicao_manual' => 'DATETIME NULL'
];

foreach ($camposProtecao as $campo => $tipo) {
    echo "Verificando campo: $campo\n";
    
    // Verificar se o campo já existe
    $result = $mysqli->query("SHOW COLUMNS FROM clientes LIKE '$campo'");
    
    if ($result && $result->num_rows === 0) {
        echo "  ❌ Campo não existe. Adicionando...\n";
        
        $sql = "ALTER TABLE clientes ADD COLUMN $campo $tipo";
        if ($mysqli->query($sql)) {
            echo "  ✅ Campo $campo adicionado com sucesso\n";
        } else {
            echo "  ❌ Erro ao adicionar campo $campo: " . $mysqli->error . "\n";
        }
    } else {
        echo "  ✅ Campo $campo já existe\n";
    }
}

echo "\n🎯 Verificação da estrutura da tabela:\n";
$result = $mysqli->query("DESCRIBE clientes");
while ($row = $result->fetch_assoc()) {
    if (strpos($row['Field'], '_editado_manual') !== false) {
        echo "  ✅ {$row['Field']} | {$row['Type']}\n";
    }
}

echo "\n✅ Processo concluído!\n";
?> 