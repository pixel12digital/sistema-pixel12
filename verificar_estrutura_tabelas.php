<?php
require_once 'config.php';

// Conectar ao banco de dados
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("❌ Erro na conexão com o banco: " . $mysqli->connect_error);
}

echo "🔍 VERIFICANDO ESTRUTURA DAS TABELAS\n";
echo "====================================\n\n";

// 1. Verificar estrutura da tabela mensagens_comunicacao
echo "1️⃣ Estrutura da tabela 'mensagens_comunicacao':\n";
$sql = "DESCRIBE mensagens_comunicacao";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura\n";
}
echo "\n";

// 2. Verificar estrutura da tabela logs_integracao_ana
echo "2️⃣ Estrutura da tabela 'logs_integracao_ana':\n";
$sql = "DESCRIBE logs_integracao_ana";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura\n";
}
echo "\n";

// 3. Verificar se há dados nas tabelas
echo "3️⃣ Verificando dados nas tabelas:\n";

// Mensagens
$sql_count = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
$result = $mysqli->query($sql_count);
if ($result) {
    $count = $result->fetch_assoc();
    echo "   mensagens_comunicacao (hoje): {$count['total']} registros\n";
} else {
    echo "   ❌ Erro ao contar mensagens\n";
}

// Logs
$sql_count_logs = "SELECT COUNT(*) as total FROM logs_integracao_ana WHERE DATE(data_log) = CURDATE()";
$result = $mysqli->query($sql_count_logs);
if ($result) {
    $count = $result->fetch_assoc();
    echo "   logs_integracao_ana (hoje): {$count['total']} registros\n";
} else {
    echo "   ❌ Erro ao contar logs\n";
}
echo "\n";

// 4. Verificar últimas mensagens (se houver)
echo "4️⃣ Últimas mensagens (últimos 5 registros):\n";
$sql_last = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5";
$result = $mysqli->query($sql_last);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($row['data_hora']));
        $msg = substr($row['mensagem'], 0, 40) . (strlen($row['mensagem']) > 40 ? '...' : '');
        echo "   $hora | {$row['direcao']} | {$row['status']} | $msg\n";
    }
} else {
    echo "   ❌ Nenhuma mensagem encontrada\n";
}
echo "\n";

// 5. Verificar últimos logs (se houver)
echo "5️⃣ Últimos logs (últimos 5 registros):\n";
$sql_last_logs = "SELECT * FROM logs_integracao_ana ORDER BY data_log DESC LIMIT 5";
$result = $mysqli->query($sql_last_logs);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($row['data_log']));
        // Tentar diferentes nomes de coluna
        $msg = '';
        if (isset($row['mensagem'])) {
            $msg = substr($row['mensagem'], 0, 40) . (strlen($row['mensagem']) > 40 ? '...' : '');
        } elseif (isset($row['log_mensagem'])) {
            $msg = substr($row['log_mensagem'], 0, 40) . (strlen($row['log_mensagem']) > 40 ? '...' : '');
        } else {
            $msg = 'Coluna de mensagem não encontrada';
        }
        echo "   $hora | {$row['tipo_log']} | $msg\n";
    }
} else {
    echo "   ❌ Nenhum log encontrado\n";
}

$mysqli->close();
?> 