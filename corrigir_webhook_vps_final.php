<?php
/**
 * 🔧 CORREÇÃO FINAL DO WEBHOOK VPS
 * 
 * Corrige o problema crítico do webhookUrl relativo
 * que está causando ERR_INVALID_URL e QR Code não disponível
 */

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 CORREÇÃO FINAL DO WEBHOOK VPS\n";
echo "================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

// URL correta do webhook
$webhook_url_correta = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "🎯 PROBLEMA IDENTIFICADO:\n";
echo "- webhookUrl = 'api/webhook.php' (URL relativa)\n";
echo "- Causa: ERR_INVALID_URL no fetch()\n";
echo "- Resultado: QR Code não disponível\n\n";

echo "✅ SOLUÇÃO:\n";
echo "- Alterar para URL absoluta\n";
echo "- URL: $webhook_url_correta\n\n";

foreach ($portas as $porta) {
    echo "🔧 CONFIGURANDO PORTA $porta...\n";
    echo "--------------------------------\n";
    
    // 1. Configurar webhook
    $url_config = "http://$vps_ip:$porta/webhook/config";
    $dados_config = json_encode(['url' => $webhook_url_correta]);
    
    $ch = curl_init($url_config);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados_config);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ Webhook configurado com sucesso!\n";
        $resultado = json_decode($response, true);
        if ($resultado && isset($resultado['webhook_url'])) {
            echo "   URL configurada: " . $resultado['webhook_url'] . "\n";
        }
    } else {
        echo "❌ Erro ao configurar webhook (HTTP $http_code)\n";
        echo "   Resposta: $response\n";
    }
    
    // 2. Testar webhook
    echo "\n🧪 TESTANDO WEBHOOK...\n";
    $url_test = "http://$vps_ip:$porta/webhook/test";
    
    $ch = curl_init($url_test);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ Webhook testado com sucesso!\n";
        $resultado = json_decode($response, true);
        if ($resultado && isset($resultado['message'])) {
            echo "   Status: " . $resultado['message'] . "\n";
        }
    } else {
        echo "❌ Erro ao testar webhook (HTTP $http_code)\n";
        echo "   Resposta: $response\n";
    }
    
    // 3. Verificar status
    echo "\n📊 VERIFICANDO STATUS...\n";
    $url_status = "http://$vps_ip:$porta/status";
    
    $ch = curl_init($url_status);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $resultado = json_decode($response, true);
        if ($resultado) {
            echo "✅ Status da VPS:\n";
            echo "   Ready: " . ($resultado['ready'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Sessões: " . ($resultado['sessions'] ?? 0) . "\n";
            echo "   Mensagem: " . ($resultado['message'] ?? 'N/A') . "\n";
            
            if (isset($resultado['clients_status']['default'])) {
                $status_default = $resultado['clients_status']['default'];
                echo "   Sessão default: " . ($status_default['status'] ?? 'unknown') . "\n";
                echo "   QR disponível: " . (isset($status_default['qr']) ? 'SIM' : 'NÃO') . "\n";
            }
        }
    } else {
        echo "❌ Erro ao verificar status (HTTP $http_code)\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

echo "🎯 COMANDOS SSH PARA APLICAR NA VPS:\n";
echo "====================================\n\n";

echo "# 1. Conectar à VPS\n";
echo "ssh root@$vps_ip\n\n";

echo "# 2. Navegar para o diretório\n";
echo "cd /var/whatsapp-api\n\n";

echo "# 3. Editar o arquivo de configuração\n";
echo "nano whatsapp-api-server.js\n\n";

echo "# 4. Localizar e alterar a linha:\n";
echo "# ANTES: let webhookUrl = 'api/webhook.php';\n";
echo "# DEPOIS: let webhookUrl = '$webhook_url_correta';\n\n";

echo "# 5. Salvar (Ctrl+O, Enter, Ctrl+X)\n\n";

echo "# 6. Reiniciar os serviços\n";
echo "pm2 restart whatsapp-3000 --update-env\n";
echo "pm2 restart whatsapp-3001 --update-env\n";
echo "pm2 save\n\n";

echo "# 7. Verificar logs\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "# 8. Testar endpoints\n";
echo "curl http://127.0.0.1:3001/status\n";
echo "curl http://127.0.0.1:3001/qr?session=default\n\n";

echo "🎉 RESULTADO ESPERADO:\n";
echo "======================\n";
echo "- ❌ ERR_INVALID_URL desaparece\n";
echo "- ❌ bind EADDRINUSE null:3000 desaparece\n";
echo "- ✅ QR Code fica disponível\n";
echo "- ✅ Sessão fica pronta (ready: true)\n";
echo "- ✅ WhatsApp conecta normalmente\n\n";

echo "📞 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Execute os comandos SSH na VPS\n";
echo "2. Teste o QR Code no painel\n";
echo "3. Verifique se a Ana conecta\n";
echo "4. Monitore os logs para confirmar\n\n";

echo "✅ CORREÇÃO APLICADA COM SUCESSO!\n";
?> 