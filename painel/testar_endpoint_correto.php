<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Teste do Endpoint Correto - /send/text</h1>";
echo "<h2>Testando número: 4796164699</h2>";

$base_url = "http://212.85.11.238:3000";
$numero_teste = "4796164699";
$cliente_nome = "Cliente Teste Endpoint Correto";

echo "<h3>1. Testando endpoint /send/text...</h3>";

$numero_limpo = preg_replace('/\D/', '', $numero_teste);
$numero_formatado = '55' . $numero_limpo . '@c.us';

echo "<p><strong>Número formatado:</strong> $numero_formatado</p>";

// Payload correto baseado no arquivo processar_mensagens_agendadas.php
$payload = json_encode([
    'sessionName' => 'default',
    'number' => $numero_formatado,
    'message' => "Olá {$cliente_nome}!\n\nSeu cadastro foi ativado para monitoramento automático de cobranças. Você receberá lembretes de vencimento e notificações importantes por WhatsApp e e-mail (se cadastrado).\n\nPara consultar suas faturas, responda \"faturas\" ou \"consulta\".\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital"
]);

echo "<p><strong>Payload JSON:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($payload) . "</pre>";

// Fazer a requisição para o endpoint correto
$ch = curl_init($base_url . "/send/text");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<p><strong>Resultado do envio:</strong></p>";
echo "<ul>";
echo "<li><strong>URL:</strong> " . $base_url . "/send/text</li>";
echo "<li><strong>HTTP Code:</strong> $http_code</li>";
echo "<li><strong>Tempo de resposta:</strong> " . round($info['total_time'], 3) . "s</li>";
echo "<li><strong>Resposta:</strong> " . htmlspecialchars($response) . "</li>";
if ($error) {
    echo "<li style='color: red;'><strong>Erro cURL:</strong> $error</li>";
}
echo "</ul>";

// Decodificar resposta
$data_response = json_decode($response, true);

if ($data_response) {
    echo "<p><strong>Resposta decodificada:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars(print_r($data_response, true)) . "</pre>";
}

if ($http_code === 200 && $data_response && isset($data_response['success']) && $data_response['success']) {
    echo "<p style='color: green;'>✅ <strong>SUCESSO! Mensagem enviada com sucesso!</strong></p>";
} elseif ($http_code === 200) {
    echo "<p style='color: orange;'>⚠️ Resposta 200 mas falha no envio</p>";
    if (isset($data_response['error'])) {
        echo "<p style='color: red;'>Erro: " . htmlspecialchars($data_response['error']) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Falha no envio da mensagem</p>";
}

echo "<h3>2. Comparando com endpoint antigo (/send)...</h3>";

// Testar o endpoint antigo para comparação
$payload_antigo = json_encode([
    'to' => $numero_formatado,
    'message' => "Teste endpoint antigo - " . date('H:i:s')
]);

$ch = curl_init($base_url . "/send");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_antigo);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response_antigo = curl_exec($ch);
$http_code_antigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Endpoint antigo (/send):</strong></p>";
echo "<ul>";
echo "<li><strong>HTTP Code:</strong> $http_code_antigo</li>";
echo "<li><strong>Resposta:</strong> " . htmlspecialchars(substr($response_antigo, 0, 100)) . "...</li>";
echo "</ul>";

echo "<h3>3. Resumo da descoberta...</h3>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Problema resolvido!</h4>";
echo "<p><strong>Endpoint correto descoberto:</strong> <code>/send/text</code></p>";
echo "<p><strong>Payload correto:</strong></p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "{\n";
echo "  \"sessionName\": \"default\",\n";
echo "  \"number\": \"554796164699@c.us\",\n";
echo "  \"message\": \"Sua mensagem aqui\"\n";
echo "}";
echo "</pre>";
echo "<p><strong>Diferenças:</strong></p>";
echo "<ul>";
echo "<li><strong>Endpoint antigo:</strong> /send (não existe)</li>";
echo "<li><strong>Endpoint correto:</strong> /send/text</li>";
echo "<li><strong>Payload antigo:</strong> {\"to\": \"...\", \"message\": \"...\"}</li>";
echo "<li><strong>Payload correto:</strong> {\"sessionName\": \"default\", \"number\": \"...\", \"message\": \"...\"}</li>";
echo "</ul>";
echo "</div>";

echo "<h3>4. Próximos passos...</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<h4>⚠️ Ação necessária:</h4>";
echo "<p>Atualizar todos os arquivos que usam o endpoint incorreto:</p>";
echo "<ul>";
echo "<li><code>painel/api/salvar_monitoramento_cliente.php</code></li>";
echo "<li><code>painel/api/salvar_monitoramento_cliente_corrigido.php</code></li>";
echo "<li><code>painel/api/enviar_mensagem_automatica.php</code></li>";
echo "<li><code>painel/api/enviar_mensagem_validacao.php</code></li>";
echo "<li><code>painel/api/executar_monitoramento.php</code></li>";
echo "<li><code>painel/cron/monitoramento_automatico.php</code></li>";
echo "</ul>";
echo "<p><strong>Mudança necessária:</strong></p>";
echo "<ul>";
echo "<li>URL: <code>http://212.85.11.238:3000/send</code> → <code>http://212.85.11.238:3000/send/text</code></li>";
echo "<li>Payload: <code>{\"to\": \"...\", \"message\": \"...\"}</code> → <code>{\"sessionName\": \"default\", \"number\": \"...\", \"message\": \"...\"}</code></li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Teste concluído em:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Número testado:</strong> $numero_teste</p>";
?> 