<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🗑️  REMOVENDO REGISTROS PROBLEMÁTICOS\n";
echo "====================================\n\n";

// Backup antes da remoção
echo "💾 Criando backup antes da remoção...\n";
$backup_file = 'backups/registros_problematicos_' . date('Y-m-d_H-i-s') . '.sql';

// Criar diretório de backup se não existir
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
}

// Fazer backup dos registros que serão removidos
$backup_content = "-- Backup de registros problemáticos - " . date('Y-m-d H:i:s') . "\n";
$backup_content .= "-- Registros sem asaas_id\n\n";

// Backup dos registros sem asaas_id
$sql_backup = "SELECT * FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''";
$result = $mysqli->query($sql_backup);

if ($result && $result->num_rows > 0) {
    while ($cliente = $result->fetch_assoc()) {
        $backup_content .= "INSERT INTO clientes VALUES (";
        $values = [];
        foreach ($cliente as $value) {
            $values[] = $mysqli->real_escape_string($value);
        }
        $backup_content .= "'" . implode("', '", $values) . "');\n";
    }
}

file_put_contents($backup_file, $backup_content);
echo "✅ Backup criado: $backup_file\n";

echo "\n🔍 EXECUTANDO REMOÇÃO:\n";

// Remover registros sem asaas_id
echo "🗑️  Removendo registros sem asaas_id...\n";
$sql_delete = "DELETE FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''";
if ($mysqli->query($sql_delete)) {
    $linhas = $mysqli->affected_rows;
    echo "✅ $linhas registros sem asaas_id removidos\n";
} else {
    echo "❌ Erro ao remover registros: " . $mysqli->error . "\n";
}

echo "\n📊 VERIFICANDO RESULTADO:\n";

// Verificar se ainda há registros com asaas_id vazio
$sql_check = "SELECT COUNT(*) as total FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''";
$result = $mysqli->query($sql_check);
if ($result) {
    $total = $result->fetch_assoc()['total'];
    echo "   Registros com asaas_id vazio: $total\n";
}

// Verificar total de clientes
$total_clientes = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
if ($total_clientes) {
    $total = $total_clientes->fetch_assoc()['total'];
    echo "   Total de clientes no banco: $total\n";
}

echo "\n🔧 CRIANDO ÍNDICE ÚNICO PARA ASAAS_ID:\n";

// Agora tentar criar o índice único
$sql_create = "CREATE UNIQUE INDEX idx_asaas_id_unique ON clientes(asaas_id)";
if ($mysqli->query($sql_create)) {
    echo "✅ Índice único criado para asaas_id com sucesso!\n";
} else {
    echo "❌ Erro ao criar índice único: " . $mysqli->error . "\n";
}

echo "\n📊 VERIFICANDO ÍNDICES CRIADOS:\n";
$sql_indices = "SHOW INDEX FROM clientes WHERE Key_name LIKE '%unique%'";
$result = $mysqli->query($sql_indices);

if ($result) {
    echo "   Índices únicos ativos:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Key_name']} em {$row['Column_name']}\n";
    }
} else {
    echo "   Nenhum índice único encontrado\n";
}

$mysqli->close();
echo "\n✅ Remoção de registros problemáticos concluída!\n";
echo "💾 Backup salvo em: $backup_file\n";
?> 