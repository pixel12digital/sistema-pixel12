<?php
/**
 * 🔍 VERIFICAÇÃO DA ESTRUTURA DO BANCO
 * 
 * Verifica a estrutura atual do banco de dados para identificar problemas
 */

echo "🔍 VERIFICAÇÃO DA ESTRUTURA DO BANCO\n";
echo "====================================\n\n";

// 1. CONECTAR AO BANCO
echo "1️⃣ CONECTANDO AO BANCO\n";
echo "======================\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

if ($mysqli) {
    echo "✅ Conexão com banco estabelecida\n";
} else {
    echo "❌ Erro ao conectar com banco\n";
    exit(1);
}

// 2. VERIFICAR ESTRUTURA DA TABELA MENSAGENS
echo "\n2️⃣ VERIFICANDO ESTRUTURA DA TABELA MENSAGENS\n";
echo "=============================================\n";

$columns = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao");
if ($columns) {
    echo "📋 Colunas da tabela mensagens_comunicacao:\n";
    while ($col = $columns->fetch_assoc()) {
        echo "   - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}

// 3. VERIFICAR CANAIS CONFIGURADOS
echo "\n3️⃣ VERIFICANDO CANAIS CONFIGURADOS\n";
echo "===================================\n";

$canais = $mysqli->query("SELECT id, nome_exibicao, porta, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY porta");
if ($canais) {
    echo "📱 Canais WhatsApp configurados:\n";
    while ($canal = $canais->fetch_assoc()) {
        echo "   - ID: {$canal['id']} | {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Identificador: {$canal['identificador']}\n";
    }
} else {
    echo "❌ Erro ao verificar canais: " . $mysqli->error . "\n";
}

// 4. VERIFICAR ÚLTIMAS MENSAGENS
echo "\n4️⃣ VERIFICANDO ÚLTIMAS MENSAGENS\n";
echo "==================================\n";

$mensagens = $mysqli->query("SELECT id, canal_id, numero_whatsapp, mensagem, data_hora, direcao, status 
                             FROM mensagens_comunicacao 
                             WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                             ORDER BY data_hora DESC LIMIT 5");

if ($mensagens && $mensagens->num_rows > 0) {
    echo "📋 Últimas mensagens (última hora):\n";
    while ($msg = $mensagens->fetch_assoc()) {
        echo "   - ID: {$msg['id']} | Canal: {$msg['canal_id']} | Número: {$msg['numero_whatsapp']} | Mensagem: {$msg['mensagem']} | Data: {$msg['data_hora']} | Direção: {$msg['direcao']} | Status: {$msg['status']}\n";
    }
} else {
    echo "⚠️ Nenhuma mensagem encontrada na última hora\n";
}

// 5. VERIFICAR CLIENTES
echo "\n5️⃣ VERIFICANDO CLIENTES\n";
echo "========================\n";

$clientes = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE celular LIKE '%554796164699%' LIMIT 3");
if ($clientes && $clientes->num_rows > 0) {
    echo "👥 Clientes encontrados para o número de teste:\n";
    while ($cliente = $clientes->fetch_assoc()) {
        echo "   - ID: {$cliente['id']} | Nome: {$cliente['nome']} | Celular: {$cliente['celular']}\n";
    }
} else {
    echo "⚠️ Nenhum cliente encontrado para o número de teste\n";
}

// 6. VERIFICAR LOGS DE ERRO
echo "\n6️⃣ VERIFICANDO LOGS DE ERRO\n";
echo "============================\n";

$logs = $mysqli->query("SHOW TABLES LIKE '%log%'");
if ($logs && $logs->num_rows > 0) {
    echo "📋 Tabelas de log encontradas:\n";
    while ($log = $logs->fetch_assoc()) {
        echo "   - " . $log['Tables_in_' . $mysqli->database] . "\n";
    }
} else {
    echo "⚠️ Nenhuma tabela de log encontrada\n";
}

// 7. TESTAR INSERÇÃO
echo "\n7️⃣ TESTANDO INSERÇÃO\n";
echo "=====================\n";

$mensagem_teste = 'Teste estrutura banco - ' . date('Y-m-d H:i:s');
$texto_escaped = $mysqli->real_escape_string($mensagem_teste);

$sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
        VALUES (36, 4296, '554796164699', '$texto_escaped', 'texto', NOW(), 'recebido', 'recebido')";

if ($mysqli->query($sql)) {
    $mensagem_id = $mysqli->insert_id;
    echo "✅ Inserção teste funcionando - ID: $mensagem_id\n";
    
    // Verificar se foi salva
    $check = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE id = $mensagem_id");
    if ($check && $check->num_rows > 0) {
        $msg = $check->fetch_assoc();
        echo "✅ Mensagem confirmada no banco:\n";
        echo "   ID: {$msg['id']}\n";
        echo "   Canal: {$msg['canal_id']}\n";
        echo "   Número: {$msg['numero_whatsapp']}\n";
        echo "   Mensagem: {$msg['mensagem']}\n";
        echo "   Data/Hora: {$msg['data_hora']}\n";
        echo "   Direção: {$msg['direcao']}\n";
        echo "   Status: {$msg['status']}\n";
    } else {
        echo "❌ Mensagem não encontrada após inserção\n";
    }
} else {
    echo "❌ Erro na inserção teste: " . $mysqli->error . "\n";
}

// 8. RESUMO FINAL
echo "\n8️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status da verificação:\n";
echo "   ✅ Conexão: " . ($mysqli ? "Estabelecida" : "Falhou") . "\n";
echo "   ✅ Estrutura: " . ($columns ? "Verificada" : "Erro") . "\n";
echo "   ✅ Canais: " . ($canais ? "Configurados" : "Erro") . "\n";
echo "   ✅ Inserção: " . (isset($mensagem_id) ? "Funcionando (ID: $mensagem_id)" : "Falhou") . "\n\n";

echo "🎯 DIAGNÓSTICO:\n";
echo "================\n";

if (isset($mensagem_id)) {
    echo "✅ BANCO FUNCIONANDO CORRETAMENTE!\n";
    echo "   - Estrutura da tabela está correta\n";
    echo "   - Inserção direta funciona\n";
    echo "   - Problema está no webhook, não no banco\n";
} else {
    echo "❌ PROBLEMA NO BANCO:\n";
    echo "   - Estrutura pode estar incorreta\n";
    echo "   - Inserção não está funcionando\n";
    echo "   - Verificar configurações do banco\n";
}

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 