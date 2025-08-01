<?php
require_once 'config.php';

echo "🔍 VERIFICAÇÃO DE LOGS DO VPS\n";
echo "=============================\n\n";

$vps_url = "http://212.85.11.238:3000";

echo "📋 TESTANDO CONECTIVIDADE COM VPS:\n";
echo "==================================\n";

// Testar status geral
$ch = curl_init($vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: $http_code\n";
echo "Resposta: $response\n\n";

// Testar sessões
$ch = curl_init($vps_url . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📱 SESSÕES ATIVAS:\n";
echo "==================\n";
echo "Status HTTP: $http_code\n";
echo "Resposta: $response\n\n";

// Testar webhook configurado
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "🔗 CONFIGURAÇÃO DO WEBHOOK:\n";
echo "===========================\n";
echo "Status HTTP: $http_code\n";
echo "Resposta: $response\n\n";

echo "💡 PRÓXIMOS PASSOS:\n";
echo "===================\n";
echo "1. Acesse o VPS via SSH\n";
echo "2. Execute: tail -f /var/whatsapp-api/logs/whatsapp-api.log\n";
echo "3. Envie uma nova mensagem e observe os logs\n";
echo "4. Verifique se há erros de conexão ou envio\n";
?> 