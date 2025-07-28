<?php
require_once __DIR__ . '/../config.php';
require_once 'db.php';

echo "🔧 CORRIGINDO DADOS PROBLEMÁTICOS NA TABELA CLIENTES\n\n";

// 1. Corrigir cliente específico que está causando erro
echo "1. Corrigindo cliente cus_000123603388:\n";
$asaas_id = 'cus_000123603388';
$cidade_corrigida = 'Osasco'; // Baseado no CEP 06236795
$pais_corrigido = 'Brasil';

$sql = "UPDATE clientes SET 
        cidade = ?, 
        pais = ? 
        WHERE asaas_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('sss', $cidade_corrigida, $pais_corrigido, $asaas_id);

if ($stmt->execute()) {
    echo "   ✅ Cliente $asaas_id corrigido com sucesso\n";
    echo "   📝 Cidade: $cidade_corrigida, País: $pais_corrigido\n";
} else {
    echo "   ❌ Erro ao corrigir cliente: " . $stmt->error . "\n";
}
$stmt->close();

// 2. Verificar e corrigir outros clientes com problemas similares
echo "\n2. Verificando outros clientes com dados incorretos:\n";

// Buscar clientes com cidade numérica
$result = $mysqli->query("SELECT id, asaas_id, nome, cidade, pais FROM clientes WHERE cidade REGEXP '^[0-9]+$'");
if ($result && $result->num_rows > 0) {
    echo "   📊 Encontrados " . $result->num_rows . " clientes com cidade numérica:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['id']}, Asaas: {$row['asaas_id']}, Nome: {$row['nome']}, Cidade: {$row['cidade']}\n";
    }
} else {
    echo "   ✅ Nenhum cliente com cidade numérica encontrado\n";
}

// Buscar clientes com país numérico ou vazio
$result = $mysqli->query("SELECT id, asaas_id, nome, pais FROM clientes WHERE pais = '0' OR pais = '' OR pais IS NULL");
if ($result && $result->num_rows > 0) {
    echo "\n   📊 Encontrados " . $result->num_rows . " clientes com país incorreto:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['id']}, Asaas: {$row['asaas_id']}, Nome: {$row['nome']}, País: '{$row['pais']}'\n";
    }
    
    // Corrigir todos os países incorretos
    echo "\n   🔧 Corrigindo países incorretos...\n";
    $pais_padrao = 'Brasil';
    $sql_corrigir_pais = "UPDATE clientes SET pais = ? WHERE pais = '0' OR pais = '' OR pais IS NULL";
    $stmt_pais = $mysqli->prepare($sql_corrigir_pais);
    $stmt_pais->bind_param('s', $pais_padrao);
    
    if ($stmt_pais->execute()) {
        echo "   ✅ " . $stmt_pais->affected_rows . " clientes tiveram o país corrigido para '$pais_padrao'\n";
    } else {
        echo "   ❌ Erro ao corrigir países: " . $stmt_pais->error . "\n";
    }
    $stmt_pais->close();
} else {
    echo "   ✅ Nenhum cliente com país incorreto encontrado\n";
}

// 3. Verificar estrutura da tabela
echo "\n3. Verificando estrutura da tabela:\n";
$result = $mysqli->query("DESCRIBE clientes");
if ($result) {
    echo "   📋 Campos da tabela clientes:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Field']}: {$row['Type']} | Null: {$row['Null']} | Default: " . ($row['Default'] ?? 'NULL') . "\n";
    }
}

// 4. Verificar índices
echo "\n4. Verificando índices:\n";
$result = $mysqli->query("SHOW INDEX FROM clientes");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Key_name']}: {$row['Column_name']} | Unique: " . ($row['Non_unique'] ? 'Não' : 'Sim') . "\n";
    }
}

echo "\n🎯 Correções concluídas! Agora a sincronização deve funcionar sem problemas.\n";
?> 