<?php
echo "🛡️ CONFIGURADOR WEBHOOK SUPER SEGURO\n";
echo "====================================\n\n";

$vps_ip = '212.85.11.238';
$vps_port = '3000';

// URLs para testar (em ordem de prioridade)
$webhook_urls = [
    'https://app.pixel12digital.com.br/webhook.php',
    'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php'
];

echo "📡 VPS: $vps_ip:$vps_port\n\n";

// ETAPA 1: Backup da configuração atual
echo "🔄 ETAPA 1: Fazendo backup da configuração atual...\n";
echo str_repeat('-', 50) . "\n";

$backup_check = curl_init("http://$vps_ip:$vps_port/webhook/status");
curl_setopt($backup_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($backup_check, CURLOPT_TIMEOUT, 5);

$backup_response = curl_exec($backup_check);
$backup_code = curl_getinfo($backup_check, CURLINFO_HTTP_CODE);
curl_close($backup_check);

$webhook_backup = null;
if ($backup_code === 200) {
    $backup_data = json_decode($backup_response, true);
    $webhook_backup = $backup_data['webhook_url'] ?? 'nenhum';
    echo "✅ Backup realizado\n";
    echo "📋 Configuração atual: $webhook_backup\n\n";
} else {
    echo "⚠️ Não conseguiu fazer backup (prosseguindo mesmo assim)\n\n";
}

// ETAPA 2: Testar cada webhook sem configurar
echo "🔄 ETAPA 2: Testando webhooks sem afetar configuração...\n";
echo str_repeat('-', 50) . "\n";

$webhook_funcionando = null;

foreach ($webhook_urls as $index => $webhook_url) {
    echo "🧪 Testando: $webhook_url\n";
    
    // Teste direto via curl
    $test_webhook = curl_init($webhook_url);
    curl_setopt($test_webhook, CURLOPT_POST, true);
    curl_setopt($test_webhook, CURLOPT_POSTFIELDS, json_encode([
        'from' => '5547999999999',
        'body' => 'teste seguro'
    ]));
    curl_setopt($test_webhook, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($test_webhook, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($test_webhook, CURLOPT_TIMEOUT, 10);
    curl_setopt($test_webhook, CURLOPT_SSL_VERIFYPEER, false);
    
    $test_response = curl_exec($test_webhook);
    $test_code = curl_getinfo($test_webhook, CURLINFO_HTTP_CODE);
    curl_close($test_webhook);
    
    if ($test_code === 200) {
        $test_data = json_decode($test_response, true);
        if (isset($test_data['success']) && $test_data['success']) {
            echo "   ✅ Funcionando! JSON válido\n";
            echo "   📩 Resposta: " . substr($test_data['ana_response'], 0, 50) . "...\n";
            $webhook_funcionando = $webhook_url;
            break;
        } else {
            echo "   ❌ JSON inválido\n";
        }
    } else {
        echo "   ❌ HTTP $test_code\n";
    }
}

if (!$webhook_funcionando) {
    echo "\n❌ NENHUM WEBHOOK FUNCIONOU\n";
    echo "Não vou alterar a configuração da VPS\n";
    echo "Sistema permanece como estava\n";
    exit(1);
}

echo "\n✅ Webhook funcionando encontrado: $webhook_funcionando\n\n";

// ETAPA 3: Configurar na VPS com segurança
echo "🔄 ETAPA 3: Configurando na VPS...\n";
echo str_repeat('-', 50) . "\n";

$config_data = ['url' => $webhook_funcionando];

$config_ch = curl_init("http://$vps_ip:$vps_port/webhook/config");
curl_setopt($config_ch, CURLOPT_POST, true);
curl_setopt($config_ch, CURLOPT_POSTFIELDS, json_encode($config_data));
curl_setopt($config_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($config_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($config_ch, CURLOPT_TIMEOUT, 10);

$config_response = curl_exec($config_ch);
$config_code = curl_getinfo($config_ch, CURLINFO_HTTP_CODE);
curl_close($config_ch);

if ($config_code !== 200) {
    echo "❌ FALHA NA CONFIGURAÇÃO: HTTP $config_code\n";
    echo "Sistema permanece com configuração anterior\n";
    exit(1);
}

echo "✅ CONFIGURADO COM SUCESSO!\n";
echo "Resposta: " . substr($config_response, 0, 200) . "\n\n";

// ETAPA 4: Verificar se canais ainda funcionam
echo "🔄 ETAPA 4: Verificando se canais ainda funcionam...\n";
echo str_repeat('-', 50) . "\n";

$canais_ok = true;
$canais = ['3000', '3001'];

foreach ($canais as $porta) {
    $status_check = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($status_check, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($status_check, CURLOPT_TIMEOUT, 5);
    
    $status_response = curl_exec($status_check);
    $status_code = curl_getinfo($status_check, CURLINFO_HTTP_CODE);
    curl_close($status_check);
    
    if ($status_code === 200) {
        echo "✅ Canal $porta: OK\n";
    } else {
        echo "❌ Canal $porta: Problema (HTTP $status_code)\n";
        $canais_ok = false;
    }
}

if (!$canais_ok) {
    echo "\n⚠️ PROBLEMA DETECTADO NOS CANAIS!\n";
    echo "Fazendo rollback da configuração...\n";
    
    if ($webhook_backup && $webhook_backup !== 'nenhum') {
        $rollback_data = ['url' => $webhook_backup];
        
        $rollback_ch = curl_init("http://$vps_ip:$vps_port/webhook/config");
        curl_setopt($rollback_ch, CURLOPT_POST, true);
        curl_setopt($rollback_ch, CURLOPT_POSTFIELDS, json_encode($rollback_data));
        curl_setopt($rollback_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($rollback_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rollback_ch, CURLOPT_TIMEOUT, 10);
        
        $rollback_response = curl_exec($rollback_ch);
        $rollback_code = curl_getinfo($rollback_ch, CURLINFO_HTTP_CODE);
        curl_close($rollback_ch);
        
        if ($rollback_code === 200) {
            echo "✅ Rollback realizado com sucesso\n";
            echo "Sistema voltou à configuração anterior\n";
        } else {
            echo "❌ Falha no rollback\n";
        }
    }
    exit(1);
}

// ETAPA 5: Teste final
echo "\n🔄 ETAPA 5: Teste final...\n";
echo str_repeat('-', 50) . "\n";

$test_message = [
    'sessionName' => 'default',
    'number' => '5547999999999',
    'message' => '🎉 Sistema Ana configurado com sucesso! - ' . date('H:i:s')
];

$final_test = curl_init("http://$vps_ip:$vps_port/send/text");
curl_setopt($final_test, CURLOPT_POST, true);
curl_setopt($final_test, CURLOPT_POSTFIELDS, json_encode($test_message));
curl_setopt($final_test, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($final_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($final_test, CURLOPT_TIMEOUT, 10);

$final_response = curl_exec($final_test);
$final_code = curl_getinfo($final_test, CURLINFO_HTTP_CODE);
curl_close($final_test);

if ($final_code === 200) {
    $final_data = json_decode($final_response, true);
    if (isset($final_data['success']) && $final_data['success']) {
        echo "✅ TESTE FINAL PASSOU!\n\n";
        
        echo "🎉 CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!\n";
        echo "=====================================\n\n";
        
        echo "📱 WEBHOOK ATIVO:\n";
        echo "• URL: $webhook_funcionando\n";
        echo "• VPS: $vps_ip:$vps_port\n";
        echo "• Status: ✅ OPERACIONAL\n";
        echo "• Canais: ✅ TODOS FUNCIONANDO\n\n";
        
        echo "📋 TESTE REAL AGORA:\n";
        echo "1. Envie 'olá' para WhatsApp\n";
        echo "2. Ana deve responder automaticamente\n";
        echo "3. Teste 'quero um site' → Rafael\n";
        echo "4. Teste 'problema' → Suporte\n";
        echo "5. Teste 'pessoa' → Humano\n\n";
        
        echo "📊 MONITORAMENTO:\n";
        echo "• Logs: Procure por '[WEBHOOK_FISICO]'\n";
        echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php\n\n";
        
        echo "🔧 ROLLBACK (se necessário):\n";
        if ($webhook_backup && $webhook_backup !== 'nenhum') {
            echo "Para voltar: curl -X POST http://$vps_ip:$vps_port/webhook/config \\\n";
            echo "  -H 'Content-Type: application/json' \\\n";
            echo "  -d '{\"url\":\"$webhook_backup\"}'\n";
        } else {
            echo "Configuração anterior: nenhuma\n";
        }
        
    } else {
        echo "❌ TESTE FINAL FALHOU\n";
        echo "Sistema configurado mas com problema\n";
    }
} else {
    echo "❌ TESTE FINAL FALHOU: HTTP $final_code\n";
    echo "Sistema configurado mas com problema\n";
}
?> 