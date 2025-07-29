<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 IMPLEMENTANDO VALIDAÇÕES PARA EVITAR DUPLICATAS\n";
echo "==================================================\n\n";

// Verificar índices existentes
echo "📊 VERIFICANDO ÍNDICES EXISTENTES:\n";
$sql_indices = "SHOW INDEX FROM clientes";
$result = $mysqli->query($sql_indices);

if ($result) {
    echo "   Índices atuais:\n";
    while ($row = $result->fetch_assoc()) {
        if ($row['Key_name'] !== 'PRIMARY') {
            echo "   - {$row['Key_name']} em {$row['Column_name']}\n";
        }
    }
} else {
    echo "   Erro ao verificar índices: " . $mysqli->error . "\n";
}

echo "\n🔧 IMPLEMENTANDO ÍNDICES ÚNICOS:\n";

// Lista de campos que devem ser únicos
$campos_unicos = [
    'asaas_id' => 'ID do Asaas',
    'email' => 'Email',
    'cpf_cnpj' => 'CPF/CNPJ'
];

foreach ($campos_unicos as $campo => $descricao) {
    echo "   📋 Verificando índice único para $descricao ($campo)...\n";
    
    // Verificar se já existe índice único
    $sql_check = "SHOW INDEX FROM clientes WHERE Column_name = '$campo' AND Non_unique = 0";
    $result = $mysqli->query($sql_check);
    
    if ($result && $result->num_rows > 0) {
        echo "      ✅ Índice único já existe para $campo\n";
    } else {
        // Verificar se há valores duplicados antes de criar o índice
        $sql_duplicados = "SELECT $campo, COUNT(*) as total 
                          FROM clientes 
                          WHERE $campo IS NOT NULL AND $campo != '' 
                          GROUP BY $campo 
                          HAVING COUNT(*) > 1";
        $result_dup = $mysqli->query($sql_duplicados);
        
        if ($result_dup && $result_dup->num_rows > 0) {
            echo "      ⚠️  Existem valores duplicados em $campo. Corrija antes de criar índice único.\n";
            while ($dup = $result_dup->fetch_assoc()) {
                echo "         $campo: '{$dup[$campo]}' - {$dup['total']} registros\n";
            }
        } else {
            // Criar índice único
            $sql_create = "CREATE UNIQUE INDEX idx_{$campo}_unique ON clientes($campo)";
            if ($mysqli->query($sql_create)) {
                echo "      ✅ Índice único criado para $campo\n";
            } else {
                echo "      ❌ Erro ao criar índice único para $campo: " . $mysqli->error . "\n";
            }
        }
    }
}

echo "\n🔍 VERIFICANDO DADOS PROBLEMÁTICOS:\n";

// Verificar registros com dados vazios que podem causar problemas
$problemas = [
    'email_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE email IS NULL OR email = ''",
    'cpf_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE cpf_cnpj IS NULL OR cpf_cnpj = ''",
    'asaas_id_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''"
];

foreach ($problemas as $tipo => $sql) {
    $result = $mysqli->query($sql);
    if ($result) {
        $total = $result->fetch_assoc()['total'];
        if ($total > 0) {
            echo "   ⚠️  $tipo: $total registros\n";
        }
    }
}

echo "\n💡 RECOMENDAÇÕES PARA EVITAR DUPLICATAS FUTURAS:\n";
echo "================================================\n";
echo "   1. ✅ Índices únicos implementados para campos críticos\n";
echo "   2. 🔍 Sempre verificar se cliente já existe antes de inserir\n";
echo "   3. 📝 Implementar validação no código de sincronização com Asaas\n";
echo "   4. 🔄 Usar INSERT ... ON DUPLICATE KEY UPDATE para atualizações\n";
echo "   5. 📊 Monitorar regularmente duplicatas com script de verificação\n";

echo "\n📋 EXEMPLO DE CÓDIGO PARA INSERÇÃO SEGURA:\n";
echo "```php\n";
echo "// Verificar se cliente já existe\n";
echo "\$sql_check = \"SELECT id FROM clientes WHERE asaas_id = ? OR email = ? OR cpf_cnpj = ?\";\n";
echo "\$stmt = \$mysqli->prepare(\$sql_check);\n";
echo "\$stmt->bind_param('sss', \$asaas_id, \$email, \$cpf_cnpj);\n";
echo "\$stmt->execute();\n";
echo "\$result = \$stmt->get_result();\n";
echo "\n";
echo "if (\$result->num_rows > 0) {\n";
echo "    // Cliente já existe, atualizar dados\n";
echo "    \$cliente = \$result->fetch_assoc();\n";
echo "    \$sql_update = \"UPDATE clientes SET ... WHERE id = ?\";\n";
echo "} else {\n";
echo "    // Cliente não existe, inserir novo\n";
echo "    \$sql_insert = \"INSERT INTO clientes (...) VALUES (...)\";\n";
echo "}\n";
echo "```\n";

$mysqli->close();
echo "\n✅ Implementação de validações concluída!\n";
?> 