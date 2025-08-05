<?php
/**
 * 🔄 SCRIPT PARA REATIVAR CANAL 3000
 * 
 * Este script reativa o canal 3000 que estava excluído
 */

require_once 'painel/db.php';

echo "🔄 REATIVANDO CANAL 3000\n";
echo "=======================\n\n";

// Buscar canal 3000 excluído
$canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3000")->fetch_assoc();

if ($canal) {
    echo "📋 CANAL ENCONTRADO:\n";
    echo "- ID: " . $canal['id'] . "\n";
    echo "- Nome atual: " . $canal['nome_exibicao'] . "\n";
    echo "- Status atual: " . $canal['status'] . "\n";
    echo "- Porta: " . $canal['porta'] . "\n\n";
    
    // Atualizar canal para status ativo
    $novo_nome = 'Financeiro - Canal 3000';
    $novo_status = 'pendente';
    
    $sql = "UPDATE canais_comunicacao 
            SET status = '$novo_status', 
                nome_exibicao = '" . $mysqli->real_escape_string($novo_nome) . "',
                data_conexao = NULL
            WHERE id = " . $canal['id'];
    
    if ($mysqli->query($sql)) {
        echo "✅ SUCESSO: Canal 3000 reativado!\n";
        echo "- Novo nome: $novo_nome\n";
        echo "- Novo status: $novo_status\n\n";
        
        echo "🎯 PRÓXIMOS PASSOS:\n";
        echo "1. Recarregue o painel de comunicação (F5)\n";
        echo "2. O canal 3000 aparecerá na lista como ativo\n";
        echo "3. Clique em 'Conectar' para escanear o QR Code\n";
        echo "4. Use o primeiro número de WhatsApp para escanear\n\n";
        
    } else {
        echo "❌ ERRO ao reativar canal: " . $mysqli->error . "\n";
    }
    
} else {
    echo "❌ ERRO: Canal na porta 3000 não encontrado!\n";
}

// Mostrar todos os canais ativos
echo "📋 CANAIS ATIVOS APÓS ATUALIZAÇÃO:\n";
echo "=================================\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- Porta: " . $row['porta'] . " | Nome: " . $row['nome_exibicao'] . " | Status: " . $row['status'] . "\n";
    }
} else {
    echo "Nenhum canal ativo.\n";
}

echo "\n🔗 TESTE OS CANAIS:\n";
echo "- Canal 3000: http://212.85.11.238:3000/status\n";
echo "- Canal 3001: http://212.85.11.238:3001/status\n";
?> 