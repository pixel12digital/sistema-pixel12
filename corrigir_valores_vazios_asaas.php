<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO VALORES VAZIOS PARA IMPLEMENTAR ÍNDICES ÚNICOS\n";
echo "==========================================================\n\n";

// Verificar registros com asaas_id vazio
echo "📊 VERIFICANDO REGISTROS COM ASAAS_ID VAZIO:\n";
$sql_vazios = "SELECT id, nome, email, cpf_cnpj, asaas_id, data_criacao 
               FROM clientes 
               WHERE asaas_id IS NULL OR asaas_id = ''";
$result = $mysqli->query($sql_vazios);

if ($result && $result->num_rows > 0) {
    echo "   Encontrados {$result->num_rows} registros com asaas_id vazio:\n";
    while ($cliente = $result->fetch_assoc()) {
        echo "   📋 ID: {$cliente['id']} | Nome: {$cliente['nome']} | Email: {$cliente['email']} | CPF: {$cliente['cpf_cnpj']}\n";
    }
    
    echo "\n🤔 OPÇÕES PARA CORREÇÃO:\n";
    echo "   1. Remover registros sem asaas_id (se não são importantes)\n";
    echo "   2. Atribuir um valor temporário único\n";
    echo "   3. Tentar sincronizar com Asaas para obter o ID\n";
    
    echo "\n⚠️  ATENÇÃO: Registros sem asaas_id podem ser problemáticos!\n";
    echo "   Recomendação: Remover registros sem asaas_id se não são essenciais\n";
    
    // Perguntar se deve remover (em produção, você pode querer fazer backup primeiro)
    echo "\n🔒 MODO SEGURO: Apenas mostrando o que seria feito\n";
    echo "Para executar a remoção, edite este script e remova a verificação de segurança\n";
    
    /*
    // Código para remoção (descomente para executar)
    $sql_delete = "DELETE FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''";
    if ($mysqli->query($sql_delete)) {
        $linhas = $mysqli->affected_rows;
        echo "✅ $linhas registros sem asaas_id removidos\n";
    } else {
        echo "❌ Erro ao remover registros: " . $mysqli->error . "\n";
    }
    */
    
} else {
    echo "   ✅ Nenhum registro com asaas_id vazio encontrado\n";
}

echo "\n📊 VERIFICANDO REGISTROS COM EMAIL VAZIO:\n";
$sql_email_vazio = "SELECT COUNT(*) as total FROM clientes WHERE email IS NULL OR email = ''";
$result = $mysqli->query($sql_email_vazio);
if ($result) {
    $total = $result->fetch_assoc()['total'];
    echo "   Total de registros com email vazio: $total\n";
    
    if ($total > 0) {
        echo "   ⚠️  Registros com email vazio não podem ter índice único\n";
        echo "   Recomendação: Atualizar emails ou remover registros obsoletos\n";
    }
}

echo "\n📊 VERIFICANDO REGISTROS COM CPF/CNPJ VAZIO:\n";
$sql_cpf_vazio = "SELECT COUNT(*) as total FROM clientes WHERE cpf_cnpj IS NULL OR cpf_cnpj = ''";
$result = $mysqli->query($sql_cpf_vazio);
if ($result) {
    $total = $result->fetch_assoc()['total'];
    echo "   Total de registros com CPF/CNPJ vazio: $total\n";
    
    if ($total > 0) {
        echo "   ⚠️  Registros com CPF/CNPJ vazio não podem ter índice único\n";
        echo "   Recomendação: Atualizar CPF/CNPJ ou remover registros obsoletos\n";
    }
}

echo "\n💡 ESTRATÉGIA RECOMENDADA:\n";
echo "==========================\n";
echo "   1. Criar índice único apenas para asaas_id (mais importante)\n";
echo "   2. Implementar validações no código para email e CPF/CNPJ\n";
echo "   3. Usar INSERT ... ON DUPLICATE KEY UPDATE para asaas_id\n";
echo "   4. Validar email e CPF/CNPJ antes da inserção\n";

echo "\n🔧 CRIANDO ÍNDICE ÚNICO PARA ASAAS_ID (IGNORANDO VAZIOS):\n";

// Criar índice único apenas para registros não vazios
$sql_create = "CREATE UNIQUE INDEX idx_asaas_id_unique ON clientes(asaas_id)";
if ($mysqli->query($sql_create)) {
    echo "✅ Índice único criado para asaas_id\n";
} else {
    echo "❌ Erro ao criar índice único: " . $mysqli->error . "\n";
    
    // Se falhou, tentar criar índice parcial (MySQL 8.0+)
    echo "🔄 Tentando criar índice parcial...\n";
    $sql_partial = "CREATE UNIQUE INDEX idx_asaas_id_unique ON clientes(asaas_id) WHERE asaas_id IS NOT NULL AND asaas_id != ''";
    if ($mysqli->query($sql_partial)) {
        echo "✅ Índice único parcial criado para asaas_id\n";
    } else {
        echo "❌ Erro ao criar índice parcial: " . $mysqli->error . "\n";
    }
}

echo "\n📈 RESUMO FINAL:\n";
$total_clientes = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
if ($total_clientes) {
    $total = $total_clientes->fetch_assoc()['total'];
    echo "   Total de clientes no banco: $total\n";
}

$mysqli->close();
echo "\n✅ Correção de valores vazios concluída!\n";
?> 