<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO ESTRUTURA DA TABELA MENSAGENS_COMUNICACAO\n";
echo "=======================================================\n\n";

// Verificar estrutura da tabela
$result = $mysqli->query("DESCRIBE mensagens_comunicacao");

if ($result) {
    echo "📊 ESTRUTURA DA TABELA:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}

echo "\n🔍 VERIFICANDO ALGUMAS MENSAGENS EXISTENTES:\n";
$mensagens = $mysqli->query("SELECT * FROM mensagens_comunicacao LIMIT 3");

if ($mensagens) {
    while ($msg = $mensagens->fetch_assoc()) {
        echo "   ID: {$msg['id']} | Canal: {$msg['canal_id']} | Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
} else {
    echo "❌ Erro ao buscar mensagens: " . $mysqli->error . "\n";
}

echo "\n🎯 VERIFICAÇÃO CONCLUÍDA!\n";
?> 