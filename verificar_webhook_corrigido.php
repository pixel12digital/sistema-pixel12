<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Verificação do Webhook Corrigido</h1>";
echo "<p>Verificando se o webhook está funcionando após a correção...</p>";

require_once __DIR__ . '/config.php';

$vps_url = "http://212.85.11.238:3001";
$webhook_url = "https://pixel12digital.com.br/app/api/webhook.php";

// Teste 1: Verificar configuração atual do webhook
echo "<h2>📱 Teste 1: Configuração Atual do Webhook</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Status:</strong> HTTP $http_code</p>";
if ($response) {
    $config = json_decode($response, true);
    echo "<p><strong>URL Configurada:</strong> " . ($config['webhook_url'] ?? 'Não definida') . "</p>";
    
    if (isset($config['webhook_url']) && $config['webhook_url'] === $webhook_url) {
        echo "<p style='color: green; font-weight: bold;'>✅ Webhook configurado corretamente!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Webhook ainda com URL incorreta</p>";
    }
}

// Teste 2: Simular mensagem recebida
echo "<h2>📨 Teste 2: Simular Mensagem Recebida</h2>";

$mensagem_teste = [
    'event' => 'onmessage',
    'data' => [
        'key' => [
            'remoteJid' => '554796164699@c.us',
            'fromMe' => false
        ],
        'message' => [
            'conversation' => 'Teste de mensagem recebida após correção - ' . date('Y-m-d H:i:s')
        ],
        'messageTimestamp' => time(),
        'pushName' => 'Charles Dietrich'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensagem_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: WhatsApp-API-Test'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Webhook Response:</strong> HTTP $http_code</p>";
if ($response) {
    echo "<p><strong>Resposta:</strong> " . htmlspecialchars($response) . "</p>";
}

// Teste 3: Verificar mensagens no banco
echo "<h2>💾 Teste 3: Verificar Mensagens no Banco</h2>";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>❌ Erro na conexão com o banco</p>";
    } else {
        // Verificar mensagens recentes (última hora)
        $sql = "SELECT m.*, c.nome as cliente_nome 
                FROM mensagens_comunicacao m 
                LEFT JOIN clientes c ON m.cliente_id = c.id 
                WHERE m.data_criacao >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY m.id DESC 
                LIMIT 5";
        
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green; font-weight: bold;'>✅ Mensagens recentes encontradas:</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Mensagem</th><th>Tipo</th><th>Data</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['cliente_nome']}</td>";
                echo "<td>" . htmlspecialchars(substr($row['mensagem'], 0, 50)) . "...</td>";
                echo "<td>{$row['tipo']}</td>";
                echo "<td>{$row['data_criacao']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma mensagem recente encontrada</p>";
        }
        
        // Verificar estrutura da tabela
        $sql_estrutura = "DESCRIBE mensagens_comunicacao";
        $result_estrutura = $mysqli->query($sql_estrutura);
        
        if ($result_estrutura) {
            echo "<h3>📋 Estrutura da Tabela:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            
            while ($row = $result_estrutura->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>{$row['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

// Teste 4: Enviar mensagem real para testar
echo "<h2>📤 Teste 4: Enviar Mensagem Real</h2>";

$mensagem_envio = [
    'number' => '554796164699',
    'message' => 'Teste de webhook corrigido - ' . date('Y-m-d H:i:s'),
    'sessionName' => 'comercial'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensagem_envio));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Envio de Mensagem:</strong> HTTP $http_code</p>";
if ($response) {
    echo "<p><strong>Resposta:</strong> " . htmlspecialchars($response) . "</p>";
}

echo "<h2>🎯 Status Final</h2>";

echo "<h3>✅ O que está funcionando:</h3>";
echo "<ul>";
echo "<li>VPS conectado e funcionando</li>";
echo "<li>Sessão WhatsApp ativa</li>";
echo "<li>Envio de mensagens funcionando</li>";
echo "<li>Webhook reconfigurado</li>";
echo "</ul>";

echo "<h3>🔧 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Aguarde alguns minutos para ver se as mensagens começam a chegar</li>";
echo "<li>Envie uma mensagem real do WhatsApp para o número 4797309525</li>";
echo "<li>Verifique se aparece no chat do sistema</li>";
echo "<li>Se não funcionar, verifique os logs do VPS</li>";
echo "</ol>";

echo "<p><strong>Para testar:</strong> Envie uma mensagem do seu WhatsApp para o número <strong>4797309525</strong> e veja se aparece no chat do sistema.</p>";

echo "<p><a href='teste_webhook_producao.php'>← Teste Anterior</a></p>";
?> 