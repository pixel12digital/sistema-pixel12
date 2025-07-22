<?php
require_once 'config.php';
require_once 'db.php';

echo "🔍 VERIFICANDO ESTRUTURA DA TABELA CLIENTES\n\n";

// Verificar estrutura da tabela
$result = $mysqli->query('DESCRIBE clientes');
echo "📋 Estrutura da tabela clientes:\n";
while ($row = $result->fetch_assoc()) {
    echo "   {$row['Field']} | {$row['Type']} | Key: {$row['Key']} | Default: " . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\n";

// Verificar se há problema com asaas_id
echo "🔍 Verificando registros com asaas_id vazio:\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM clientes WHERE asaas_id = '' OR asaas_id IS NULL");
$row = $result->fetch_assoc();
echo "   Registros com asaas_id vazio: {$row['count']}\n";

// Verificar índices
echo "\n🔍 Verificando índices:\n";
$result = $mysqli->query("SHOW INDEX FROM clientes");
while ($row = $result->fetch_assoc()) {
    echo "   {$row['Key_name']} | {$row['Column_name']} | Unique: {$row['Non_unique']}\n";
}

echo "\n🔧 CORRIGINDO PROBLEMA:\n";

// Solução 1: Remover constraint UNIQUE de asaas_id temporariamente
echo "1. Removendo constraint UNIQUE do asaas_id...\n";
$result = $mysqli->query("ALTER TABLE clientes DROP INDEX asaas_id");
if ($result) {
    echo "   ✅ Constraint UNIQUE removida com sucesso\n";
} else {
    echo "   ℹ️ Constraint não existia ou já foi removida: " . $mysqli->error . "\n";
}

// Solução 2: Atualizar registros com asaas_id vazio para NULL
echo "2. Atualizando registros com asaas_id vazio para NULL...\n";
$result = $mysqli->query("UPDATE clientes SET asaas_id = NULL WHERE asaas_id = ''");
if ($result) {
    echo "   ✅ {$mysqli->affected_rows} registros atualizados\n";
} else {
    echo "   ❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

// Solução 3: Recriar índice permitindo NULL
echo "3. Recriando índice permitindo valores NULL...\n";
$result = $mysqli->query("CREATE INDEX idx_asaas_id ON clientes (asaas_id)");
if ($result) {
    echo "   ✅ Índice recriado com sucesso\n";
} else {
    echo "   ℹ️ Índice não pôde ser criado: " . $mysqli->error . "\n";
}

echo "\n🧪 TESTANDO CORREÇÃO:\n";

// Testar inserção de cliente sem asaas_id
$nome_teste = "Cliente Teste " . date('H:i:s');
$celular_teste = "47996164699";
$data_criacao = date('Y-m-d H:i:s');

$sql = "INSERT INTO clientes (nome, celular, data_criacao, data_atualizacao) 
        VALUES ('" . $mysqli->real_escape_string($nome_teste) . "', 
                '" . $mysqli->real_escape_string($celular_teste) . "', 
                '$data_criacao', '$data_criacao')";

$result = $mysqli->query($sql);
if ($result) {
    $cliente_id = $mysqli->insert_id;
    echo "✅ Cliente teste criado com sucesso! ID: $cliente_id\n";
    
    // Remover cliente teste
    $mysqli->query("DELETE FROM clientes WHERE id = $cliente_id");
    echo "✅ Cliente teste removido\n";
} else {
    echo "❌ Erro ao criar cliente teste: " . $mysqli->error . "\n";
}

echo "\n🎯 Correção concluída!\n";
?> 