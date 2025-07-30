<?php
/**
 * 🧪 TESTE DO WEBHOOK WHATSAPP
 * Testa se o webhook está funcionando corretamente
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste do Webhook WhatsApp</h2>";
echo "<p><strong>Testando:</strong> Funcionamento do webhook e processamento de mensagens</p>";
echo "<hr>";

// Simular dados que viriam do WhatsApp
$payload_teste = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699', // Seu número
        'text' => 'boa tarde',
        'type' => 'text'
    ]
];

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📤 Enviando Teste para Webhook:</h3>";
echo "<p><strong>Payload:</strong></p>";
echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
echo htmlspecialchars(json_encode($payload_teste, JSON_PRETTY_PRINT));
echo "</pre>";
echo "</div>";

// Chamar o webhook
$ch = curl_init('http://localhost/loja-virtual-revenda/api/webhook_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📥 Resposta do Webhook:</h3>";
echo "<p><strong>HTTP Code:</strong> $http_code</p>";

if ($error) {
    echo "<p style='color: red;'><strong>Erro de conexão:</strong> $error</p>";
} else {
    echo "<p><strong>Resposta:</strong></p>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    $response_data = json_decode($response, true);
    
    if ($response_data && isset($response_data['success']) && $response_data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Webhook funcionando corretamente!</p>";
        
        echo "<h4>📊 Detalhes da Resposta:</h4>";
        echo "<ul>";
        echo "<li><strong>Cliente ID:</strong> " . ($response_data['cliente_id'] ?? 'Não encontrado') . "</li>";
        echo "<li><strong>Cliente Nome:</strong> " . ($response_data['cliente_nome'] ?? 'Não encontrado') . "</li>";
        echo "<li><strong>Formato Encontrado:</strong> " . ($response_data['formato_encontrado'] ?? 'Não encontrado') . "</li>";
        echo "<li><strong>Canal ID:</strong> " . ($response_data['canal_id'] ?? 'N/A') . "</li>";
        echo "<li><strong>Mensagem ID:</strong> " . ($response_data['mensagem_id'] ?? 'N/A') . "</li>";
        echo "<li><strong>Resposta Enviada:</strong> " . ($response_data['resposta_enviada'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>Tem Conversa Recente:</strong> " . ($response_data['tem_conversa_recente'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>Total Mensagens 24h:</strong> " . ($response_data['total_mensagens_24h'] ?? 0) . "</li>";
        echo "<li><strong>Respostas Automáticas 24h:</strong> " . ($response_data['respostas_automaticas_24h'] ?? 0) . "</li>";
        echo "<li><strong>Mensagens Automáticas 24h:</strong> " . ($response_data['mensagens_automaticas_24h'] ?? 0) . "</li>";
        echo "<li><strong>É Saudação:</strong> " . ($response_data['eh_saudacao'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>É Fatura:</strong> " . ($response_data['eh_fatura'] ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>É CPF:</strong> " . ($response_data['eh_cpf'] ? 'Sim' : 'Não') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro no webhook</p>";
        if (isset($response_data['message'])) {
            echo "<p><strong>Erro:</strong> " . $response_data['message'] . "</p>";
        }
    }
}
echo "</div>";

// Verificar logs
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📋 Verificando Logs:</h3>";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = explode("\n", $log_content);
    $recent_logs = array_slice($log_lines, -10); // Últimas 10 linhas
    
    echo "<p><strong>Log file:</strong> $log_file</p>";
    echo "<p><strong>Últimas 10 linhas:</strong></p>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    foreach ($recent_logs as $line) {
        if (trim($line)) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Arquivo de log não encontrado: $log_file</p>";
}
echo "</div>";

// Verificar mensagens no banco
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🗄️ Verificando Mensagens no Banco:</h3>";

$numero_teste = '554796164699';
$sql = "SELECT 
            mc.id,
            mc.mensagem,
            mc.tipo,
            mc.data_hora,
            mc.direcao,
            mc.status,
            mc.numero_whatsapp,
            c.nome as cliente_nome
        FROM mensagens_comunicacao mc
        LEFT JOIN clientes c ON mc.cliente_id = c.id
        WHERE mc.numero_whatsapp = '$numero_teste'
        ORDER BY mc.data_hora DESC
        LIMIT 5";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<p><strong>Últimas 5 mensagens para $numero_teste:</strong></p>";
    echo "<table style='width: 100%; border-collapse: collapse; background: white; border: 1px solid #ccc;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>ID</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Data/Hora</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Direção</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Cliente</th>";
    echo "<th style='border: 1px solid #ccc; padding: 8px;'>Mensagem</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>" . $row['id'] . "</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>" . $row['data_hora'] . "</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>" . $row['direcao'] . "</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px;'>" . ($row['cliente_nome'] ?: 'Não encontrado') . "</td>";
        echo "<td style='border: 1px solid #ccc; padding: 8px; max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($row['mensagem'], 0, 100)) . (strlen($row['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma mensagem encontrada para $numero_teste</p>";
}
echo "</div>";

// Teste de envio direto para WhatsApp
echo "<div style='background: #f8d7da; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📱 Teste de Envio Direto para WhatsApp:</h3>";

$numero_destino = '554796164699@c.us';
$mensagem_teste = "🧪 *TESTE WEBHOOK - FUNCIONAMENTO*\n\n";
$mensagem_teste .= "✅ Webhook testado com sucesso!\n";
$mensagem_teste .= "📊 Sistema funcionando corretamente\n";
$mensagem_teste .= "🕐 " . date('d/m/Y H:i:s') . "\n\n";
$mensagem_teste .= "🤖 Esta é uma mensagem de teste automática";

$payload_whatsapp = [
    'sessionName' => 'default',
    'number' => $numero_destino,
    'message' => $mensagem_teste
];

$ch_whatsapp = curl_init("http://212.85.11.238:3000/send/text");
curl_setopt($ch_whatsapp, CURLOPT_POST, 1);
curl_setopt($ch_whatsapp, CURLOPT_POSTFIELDS, json_encode($payload_whatsapp));
curl_setopt($ch_whatsapp, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch_whatsapp, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_whatsapp, CURLOPT_TIMEOUT, 10);

$response_whatsapp = curl_exec($ch_whatsapp);
$http_code_whatsapp = curl_getinfo($ch_whatsapp, CURLINFO_HTTP_CODE);
$error_whatsapp = curl_error($ch_whatsapp);
curl_close($ch_whatsapp);

echo "<p><strong>Enviando para:</strong> $numero_destino</p>";
echo "<p><strong>HTTP Code:</strong> $http_code_whatsapp</p>";

if ($error_whatsapp) {
    echo "<p style='color: red;'><strong>Erro de conexão:</strong> $error_whatsapp</p>";
} else {
    echo "<p><strong>Resposta:</strong></p>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo htmlspecialchars($response_whatsapp);
    echo "</pre>";
    
    $response_whatsapp_data = json_decode($response_whatsapp, true);
    
    if ($response_whatsapp_data && isset($response_whatsapp_data['success']) && $response_whatsapp_data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Mensagem de teste enviada com sucesso!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro ao enviar mensagem de teste</p>";
    }
}
echo "</div>";

echo "<hr>";
echo "<h3>📊 Resumo do Teste</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px;'>";
echo "<p><strong>Teste concluído!</strong></p>";
echo "<ul>";
echo "<li>✅ Webhook testado com payload simulado</li>";
echo "<li>✅ Logs verificados</li>";
echo "<li>✅ Banco de dados consultado</li>";
echo "<li>✅ Envio direto para WhatsApp testado</li>";
echo "</ul>";
echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para confirmar o teste!</strong></p>";
echo "</div>";
?> 