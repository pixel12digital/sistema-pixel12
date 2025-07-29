<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO DUPLICATAS COM TRANSFERÊNCIA DE DEPENDÊNCIAS\n";
echo "========================================================\n\n";

$cliente_duplicado_id = 4295; // ID do cliente que será removido
$cliente_principal_id = 156;  // ID do cliente que será mantido

// Backup antes da correção
echo "💾 Criando backup antes da correção...\n";
$backup_file = 'backups/correcao_completa_' . date('Y-m-d_H-i-s') . '.sql';

// Criar diretório de backup se não existir
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
}

// Fazer backup dos dados que serão alterados
$backup_content = "-- Backup de correção de duplicatas - " . date('Y-m-d H:i:s') . "\n";
$backup_content .= "-- Cliente duplicado: ID $cliente_duplicado_id\n";
$backup_content .= "-- Cliente principal: ID $cliente_principal_id\n\n";

// Backup do cliente duplicado
$sql_cliente = "SELECT * FROM clientes WHERE id = $cliente_duplicado_id";
$result = $mysqli->query($sql_cliente);
if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    $backup_content .= "-- Cliente duplicado\n";
    $backup_content .= "INSERT INTO clientes VALUES (";
    $values = [];
    foreach ($cliente as $value) {
        $values[] = $mysqli->real_escape_string($value);
    }
    $backup_content .= "'" . implode("', '", $values) . "');\n\n";
}

// Backup das cobranças do cliente duplicado
$sql_cobrancas = "SELECT * FROM cobrancas WHERE cliente_id = $cliente_duplicado_id";
$result = $mysqli->query($sql_cobrancas);
if ($result && $result->num_rows > 0) {
    $backup_content .= "-- Cobranças do cliente duplicado\n";
    while ($cobranca = $result->fetch_assoc()) {
        $backup_content .= "INSERT INTO cobrancas VALUES (";
        $values = [];
        foreach ($cobranca as $value) {
            $values[] = $mysqli->real_escape_string($value);
        }
        $backup_content .= "'" . implode("', '", $values) . "');\n";
    }
}

file_put_contents($backup_file, $backup_content);
echo "✅ Backup criado: $backup_file\n";

echo "\n🔍 EXECUTANDO CORREÇÃO:\n";

// 1. Transferir cobranças do cliente duplicado para o principal
echo "💰 Transferindo cobranças...\n";
$sql_transferir = "UPDATE cobrancas SET cliente_id = $cliente_principal_id WHERE cliente_id = $cliente_duplicado_id";
if ($mysqli->query($sql_transferir)) {
    $linhas_afetadas = $mysqli->affected_rows;
    echo "✅ $linhas_afetadas cobrança(s) transferida(s) com sucesso!\n";
} else {
    echo "❌ Erro ao transferir cobranças: " . $mysqli->error . "\n";
    exit;
}

// 2. Verificar se há outras tabelas que referenciam o cliente
$tabelas_para_verificar = [
    'pedidos' => 'cliente_id',
    'mensagens' => 'cliente_id',
    'historico_pagamentos' => 'cliente_id',
    'notificacoes' => 'cliente_id'
];

foreach ($tabelas_para_verificar as $tabela => $campo) {
    // Verificar se a tabela existe
    $sql_check_table = "SHOW TABLES LIKE '$tabela'";
    $result_table = $mysqli->query($sql_check_table);
    
    if ($result_table && $result_table->num_rows > 0) {
        $sql_check = "SELECT COUNT(*) as total FROM $tabela WHERE $campo = $cliente_duplicado_id";
        $result = $mysqli->query($sql_check);
        if ($result) {
            $total = $result->fetch_assoc()['total'];
            if ($total > 0) {
                echo "📋 Transferindo registros da tabela $tabela...\n";
                $sql_transferir = "UPDATE $tabela SET $campo = $cliente_principal_id WHERE $campo = $cliente_duplicado_id";
                if ($mysqli->query($sql_transferir)) {
                    $linhas = $mysqli->affected_rows;
                    echo "✅ $linhas registro(s) transferido(s) da tabela $tabela\n";
                } else {
                    echo "❌ Erro ao transferir registros da tabela $tabela: " . $mysqli->error . "\n";
                }
            }
        }
    } else {
        echo "📋 Tabela $tabela não existe, pulando...\n";
    }
}

// 3. Remover o cliente duplicado
echo "\n🗑️  Removendo cliente duplicado...\n";
$sql_delete = "DELETE FROM clientes WHERE id = $cliente_duplicado_id";
if ($mysqli->query($sql_delete)) {
    echo "✅ Cliente duplicado ID $cliente_duplicado_id removido com sucesso!\n";
} else {
    echo "❌ Erro ao remover cliente: " . $mysqli->error . "\n";
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

// Verificar cobranças do cliente principal
echo "\n💰 COBRANÇAS DO CLIENTE PRINCIPAL:\n";
$sql_cobrancas = "SELECT id, asaas_payment_id, valor, status, vencimento, descricao 
                  FROM cobrancas WHERE cliente_id = $cliente_principal_id";
$result = $mysqli->query($sql_cobrancas);

if ($result && $result->num_rows > 0) {
    echo "   Total de cobranças: {$result->num_rows}\n";
    while ($cobranca = $result->fetch_assoc()) {
        echo "   📋 ID: {$cobranca['id']} | Asaas: {$cobranca['asaas_payment_id']} | Valor: {$cobranca['valor']} | Status: {$cobranca['status']}\n";
    }
} else {
    echo "   Nenhuma cobrança encontrada\n";
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