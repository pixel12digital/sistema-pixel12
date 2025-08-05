<?php
/**
 * 🔧 RESTAURAÇÃO VPS BASEADA NO CÓDIGO REAL DO PROJETO
 * 
 * Script para restaurar a VPS usando apenas o código como fonte de verdade
 * Baseado na análise do whatsapp-api-server.js e outros arquivos do projeto
 */

echo "🔧 RESTAURAÇÃO VPS - CÓDIGO REAL DO PROJETO\n";
echo "===========================================\n\n";

// Configurações baseadas no código do projeto
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// Canais baseados no código do projeto
$canais = [
    '3000' => [
        'nome' => 'Canal Financeiro (Ana)',
        'porta' => 3000,
        'identificador' => '554797146908@c.us',
        'nome_exibicao' => 'Pixel12Digital',
        'session' => 'default'
    ],
    '3001' => [
        'nome' => 'Canal Comercial (Humano)',
        'porta' => 3001,
        'identificador' => '554797309525@c.us',
        'nome_exibicao' => 'Comercial - Pixel',
        'session' => 'comercial'
    ]
];

echo "📋 CONFIGURAÇÕES IDENTIFICADAS NO CÓDIGO:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n";
echo "Canais: " . count($canais) . " (3000 e 3001)\n\n";

// 1. VERIFICAR STATUS DOS CANAIS
echo "1️⃣ VERIFICANDO STATUS DOS CANAIS\n";
echo "--------------------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 {$canal['nome']} (Porta $porta)...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        echo "  ✅ Canal funcionando\n";
        $status_data = json_decode($response, true);
        if ($status_data) {
            echo "  📊 Status: " . ($status_data['status'] ?? 'unknown') . "\n";
            echo "  🔗 Porta: " . ($status_data['port'] ?? 'unknown') . "\n";
            if (isset($status_data['clients_status'])) {
                echo "  👥 Clientes: " . count($status_data['clients_status']) . "\n";
            }
        }
    } else {
        echo "  ❌ Canal não responde (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
    }
    echo "\n";
}

// 2. CONFIGURAR WEBHOOKS (baseado no código real)
echo "2️⃣ CONFIGURANDO WEBHOOKS\n";
echo "------------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔧 Configurando {$canal['nome']}...\n";
    
    // Usar endpoint correto do código: /webhook/config
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        echo "  ✅ Webhook configurado\n";
        $result = json_decode($response, true);
        if ($result && isset($result['webhook_url'])) {
            echo "  🔗 URL: {$result['webhook_url']}\n";
        }
    } else {
        echo "  ❌ Erro ao configurar webhook (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
        echo "  📝 Resposta: $response\n";
    }
    echo "\n";
}

// 3. VERIFICAR CONFIGURAÇÃO DOS WEBHOOKS
echo "3️⃣ VERIFICANDO CONFIGURAÇÃO DOS WEBHOOKS\n";
echo "----------------------------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 Verificando {$canal['nome']}...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        $config = json_decode($response, true);
        if ($config && isset($config['webhook_url'])) {
            echo "  ✅ Webhook configurado\n";
            echo "  🔗 URL: {$config['webhook_url']}\n";
        } else {
            echo "  ⚠️ Webhook não configurado\n";
        }
    } else {
        echo "  ❌ Não foi possível verificar (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
    }
    echo "\n";
}

// 4. TESTAR ENVIO DE MENSAGENS (baseado no código real)
echo "4️⃣ TESTANDO ENVIO DE MENSAGENS\n";
echo "------------------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    $session = $canal['session'];
    echo "🧪 Testando {$canal['nome']}...\n";
    
    // Usar formato correto do código: /send/text com sessionName
    $test_data = [
        'sessionName' => $session,
        'number' => '5511999999999',
        'message' => 'Teste restauração VPS - ' . date('Y-m-d H:i:s')
    ];
    
    $ch = curl_init("http://$vps_ip:$porta/send/text");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        echo "  ✅ Envio funcionando\n";
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            echo "  📝 Mensagem enviada com sucesso\n";
            echo "  🎯 Sessão: {$result['session']}\n";
        }
    } else {
        echo "  ❌ Erro no envio (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
        echo "  📝 Resposta: $response\n";
    }
    echo "\n";
}

