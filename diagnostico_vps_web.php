<?php
/**
 * Diagnóstico Completo VPS WhatsApp via Web
 * Executa todas as verificações possíveis
 */

echo "<h1>🔍 DIAGNÓSTICO COMPLETO VPS WHATSAPP</h1>";
echo "<p>Data/Hora: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. VERIFICAR ENDPOINTS HTTP
echo "<h2>1. 🌐 TESTANDO ENDPOINTS HTTP</h2>";

echo "<h3>Status 3000:</h3>";
$status3000 = file_get_contents('http://212.85.11.238:3000/status');
echo "<pre>" . htmlspecialchars($status3000) . "</pre>";

echo "<h3>Status 3001:</h3>";
$status3001 = file_get_contents('http://212.85.11.238:3001/status');
echo "<pre>" . htmlspecialchars($status3001) . "</pre>";

// 2. TESTAR INICIALIZAÇÃO DE SESSÕES
echo "<h2>2. 🚀 TESTANDO INICIALIZAÇÃO DE SESSÕES</h2>";

echo "<h3>Iniciando sessão default:</h3>";
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'timeout' => 30
    ]
]);
$result_default = file_get_contents('http://212.85.11.238:3000/session/start/default', false, $context);
echo "<pre>" . htmlspecialchars($result_default) . "</pre>";

echo "<h3>Iniciando sessão comercial:</h3>";
$result_comercial = file_get_contents('http://212.85.11.238:3001/session/start/comercial', false, $context);
echo "<pre>" . htmlspecialchars($result_comercial) . "</pre>";

// 3. VERIFICAR QR CODE
echo "<h2>3. 📱 VERIFICANDO QR CODE</h2>";

echo "<h3>QR Default:</h3>";
$qr_default = file_get_contents('http://212.85.11.238:3000/qr?session=default');
echo "<pre>" . htmlspecialchars($qr_default) . "</pre>";

echo "<h3>QR Comercial:</h3>";
$qr_comercial = file_get_contents('http://212.85.11.238:3001/qr?session=comercial');
echo "<pre>" . htmlspecialchars($qr_comercial) . "</pre>";

// 4. VERIFICAR WEBHOOK CONFIG
echo "<h2>4. 🔗 VERIFICANDO CONFIGURAÇÃO WEBHOOK</h2>";

echo "<h3>Webhook Config 3000:</h3>";
$webhook3000 = file_get_contents('http://212.85.11.238:3000/webhook/config');
echo "<pre>" . htmlspecialchars($webhook3000) . "</pre>";

echo "<h3>Webhook Config 3001:</h3>";
$webhook3001 = file_get_contents('http://212.85.11.238:3001/webhook/config');
echo "<pre>" . htmlspecialchars($webhook3001) . "</pre>";

// 5. TESTAR CONECTIVIDADE
echo "<h2>5. 🔌 TESTANDO CONECTIVIDADE</h2>";

$ports = [3000, 3001];
foreach ($ports as $port) {
    $connection = @fsockopen('212.85.11.238', $port, $errno, $errstr, 5);
    if ($connection) {
        echo "<p>✅ Porta $port: CONECTADA</p>";
        fclose($connection);
    } else {
        echo "<p>❌ Porta $port: FALHOU ($errstr)</p>";
    }
}

// 6. VERIFICAR RESPONSE TIMES
echo "<h2>6. ⏱️ VERIFICANDO TEMPOS DE RESPOSTA</h2>";

$start = microtime(true);
$response = file_get_contents('http://212.85.11.238:3000/status');
$time3000 = (microtime(true) - $start) * 1000;
echo "<p>Porta 3000: " . round($time3000, 2) . "ms</p>";

$start = microtime(true);
$response = file_get_contents('http://212.85.11.238:3001/status');
$time3001 = (microtime(true) - $start) * 1000;
echo "<p>Porta 3001: " . round($time3001, 2) . "ms</p>";

echo "<hr>";
echo "<h2>🎯 RESUMO DO DIAGNÓSTICO</h2>";

// Analisar resultados
$status3000_data = json_decode($status3000, true);
$status3001_data = json_decode($status3001, true);

echo "<h3>Status das Sessões:</h3>";
if ($status3000_data && isset($status3000_data['clients_status'])) {
    echo "<p>Porta 3000 (Default): " . json_encode($status3000_data['clients_status']) . "</p>";
}
if ($status3001_data && isset($status3001_data['clients_status'])) {
    echo "<p>Porta 3001 (Comercial): " . json_encode($status3001_data['clients_status']) . "</p>";
}

echo "<h3>Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Se as sessões não aparecerem, verifique os logs do PM2 no VPS</li>";
echo "<li>Se o QR não aparecer, aguarde alguns minutos e tente novamente</li>";
echo "<li>Se houver erros de conexão, verifique se o firewall está bloqueando</li>";
echo "<li>Se os tempos de resposta forem altos, pode haver problema de rede</li>";
echo "</ol>";

echo "<p><strong>Status Final:</strong> ";
if ($time3000 < 1000 && $time3001 < 1000) {
    echo "✅ CONECTIVIDADE OK";
} else {
    echo "⚠️ CONECTIVIDADE LENTA";
}
echo "</p>";
?> 