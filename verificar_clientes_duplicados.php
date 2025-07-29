<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO CLIENTES DUPLICADOS NO BANCO DE DADOS\n";
echo "==================================================\n\n";

// Verificar duplicatas por diferentes critérios
$criterios = [
    'email' => 'Email',
    'cpf_cnpj' => 'CPF/CNPJ',
    'telefone' => 'Telefone',
    'asaas_id' => 'ID do Asaas'
];

foreach ($criterios as $campo => $descricao) {
    echo "📊 Verificando duplicatas por $descricao:\n";
    
    $sql = "SELECT $campo, COUNT(*) as total 
            FROM clientes 
            WHERE $campo IS NOT NULL AND $campo != '' 
            GROUP BY $campo 
            HAVING COUNT(*) > 1 
            ORDER BY total DESC";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "   ❌ Encontradas duplicatas:\n";
        while ($row = $result->fetch_assoc()) {
            echo "      $campo: '{$row[$campo]}' - {$row['total']} registros\n";
            
            // Mostrar detalhes dos registros duplicados
            $sql_detalhes = "SELECT id, nome, email, cpf_cnpj, telefone, asaas_id, data_criacao 
                           FROM clientes 
                           WHERE $campo = '{$row[$campo]}' 
                           ORDER BY data_criacao";
            $detalhes = $mysqli->query($sql_detalhes);
            
            while ($detalhe = $detalhes->fetch_assoc()) {
                echo "         ID: {$detalhe['id']} | Nome: {$detalhe['nome']} | Criado: {$detalhe['data_criacao']}\n";
            }
            echo "\n";
        }
    } else {
        echo "   ✅ Nenhuma duplicata encontrada por $descricao\n";
    }
    echo "\n";
}

// Verificar registros com dados vazios ou nulos
echo "🔍 Verificando registros com dados problemáticos:\n";
echo "================================================\n";

$problemas = [
    'email_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE email IS NULL OR email = ''",
    'cpf_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE cpf_cnpj IS NULL OR cpf_cnpj = ''",
    'telefone_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE telefone IS NULL OR telefone = ''",
    'nome_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE nome IS NULL OR nome = ''",
    'asaas_id_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''"
];

foreach ($problemas as $tipo => $sql) {
    $result = $mysqli->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $total = $row['total'];
        if ($total > 0) {
            echo "   ⚠️  $tipo: $total registros\n";
        }
    }
}

echo "\n";

// Verificar total de registros
$total_clientes = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
if ($total_clientes) {
    $total = $total_clientes->fetch_assoc()['total'];
    echo "📈 Total de clientes no banco: $total\n";
}

// Verificar registros recentes (últimos 7 dias)
$recentes = $mysqli->query("SELECT COUNT(*) as total FROM clientes WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($recentes) {
    $total_recentes = $recentes->fetch_assoc()['total'];
    echo "📅 Clientes criados nos últimos 7 dias: $total_recentes\n";
}

echo "\n🎯 RECOMENDAÇÕES:\n";
echo "   1. Se houver duplicatas, considere usar um script de limpeza\n";
echo "   2. Verifique se o processo de sincronização com Asaas está correto\n";
echo "   3. Implemente validações para evitar duplicatas futuras\n";
echo "   4. Considere adicionar índices únicos nos campos críticos\n";

$mysqli->close();
?> 