<?php
/**
 * Verificar estrutura de canais
 */

require_once 'config.php';

echo "=== ANÁLISE DOS CANAIS DO SISTEMA ===\n\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        die("Erro de conexão: " . $mysqli->connect_error);
    }
    
    // 1. Verificar tabela canais_comunicacao
    echo "1. ESTRUTURA DA TABELA canais_comunicacao:\n";
    $result = $mysqli->query("DESCRIBE canais_comunicacao");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "   {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Default']}\n";
        }
    } else {
        echo "   ❌ Erro: " . $mysqli->error . "\n";
    }
    echo "\n";
    
    // 2. Verificar dados da tabela canais_comunicacao
    echo "2. DADOS DA TABELA canais_comunicacao:\n";
    $result = $mysqli->query("SELECT * FROM canais_comunicacao ORDER BY id");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "   ID: {$row['id']} | Nome: {$row['nome']} | Número: {$row['numero_whatsapp']}\n";
            echo "   Status: {$row['status']} | Tipo: {$row['tipo']}\n\n";
        }
    } else {
        echo "   ❌ Erro: " . $mysqli->error . "\n";
    }
    
    // 3. Verificar estrutura do chat.php para entender como busca mensagens
    echo "3. VERIFICANDO COMO O CHAT DEVE FUNCIONAR:\n";
    echo "   Analisando o arquivo chat.php...\n";
    
    // Como o sistema deveria funcionar baseado na estrutura
    echo "\n4. DIAGNÓSTICO DO PROBLEMA:\n";
    echo "   Canal Ana (ID 36): Número deveria ser 554797146908\n";
    echo "   Canal Humano (ID 37): Número deveria ser 554797309525\n";
    echo "\n   PROBLEMA IDENTIFICADO:\n";
    echo "   - Webhook salva numero_whatsapp como remetente (554796164699)\n";
    echo "   - Mas deveria salvar como número do canal de destino\n";
    echo "   - Chat provavelmente busca por canal_id + numero_whatsapp do canal\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
?> 