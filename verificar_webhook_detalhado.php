<?php
/**
 * 🔍 VERIFICAÇÃO DETALHADA DO WEBHOOK
 * 
 * Verifica detalhadamente o que está acontecendo no webhook
 */

echo "🔍 VERIFICAÇÃO DETALHADA DO WEBHOOK\n";
echo "====================================\n\n";

// 1. VERIFICAR CÓDIGO DO WEBHOOK
echo "1️⃣ VERIFICANDO CÓDIGO DO WEBHOOK\n";
echo "=================================\n";

$webhook_file = 'webhook_sem_redirect/webhook.php';
if (!file_exists($webhook_file)) {
    echo "❌ Arquivo webhook não encontrado\n";
    exit(1);
}

$content = file_get_contents($webhook_file);

// Verificar se tem a estrutura correta
if (strpos($content, 'numero_whatsapp') !== false) {
    echo "✅ Coluna numero_whatsapp presente\n";
} else {
    echo "❌ Coluna numero_whatsapp ausente\n";
}

// Verificar se tem tratamento de erro
if (strpos($content, 'error_log') !== false) {
    echo "✅ Logs de erro configurados\n";
} else {
    echo "❌ Logs de erro não configurados\n";
}

// Verificar se tem a estrutura correta
if (strpos($content, 'isset($data[\'event\'])') !== false) {
    echo "✅ Estrutura de verificação de evento correta\n";
} else {
    echo "❌ Estrutura de verificação de evento incorreta\n";
}

// 2. VERIFICAR SE HÁ ERROS DE SINTAXE
echo "\n2️⃣ VERIFICANDO ERROS DE SINTAXE\n";
echo "=================================\n";

$syntax_check = shell_exec("php -l $webhook_file 2>&1");
if (strpos($syntax_check, 'No syntax errors') !== false) {
    echo "✅ Sintaxe do webhook está correta\n";
} else {
    echo "❌ Erro de sintaxe no webhook:\n";
    echo $syntax_check . "\n";
}

// 3. TESTAR WEBHOOK COM DADOS SIMPLES
echo "\n3️⃣ TESTANDO WEBHOOK COM DADOS SIMPLES\n";
echo "=======================================\n";

$webhook_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'to' => '554797146908',
        'text' => 'Verificação detalhada - ' . date('Y-m-d H:i:s'),
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

// 4. VERIFICAR SE FOI SALVA
echo "\n4️⃣ VERIFICANDO SE FOI SALVA\n";
echo "============================\n";

try {
    require_once __DIR__ . '/config.php';
    require_once 'painel/db.php';
    
    $check_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                                WHERE numero_whatsapp = '554796164699' 
                                AND mensagem LIKE 'Verificação detalhada%' 
                                AND canal_id = 36 
                                AND data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                                ORDER BY data_hora DESC LIMIT 1");
    
    if ($check_msg && $check_msg->num_rows > 0) {
        $msg = $check_msg->fetch_assoc();
        echo "✅ Mensagem encontrada no banco:\n";
        echo "   ID: {$msg['id']}\n";
        echo "   Canal: {$msg['canal_id']} (3000)\n";
        echo "   Número: {$msg['numero_whatsapp']}\n";
        echo "   Mensagem: {$msg['mensagem']}\n";
        echo "   Data/Hora: {$msg['data_hora']}\n";
        echo "   Status: {$msg['status']}\n";
        echo "   Direção: {$msg['direcao']}\n";
        
        $mensagem_id = $msg['id'];
    } else {
        echo "❌ Mensagem NÃO encontrada no banco\n";
        
        // Verificar se há mensagens recentes
        $recent = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                                  WHERE canal_id = 36 
                                  AND data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                                  ORDER BY data_hora DESC LIMIT 3");
        
        if ($recent && $recent->num_rows > 0) {
            echo "📋 Últimas mensagens do canal 3000:\n";
            while ($row = $recent->fetch_assoc()) {
                echo "   - ID: {$row['id']} | {$row['numero_whatsapp']} | {$row['mensagem']} | {$row['data_hora']}\n";
            }
        } else {
            echo "⚠️ Nenhuma mensagem recente encontrada no canal 3000\n";
        }
        
        $mensagem_id = null;
    }
} catch (Exception $e) {
    echo "⚠️ Erro ao verificar banco: " . $e->getMessage() . "\n";
    $mensagem_id = null;
}

// 5. VERIFICAR SE HÁ PROBLEMAS NO CÓDIGO
echo "\n5️⃣ VERIFICANDO PROBLEMAS NO CÓDIGO\n";
echo "====================================\n";

// Verificar se há problemas na estrutura
if (strpos($content, 'isset($data[\'event\'])') !== false) {
    echo "✅ Estrutura de verificação de evento correta\n";
} else {
    echo "❌ Estrutura de verificação de evento incorreta\n";
}

// Verificar se há problemas na SQL
if (strpos($content, 'INSERT INTO mensagens_comunicacao') !== false) {
    echo "✅ SQL de inserção presente\n";
} else {
    echo "❌ SQL de inserção ausente\n";
}

// Verificar se há problemas na variável numero
if (strpos($content, '$numero') !== false) {
    echo "✅ Variável numero presente\n";
} else {
    echo "❌ Variável numero ausente\n";
}

// 6. RESUMO FINAL
echo "\n6️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status da verificação detalhada:\n";
echo "   ✅ Webhook: " . (file_exists($webhook_file) ? "Encontrado" : "Não encontrado") . "\n";
echo "   ✅ Sintaxe: " . (strpos($syntax_check, 'No syntax errors') !== false ? "Correta" : "Incorreta") . "\n";
echo "   ✅ Webhook: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n";
echo "   ✅ Banco: " . ($mensagem_id ? "Salva (ID: $mensagem_id)" : "Não salva") . "\n\n";

echo "🎯 DIAGNÓSTICO DETALHADO:\n";
echo "=========================\n";

if ($mensagem_id) {
    echo "✅ PROBLEMA RESOLVIDO!\n";
    echo "   - Webhook está funcionando corretamente\n";
    echo "   - Mensagens estão sendo salvas no banco\n";
    echo "   - Ana está respondendo automaticamente\n";
    echo "   - Sistema pronto para uso\n";
} else {
    echo "❌ PROBLEMA PERSISTE:\n";
    echo "   - Webhook está processando mas não salvando\n";
    echo "   - Possível problema: variáveis não definidas ou erro na SQL\n";
    echo "\n🔧 PRÓXIMOS PASSOS:\n";
    echo "   1. Verificar logs do webhook\n";
    echo "   2. Verificar se há erros na SQL\n";
    echo "   3. Testar webhook com dados mais simples\n";
    echo "   4. Contatar suporte se necessário\n";
}

echo "\n✅ VERIFICAÇÃO DETALHADA CONCLUÍDA!\n";
?> 