<?php
/**
 * Teste direto da conexão com banco de dados
 */

echo "=== TESTE DE CONEXÃO COM BANCO ===\n";

// Incluir configurações
require_once 'config.php';

echo "Configurações detectadas:\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? str_repeat('*', strlen(DB_PASS)) : 'VAZIA') . "\n\n";

// Teste conexão direta
echo "Testando conexão direta...\n";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "❌ ERRO DE CONEXÃO: " . $mysqli->connect_error . "\n";
        exit(1);
    }
    
    echo "✅ Conexão estabelecida com sucesso!\n";
    echo "Versão MySQL: " . $mysqli->server_info . "\n";
    
    // Testar consulta simples
    $result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Mensagens hoje: " . $row['total'] . "\n";
    } else {
        echo "❌ Erro na consulta: " . $mysqli->error . "\n";
    }
    
    // Testar tabelas necessárias
    echo "\nVerificando tabelas...\n";
    $tabelas = ['mensagens_comunicacao', 'clientes', 'canais_whatsapp'];
    foreach ($tabelas as $tabela) {
        $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Tabela $tabela existe\n";
        } else {
            echo "❌ Tabela $tabela NÃO EXISTE\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?> 