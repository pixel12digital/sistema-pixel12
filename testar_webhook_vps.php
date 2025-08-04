<?php
/**
 * 🧪 TESTE WEBHOOK VPS
 * 
 * Testa a configuração do webhook no VPS e verifica se está funcionando
 */

echo "🧪 TESTE WEBHOOK VPS\n";
echo "====================\n\n";

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
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        echo "  ✅ Conectado (HTTP $http_code)\n";
        if ($data) {
            echo "  📊 Status: " . ($data['status'] ?? 'N/A') . "\n";
            echo "  📱 Sessão: " . ($data['session'] ?? 'N/A') . "\n";
            echo "  🔗 Conectado: " . (isset($data['connected']) && $data['connected'] ? '✅ SIM' : '❌ NÃO') . "\n";
        }
    } else {
        echo "  ❌ Erro (HTTP $http_code): $error\n";
    }
    echo "\n";
}

// 2. VERIFICAR CONFIGURAÇÃO ATUAL DO WEBHOOK
echo "2️⃣ VERIFICANDO CONFIGURAÇÃO ATUAL DO WEBHOOK\n";
echo "--------------------------------------------\n";

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
            echo "  🔗 URL configurada: " . ($config['url'] ?? 'N/A') . "\n";
            echo "  📊 Ativo: " . (isset($config['active']) && $config['active'] ? '✅ SIM' : '❌ NÃO') . "\n";
            
            // Verificar se a URL está correta
            if (($config['url'] ?? '') === $webhook_url) {
                echo "  ✅ URL correta configurada\n";
            } else {
                echo "  ❌ URL incorreta! Configurada: " . ($config['url'] ?? 'N/A') . "\n";
            }
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
    }
    echo "\n";
}

// 3. TESTAR ACESSIBILIDADE DO WEBHOOK
echo "3️⃣ TESTANDO ACESSIBILIDADE DO WEBHOOK\n";
echo "-------------------------------------\n";

echo "🔍 Testando webhook: $webhook_url\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Acessível: " . ($http_code > 0 && $http_code < 400 ? '✅ SIM' : '❌ NÃO') . "\n";
if ($error) {
    echo "  Erro: $error\n";
}
echo "\n";

// 4. CONFIGURAR WEBHOOK CORRETAMENTE
echo "4️⃣ CONFIGURANDO WEBHOOK CORRETAMENTE\n";
echo "-----------------------------------\n";

foreach ($canal_urls as $nome => $url) {
    echo "🔧 Configurando webhook em $nome...\n";
    
    $ch = curl_init($url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Webhook configurado com sucesso\n";
        $result = json_decode($response, true);
        if ($result) {
            echo "  📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "  ❌ Erro ao configurar webhook (HTTP $http_code)\n";
        if ($error) {
            echo "  Erro cURL: $error\n";
        }
        echo "  Resposta: $response\n";
    }
    echo "\n";
}

// 5. TESTAR ENVIO DE MENSAGEM SIMULADA
echo "5️⃣ TESTANDO ENVIO DE MENSAGEM SIMULADA\n";
echo "--------------------------------------\n";

$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE WEBHOOK VPS - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Enviando mensagem de teste...\n";
echo "  De: {$test_data['from']}\n";
echo "  Para: {$test_data['to']}\n";
echo "  Mensagem: {$test_data['body']}\n\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
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
echo "  Response: $response\n";
if ($error) {
    echo "  Error: $error\n";
}

// 6. VERIFICAR SE MENSAGEM FOI SALVA NO BANCO
echo "\n6️⃣ VERIFICANDO SE MENSAGEM FOI SALVA NO BANCO\n";
echo "---------------------------------------------\n";

try {
    $mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
    
    if ($mysqli->connect_error) {
        echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ Conectado ao banco de dados\n";
        
        // Verificar mensagens recentes
        $sql = "SELECT m.*, c.nome as cliente_nome, ch.nome_exibicao as canal_nome
                FROM mensagens_comunicacao m 
                LEFT JOIN clientes c ON m.cliente_id = c.id 
                LEFT JOIN canais_comunicacao ch ON m.canal_id = ch.id
                WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ORDER BY m.data_hora DESC 
                LIMIT 10";
        
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "📨 Mensagens recentes (últimos 5 minutos):\n";
            while ($row = $result->fetch_assoc()) {
                echo "  - ID: {$row['id']} | Canal: {$row['canal_nome']} | Cliente: {$row['cliente_nome']} | Direção: {$row['direcao']} | Data: {$row['data_hora']}\n";
                echo "    Mensagem: {$row['mensagem']}\n";
            }
        } else {
            echo "❌ Nenhuma mensagem encontrada nos últimos 5 minutos\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

// 7. RECOMENDAÇÕES
echo "\n7️⃣ RECOMENDAÇÕES\n";
echo "----------------\n";

echo "🎯 PRÓXIMOS PASSOS:\n\n";

echo "1. 🔧 **Se webhook não estiver configurado:**\n";
echo "   Execute este script novamente para configurar\n\n";

echo "2. 🔄 **Se canais não estiverem conectados:**\n";
echo "   ssh root@{$vps_ip} 'pm2 restart whatsapp-3000 whatsapp-3001'\n\n";

echo "3. 📊 **Para verificar logs do VPS:**\n";
echo "   ssh root@{$vps_ip} 'pm2 logs whatsapp-3000 --lines 20'\n\n";

echo "4. 🧪 **Para testar envio real:**\n";
echo "   Envie uma mensagem para 554797146908 via WhatsApp\n\n";

echo "5. 🌐 **Para verificar no chat:**\n";
echo "   Acesse: https://app.pixel12digital.com.br/painel/chat.php\n\n";

echo "✅ TESTE CONCLUÍDO!\n";
echo "Verifique os resultados acima e execute as recomendações se necessário.\n";
?> 