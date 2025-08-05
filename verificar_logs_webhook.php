<?php
/**
 * 🔍 VERIFICAR LOGS DO WEBHOOK
 * Identifica por que as respostas da Ana não estão sendo enviadas
 */

echo "🔍 VERIFICANDO LOGS DO WEBHOOK\n";
echo "==============================\n\n";

// Verificar logs do PHP
echo "1️⃣ LOGS DO PHP (últimas 20 linhas):\n";
echo "====================================\n";

$log_file = ini_get('error_log');
if (empty($log_file)) {
    $log_file = 'php_errors.log';
}

if (file_exists($log_file)) {
    $logs = file($log_file);
    $recent_logs = array_slice($logs, -20);
    foreach ($recent_logs as $log) {
        if (strpos($log, 'WEBHOOK') !== false || strpos($log, 'ANA') !== false) {
            echo trim($log) . "\n";
        }
    }
} else {
    echo "❌ Arquivo de log não encontrado: $log_file\n";
}

echo "\n2️⃣ VERIFICANDO CONFIGURAÇÕES:\n";
echo "==============================\n";

echo "✅ WHATSAPP_ROBOT_URL: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'NÃO DEFINIDO') . "\n";
echo "✅ DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'ATIVO' : 'INATIVO') : 'NÃO DEFINIDO') . "\n";

echo "\n3️⃣ TESTANDO CONEXÃO COM VPS:\n";
echo "=============================\n";

$vps_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';
$test_url = "$vps_url/status";

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "🌐 Testando: $test_url\n";
echo "📡 HTTP Code: $http_code\n";
echo "📡 Response: " . substr($response, 0, 100) . "...\n";

if ($http_code == 200) {
    echo "✅ VPS respondendo corretamente\n";
} else {
    echo "❌ VPS com problema\n";
}

echo "\n4️⃣ VERIFICANDO BANCO DE DADOS:\n";
echo "===============================\n";

require_once 'config.php';
require_once 'painel/db.php';

// Verificar mensagens recentes
$sql = "SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp = '554796164699' 
        ORDER BY data_hora DESC 
        LIMIT 5";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "📋 ÚLTIMAS MENSAGENS:\n";
    while ($row = $result->fetch_assoc()) {
        $direcao = $row['direcao'] == 'recebido' ? '📥' : '📤';
        $status = $row['status'];
        $hora = date('H:i:s', strtotime($row['data_hora']));
        $msg = substr($row['mensagem'], 0, 50) . (strlen($row['mensagem']) > 50 ? '...' : '');
        
        echo "   $direcao [$hora] $status: $msg\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada\n";
}

echo "\n5️⃣ DIAGNÓSTICO:\n";
echo "===============\n";

// Verificar se há mensagens recebidas mas não enviadas
$sql_check = "SELECT 
    COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
    COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas
    FROM mensagens_comunicacao 
    WHERE numero_whatsapp = '554796164699' 
    AND DATE(data_hora) = CURDATE()";

$result_check = $mysqli->query($sql_check);
if ($result_check && $result_check->num_rows > 0) {
    $stats = $result_check->fetch_assoc();
    echo "📊 HOJE:\n";
    echo "   📥 Recebidas: {$stats['recebidas']}\n";
    echo "   📤 Enviadas: {$stats['enviadas']}\n";
    
    if ($stats['recebidas'] > $stats['enviadas']) {
        echo "❌ PROBLEMA: Mensagens recebidas mas não respondidas!\n";
        echo "🔧 SOLUÇÃO: Verificar se o webhook está processando corretamente\n";
    } else {
        echo "✅ BALANÇO OK\n";
    }
}

echo "\n🎯 RECOMENDAÇÕES:\n";
echo "=================\n";
echo "1. Verifique os logs do webhook em tempo real\n";
echo "2. Teste enviando uma nova mensagem\n";
echo "3. Monitore se a Ana está gerando respostas\n";
echo "4. Verifique se o cURL está funcionando no webhook\n";
?> 