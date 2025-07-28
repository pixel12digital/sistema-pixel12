<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO ESTRUTURA DA TABELA CLIENTES\n\n";

// Verificar estrutura da tabela
$result = $mysqli->query('DESCRIBE clientes');
echo "📋 Estrutura da tabela clientes:\n";
while ($row = $result->fetch_assoc()) {
    echo "   {$row['Field']} | {$row['Type']} | Null: {$row['Null']} | Default: " . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\n🔍 COMPARANDO COM DADOS DO ASAAS:\n";

// Dados que o Asaas retorna (baseado no código de sincronização)
$dados_asaas = [
    'id' => 'asaas_id',
    'name' => 'nome', 
    'email' => 'email',
    'phone' => 'telefone',
    'mobilePhone' => 'celular', // ⚠️ FALTANDO na tabela
    'cpfCnpj' => 'cpf_cnpj',
    'postalCode' => 'cep', // ⚠️ FALTANDO na tabela
    'address' => 'rua',
    'addressNumber' => 'numero',
    'complement' => 'complemento',
    'province' => 'bairro',
    'city' => 'cidade',
    'state' => 'estado',
    'country' => 'pais',
    'notificationDisabled' => 'notificacao_desativada',
    'additionalEmails' => 'emails_adicionais',
    'externalReference' => 'referencia_externa',
    'observations' => 'observacoes',
    'companyName' => 'razao_social',
    'dateCreated' => 'criado_em_asaas'
];

echo "📊 Campos que o Asaas envia vs campos na tabela:\n";
foreach ($dados_asaas as $campo_asaas => $campo_tabela) {
    $existe = false;
    $result_check = $mysqli->query("SHOW COLUMNS FROM clientes LIKE '$campo_tabela'");
    if ($result_check && $result_check->num_rows > 0) {
        $existe = true;
    }
    
    $status = $existe ? "✅" : "❌";
    echo "   $status $campo_asaas -> $campo_tabela\n";
}

echo "\n🔧 CAMPOS FALTANDO:\n";
$campos_faltando = ['celular', 'cep'];
foreach ($campos_faltando as $campo) {
    echo "   ❌ $campo\n";
}

echo "\n🎯 RECOMENDAÇÕES:\n";
echo "   1. Adicionar coluna 'celular' para mobilePhone do Asaas\n";
echo "   2. Adicionar coluna 'cep' para postalCode do Asaas\n";
echo "   3. Verificar se 'endereco' é necessário (parece ser campo composto)\n";

$mysqli->close();
?> 