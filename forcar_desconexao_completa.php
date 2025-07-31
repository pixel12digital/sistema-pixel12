<?php
require_once "config.php";
require_once "painel/db.php";

echo "🔧 FORÇANDO DESCONEXÃO COMPLETA\n";
echo "===============================\n\n";

// 1. Atualizar status no banco para 'pendente' e limpar identificador
$update = $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente', identificador = '', data_conexao = NULL WHERE nome_exibicao LIKE '%Comercial%'");
if ($update) {
    echo "✅ Status atualizado para 'pendente' no banco\n";
    echo "✅ Identificador limpo\n";
} else {
    echo "❌ Erro ao atualizar banco: " . $mysqli->error . "\n";
}

// 2. Limpar cache do navegador (forçar refresh)
echo "🧹 Limpando cache...\n";

// 3. Verificar status atual no banco
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE nome_exibicao LIKE '%Comercial%'");
if ($row = $result->fetch_assoc()) {
    echo "📋 STATUS ATUAL NO BANCO:\n";
    echo "   ID: " . $row['id'] . "\n";
    echo "   Nome: " . $row['nome_exibicao'] . "\n";
    echo "   Status: " . $row['status'] . "\n";
    echo "   Porta: " . $row['porta'] . "\n";
    echo "   Identificador: " . ($row['identificador'] ?: 'VAZIO') . "\n";
    echo "   Data Conexão: " . ($row['data_conexao'] ?: 'NULL') . "\n";
}

echo "\n✅ DESCONEXÃO FORÇADA CONCLUÍDA!\n";
echo "Agora recarregue a página do painel (F5) para ver as mudanças.\n";
echo "O canal deve aparecer como 'Pendente' agora.\n";
?> 