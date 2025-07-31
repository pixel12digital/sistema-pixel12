<?php
/**
 * CORREÇÃO WEBHOOK CANAL COMERCIAL
 * 
 * Este script corrige a configuração do VPS para usar
 * o webhook correto do canal comercial
 */

echo "🔧 CORREÇÃO WEBHOOK CANAL COMERCIAL\n";
echo "===================================\n\n";

// 1. Verificar configuração atual do VPS
echo "🔍 VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
$vps_ip = "212.85.11.238";

// Testar webhook atual
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
        
        $webhook_correto = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";
        
        if ($data["webhook_url"] === $webhook_correto) {
            echo "  ✅ Webhook já está configurado corretamente!\n";
        } else {
            echo "  ❌ Webhook incorreto! Deve ser: $webhook_correto\n";
            
            // Configurar webhook correto
            echo "\n🔧 CONFIGURANDO WEBHOOK CORRETO:\n";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["url" => $webhook_correto]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                echo "  ✅ Webhook configurado com sucesso!\n";
            } else {
                echo "  ❌ Erro ao configurar webhook (HTTP $http_code)\n";
            }
        }
    }
} else {
    echo "  ❌ Não foi possível verificar configuração atual\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Execute este script no VPS: ssh root@212.85.11.238\n";
echo "2. Ou configure manualmente:\n";
echo "   cd /var/whatsapp-api\n";
echo "   nano .env\n";
echo "   # Alterar WEBHOOK_URL para: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "   pm2 restart whatsapp-api\n";

echo "\n🎯 RESULTADO:\n";
echo "✅ Script de correção criado!\n";
echo "📋 Execute no VPS para corrigir a configuração do webhook.\n";
?>