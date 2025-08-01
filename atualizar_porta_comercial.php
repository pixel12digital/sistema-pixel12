<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 ATUALIZANDO PORTA DO CANAL COMERCIAL\n";
echo "=======================================\n\n";

// Verificar configuração atual
$sql_atual = "SELECT id, nome_exibicao, identificador, porta, status, sessao FROM canais_comunicacao WHERE id = 37";
$result = $mysqli->query($sql_atual);

if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "📱 CONFIGURAÇÃO ATUAL:\n";
    echo "======================\n";
    echo "ID: {$canal['id']}\n";
    echo "Nome: {$canal['nome_exibicao']}\n";
    echo "Identificador: {$canal['identificador']}\n";
    echo "Porta: {$canal['porta']}\n";
    echo "Status: {$canal['status']}\n";
    echo "Sessão: " . ($canal['sessao'] ?: 'NULL') . "\n\n";
}

// Atualizar porta para 3001
$sql_update = "UPDATE canais_comunicacao SET porta = 3001 WHERE id = 37";
if ($mysqli->query($sql_update)) {
    echo "✅ Porta atualizada para 3001!\n\n";
} else {
    echo "❌ Erro ao atualizar: " . $mysqli->error . "\n\n";
}

// Verificar configuração final
$sql_final = "SELECT id, nome_exibicao, identificador, porta, status, sessao FROM canais_comunicacao WHERE id = 37";
$result_final = $mysqli->query($sql_final);

if ($result_final && $result_final->num_rows > 0) {
    $canal_final = $result_final->fetch_assoc();
    echo "📱 CONFIGURAÇÃO FINAL:\n";
    echo "======================\n";
    echo "ID: {$canal_final['id']}\n";
    echo "Nome: {$canal_final['nome_exibicao']}\n";
    echo "Identificador: {$canal_final['identificador']}\n";
    echo "Porta: {$canal_final['porta']}\n";
    echo "Status: {$canal_final['status']}\n";
    echo "Sessão: " . ($canal_final['sessao'] ?: 'NULL') . "\n\n";
}

echo "💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "2. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "3. O sistema deve conectar automaticamente na porta 3001\n";
echo "4. Teste o envio de mensagem pelo chat\n";
?> 