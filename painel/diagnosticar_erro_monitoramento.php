<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'diagnostico' => []
]);

$diagnostico = [];

// 1. Verificar se as tabelas existem
$tabelas_necessarias = ['clientes_monitoramento', 'mensagens_agendadas', 'clientes', 'cobrancas'];

foreach ($tabelas_necessarias as $tabela) {
    $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
    $existe = $result && $result->num_rows > 0;
    $diagnostico['tabelas'][$tabela] = $existe ? 'OK' : 'NÃO EXISTE';
}

// 2. Verificar estrutura da tabela clientes_monitoramento
if (isset($diagnostico['tabelas']['clientes_monitoramento']) && $diagnostico['tabelas']['clientes_monitoramento'] === 'OK') {
    $result = $mysqli->query("DESCRIBE clientes_monitoramento");
    $campos = [];
    while ($row = $result->fetch_assoc()) {
        $campos[] = $row['Field'] . ' (' . $row['Type'] . ')';
    }
    $diagnostico['estrutura_monitoramento'] = $campos;
}

// 3. Verificar estrutura da tabela mensagens_agendadas
if (isset($diagnostico['tabelas']['mensagens_agendadas']) && $diagnostico['tabelas']['mensagens_agendadas'] === 'OK') {
    $result = $mysqli->query("DESCRIBE mensagens_agendadas");
    $campos = [];
    while ($row = $result->fetch_assoc()) {
        $campos[] = $row['Field'] . ' (' . $row['Type'] . ')';
    }
    $diagnostico['estrutura_mensagens'] = $campos;
}

// 4. Verificar permissões de escrita no diretório de logs
$log_dir = __DIR__ . '/logs';
$diagnostico['logs_dir'] = [
    'existe' => is_dir($log_dir),
    'gravavel' => is_writable($log_dir),
    'caminho' => $log_dir
];

// 5. Verificar se há clientes com cobranças vencidas
if (isset($diagnostico['tabelas']['cobrancas']) && $diagnostico['tabelas']['cobrancas'] === 'OK') {
    $sql = "SELECT COUNT(*) as total FROM cobrancas WHERE status IN ('PENDING', 'OVERDUE') AND vencimento < CURDATE()";
    $result = $mysqli->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $diagnostico['cobrancas_vencidas'] = $row['total'];
    } else {
        $diagnostico['cobrancas_vencidas'] = 'ERRO: ' . $mysqli->error;
    }
}

// 6. Verificar clientes monitorados
if (isset($diagnostico['tabelas']['clientes_monitoramento']) && $diagnostico['tabelas']['clientes_monitoramento'] === 'OK') {
    $sql = "SELECT COUNT(*) as total FROM clientes_monitoramento WHERE monitorado = 1";
    $result = $mysqli->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $diagnostico['clientes_monitorados'] = $row['total'];
    } else {
        $diagnostico['clientes_monitorados'] = 'ERRO: ' . $mysqli->error;
    }
}

// 7. Testar inserção na tabela de monitoramento
if (isset($diagnostico['tabelas']['clientes_monitoramento']) && $diagnostico['tabelas']['clientes_monitoramento'] === 'OK') {
    // Buscar um cliente de teste
    $result = $mysqli->query("SELECT id FROM clientes LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $cliente_teste = $result->fetch_assoc();
        $cliente_id = $cliente_teste['id'];
        
        // Testar inserção
        $sql_test = "INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                     VALUES ($cliente_id, 0, NOW(), NOW()) 
                     ON DUPLICATE KEY UPDATE monitorado = 0, data_atualizacao = NOW()";
        
        if ($mysqli->query($sql_test)) {
            $diagnostico['teste_insercao_monitoramento'] = 'OK';
        } else {
            $diagnostico['teste_insercao_monitoramento'] = 'ERRO: ' . $mysqli->error;
        }
    } else {
        $diagnostico['teste_insercao_monitoramento'] = 'NÃO HÁ CLIENTES PARA TESTE';
    }
}

// 8. Testar inserção na tabela de mensagens agendadas
if (isset($diagnostico['tabelas']['mensagens_agendadas']) && $diagnostico['tabelas']['mensagens_agendadas'] === 'OK') {
    $result = $mysqli->query("SELECT id FROM clientes LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $cliente_teste = $result->fetch_assoc();
        $cliente_id = $cliente_teste['id'];
        
        $sql_test = "INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                     VALUES ($cliente_id, 'Teste de mensagem', 'teste', 'normal', NOW(), 'agendada', NOW())";
        
        if ($mysqli->query($sql_test)) {
            $diagnostico['teste_insercao_mensagens'] = 'OK';
            // Limpar o registro de teste
            $mysqli->query("DELETE FROM mensagens_agendadas WHERE tipo = 'teste'");
        } else {
            $diagnostico['teste_insercao_mensagens'] = 'ERRO: ' . $mysqli->error;
        }
    } else {
        $diagnostico['teste_insercao_mensagens'] = 'NÃO HÁ CLIENTES PARA TESTE';
    }
}

// 9. Verificar logs recentes
$log_file = __DIR__ . '/logs/monitoramento_clientes.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $linhas = explode("\n", $log_content);
    $ultimas_linhas = array_slice($linhas, -5); // Últimas 5 linhas
    $diagnostico['ultimas_linhas_log'] = array_filter($ultimas_linhas);
} else {
    $diagnostico['ultimas_linhas_log'] = 'ARQUIVO DE LOG NÃO EXISTE';
}

// 10. Verificar configuração do PHP
$diagnostico['php_config'] = [
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'error_reporting' => ini_get('error_reporting'),
    'display_errors' => ini_get('display_errors')
];

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'diagnostico' => $diagnostico
], JSON_PRETTY_PRINT);
?> 