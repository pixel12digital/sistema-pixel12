<?php
/**
 * 🔍 INVESTIGAÇÃO WEBHOOK WHATSAPP
 * 
 * Diagnóstico completo do problema: mensagens não chegam quando enviadas do WhatsApp
 * mas testes locais comprovam que estão sendo salvas
 */

echo "🔍 INVESTIGAÇÃO WEBHOOK WHATSAPP\n";
echo "================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_urls = [
    'Principal' => 'https://app.pixel12digital.com.br/webhook.php',
    'Alternativo' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php',
    'VPS Local' => 'http://212.85.11.238:8080/api/webhook.php'
];

$canal_urls = [
    'Canal 3000 (Ana)' => "http://{$vps_ip}:3000",
    'Canal 3001 (Humano)' => "http://{$vps_ip}:3001"
];

echo "📋 CONFIGURAÇÕES IDENTIFICADAS:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhooks configurados:\n";
foreach ($webhook_urls as $nome => $url) {
    echo "  - $nome: $url\n";
}
echo "\n";

// 1. VERIFICAR STATUS DOS CANAIS WHATSAPP
echo "1️⃣ VERIFICANDO STATUS DOS CANAIS WHATSAPP\n";
echo "-----------------------------------------\n";

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

// 2. VERIFICAR CONFIGURAÇÃO DE WEBHOOK NO VPS
echo "2️⃣ VERIFICANDO CONFIGURAÇÃO DE WEBHOOK NO VPS\n";
echo "---------------------------------------------\n";

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
        }
    } else {
        echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
    }
    echo "\n";
}

// 3. TESTAR ACESSIBILIDADE DOS WEBHOOKS
echo "3️⃣ TESTANDO ACESSIBILIDADE DOS WEBHOOKS\n";
echo "---------------------------------------\n";

foreach ($webhook_urls as $nome => $url) {
    echo "🔍 Testando $nome...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "  URL: $url\n";
    echo "  HTTP Code: $http_code\n";
    echo "  Acessível: " . ($http_code > 0 && $http_code < 400 ? '✅ SIM' : '❌ NÃO') . "\n";
    if ($error) {
        echo "  Erro: $error\n";
    }
    echo "\n";
}

// 4. TESTAR ENVIO DE MENSAGEM SIMULADA
echo "4️⃣ TESTANDO ENVIO DE MENSAGEM SIMULADA\n";
echo "--------------------------------------\n";

$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE INVESTIGAÇÃO WEBHOOK - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Enviando mensagem de teste...\n";
echo "  De: {$test_data['from']}\n";
echo "  Para: {$test_data['to']}\n";
echo "  Mensagem: {$test_data['body']}\n\n";

// Testar no webhook principal
$ch = curl_init($webhook_urls['Principal']);
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

// 5. VERIFICAR LOGS RECENTES
echo "\n5️⃣ VERIFICANDO LOGS RECENTES\n";
echo "-----------------------------\n";

// Verificar se há logs de webhook
$log_files = [
    'webhook_debug.log',
    'logs/webhook_' . date('Y-m-d') . '.log',
    'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "📄 Log encontrado: $log_file\n";
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -10); // Últimas 10 linhas
        echo "  Últimas linhas:\n";
        foreach ($recent_lines as $line) {
            echo "    " . trim($line) . "\n";
        }
        echo "\n";
    }
}

// 6. VERIFICAR BANCO DE DADOS
echo "6️⃣ VERIFICANDO BANCO DE DADOS\n";
echo "-----------------------------\n";

try {
    $mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
    
    if ($mysqli->connect_error) {
        echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ Conectado ao banco de dados\n";
        
        // Verificar mensagens recentes
        $sql = "SELECT m.*, c.nome as cliente_nome, ch.nome as canal_nome
                FROM mensagens_comunicacao m 
                LEFT JOIN clientes c ON m.cliente_id = c.id 
                LEFT JOIN canais_whatsapp ch ON m.canal_id = ch.id
                WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                ORDER BY m.data_hora DESC 
                LIMIT 5";
        
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "📨 Mensagens recentes (últimos 10 minutos):\n";
            while ($row = $result->fetch_assoc()) {
                echo "  - ID: {$row['id']} | Canal: {$row['canal_nome']} | Cliente: {$row['cliente_nome']} | Direção: {$row['direcao']} | Data: {$row['data_hora']}\n";
                echo "    Mensagem: {$row['mensagem']}\n";
            }
        } else {
            echo "❌ Nenhuma mensagem encontrada nos últimos 10 minutos\n";
        }
        
        // Verificar canais ativos
        $sql_canais = "SELECT * FROM canais_whatsapp WHERE ativo = 1";
        $result_canais = $mysqli->query($sql_canais);
        
        if ($result_canais && $result_canais->num_rows > 0) {
            echo "\n📱 Canais ativos:\n";
            while ($row = $result_canais->fetch_assoc()) {
                echo "  - ID: {$row['id']} | Nome: {$row['nome']} | Número: {$row['numero_whatsapp']} | Ativo: " . ($row['ativo'] ? '✅' : '❌') . "\n";
            }
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

// 7. ANÁLISE E RECOMENDAÇÕES
echo "\n7️⃣ ANÁLISE E RECOMENDAÇÕES\n";
echo "---------------------------\n";

echo "🔍 POSSÍVEIS CAUSAS DO PROBLEMA:\n\n";

echo "1. 🌐 **Problema de DNS/URL:**\n";
echo "   - O VPS pode não conseguir acessar a URL do webhook\n";
echo "   - Verificar se a URL está correta e acessível externamente\n\n";

echo "2. 🔒 **Problema de Firewall/Segurança:**\n";
echo "   - Firewall bloqueando conexões do VPS para o webhook\n";
echo "   - Configurações de CORS ou segurança\n\n";

echo "3. ⚙️ **Problema de Configuração no VPS:**\n";
echo "   - Webhook não configurado corretamente no servidor WhatsApp\n";
echo "   - Sessões não ativas ou desconectadas\n\n";

echo "4. 📡 **Problema de Rede:**\n";
echo "   - Latência alta entre VPS e servidor webhook\n";
echo "   - Timeout nas requisições\n\n";

echo "🎯 PRÓXIMOS PASSOS RECOMENDADOS:\n\n";

echo "1. 🔧 **Reconfigurar webhook no VPS:**\n";
echo "   curl -X POST \"http://{$vps_ip}:3000/webhook/config\" \\\n";
echo "        -H \"Content-Type: application/json\" \\\n";
echo "        -d '{\"url\": \"{$webhook_urls['Principal']}\"}'\n\n";

echo "2. 🧪 **Testar conectividade direta:**\n";
echo "   curl -X POST \"{$webhook_urls['Principal']}\" \\\n";
echo "        -H \"Content-Type: application/json\" \\\n";
echo "        -d '" . json_encode($test_data) . "'\n\n";

echo "3. 📊 **Verificar logs do VPS:**\n";
echo "   ssh root@{$vps_ip} 'pm2 logs whatsapp-3000 --lines 50'\n\n";

echo "4. 🔄 **Reiniciar serviços no VPS:**\n";
echo "   ssh root@{$vps_ip} 'pm2 restart whatsapp-3000'\n\n";

echo "5. 🌐 **Verificar se o domínio está acessível:**\n";
echo "   curl -I {$webhook_urls['Principal']}\n\n";

echo "✅ INVESTIGAÇÃO CONCLUÍDA!\n";
echo "Use as recomendações acima para resolver o problema.\n";
?> 