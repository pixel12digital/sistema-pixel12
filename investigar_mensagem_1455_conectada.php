<?php
/**
 * 🔍 INVESTIGAR MENSAGEM 14:55 - CANAIS CONECTADOS
 * 
 * Investiga por que a mensagem das 14:55 não foi processada
 * mesmo com ambos os canais conectados
 */

echo "🔍 INVESTIGAR MENSAGEM 14:55 - CANAIS CONECTADOS\n";
echo "===============================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. VERIFICAR STATUS DOS CANAIS
echo "1️⃣ VERIFICANDO STATUS DOS CANAIS\n";
echo "--------------------------------\n";

$canal_urls = [
    'Canal 3000 (Ana)' => "http://{$vps_ip}:3000",
    'Canal 3001 (Humano)' => "http://{$vps_ip}:3001"
];

foreach ($canal_urls as $nome => $url) {
    echo "🔍 $nome...\n";
    
    $ch = curl_init($url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        echo "  📊 Status: " . ($data['status'] ?? 'N/A') . "\n";
        echo "  🔗 Conectado: " . (isset($data['connected']) && $data['connected'] ? '✅ SIM' : '❌ NÃO') . "\n";
        echo "  📱 Sessão: " . ($data['session'] ?? 'N/A') . "\n";
    } else {
        echo "  ❌ Erro (HTTP $http_code)\n";
    }
    echo "\n";
}

// 2. VERIFICAR CONFIGURAÇÃO DOS WEBHOOKS
echo "2️⃣ VERIFICANDO CONFIGURAÇÃO DOS WEBHOOKS\n";
echo "----------------------------------------\n";

foreach ($canal_urls as $nome => $url) {
    echo "🔍 Verificando webhook em $nome...\n";
    
    $ch = curl_init($url . '/webhook/config');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $config = json_decode($response, true);
        echo "  ✅ Configuração obtida\n";
        if ($config) {
            echo "  🔗 URL configurada: " . ($config['webhook_url'] ?? $config['url'] ?? 'N/A') . "\n";
            echo "  📊 Ativo: " . (isset($config['active']) && $config['active'] ? '✅ SIM' : '❌ NÃO') . "\n";
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
    }
    echo "\n";
}

// 3. VERIFICAR MENSAGENS NO BANCO (14:55 ESPECÍFICA)
echo "3️⃣ VERIFICANDO MENSAGEM 14:55 NO BANCO\n";
echo "---------------------------------------\n";

// Conectar ao banco
$host = 'localhost';
$dbname = 'u342734079_revendaweb';
$username = 'u342734079_revendaweb';
$password = 'Revenda@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar mensagem específica das 14:55
    $sql = "SELECT m.*, c.nome as cliente_nome, ch.nome_exibicao as canal_nome
            FROM mensagens_comunicacao m 
            LEFT JOIN clientes c ON m.cliente_id = c.id 
            LEFT JOIN canais_comunicacao ch ON m.canal_id = ch.id
            WHERE DATE(m.data_hora) = CURDATE() 
            AND HOUR(m.data_hora) = 14 
            AND MINUTE(m.data_hora) = 55
            ORDER BY m.data_hora DESC";
    
    $stmt = $pdo->query($sql);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Mensagens encontradas às 14:55:\n";
    if ($mensagens) {
        foreach ($mensagens as $msg) {
            echo "  📨 ID: " . $msg['id'] . "\n";
            echo "     Cliente: " . $msg['cliente_nome'] . "\n";
            echo "     Canal: " . $msg['canal_nome'] . "\n";
            echo "     Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
            echo "     Tipo: " . $msg['tipo'] . "\n";
            echo "     Data: " . $msg['data_hora'] . "\n";
            echo "     Direção: " . ($msg['direcao'] ?? 'N/A') . "\n";
            echo "     ---\n";
        }
    } else {
        echo "  ❌ Nenhuma mensagem encontrada às 14:55\n";
    }
    
    // Verificar mensagens do Charles Dietrich (554796164699)
    echo "\n📊 Mensagens do Charles Dietrich (554796164699) hoje:\n";
    $sql_charles = "SELECT m.*, c.nome as cliente_nome, ch.nome_exibicao as canal_nome
                    FROM mensagens_comunicacao m 
                    LEFT JOIN clientes c ON m.cliente_id = c.id 
                    LEFT JOIN canais_comunicacao ch ON m.canal_id = ch.id
                    WHERE c.telefone = '554796164699'
                    AND DATE(m.data_hora) = CURDATE()
                    ORDER BY m.data_hora DESC";
    
    $stmt_charles = $pdo->query($sql_charles);
    $mensagens_charles = $stmt_charles->fetchAll(PDO::FETCH_ASSOC);
    
    if ($mensagens_charles) {
        foreach ($mensagens_charles as $msg) {
            echo "  📨 ID: " . $msg['id'] . "\n";
            echo "     Cliente: " . $msg['cliente_nome'] . "\n";
            echo "     Canal: " . $msg['canal_nome'] . "\n";
            echo "     Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
            echo "     Tipo: " . $msg['tipo'] . "\n";
            echo "     Data: " . $msg['data_hora'] . "\n";
            echo "     Direção: " . ($msg['direcao'] ?? 'N/A') . "\n";
            echo "     ---\n";
        }
    } else {
        echo "  ❌ Nenhuma mensagem do Charles Dietrich encontrada hoje\n";
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erro ao conectar ao banco: " . $e->getMessage() . "\n";
}

