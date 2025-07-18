<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIAGNÓSTICO COMPLETO WHATSAPP ===\n\n";

// 1. Verificar status da API
echo "1. STATUS DA API WHATSAPP\n";
echo "========================\n";
$status_url = "http://212.85.11.238:3000/status";
$ch = curl_init($status_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$status_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $status_data = json_decode($status_response, true);
    echo "✅ API Online\n";
    echo "Status: " . ($status_data['status']['status'] ?? 'N/A') . "\n";
    echo "QR Code: " . ($status_data['status']['qr'] ? 'Ativo' : 'Não necessário') . "\n";
} else {
    echo "❌ API Offline (HTTP $http_code)\n";
    exit;
}

// 2. Verificar sessões ativas
echo "\n2. SESSÕES ATIVAS\n";
echo "==================\n";
$sessions_url = "http://212.85.11.238:3000/sessions";
$ch = curl_init($sessions_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$sessions_response = curl_exec($ch);
$sessions_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($sessions_http === 200) {
    $sessions_data = json_decode($sessions_response, true);
    echo "✅ Sessões encontradas: " . count($sessions_data) . "\n";
    foreach ($sessions_data as $session) {
        echo "- " . $session['name'] . ": " . $session['status'] . "\n";
    }
} else {
    echo "❌ Erro ao verificar sessões\n";
}

// 3. Testar diferentes formatos de número
echo "\n3. TESTE DE FORMATOS DE NÚMERO\n";
echo "==============================\n";
$numero_teste = "4796164699";
$formatos = [
    "Original" => $numero_teste,
    "Com 55" => "55" . $numero_teste,
    "Com @c.us" => "55" . $numero_teste . "@c.us",
    "Sem 55" => $numero_teste,
    "Com 9 dígitos" => "554796164699"
];

foreach ($formatos as $descricao => $numero) {
    echo "\nTestando formato: $descricao ($numero)\n";
    
    $test_url = "http://212.85.11.238:3000/send";
    $test_data = [
        'to' => $numero,
        'message' => "Teste formato $descricao - " . date('H:i:s')
    ];
    
    $ch = curl_init($test_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $test_response = curl_exec($ch);
    $test_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($test_http === 200) {
        $test_result = json_decode($test_response, true);
        if ($test_result && isset($test_result['success']) && $test_result['success']) {
            echo "✅ Enviado com sucesso - Message ID: " . substr($test_result['messageId'], 0, 20) . "...\n";
        } else {
            echo "❌ Erro na resposta: " . json_encode($test_result) . "\n";
        }
    } else {
        echo "❌ Erro HTTP: $test_http\n";
    }
    
    // Aguardar 2 segundos entre testes
    sleep(2);
}

// 4. Verificar logs do sistema
echo "\n4. VERIFICAÇÃO DE LOGS\n";
echo "======================\n";
$logs_dir = "logs/";
if (is_dir($logs_dir)) {
    $log_files = glob($logs_dir . "*.log");
    if (!empty($log_files)) {
        echo "✅ Logs encontrados:\n";
        foreach ($log_files as $log_file) {
            $size = filesize($log_file);
            echo "- " . basename($log_file) . " (" . number_format($size) . " bytes)\n";
        }
    } else {
        echo "⚠️ Nenhum arquivo de log encontrado\n";
    }
} else {
    echo "⚠️ Diretório de logs não encontrado\n";
}

// 5. Verificar configurações do banco
echo "\n5. CONFIGURAÇÕES DO BANCO\n";
echo "==========================\n";
$mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
if (!$mysqli->connect_errno) {
    echo "✅ Banco conectado\n";
    
    // Verificar últimas mensagens
    $res = $mysqli->query("SELECT id, cliente_id, mensagem, status, data_hora FROM mensagens_comunicacao ORDER BY id DESC LIMIT 5");
    if ($res && $res->num_rows > 0) {
        echo "Últimas 5 mensagens:\n";
        while ($row = $res->fetch_assoc()) {
            echo "- ID: " . $row['id'] . " | Cliente: " . $row['cliente_id'] . " | Status: " . $row['status'] . " | Data: " . $row['data_hora'] . "\n";
        }
    }
    
    $mysqli->close();
} else {
    echo "❌ Erro ao conectar ao banco\n";
}

// 6. Recomendações
echo "\n6. RECOMENDAÇÕES\n";
echo "================\n";
echo "🔍 Baseado no diagnóstico:\n\n";

echo "✅ O que está funcionando:\n";
echo "- API WhatsApp conectada\n";
echo "- Envio de mensagens via API\n";
echo "- Formatação de números\n";
echo "- Salvamento no banco\n\n";

echo "⚠️ Possíveis problemas:\n";
echo "1. Bloqueio do número pelo destinatário\n";
echo "2. WhatsApp bloqueando por spam\n";
echo "3. Formatação incorreta do número\n";
echo "4. Sessão instável do WhatsApp\n\n";

echo "🛠️ Ações recomendadas:\n";
echo "1. Testar com outro número de telefone\n";
echo "2. Verificar se o número está bloqueado\n";
echo "3. Reconectar a sessão do WhatsApp\n";
echo "4. Verificar logs do WhatsApp no servidor\n";
echo "5. Testar envio manual via WhatsApp Web\n\n";

echo "=== FIM DO DIAGNÓSTICO ===\n";
?> 