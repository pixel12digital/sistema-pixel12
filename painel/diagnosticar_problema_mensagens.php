<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once 'db.php';

echo "🔍 DIAGNÓSTICO: Por que as mensagens não estão sendo recebidas?\n\n";

// 1. Verificar se o WhatsApp está conectado
echo "1. 📱 Status do WhatsApp na VPS:\n";
$ch = curl_init('http://212.85.11.238:3000/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $status = json_decode($response, true);
    $connected = $status['clients_status']['default']['status'] ?? 'unknown';
    echo "   Status: $connected\n";
    if ($connected !== 'connected') {
        echo "   ❌ PROBLEMA: WhatsApp não está conectado!\n";
        echo "   🔧 SOLUÇÃO: Execute iniciarSessaoWhatsApp() no console\n\n";
    } else {
        echo "   ✅ WhatsApp conectado\n\n";
    }
} else {
    echo "   ❌ PROBLEMA: Não conseguiu conectar com VPS\n\n";
}

// 2. Verificar configuração do webhook
echo "2. 🔗 Configuração do webhook:\n";
$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $config = json_decode($response, true);
    $webhook_url = $config['webhook_url'] ?? 'não configurado';
    echo "   URL atual: $webhook_url\n";
    
    $url_esperada = 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php';
    if ($webhook_url !== $url_esperada) {
        echo "   ❌ PROBLEMA: Webhook aponta para URL errada!\n";
        echo "   💡 Esperado: $url_esperada\n";
        echo "   🔧 SOLUÇÃO: Execute configurar_webhook_ambiente.php\n\n";
    } else {
        echo "   ✅ Webhook configurado corretamente\n\n";
    }
} else {
    echo "   ❌ PROBLEMA: Não conseguiu obter configuração do webhook\n\n";
}

// 3. Testar se o webhook local está funcionando
echo "3. 🧪 Teste do webhook local:\n";
$webhook_url = 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php';
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook local respondendo\n\n";
} else {
    echo "   ❌ PROBLEMA: Webhook local não está respondendo (HTTP $http_code)\n";
    echo "   🔧 SOLUÇÃO: Verifique se XAMPP está rodando\n\n";
}

// 4. Verificar logs recentes
echo "4. 📋 Verificar logs recentes do webhook:\n";
$log_files = [
    '../api/debug_webhook.log',
    'logs/webhook_whatsapp_' . date('Y-m-d') . '.log'
];

$logs_encontrados = false;
foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "   📄 Log: $log_file\n";
        $content = file_get_contents($log_file);
        $lines = explode("\n", $content);
        $recent_lines = array_slice($lines, -5);
        
        foreach ($recent_lines as $line) {
            if (trim($line) && strpos($line, date('Y-m-d')) !== false) {
                echo "      " . trim($line) . "\n";
                $logs_encontrados = true;
            }
        }
    }
}

if (!$logs_encontrados) {
    echo "   ⚠️ Nenhum log recente encontrado\n";
    echo "   💡 Isso indica que mensagens não estão chegando ao webhook\n\n";
} else {
    echo "\n";
}

// 5. Verificar mensagens nas últimas 15 minutos
echo "5. ⏰ Mensagens recebidas nas últimas 15 minutos:\n";
$result = $mysqli->query("
    SELECT m.*, c.nome as cliente_nome 
    FROM mensagens_comunicacao m
    LEFT JOIN clientes c ON m.cliente_id = c.id
    WHERE m.direcao = 'recebido'
    AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ORDER BY m.data_hora DESC
");

if ($result && $result->num_rows > 0) {
    while ($msg = $result->fetch_assoc()) {
        $cliente = $msg['cliente_nome'] ?? 'Cliente não identificado';
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        echo "   📥 [$hora] $cliente: " . substr($msg['mensagem'], 0, 30) . "...\n";
    }
    echo "\n";
} else {
    echo "   ❌ PROBLEMA: Nenhuma mensagem recebida nas últimas 15 minutos\n\n";
}

// 6. Teste de conectividade com a VPS
echo "6. 🌐 Teste de conectividade VPS → Local:\n";
echo "   Enviando teste do VPS para o webhook local...\n";

$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '5547997146908@c.us',
        'text' => 'TESTE DE CONECTIVIDADE ' . date('H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init('http://212.85.11.238:3000/webhook/test');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Teste de webhook executado\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "   📝 Resposta: " . json_encode($result) . "\n\n";
    }
} else {
    echo "   ❌ PROBLEMA: Teste de webhook falhou (HTTP $http_code)\n\n";
}

// 7. Verificar número correto para teste
echo "7. 📞 Número para teste:\n";
$canal = $mysqli->query("SELECT identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status = 'conectado' LIMIT 1")->fetch_assoc();
if ($canal) {
    echo "   📱 Número do robô: {$canal['identificador']}\n";
    echo "   💡 Envie mensagens para este número para testar\n\n";
} else {
    echo "   ❌ PROBLEMA: Nenhum canal WhatsApp conectado encontrado\n\n";
}

// RESUMO E SOLUÇÕES
echo "=== 🎯 RESUMO DOS PROBLEMAS ENCONTRADOS ===\n\n";

$problemas = [];

// Verificar novamente status do WhatsApp
$ch = curl_init('http://212.85.11.238:3000/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $status = json_decode($response, true);
    $connected = $status['clients_status']['default']['status'] ?? 'unknown';
    if ($connected !== 'connected') {
        $problemas[] = "WhatsApp não está conectado na VPS";
    }
}

// Verificar webhook
$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $config = json_decode($response, true);
    $webhook_url = $config['webhook_url'] ?? '';
    if (!strpos($webhook_url, 'localhost:8080')) {
        $problemas[] = "Webhook não está apontando para localhost";
    }
}

// Verificar XAMPP
$ch = curl_init('http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    $problemas[] = "XAMPP não está rodando ou webhook não está acessível";
}

// Mostrar problemas e soluções
if (empty($problemas)) {
    echo "✅ NENHUM PROBLEMA TÉCNICO ENCONTRADO!\n\n";
    echo "🤔 Possíveis causas:\n";
    echo "   1. As mensagens estão sendo enviadas para o número errado\n";
    echo "   2. As mensagens estão sendo enviadas de um número não cadastrado\n";
    echo "   3. Há um delay no processamento\n\n";
    
    echo "🧪 TESTE IMEDIATO:\n";
    echo "   1. Envie uma mensagem agora para: {$canal['identificador']}\n";
    echo "   2. Execute: php monitorar_mensagens.php\n";
    echo "   3. Verifique se aparece nos logs\n\n";
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n";
    foreach ($problemas as $i => $problema) {
        echo "   " . ($i + 1) . ". $problema\n";
    }
    echo "\n";
    
    echo "🔧 SOLUÇÕES:\n";
    if (in_array("WhatsApp não está conectado na VPS", $problemas)) {
        echo "   1. Execute no console: iniciarSessaoWhatsApp()\n";
        echo "   2. Escaneie o QR Code que aparecer\n";
    }
    if (in_array("Webhook não está apontando para localhost", $problemas)) {
        echo "   3. Execute: php configurar_webhook_ambiente.php\n";
    }
    if (in_array("XAMPP não está rodando ou webhook não está acessível", $problemas)) {
        echo "   4. Verifique se o XAMPP está rodando\n";
        echo "   5. Acesse: http://localhost:8080/ para confirmar\n";
    }
}

echo "\n🎯 Diagnóstico concluído!\n";
?> 