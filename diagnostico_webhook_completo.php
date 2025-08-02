<?php
/**
 * Diagnóstico Completo do Webhook
 * Identifica e resolve problemas de conectividade
 */

echo "🔍 DIAGNÓSTICO COMPLETO DO WEBHOOK\n";
echo "=====================================\n\n";

// 1. Verificar conectividade básica
echo "1️⃣ VERIFICANDO CONECTIVIDADE BÁSICA\n";
echo "------------------------------------\n";

$webhook_url = 'http://212.85.11.238:8080/api/webhook.php';

// Teste de conectividade
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Webhook URL: $webhook_url\n";
echo "HTTP Code: $http_code\n";
echo "Error: " . ($error ?: 'Nenhum') . "\n";
echo "Acessível: " . ($http_code > 0 ? '✅ SIM' : '❌ NÃO') . "\n\n";

// 2. Testar webhook com dados simples
echo "2️⃣ TESTANDO WEBHOOK COM DADOS SIMPLES\n";
echo "---------------------------------------\n";

$test_data = [
    'event' => 'test',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste diagnóstico - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "POST Request:\n";
echo "  Dados: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";
echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n";
echo "  Error: " . ($error ?: 'Nenhum') . "\n\n";

// 3. Verificar se a mensagem foi salva
echo "3️⃣ VERIFICANDO SE MENSAGEM FOI SALVA\n";
echo "-------------------------------------\n";

// Conectar ao banco
$mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');

if ($mysqli->connect_error) {
    echo "❌ Erro ao conectar ao banco: " . $mysqli->connect_error . "\n\n";
} else {
    echo "✅ Conectado ao banco de dados\n";
    
    // Buscar mensagens recentes
    $sql = "SELECT m.*, c.nome as cliente_nome 
            FROM mensagens_comunicacao m 
            LEFT JOIN clientes c ON m.cliente_id = c.id 
            WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY m.data_hora DESC 
            LIMIT 10";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "📨 Mensagens recentes (últimos 5 minutos):\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - ID: {$row['id']} | Cliente: {$row['cliente_nome']} | Mensagem: {$row['mensagem']} | Data: {$row['data_hora']}\n";
        }
    } else {
        echo "❌ Nenhuma mensagem encontrada nos últimos 5 minutos\n";
    }
    
    $mysqli->close();
}

// 4. Verificar configuração do servidor WhatsApp
echo "\n4️⃣ VERIFICANDO CONFIGURAÇÃO DO SERVIDOR WHATSAPP\n";
echo "------------------------------------------------\n";

$servers = [
    'default' => 'http://212.85.11.238:3000',
    'comercial' => 'http://212.85.11.238:3001'
];

foreach ($servers as $name => $server_url) {
    echo "📱 Servidor $name:\n";
    
    // Verificar status
    $ch = curl_init($server_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $status_response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Status: HTTP $status_code\n";
    
    // Verificar webhook config
    $ch = curl_init($server_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $webhook_config = curl_exec($ch);
    $webhook_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($webhook_config) {
        $config_data = json_decode($webhook_config, true);
        if ($config_data) {
            echo "  Webhook URL: " . ($config_data['webhook_url'] ?? 'N/A') . "\n";
            echo "  Configurado: " . (($config_data['webhook_url'] === $webhook_url) ? '✅ SIM' : '❌ NÃO') . "\n";
        }
    }
    
    echo "\n";
}

// 5. Análise do problema
echo "5️⃣ ANÁLISE DO PROBLEMA\n";
echo "----------------------\n";

echo "🔍 POSSÍVEIS CAUSAS:\n";
echo "1. Webhook retornando HTTP 301 (redirecionamento)\n";
echo "2. Servidor WhatsApp não consegue acessar o webhook\n";
echo "3. Problema de DNS ou conectividade interna\n";
echo "4. Configuração incorreta do webhook\n\n";

echo "💡 SOLUÇÕES SUGERIDAS:\n";
echo "1. Verificar se o webhook está acessível internamente\n";
echo "2. Configurar webhook com URL interna se necessário\n";
echo "3. Verificar logs do servidor WhatsApp\n";
echo "4. Testar conectividade entre servidores\n\n";

echo "✅ DIAGNÓSTICO CONCLUÍDO!\n";
?> 