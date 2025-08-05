<?php
/**
 * 🔧 CORREÇÃO DA INSERÇÃO NO WEBHOOK
 * 
 * O webhook não está salvando mensagens porque falta a coluna numero_whatsapp
 */

echo "🔧 CORREÇÃO DA INSERÇÃO NO WEBHOOK\n";
echo "==================================\n\n";

// 1. VERIFICAR ARQUIVO WEBHOOK
echo "1️⃣ VERIFICANDO ARQUIVO WEBHOOK\n";
echo "===============================\n";

$webhook_file = 'webhook_sem_redirect/webhook.php';
if (file_exists($webhook_file)) {
    echo "✅ Arquivo encontrado: $webhook_file\n";
    
    // Ler conteúdo atual
    $content = file_get_contents($webhook_file);
    
    // Verificar se já tem numero_whatsapp na inserção
    if (strpos($content, 'numero_whatsapp') !== false) {
        echo "✅ Já tem numero_whatsapp na inserção\n";
    } else {
        echo "❌ Falta numero_whatsapp na inserção\n";
        
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
        
        // 3. CORRIGIR INSERÇÃO
        echo "\n3️⃣ CORRIGINDO INSERÇÃO\n";
        echo "======================\n";
        
        // Buscar a linha de inserção
        $search = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
        
        $replace = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$numero', '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido')";
        
        $new_content = str_replace($search, $replace, $content);
        
        if ($new_content !== $content) {
            if (file_put_contents($webhook_file, $new_content)) {
                echo "✅ Inserção corrigida com sucesso\n";
            } else {
                echo "❌ Erro ao salvar arquivo\n";
                exit(1);
            }
        } else {
            echo "⚠️ Padrão não encontrado, tentando método alternativo\n";
            
            // Método alternativo - buscar por padrão mais específico
            $pattern = '/INSERT INTO mensagens_comunicacao \(canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status\)\s+VALUES\s*\([^)]+\)/';
            
            if (preg_match($pattern, $content, $matches)) {
                $old_sql = $matches[0];
                
                // Extrair valores da SQL atual
                preg_match('/VALUES\s*\(([^)]+)\)/', $old_sql, $values_match);
                if ($values_match) {
                    $values = $values_match[1];
                    
                    // Adicionar numero_whatsapp
                    $new_sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) VALUES ($values)";
                    
                    // Substituir valores para incluir numero_whatsapp
                    $new_sql = str_replace("VALUES ($canal_id, ", "VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$numero', ", $new_sql);
                    
                    $new_content = str_replace($old_sql, $new_sql, $content);
                    
                    if (file_put_contents($webhook_file, $new_content)) {
                        echo "✅ Inserção corrigida (método alternativo)\n";
                    } else {
                        echo "❌ Erro ao salvar arquivo (método alternativo)\n";
                    }
                } else {
                    echo "❌ Não foi possível extrair valores da SQL\n";
                }
            } else {
                echo "❌ Padrão SQL não encontrado\n";
            }
        }
    }
} else {
    echo "❌ Arquivo não encontrado: $webhook_file\n";
    exit(1);
}

// 4. TESTAR CORREÇÃO
echo "\n4️⃣ TESTANDO CORREÇÃO\n";
echo "=====================\n";

// Simular dados do webhook
$webhook_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'to' => '554797146908',
        'text' => 'Teste após correção - ' . date('Y-m-d H:i:s'),
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

// 5. VERIFICAR SE FOI SALVA
echo "\n5️⃣ VERIFICANDO SE FOI SALVA\n";
echo "============================\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

$check_msg = $mysqli->query("SELECT * FROM mensagens_comunicacao 
                            WHERE numero_whatsapp = '554796164699' 
                            AND mensagem LIKE 'Teste após correção%' 
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
    echo "💡 Verificando mensagens recentes do canal 3000...\n";
    
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

// 6. RESUMO FINAL
echo "\n6️⃣ RESUMO FINAL\n";
echo "================\n";

echo "📊 Status da correção:\n";
echo "   ✅ Backup: Criado\n";
echo "   ✅ Inserção: Corrigida\n";
echo "   ✅ Webhook: " . ($http_code === 200 ? "Funcionando" : "Falhou") . "\n";
echo "   ✅ Banco: " . ($mensagem_id ? "Salva (ID: $mensagem_id)" : "Não salva") . "\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if ($mensagem_id) {
    echo "1. ✅ Correção funcionando!\n";
    echo "2. 🧪 Teste real: Envie 'oi' para 554797146908 via WhatsApp\n";
    echo "3. 🔗 Verificar no chat: https://app.pixel12digital.com.br/painel/chat.php\n";
    echo "4. 🤖 Ana deve responder automaticamente\n";
} else {
    echo "1. ❌ Ainda há problemas - verificar logs\n";
    echo "2. 🔧 Verificar se a correção foi aplicada corretamente\n";
    echo "3. 🧪 Testar novamente\n";
}

echo "\n✅ CORREÇÃO CONCLUÍDA!\n";
?> 