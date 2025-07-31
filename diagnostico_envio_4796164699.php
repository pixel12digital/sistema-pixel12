<?php
/**
 * Diagnóstico Detalhado - Número 4796164699
 * Investigar por que a mensagem não foi recebida
 */

echo "🔍 DIAGNÓSTICO DETALHADO - 4796164699\n";
echo "=====================================\n\n";

// Configurações
$vps_url = "http://212.85.11.238:3000";
$numero = "4796164699";

echo "1️⃣ Verificando status da sessão WhatsApp...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
echo "   📄 Resposta: $response\n\n";

$status_data = json_decode($response, true);
if ($status_data) {
    echo "   📋 Status decodificado:\n";
    print_r($status_data);
    echo "\n";
}

echo "2️⃣ Verificando QR Code (se necessário)...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . "/qr");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n\n";

echo "3️⃣ Testando envio com diferentes formatos de número...\n";

$formatos_numero = [
    "4796164699",
    "554796164699",
    "+554796164699",
    "4796164699@c.us"
];

foreach ($formatos_numero as $formato) {
    echo "   📞 Testando formato: $formato\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . "/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $formato,
        'message' => "Teste formato $formato - " . date('H:i:s')
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "      📊 HTTP Code: $http_code\n";
    echo "      📄 Resposta: $response\n";
    
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        echo "      ✅ Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        if (isset($data['message'])) {
            echo "      📋 Message: " . $data['message'] . "\n";
        }
        if (isset($data['error'])) {
            echo "      ❌ Error: " . $data['error'] . "\n";
        }
    }
    echo "\n";
}

echo "4️⃣ Verificando logs do servidor...\n";
echo "   💡 Execute na VPS: pm2 logs whatsapp-api --lines 10\n";
echo "   💡 Verifique se há erros de conexão ou validação\n\n";

echo "5️⃣ Possíveis problemas:\n";
echo "   ❓ Número não existe no WhatsApp\n";
echo "   ❓ Número bloqueou o contato\n";
echo "   ❓ Formato incorreto do número\n";
echo "   ❓ Sessão WhatsApp não conectada\n";
echo "   ❓ Erro na API do WhatsApp\n\n";

echo "6️⃣ Teste manual na VPS:\n";
echo "   curl -X POST http://212.85.11.238:3000/send \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"to\":\"4796164699\",\"message\":\"Teste manual\"}'\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Verifique se o número existe no WhatsApp\n";
echo "2. Teste com outro número conhecido\n";
echo "3. Verifique os logs do servidor\n";
echo "4. Confirme se a sessão está conectada\n\n";

echo "🎯 Diagnóstico concluído!\n";
?> 