// 4. TESTAR ENVIO DIRETO PARA O CANAL 3000
echo "\n4️⃣ TESTANDO ENVIO DIRETO PARA CANAL 3000\n";
echo "----------------------------------------\n";

// Testar envio direto para o canal 3000
$test_data = [
    'to' => '554796164699@c.us',
    'message' => 'TESTE DIRETO CANAL 3000 - ' . date('Y-m-d H:i:s'),
    'session' => 'default'
];

echo "📤 Enviando teste direto para canal 3000...\n";
echo "  Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init("http://{$vps_ip}:3000/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do canal 3000:\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ Envio direto bem-sucedido!\n";
        echo "  📝 Message ID: " . ($data['messageId'] ?? 'N/A') . "\n";
    } else {
        echo "  ❌ Erro no envio: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "  ❌ Erro HTTP: $http_code\n";
    if ($error) {
        echo "  Error: $error\n";
    }
    echo "  Response: $response\n";
}

// 5. VERIFICAR SE O WEBHOOK ESTÁ RECEBENDO MENSAGENS
echo "\n5️⃣ VERIFICANDO SE WEBHOOK RECEBE MENSAGENS\n";
echo "------------------------------------------\n";

// Simular mensagem recebida do WhatsApp
$webhook_test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE WEBHOOK RECEBIMENTO - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Testando recebimento no webhook...\n";
echo "  Dados: " . json_encode($webhook_test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do webhook:\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ Webhook processando corretamente!\n";
        echo "  📝 Message ID: " . ($data['message_id'] ?? 'N/A') . "\n";
        echo "  📝 Response ID: " . ($data['response_id'] ?? 'N/A') . "\n";
    } else {
        echo "  ❌ Erro no processamento: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "  ❌ Erro HTTP: $http_code\n";
    if ($error) {
        echo "  Error: $error\n";
    }
    echo "  Response: $response\n";
}

// 6. ANÁLISE E DIAGNÓSTICO
echo "\n6️⃣ ANÁLISE E DIAGNÓSTICO\n";
echo "------------------------\n";

echo "🔍 **POSSÍVEIS CAUSAS PARA MENSAGEM 14:55 NÃO PROCESSADA:**\n\n";

echo "1. 📱 **Mensagem não chegou ao VPS:**\n";
echo "   - Problema de conectividade WhatsApp\n";
echo "   - Mensagem perdida na rede\n";
echo "   - Sessão instável no momento\n\n";

echo "2. 🔗 **Webhook não foi chamado:**\n";
echo "   - VPS recebeu mas não enviou para webhook\n";
echo "   - Erro interno no VPS\n";
echo "   - Webhook temporariamente indisponível\n\n";

echo "3. 📨 **Mensagem processada mas não salva:**\n";
echo "   - Erro no banco de dados\n";
echo "   - Problema de conexão com banco\n";
echo "   - Rollback da transação\n\n";

echo "4. 🌐 **Problema de timing:**\n";
echo "   - Mensagem chegou mas foi processada em horário diferente\n";
echo "   - Problema de timezone\n";
echo "   - Clock desincronizado\n\n";

echo "🎯 **PRÓXIMOS PASSOS:**\n\n";

echo "1. 📊 **Verificar logs do VPS:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n";
echo "   # Procure por logs de recebimento às 14:55\n\n";

echo "2. 🔍 **Verificar logs do webhook:**\n";
echo "   - Acesse o painel\n";
echo "   - Verifique logs de erro\n";
echo "   - Procure por erros às 14:55\n\n";

echo "3. 🧪 **Teste manual:**\n";
echo "   Envie uma nova mensagem para 554797146908\n";
echo "   Verifique se aparece no banco imediatamente\n\n";

echo "4. 📋 **Verificar configuração:**\n";
echo "   - Verifique se o webhook está ativo\n";
echo "   - Confirme se a URL está correta\n";
echo "   - Teste conectividade VPS -> Webhook\n\n";

echo "✅ INVESTIGAÇÃO CONCLUÍDA!\n";
echo "O problema parece ser específico da mensagem das 14:55.\n";
echo "Verifique os logs para identificar a causa exata.\n";
?> 