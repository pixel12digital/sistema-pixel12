<?php
/**
 * 🔍 VERIFICAR MENSAGEM 14:55
 * 
 * Verifica especificamente a mensagem enviada às 14:55 para o canal 3000
 * e investiga por que a Ana não respondeu
 */

echo "🔍 VERIFICAR MENSAGEM 14:55\n";
echo "===========================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. VERIFICAR STATUS DO CANAL 3000
echo "1️⃣ VERIFICANDO STATUS DO CANAL 3000\n";
echo "-----------------------------------\n";

$canal_url = "http://{$vps_ip}:3000";

$ch = curl_init($canal_url . '/status');
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
    
    if (isset($data['connected']) && $data['connected']) {
        echo "  ✅ WhatsApp conectado e funcionando!\n";
    } else {
        echo "  ❌ WhatsApp NÃO conectado - Este é o problema!\n";
    }
} else {
    echo "  ❌ Erro (HTTP $http_code)\n";
}
echo "\n";

// 2. VERIFICAR CONFIGURAÇÃO DO WEBHOOK NO CANAL 3000
echo "2️⃣ VERIFICANDO CONFIGURAÇÃO DO WEBHOOK\n";
echo "--------------------------------------\n";

$ch = curl_init($canal_url . '/webhook/config');
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
        
        $configured_url = $config['webhook_url'] ?? $config['url'] ?? '';
        if ($configured_url === $webhook_url) {
            echo "  ✅ URL correta configurada\n";
        } else {
            echo "  ❌ URL incorreta! Configurada: $configured_url\n";
        }
    }
} else {
    echo "  ❌ Erro ao obter configuração (HTTP $http_code)\n";
}
echo "\n";

// 3. VERIFICAR MENSAGENS NO BANCO (14:55)
echo "3️⃣ VERIFICANDO MENSAGENS NO BANCO (14:55)\n";
echo "-----------------------------------------\n";

// Conectar ao banco
$host = 'localhost';
$dbname = 'u342734079_revendaweb';
$username = 'u342734079_revendaweb';
$password = 'Revenda@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar mensagens específicas de 14:55
    $sql = "SELECT m.*, c.nome as cliente_nome, ch.nome_exibicao as canal_nome
            FROM mensagens_comunicacao m 
            LEFT JOIN clientes c ON m.cliente_id = c.id 
            LEFT JOIN canais_comunicacao ch ON m.canal_id = ch.id
            WHERE DATE(m.data_hora) = CURDATE() 
            AND HOUR(m.data_hora) = 14 
            AND MINUTE(m.data_hora) >= 50
            AND MINUTE(m.data_hora) <= 59
            ORDER BY m.data_hora DESC";
    
    $stmt = $pdo->query($sql);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Mensagens encontradas entre 14:50-14:59:\n";
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
        echo "  ❌ Nenhuma mensagem encontrada entre 14:50-14:59\n";
    }
    
    // Verificar mensagens mais recentes
    echo "\n📊 Mensagens mais recentes (últimos 30 minutos):\n";
    $sql_recent = "SELECT m.*, c.nome as cliente_nome, ch.nome_exibicao as canal_nome
                   FROM mensagens_comunicacao m 
                   LEFT JOIN clientes c ON m.cliente_id = c.id 
                   LEFT JOIN canais_comunicacao ch ON m.canal_id = ch.id
                   WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                   ORDER BY m.data_hora DESC 
                   LIMIT 10";
    
    $stmt_recent = $pdo->query($sql_recent);
    $mensagens_recentes = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
    
    if ($mensagens_recentes) {
        foreach ($mensagens_recentes as $msg) {
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
        echo "  ❌ Nenhuma mensagem encontrada nos últimos 30 minutos\n";
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erro ao conectar ao banco: " . $e->getMessage() . "\n";
}

// 4. TESTAR ENVIO DE MENSAGEM PARA O CANAL 3000
echo "\n4️⃣ TESTANDO ENVIO PARA CANAL 3000\n";
echo "----------------------------------\n";

// Simular mensagem que deveria ter sido enviada às 14:55
$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE CHAT.PHP - envio de 554796164699 para CANAL 3000 +55 47 9714-6908 04/08 14:55',
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Enviando teste para canal 3000...\n";
echo "  Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

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
if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ Teste bem-sucedido!\n";
        echo "  📝 Message ID: " . ($data['message_id'] ?? 'N/A') . "\n";
        echo "  📝 Response ID: " . ($data['response_id'] ?? 'N/A') . "\n";
        echo "  📝 Ana respondeu: " . ($data['ana_response'] ?? 'N/A') . "\n";
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

// 5. VERIFICAR LOGS DO VPS
echo "\n5️⃣ VERIFICANDO LOGS DO VPS\n";
echo "---------------------------\n";

echo "🔍 Para verificar logs do VPS, execute:\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 logs whatsapp-3000 --lines 50\n";
echo "   # Procure por logs de recebimento de mensagem às 14:55\n\n";

// 6. ANÁLISE E DIAGNÓSTICO
echo "6️⃣ ANÁLISE E DIAGNÓSTICO\n";
echo "------------------------\n";

echo "🔍 **POSSÍVEIS CAUSAS PARA ANA NÃO RESPONDER:**\n\n";

echo "1. 📱 **WhatsApp desconectado:**\n";
echo "   - Se o status mostrar 'connected: false'\n";
echo "   - Mensagens não chegam ao VPS\n\n";

echo "2. 🔗 **Webhook não configurado:**\n";
echo "   - URL incorreta ou webhook desativado\n";
echo "   - Mensagens não são enviadas para o sistema\n\n";

echo "3. 📨 **Mensagem não processada:**\n";
echo "   - Erro no processamento da mensagem\n";
echo "   - Ana não consegue gerar resposta\n\n";

echo "4. 🌐 **Problemas de conectividade:**\n";
echo "   - VPS não consegue acessar o webhook\n";
echo "   - Timeout nas requisições\n\n";

echo "🎯 **PRÓXIMOS PASSOS:**\n\n";

echo "1. 🔄 **Reconectar WhatsApp:**\n";
echo "   ssh root@{$vps_ip}\n";
echo "   pm2 restart whatsapp-3000\n";
echo "   curl -s http://{$vps_ip}:3000/qr?session=default\n\n";

echo "2. 📊 **Verificar logs:**\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n";
echo "   # Procure por erros ou mensagens não processadas\n\n";

echo "3. 🧪 **Teste manual:**\n";
echo "   Envie uma nova mensagem para 554797146908\n";
echo "   Verifique se aparece nos logs\n\n";

echo "✅ VERIFICAÇÃO CONCLUÍDA!\n";
echo "Execute os passos recomendados para resolver o problema.\n";
?> 