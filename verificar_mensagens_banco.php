<?php
/**
 * Verificar mensagens no banco de dados
 */

require_once 'config.php';

echo "=== ANÁLISE DAS MENSAGENS NO BANCO ===\n\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        die("Erro de conexão: " . $mysqli->connect_error);
    }
    
    // 1. Verificar estrutura da tabela mensagens_comunicacao
    echo "1. ESTRUTURA DA TABELA mensagens_comunicacao:\n";
    $result = $mysqli->query("DESCRIBE mensagens_comunicacao");
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Default']}\n";
    }
    echo "\n";
    
    // 2. Verificar mensagens recentes
    echo "2. MENSAGENS RECENTES (últimas 10):\n";
    $result = $mysqli->query("
        SELECT id, canal_id, numero_whatsapp, mensagem, direcao, data_hora, tipo 
        FROM mensagens_comunicacao 
        ORDER BY data_hora DESC 
        LIMIT 10
    ");
    
    while ($row = $result->fetch_assoc()) {
        echo "   ID: {$row['id']} | Canal: {$row['canal_id']} | Número: {$row['numero_whatsapp']}\n";
        echo "   Direção: {$row['direcao']} | Tipo: {$row['tipo']}\n";
        echo "   Mensagem: " . substr($row['mensagem'], 0, 50) . "...\n";
        echo "   Data: {$row['data_hora']}\n\n";
    }
    
    // 3. Verificar mensagens do número específico
    echo "3. MENSAGENS DO NÚMERO 554796164699:\n";
    $result = $mysqli->query("
        SELECT id, canal_id, numero_whatsapp, mensagem, direcao, data_hora 
        FROM mensagens_comunicacao 
        WHERE numero_whatsapp LIKE '%554796164699%' 
        ORDER BY data_hora DESC 
        LIMIT 5
    ");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "   ID: {$row['id']} | Canal: {$row['canal_id']} | Direção: {$row['direcao']}\n";
            echo "   Mensagem: {$row['mensagem']}\n";
            echo "   Data: {$row['data_hora']}\n\n";
        }
    } else {
        echo "   ❌ Nenhuma mensagem encontrada para este número!\n\n";
    }
    
    // 4. Verificar canais únicos
    echo "4. CANAIS ÚNICOS NO SISTEMA:\n";
    $result = $mysqli->query("
        SELECT DISTINCT canal_id, numero_whatsapp, COUNT(*) as total 
        FROM mensagens_comunicacao 
        GROUP BY canal_id, numero_whatsapp 
        ORDER BY canal_id
    ");
    
    while ($row = $result->fetch_assoc()) {
        echo "   Canal ID: {$row['canal_id']} | Número: {$row['numero_whatsapp']} | Total msgs: {$row['total']}\n";
    }
    echo "\n";
    
    // 5. Verificar se existe tabela de canais
    echo "5. VERIFICANDO CONFIGURAÇÃO DE CANAIS:\n";
    $result = $mysqli->query("SHOW TABLES LIKE 'canais%'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            echo "   Tabela encontrada: {$row[0]}\n";
        }
    } else {
        echo "   ❌ Nenhuma tabela de canais encontrada\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
?> 