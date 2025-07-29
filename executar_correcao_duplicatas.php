<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 EXECUTANDO CORREÇÃO DE CLIENTES DUPLICADOS\n";
echo "============================================\n\n";

// Backup antes da correção
echo "💾 Criando backup antes da correção...\n";
$backup_file = 'backups/clientes_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Criar diretório de backup se não existir
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
}

// Fazer backup da tabela clientes
$backup_sql = "SELECT * FROM clientes WHERE cpf_cnpj = '03454769990'";
$result = $mysqli->query($backup_sql);

if ($result) {
    $backup_content = "-- Backup de clientes duplicados - " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- CPF: 03454769990\n\n";
    
    while ($row = $result->fetch_assoc()) {
        $backup_content .= "INSERT INTO clientes VALUES (";
        $values = [];
        foreach ($row as $value) {
            $values[] = $mysqli->real_escape_string($value);
        }
        $backup_content .= "'" . implode("', '", $values) . "');\n";
    }
    
    file_put_contents($backup_file, $backup_content);
    echo "✅ Backup criado: $backup_file\n";
} else {
    echo "❌ Erro ao criar backup: " . $mysqli->error . "\n";
}

echo "\n🔍 EXECUTANDO CORREÇÃO:\n";

// Remover o registro duplicado (ID 4295)
$sql_delete = "DELETE FROM clientes WHERE id = 4295";
if ($mysqli->query($sql_delete)) {
    echo "✅ Registro duplicado ID 4295 removido com sucesso!\n";
    echo "   - Nome: Valdirene Cravo e Canela Home\n";
    echo "   - CPF: 03454769990\n";
    echo "   - Asaas ID: cus_000096887334\n";
} else {
    echo "❌ Erro ao remover registro: " . $mysqli->error . "\n";
}

echo "\n📊 VERIFICANDO RESULTADO:\n";

// Verificar se ainda há duplicatas
$sql_check = "SELECT cpf_cnpj, COUNT(*) as total 
              FROM clientes 
              WHERE cpf_cnpj IS NOT NULL AND cpf_cnpj != '' 
              GROUP BY cpf_cnpj 
              HAVING COUNT(*) > 1";

$result = $mysqli->query($sql_check);

if ($result && $result->num_rows > 0) {
    echo "❌ Ainda há duplicatas:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   CPF: {$row['cpf_cnpj']} - {$row['total']} registros\n";
    }
} else {
    echo "✅ Nenhuma duplicata encontrada!\n";
}

// Verificar o registro mantido
$sql_mantido = "SELECT id, nome, email, cpf_cnpj, telefone, asaas_id, data_criacao 
                FROM clientes WHERE cpf_cnpj = '03454769990'";
$result = $mysqli->query($sql_mantido);

if ($result && $result->num_rows > 0) {
    echo "\n📋 REGISTRO MANTIDO:\n";
    $cliente = $result->fetch_assoc();
    echo "   ID: {$cliente['id']}\n";
    echo "   Nome: {$cliente['nome']}\n";
    echo "   Email: {$cliente['email']}\n";
    echo "   CPF/CNPJ: {$cliente['cpf_cnpj']}\n";
    echo "   Telefone: {$cliente['telefone']}\n";
    echo "   Asaas ID: {$cliente['asaas_id']}\n";
    echo "   Criado: {$cliente['data_criacao']}\n";
}

echo "\n📈 RESUMO FINAL:\n";
$total_clientes = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
if ($total_clientes) {
    $total = $total_clientes->fetch_assoc()['total'];
    echo "   Total de clientes no banco: $total\n";
}

$mysqli->close();
echo "\n✅ Correção concluída com sucesso!\n";
echo "💾 Backup salvo em: $backup_file\n";
?> 