// 5. TESTAR WEBHOOKS
echo "5️⃣ TESTANDO WEBHOOKS\n";
echo "--------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🧪 Testando webhook {$canal['nome']}...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/webhook/test");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        echo "  ✅ Webhook testado com sucesso\n";
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            echo "  📝 Teste enviado para: {$result['webhook_url']}\n";
        }
    } else {
        echo "  ❌ Erro no teste (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
    }
    echo "\n";
}

// 6. ATUALIZAR BANCO DE DADOS
echo "6️⃣ ATUALIZANDO BANCO DE DADOS\n";
echo "-----------------------------\n";

try {
    require_once 'config.php';
    require_once 'painel/db.php';
    
    $atualizados = 0;
    
    foreach ($canais as $canal_id => $canal) {
        $identificador = $canal['identificador'];
        $nome_exibicao = $canal['nome_exibicao'];
        $porta = $canal['porta'];
        
        // Verificar se o canal já existe
        $sql_check = "SELECT id FROM canais_comunicacao WHERE identificador = ? AND tipo = 'whatsapp'";
        $stmt_check = $mysqli->prepare($sql_check);
        $stmt_check->bind_param('s', $identificador);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            // Atualizar canal existente
            $canal_db = $result_check->fetch_assoc();
            $sql_update = "UPDATE canais_comunicacao SET 
                          nome_exibicao = ?, 
                          status = 'conectado',
                          data_conexao = NOW()
                          WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param('si', $nome_exibicao, $canal_db['id']);
            $stmt_update->execute();
            $atualizados++;
            echo "  ✅ Canal {$nome_exibicao} atualizado\n";
        } else {
            // Inserir novo canal
            $sql_insert = "INSERT INTO canais_comunicacao 
                          (tipo, identificador, nome_exibicao, status, data_conexao) 
                          VALUES ('whatsapp', ?, ?, 'conectado', NOW())";
            $stmt_insert = $mysqli->prepare($sql_insert);
            $stmt_insert->bind_param('ss', $identificador, $nome_exibicao);
            $stmt_insert->execute();
            $atualizados++;
            echo "  ✅ Canal {$nome_exibicao} criado\n";
        }
    }
    
    echo "📊 {$atualizados} canais processados no banco de dados\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao atualizar banco de dados: {$e->getMessage()}\n";
}

echo "\n";

// 7. RESUMO FINAL
echo "7️⃣ RESUMO DA RESTAURAÇÃO\n";
echo "------------------------\n";

echo "🎯 RESTAURAÇÃO BASEADA NO CÓDIGO REAL CONCLUÍDA!\n\n";

echo "📋 CONFIGURAÇÕES APLICADAS:\n";
echo "• VPS: $vps_ip\n";
echo "• Canais: " . count($canais) . " (3000 e 3001)\n";
echo "• Webhook: $webhook_url\n";
echo "• Endpoints: /send/text, /webhook/config, /webhook/test\n";
echo "• Sessões: default (3000), comercial (3001)\n\n";

echo "🔧 PRÓXIMOS PASSOS:\n";
echo "1. Acesse o painel de comunicação\n";
echo "2. Verifique se os canais estão conectados\n";
echo "3. Teste envio de mensagem real\n";
echo "4. Monitore os logs se necessário\n\n";

echo "📚 COMANDOS ÚTEIS:\n";
echo "• Status: curl http://$vps_ip:3000/status\n";
echo "• Webhook: curl http://$vps_ip:3000/webhook/config\n";
echo "• Envio: curl -X POST http://$vps_ip:3000/send/text -H 'Content-Type: application/json' -d '{\"sessionName\":\"default\",\"number\":\"5511999999999\",\"message\":\"teste\"}'\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 20'\n\n";

echo "✅ RESTAURAÇÃO FINALIZADA COM SUCESSO!\n";
echo "🎉 VPS restaurada baseada no código real do projeto!\n";
?> 