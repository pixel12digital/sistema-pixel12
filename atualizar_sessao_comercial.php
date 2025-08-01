<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 ATUALIZANDO SESSÃO DO CANAL COMERCIAL\n";
echo "========================================\n\n";

// Verificar se existe coluna sessao
$sql_check = "SHOW COLUMNS FROM canais_comunicacao LIKE 'sessao'";
$result_check = $mysqli->query($sql_check);

if ($result_check->num_rows == 0) {
    echo "➕ CRIANDO COLUNA 'sessao'...\n";
    $sql_add = "ALTER TABLE canais_comunicacao ADD COLUMN sessao VARCHAR(50) NULL AFTER porta";
    if ($mysqli->query($sql_add)) {
        echo "✅ Coluna 'sessao' criada com sucesso!\n\n";
    } else {
        echo "❌ Erro ao criar coluna: " . $mysqli->error . "\n\n";
        exit;
    }
} else {
    echo "✅ Coluna 'sessao' já existe!\n\n";
}

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

// Atualizar canal comercial para usar sessão 'comercial'
$sql_update = "UPDATE canais_comunicacao SET sessao = 'comercial' WHERE id = 37";
if ($mysqli->query($sql_update)) {
    echo "✅ Canal comercial atualizado para usar sessão 'comercial'!\n\n";
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
echo "1. No VPS, execute: curl -X POST http://localhost:3000/session/start/comercial\n";
echo "2. Verifique o QR Code: curl http://localhost:3000/qr/comercial\n";
echo "3. Escaneie o QR Code com o WhatsApp comercial\n";
echo "4. Teste o envio novamente\n";
?> 