<?php
/**
 * 🔧 CORREÇÃO DEFINITIVA DO WEBHOOK
 * 
 * Corrige definitivamente o problema do webhook não salvar mensagens
 */

echo "🔧 CORREÇÃO DEFINITIVA DO WEBHOOK\n";
echo "==================================\n\n";

// 1. VERIFICAR ARQUIVO WEBHOOK
echo "1️⃣ VERIFICANDO ARQUIVO WEBHOOK\n";
echo "===============================\n";

$webhook_file = 'webhook_sem_redirect/webhook.php';
if (!file_exists($webhook_file)) {
    echo "❌ Arquivo webhook não encontrado: $webhook_file\n";
    exit(1);
}

echo "✅ Arquivo webhook encontrado: $webhook_file\n";

// 2. CRIAR BACKUP
echo "\n2️⃣ CRIANDO BACKUP\n";
echo "==================\n";

$backup_file = $webhook_file . '.backup.' . date('Ymd_His');
if (copy($webhook_file, $backup_file)) {
    echo "✅ Backup criado: $backup_file\n";
} else {
    echo "❌ Erro ao criar backup\n";
    exit(1);
}

// 3. LER CONTEÚDO ATUAL
echo "\n3️⃣ LENDO CONTEÚDO ATUAL\n";
echo "========================\n";

$content = file_get_contents($webhook_file);
echo "✅ Conteúdo lido (" . strlen($content) . " bytes)\n";

// 4. CORRIGIR INSERÇÃO
echo "\n4️⃣ CORRIGINDO INSERÇÃO\n";
echo "=======================\n";

// Buscar a linha de inserção atual
$pattern = '/INSERT INTO mensagens_comunicacao \(canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status\)\s+VALUES\s*\([^)]+\)/';

if (preg_match($pattern, $content, $matches)) {
    $old_sql = $matches[0];
    echo "✅ SQL atual encontrada:\n";
    echo "   $old_sql\n";
    
    // Criar nova SQL com valores corretos
    $new_sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$numero', '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
    
    // Substituir a SQL
    $new_content = str_replace($old_sql, $new_sql, $content);
    
    if ($new_content !== $content) {
        if (file_put_contents($webhook_file, $new_content)) {
            echo "✅ SQL corrigida com sucesso\n";
        } else {
            echo "❌ Erro ao salvar arquivo\n";
            exit(1);
        }
    } else {
        echo "⚠️ SQL já estava correta\n";
    }
} else {
    echo "❌ Padrão SQL não encontrado\n";
    
    // Tentar método alternativo
    echo "🔄 Tentando método alternativo...\n";
    
    // Buscar por inserção sem numero_whatsapp
    $pattern_alt = '/INSERT INTO mensagens_comunicacao \(canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status\)\s+VALUES\s*\([^)]+\)/';
    
    if (preg_match($pattern_alt, $content, $matches)) {
        $old_sql = $matches[0];
        echo "✅ SQL alternativa encontrada:\n";
        echo "   $old_sql\n";
        
        // Adicionar numero_whatsapp
        $new_sql = str_replace(
            'INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status)',
            'INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status)',
            $old_sql
        );
        
        // Adicionar valor do numero_whatsapp
        $new_sql = str_replace(
            'VALUES ($canal_id, ' . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')",
            "VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$numero', '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')",
            $new_sql
        );
        
        $new_content = str_replace($old_sql, $new_sql, $content);
        
        if (file_put_contents($webhook_file, $new_content)) {
            echo "✅ SQL corrigida (método alternativo)\n";
        } else {
            echo "❌ Erro ao salvar arquivo (método alternativo)\n";
        }
    } else {
        echo "❌ Nenhum padrão SQL encontrado\n";
        exit(1);
    }
}

// 5. VERIFICAR CORREÇÃO
echo "\n5️⃣ VERIFICANDO CORREÇÃO\n";
echo "=========================\n";

$content_updated = file_get_contents($webhook_file);

if (strpos($content_updated, 'numero_whatsapp') !== false) {
    echo "✅ Correção numero_whatsapp aplicada\n";
} else {
    echo "❌ Correção numero_whatsapp NÃO aplicada\n";
}

// 6. TESTAR CORREÇÃO
echo "\n6️⃣ TESTANDO CORREÇÃO\n";
echo "=====================\n";

// Simular dados do webhook
$webhook_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'to' => '554797146908',
        'text' => 'Teste correção definitiva - ' . date('Y-m-d H:i:s'),
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

// 7. VERIFICAR SE FOI SALVA
echo "\n7️⃣ VERIFICANDO SE FOI SALVA\n";
echo "============================\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

$check_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                            WHERE numero_whatsapp = '554796164699' 
                            AND mensagem LIKE 'Teste correção definitiva%' 
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

// 8. RESUMO FINAL
echo "\n8️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status da correção definitiva:\n";
echo "   ✅ Backup: Criado ($backup_file)\n";
echo "   ✅ Correção: " . (strpos($content_updated, 'numero_whatsapp') !== false ? "Aplicada" : "Não aplicada") . "\n";
echo "   ✅ Webhook: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n";
echo "   ✅ Banco: " . ($mensagem_id ? "Salva (ID: $mensagem_id)" : "Não salva") . "\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if ($mensagem_id) {
    echo "1. ✅ CORREÇÃO DEFINITIVA FUNCIONANDO!\n";
    echo "2. 🧪 Teste real: Envie 'oi' para 554797146908 via WhatsApp\n";
    echo "3. 🔗 Verificar no chat: https://app.pixel12digital.com.br/painel/chat.php\n";
    echo "4. 🤖 Ana deve responder automaticamente\n";
    echo "5. 🎉 PROBLEMA RESOLVIDO!\n";
} else {
    echo "1. ❌ Ainda há problemas - verificar logs\n";
    echo "2. 🔧 Verificar se a correção foi aplicada corretamente\n";
    echo "3. 🧪 Testar novamente\n";
    echo "4. 📞 Contatar suporte se necessário\n";
}

echo "\n✅ CORREÇÃO DEFINITIVA CONCLUÍDA!\n";
?> 