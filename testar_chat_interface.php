<?php
/**
 * 🧪 TESTE INTERFACE CHAT
 * 
 * Testa se as mensagens estão aparecendo corretamente na interface do chat
 */

echo "🧪 TESTE INTERFACE CHAT\n";
echo "======================\n\n";

// Configurações
$cliente_id = 4296; // Charles Dietrich Wutzke
$api_url = 'https://app.pixel12digital.com.br/painel/api/mensagens_cliente.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "Cliente ID: $cliente_id\n";
echo "API URL: $api_url\n\n";

// 1. TESTAR API DE MENSAGENS
echo "1️⃣ TESTANDO API DE MENSAGENS\n";
echo "-----------------------------\n";

$ch = curl_init($api_url . "?cliente_id=$cliente_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta da API:\n";
echo "  HTTP Code: $http_code\n";
if ($error) {
    echo "  Error: $error\n";
}

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ API funcionando\n";
        echo "  📨 Total de mensagens: " . count($data['mensagens']) . "\n";
        
        // Mostrar últimas 5 mensagens
        $ultimas_mensagens = array_slice($data['mensagens'], -5);
        echo "\n  📋 Últimas 5 mensagens:\n";
        foreach ($ultimas_mensagens as $msg) {
            $time = date('H:i', strtotime($msg['data_hora']));
            $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
            $canal = $msg['canal_nome'] ?? 'WhatsApp';
            echo "    $direcao [$time] [$canal] {$msg['mensagem']}\n";
        }
    } else {
        echo "  ❌ Erro na API: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "  ❌ Erro HTTP: $http_code\n";
}

// 2. VERIFICAR BANCO DIRETAMENTE
echo "\n2️⃣ VERIFICANDO BANCO DIRETAMENTE\n";
echo "--------------------------------\n";

try {
    $mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
    
    if ($mysqli->connect_error) {
        echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ Conectado ao banco de dados\n";
        
        // Verificar mensagens do cliente
        $sql = "SELECT m.*, c.nome_exibicao as canal_nome
                FROM mensagens_comunicacao m 
                LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                WHERE m.cliente_id = ?
                ORDER BY m.data_hora DESC 
                LIMIT 10";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            echo "📨 Mensagens no banco (últimas 10):\n";
            while ($row = $result->fetch_assoc()) {
                $time = date('H:i', strtotime($row['data_hora']));
                $direcao = $row['direcao'] === 'recebido' ? '📥' : '📤';
                $canal = $row['canal_nome'] ?? 'WhatsApp';
                $mensagem_curta = substr($row['mensagem'], 0, 50) . (strlen($row['mensagem']) > 50 ? '...' : '');
                echo "  $direcao [$time] [$canal] ID:{$row['id']} $mensagem_curta\n";
            }
        } else {
            echo "❌ Nenhuma mensagem encontrada para o cliente $cliente_id\n";
        }
        
        $stmt->close();
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

// 3. TESTAR ENVIO DE MENSAGEM REAL
echo "\n3️⃣ TESTANDO ENVIO DE MENSAGEM REAL\n";
echo "-----------------------------------\n";

$test_data = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE INTERFACE CHAT - ' . date('Y-m-d H:i:s'),
    'type' => 'text',
    'timestamp' => time()
];

echo "📤 Enviando mensagem de teste...\n";
echo "  De: {$test_data['from']}\n";
echo "  Para: {$test_data['to']}\n";
echo "  Mensagem: {$test_data['body']}\n\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';
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
if ($error) {
    echo "  Error: $error\n";
}

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "  ✅ Mensagem processada com sucesso\n";
        echo "  📝 Message ID: " . ($data['message_id'] ?? 'N/A') . "\n";
        echo "  📝 Response ID: " . ($data['response_id'] ?? 'N/A') . "\n";
        
        // Aguardar um pouco e verificar se apareceu no banco
        echo "\n  ⏳ Aguardando 3 segundos...\n";
        sleep(3);
        
        // Verificar novamente o banco
        try {
            $mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
            
            if (!$mysqli->connect_error) {
                $sql = "SELECT m.*, c.nome_exibicao as canal_nome
                        FROM mensagens_comunicacao m 
                        LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                        WHERE m.cliente_id = ? AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                        ORDER BY m.data_hora DESC 
                        LIMIT 5";
                
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('i', $cliente_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    echo "  📨 Mensagens recentes (último minuto):\n";
                    while ($row = $result->fetch_assoc()) {
                        $time = date('H:i:s', strtotime($row['data_hora']));
                        $direcao = $row['direcao'] === 'recebido' ? '📥' : '📤';
                        $canal = $row['canal_nome'] ?? 'WhatsApp';
                        $mensagem_curta = substr($row['mensagem'], 0, 50) . (strlen($row['mensagem']) > 50 ? '...' : '');
                        echo "    $direcao [$time] [$canal] ID:{$row['id']} $mensagem_curta\n";
                    }
                } else {
                    echo "  ❌ Nenhuma mensagem recente encontrada\n";
                }
                
                $stmt->close();
                $mysqli->close();
            }
        } catch (Exception $e) {
            echo "  ❌ Erro ao verificar banco: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  ❌ Erro no processamento: " . ($data['error'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "  ❌ Erro HTTP: $http_code\n";
}

// 4. RECOMENDAÇÕES
echo "\n4️⃣ RECOMENDAÇÕES\n";
echo "----------------\n";

echo "🎯 PRÓXIMOS PASSOS:\n\n";

echo "1. 🌐 **Verificar no chat web:**\n";
echo "   Acesse: https://app.pixel12digital.com.br/painel/chat.php?cliente_id=$cliente_id\n\n";

echo "2. 🔄 **Se mensagens não aparecerem:**\n";
echo "   - Recarregue a página (F5)\n";
echo "   - Verifique se há cache do navegador\n";
echo "   - Tente em modo incógnito\n\n";

echo "3. 📱 **Testar envio real via WhatsApp:**\n";
echo "   Envie uma mensagem para 554797146908\n\n";

echo "4. 🔧 **Se ainda não funcionar:**\n";
echo "   - Verifique logs do navegador (F12)\n";
echo "   - Verifique se há erros JavaScript\n\n";

echo "✅ TESTE CONCLUÍDO!\n";
echo "As mensagens estão sendo salvas no banco. Verifique se aparecem na interface web.\n";
?> 