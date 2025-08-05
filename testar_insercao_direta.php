<?php
/**
 * 🧪 TESTE DE INSERÇÃO DIRETA - MENSAGENS COMUNICAÇÃO
 * 
 * Testa inserção direta na tabela para identificar problemas
 */

echo "🧪 TESTE DE INSERÇÃO DIRETA\n";
echo "===========================\n\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

// 1. VERIFICAR ESTRUTURA DA TABELA
echo "1️⃣ VERIFICANDO ESTRUTURA DA TABELA\n";
echo "===================================\n";

$columns = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao");
if ($columns) {
    echo "📋 Colunas da tabela mensagens_comunicacao:\n";
    while ($col = $columns->fetch_assoc()) {
        echo "   - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}
echo "\n";

// 2. TESTAR INSERÇÃO SIMPLES
echo "2️⃣ TESTANDO INSERÇÃO SIMPLES\n";
echo "=============================\n";

$numero_teste = '554796164699';
$mensagem_teste = 'Teste inserção direta - ' . date('Y-m-d H:i:s');
$canal_id = 36;
$data_hora = date('Y-m-d H:i:s');

// Primeiro, verificar se existe cliente
$cliente_check = $mysqli->query("SELECT id FROM clientes WHERE celular LIKE '%$numero_teste%' LIMIT 1");
$cliente_id = null;

if ($cliente_check && $cliente_check->num_rows > 0) {
    $cliente = $cliente_check->fetch_assoc();
    $cliente_id = $cliente['id'];
    echo "✅ Cliente encontrado: ID $cliente_id\n";
} else {
    echo "⚠️ Cliente não encontrado, criando...\n";
    
    // Criar cliente
    $nome_cliente = "Cliente Teste (" . $numero_teste . ")";
    $sql_criar = "INSERT INTO clientes (nome, celular, data_criacao, data_atualizacao) 
                  VALUES ('$nome_cliente', '$numero_teste', '$data_hora', '$data_hora')";
    
    if ($mysqli->query($sql_criar)) {
        $cliente_id = $mysqli->insert_id;
        echo "✅ Cliente criado: ID $cliente_id\n";
    } else {
        echo "❌ Erro ao criar cliente: " . $mysqli->error . "\n";
    }
}

// 3. INSERIR MENSAGEM
echo "3️⃣ INSERINDO MENSAGEM\n";
echo "=====================\n";

$texto_escaped = $mysqli->real_escape_string($mensagem_teste);

// Tentar inserção com cliente_id
if ($cliente_id) {
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, $cliente_id, '$numero_teste', '$texto_escaped', 'texto', '$data_hora', 'recebido', 'recebido')";
} else {
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, '$numero_teste', '$texto_escaped', 'texto', '$data_hora', 'recebido', 'recebido')";
}

echo "🔍 SQL: $sql\n";

if ($mysqli->query($sql)) {
    $mensagem_id = $mysqli->insert_id;
    echo "✅ Mensagem inserida com sucesso - ID: $mensagem_id\n";
    
    // Verificar se foi salva
    $check = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE id = $mensagem_id");
    if ($check && $check->num_rows > 0) {
        $msg = $check->fetch_assoc();
        echo "✅ Mensagem confirmada no banco:\n";
        echo "   ID: {$msg['id']}\n";
        echo "   Canal: {$msg['canal_id']}\n";
        echo "   Cliente: {$msg['cliente_id']}\n";
        echo "   Número: {$msg['numero_whatsapp']}\n";
        echo "   Mensagem: {$msg['mensagem']}\n";
        echo "   Data/Hora: {$msg['data_hora']}\n";
        echo "   Direção: {$msg['direcao']}\n";
        echo "   Status: {$msg['status']}\n";
    } else {
        echo "❌ Mensagem não encontrada após inserção\n";
    }
} else {
    echo "❌ Erro ao inserir mensagem: " . $mysqli->error . "\n";
    
    // Verificar se é problema de coluna
    $error_msg = $mysqli->error;
    if (strpos($error_msg, 'numero_whatsapp') !== false) {
        echo "💡 Problema com coluna numero_whatsapp\n";
        
        // Tentar sem numero_whatsapp
        $sql_alt = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                    VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', 'texto', '$data_hora', 'recebido', 'recebido')";
        
        echo "🔄 Tentando sem numero_whatsapp: $sql_alt\n";
        
        if ($mysqli->query($sql_alt)) {
            $mensagem_id = $mysqli->insert_id;
            echo "✅ Mensagem inserida sem numero_whatsapp - ID: $mensagem_id\n";
        } else {
            echo "❌ Erro na inserção alternativa: " . $mysqli->error . "\n";
        }
    }
}
echo "\n";

// 4. VERIFICAR MENSAGENS RECENTES
echo "4️⃣ VERIFICANDO MENSAGENS RECENTES\n";
echo "==================================\n";

$recent = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                          WHERE canal_id = 36 
                          AND data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                          ORDER BY data_hora DESC LIMIT 5");

if ($recent && $recent->num_rows > 0) {
    echo "📋 Mensagens recentes do canal 3000:\n";
    while ($row = $recent->fetch_assoc()) {
        echo "   - ID: {$row['id']} | {$row['numero_whatsapp']} | {$row['mensagem']} | {$row['data_hora']}\n";
    }
} else {
    echo "⚠️ Nenhuma mensagem recente encontrada\n";
}
echo "\n";

// 5. TESTAR WEBHOOK DIRETAMENTE
echo "5️⃣ TESTANDO WEBHOOK DIRETAMENTE\n";
echo "================================\n";

// Simular dados do webhook
$webhook_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => $numero_teste,
        'to' => '554797146908',
        'text' => 'Teste webhook direto - ' . date('Y-m-d H:i:s'),
        'type' => 'text',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

echo "📤 Enviando para webhook...\n";

$ch = curl_init('https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook processado (HTTP $http_code)\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "❌ Erro no webhook (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}
echo "\n";

// 6. RESUMO FINAL
echo "6️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status dos testes:\n";
echo "   ✅ Estrutura da tabela: Verificada\n";
echo "   ✅ Inserção direta: " . (isset($mensagem_id) ? "Funcionando (ID: $mensagem_id)" : "Falhou") . "\n";
echo "   ✅ Webhook: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if (isset($mensagem_id)) {
    echo "1. ✅ Inserção direta funcionando\n";
    echo "2. 🔧 Problema está no webhook - verificar processamento\n";
    echo "3. 🧪 Testar webhook com dados corretos\n";
} else {
    echo "1. ❌ Problema na inserção - verificar estrutura da tabela\n";
    echo "2. 🔧 Verificar se todas as colunas existem\n";
    echo "3. 🧪 Testar novamente\n";
}

echo "\n✅ TESTE CONCLUÍDO!\n";
?> 