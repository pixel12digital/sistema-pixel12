<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>Teste Específico - WhatsApp Monitoramento</h1>";
echo "<h2>Testando número: 4796164699</h2>";

// Simular dados de cliente para teste
$cliente_id = 999; // ID fictício para teste
$cliente_nome = "Cliente Teste WhatsApp";
$numero_teste = "4796164699";

echo "<h3>1. Testando conexão com servidor WhatsApp...</h3>";

// Teste 1: Verificar se o servidor WhatsApp está acessível
$ch = curl_init("http://212.85.11.238:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response_status = curl_exec($ch);
$http_code_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_status = curl_error($ch);
curl_close($ch);

echo "<p><strong>Status do servidor WhatsApp:</strong></p>";
echo "<ul>";
echo "<li><strong>HTTP Code:</strong> $http_code_status</li>";
echo "<li><strong>Resposta:</strong> " . htmlspecialchars($response_status) . "</li>";
if ($error_status) {
    echo "<li style='color: red;'><strong>Erro cURL:</strong> $error_status</li>";
}
echo "</ul>";

if ($http_code_status === 200) {
    echo "<p style='color: green;'>✅ Servidor WhatsApp está acessível</p>";
} else {
    echo "<p style='color: red;'>❌ Servidor WhatsApp não está respondendo corretamente</p>";
}

echo "<h3>2. Testando envio de mensagem de monitoramento...</h3>";

// Teste 2: Enviar mensagem de monitoramento
$mensagem = "Olá {$cliente_nome}!\n\nSeu cadastro foi ativado para monitoramento automático de cobranças. Você receberá lembretes de vencimento e notificações importantes por WhatsApp e e-mail (se cadastrado).\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital";

$numero_limpo = preg_replace('/\D/', '', $numero_teste);
$numero_formatado = '55' . $numero_limpo . '@c.us';

echo "<p><strong>Número formatado:</strong> $numero_formatado</p>";
echo "<p><strong>Mensagem:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($mensagem) . "</pre>";

$payload = json_encode([
    'to' => $numero_formatado,
    'message' => $mensagem
]);

echo "<p><strong>Payload JSON:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($payload) . "</pre>";

// Fazer a requisição para o WhatsApp
$ch = curl_init("http://212.85.11.238:3000/send");
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

if ($http_code === 200) {
    echo "<p style='color: green;'>✅ Mensagem enviada com sucesso!</p>";
} else {
    echo "<p style='color: red;'>❌ Falha no envio da mensagem</p>";
}

echo "<h3>3. Testando endpoint alternativo...</h3>";

// Teste 3: Tentar endpoint alternativo se o primeiro falhar
$ch = curl_init("http://212.85.11.238:3000/send-message");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response_alt = curl_exec($ch);
$http_code_alt = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_alt = curl_error($ch);
curl_close($ch);

echo "<p><strong>Endpoint alternativo (/send-message):</strong></p>";
echo "<ul>";
echo "<li><strong>HTTP Code:</strong> $http_code_alt</li>";
echo "<li><strong>Resposta:</strong> " . htmlspecialchars($response_alt) . "</li>";
if ($error_alt) {
    echo "<li style='color: red;'><strong>Erro cURL:</strong> $error_alt</li>";
}
echo "</ul>";

echo "<h3>4. Testando com diferentes formatos de número...</h3>";

// Teste 4: Testar diferentes formatos de número
$formatos_numero = [
    '55' . $numero_limpo . '@c.us',
    '55' . $numero_limpo,
    $numero_limpo . '@c.us',
    $numero_limpo
];

foreach ($formatos_numero as $formato) {
    echo "<p><strong>Testando formato:</strong> $formato</p>";
    
    $payload_teste = json_encode([
        'to' => $formato,
        'message' => 'Teste de formato - ' . date('H:i:s')
    ]);
    
    $ch = curl_init("http://212.85.11.238:3000/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_teste);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response_teste = curl_exec($ch);
    $http_code_teste = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<ul>";
    echo "<li>HTTP Code: $http_code_teste</li>";
    echo "<li>Resposta: " . htmlspecialchars($response_teste) . "</li>";
    echo "</ul>";
    
    if ($http_code_teste === 200) {
        echo "<p style='color: green;'>✅ Formato $formato funcionou!</p>";
        break;
    }
}

echo "<h3>5. Verificando logs de monitoramento...</h3>";

// Verificar logs recentes
$log_file = __DIR__ . '/logs/monitoramento_clientes.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $linhas = explode("\n", $log_content);
    $ultimas_linhas = array_slice($linhas, -5); // Últimas 5 linhas
    
    echo "<h4>Últimas 5 linhas do log de monitoramento:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
    foreach ($ultimas_linhas as $linha) {
        if (trim($linha)) {
            echo htmlspecialchars($linha) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Arquivo de log não encontrado</p>";
}

echo "<h3>6. Resumo do diagnóstico...</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<h4>Status dos testes:</h4>";
echo "<ul>";

if ($http_code_status === 200) {
    echo "<li style='color: green;'>✅ Servidor WhatsApp acessível</li>";
} else {
    echo "<li style='color: red;'>❌ Servidor WhatsApp inacessível</li>";
}

if ($http_code === 200) {
    echo "<li style='color: green;'>✅ Envio de mensagem funcionando</li>";
} else {
    echo "<li style='color: red;'>❌ Falha no envio de mensagem</li>";
}

if ($error || $error_status) {
    echo "<li style='color: orange;'>⚠️ Problemas de conectividade detectados</li>";
} else {
    echo "<li style='color: green;'>✅ Conectividade OK</li>";
}

echo "</ul>";
echo "</div>";

echo "<h3>7. Recomendações...</h3>";

if ($http_code_status !== 200) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Problema identificado:</h4>";
    echo "<p>O servidor WhatsApp (212.85.11.238:3000) não está respondendo corretamente.</p>";
    echo "<p><strong>Ações recomendadas:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar se o servidor WhatsApp está rodando</li>";
    echo "<li>Verificar conectividade de rede</li>";
    echo "<li>Verificar firewall/portas</li>";
    echo "<li>Verificar logs do servidor WhatsApp</li>";
    echo "</ul>";
    echo "</div>";
} elseif ($http_code !== 200) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Problema identificado:</h4>";
    echo "<p>O servidor está acessível mas o envio de mensagens está falhando.</p>";
    echo "<p><strong>Possíveis causas:</strong></p>";
    echo "<ul>";
    echo "<li>Formato incorreto do número</li>";
    echo "<li>WhatsApp não conectado no servidor</li>";
    echo "<li>Erro na API do WhatsApp</li>";
    echo "<li>Número não está no WhatsApp</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ Tudo funcionando:</h4>";
    echo "<p>O WhatsApp está funcionando corretamente para o número testado.</p>";
    echo "<p>O problema anterior pode ter sido temporário ou específico de outros números.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Teste concluído em:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Número testado:</strong> $numero_teste</p>";
?> 