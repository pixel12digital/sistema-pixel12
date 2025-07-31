<?php
/**
 * CORREÇÃO AUTOMÁTICA WEBHOOK VPS - CANAL COMERCIAL
 * 
 * Este script corrige automaticamente a configuração do VPS
 * para usar o webhook correto do canal comercial (webhook_canal_37.php)
 * em vez do webhook geral (webhook_whatsapp.php)
 */

echo "🔧 CORREÇÃO AUTOMÁTICA WEBHOOK VPS - CANAL COMERCIAL\n";
echo "====================================================\n\n";

$vps_ip = "212.85.11.238";
$webhook_correto = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";
$webhook_atual = "https://pixel12digital.com.br/app/api/webhook_whatsapp.php";

// 1. Verificar configuração atual
echo "🔍 VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data["webhook_url"])) {
        echo "  📋 Webhook atual: " . $data["webhook_url"] . "\n";
        
        if ($data["webhook_url"] === $webhook_correto) {
            echo "  ✅ Webhook já está configurado corretamente!\n";
            echo "  🎯 Nenhuma correção necessária.\n";
            exit;
        } else {
            echo "  ❌ Webhook incorreto! Configurando correção...\n";
        }
    }
} else {
    echo "  ⚠️ Não foi possível verificar configuração atual\n";
    echo "  🔧 Tentando configurar webhook correto...\n";
}

// 2. Configurar webhook correto
echo "\n🔧 CONFIGURANDO WEBHOOK CORRETO:\n";
echo "  📋 URL correta: $webhook_correto\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["url" => $webhook_correto]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Webhook configurado com sucesso!\n";
    echo "  📋 Resposta: $response\n";
} else {
    echo "  ❌ Erro ao configurar webhook (HTTP $http_code)\n";
    if ($error) {
        echo "  📋 Erro cURL: $error\n";
    }
    echo "  📋 Resposta: $response\n";
}

// 3. Verificar se a configuração foi aplicada
echo "\n🔍 VERIFICANDO SE CONFIGURAÇÃO FOI APLICADA:\n";
sleep(2); // Aguardar 2 segundos

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data["webhook_url"])) {
        if ($data["webhook_url"] === $webhook_correto) {
            echo "  ✅ Configuração aplicada com sucesso!\n";
            echo "  📋 Webhook atual: " . $data["webhook_url"] . "\n";
        } else {
            echo "  ❌ Configuração não foi aplicada!\n";
            echo "  📋 Webhook ainda incorreto: " . $data["webhook_url"] . "\n";
        }
    }
} else {
    echo "  ⚠️ Não foi possível verificar se a configuração foi aplicada\n";
}

// 4. Testar webhook específico
echo "\n🧪 TESTANDO WEBHOOK ESPECÍFICO:\n";
$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste correção webhook canal comercial - ' . date('H:i:s'),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_correto);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $webhook_correto\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Webhook específico funcionando!\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  📋 Canal: {$data['canal']}\n";
        echo "  📋 ID: {$data['canal_id']}\n";
        echo "  📋 Banco: {$data['banco']}\n";
    }
} else {
    echo "  ❌ Webhook específico não funcionando!\n";
    echo "  📋 Resposta: $response\n";
}

// 5. Verificar banco comercial
echo "\n🔍 VERIFICANDO BANCO COMERCIAL:\n";
require_once 'canais/comercial/canal_config.php';

$mysqli = conectarBancoCanal();
if ($mysqli) {
    // Buscar mensagens recentes
    $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 3";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "  ✅ Mensagens encontradas no banco comercial:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
            echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "  ⚠️ Nenhuma mensagem encontrada no banco comercial\n";
        echo "  💡 Isso é normal se o webhook não estava configurado corretamente\n";
    }
    
    $mysqli->close();
} else {
    echo "  ❌ Não foi possível conectar ao banco comercial\n";
}

echo "\n🎯 RESULTADO DA CORREÇÃO:\n";
echo "✅ Script de correção executado!\n";
echo "📋 Próximos passos:\n";
echo "1. Enviar mensagem real para o número do canal comercial\n";
echo "2. Verificar se aparece no chat do painel\n";
echo "3. Confirmar que está associado ao canal correto\n";
echo "4. Monitorar funcionamento por 24h\n";

echo "\n🌐 LINKS ÚTEIS:\n";
echo "• VPS Status: http://$vps_ip:3001/status\n";
echo "• Webhook Correto: $webhook_correto\n";
echo "• Painel: https://app.pixel12digital.com.br/painel/\n";

echo "\n📞 SUPORTE:\n";
echo "• Se houver problemas, execute: php diagnosticar_webhook_canal_comercial.php\n";
echo "• Para verificar logs: tail -f logs/webhook_whatsapp_*.log\n";
?> 