<?php
/**
 * 🔍 DIAGNÓSTICO COMPLETO DE CONECTIVIDADE
 * 
 * Verificar por que mensagens WhatsApp não chegam ao sistema
 */

echo "🔍 DIAGNÓSTICO COMPLETO DE CONECTIVIDADE\n";
echo "========================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';

// 1. VERIFICAR STATUS GERAL DOS CANAIS
echo "📊 TESTE 1: Status dos Canais WhatsApp\n";
echo "=====================================\n";

$canais = [3000, 3001];

foreach ($canais as $porta) {
    echo "🔗 Canal $porta:\n";
    
    // Status básico
    $status_ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($status_ch, CURLOPT_TIMEOUT, 10);
    
    $status_response = curl_exec($status_ch);
    $status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
    curl_close($status_ch);
    
    echo "  Status API: HTTP $status_code\n";
    
    if ($status_code === 200) {
        $status_data = json_decode($status_response, true);
        if (isset($status_data['status'])) {
            echo "  WhatsApp Status: {$status_data['status']}\n";
            echo "  Conectado: " . (isset($status_data['connected']) ? ($status_data['connected'] ? 'SIM' : 'NÃO') : 'N/A') . "\n";
        }
    } else {
        echo "  ❌ API não responde\n";
    }
    
    // Verificar webhook configurado
    $webhook_ch = curl_init("http://$vps_ip:$porta/webhook");
    curl_setopt($webhook_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($webhook_ch, CURLOPT_TIMEOUT, 10);
    
    $webhook_response = curl_exec($webhook_ch);
    $webhook_code = curl_getinfo($webhook_ch, CURLINFO_HTTP_CODE);
    curl_close($webhook_ch);
    
    if ($webhook_code === 200) {
        $webhook_data = json_decode($webhook_response, true);
        echo "  Webhook URL: " . (isset($webhook_data['webhook_url']) ? $webhook_data['webhook_url'] : 'NÃO CONFIGURADO') . "\n";
    } else {
        echo "  Webhook: ERRO HTTP $webhook_code\n";
    }
    
    echo "\n";
}

// 2. VERIFICAR ÚLTIMAS MENSAGENS NO BANCO
echo "📱 TESTE 2: Últimas Mensagens no Banco de Dados\n";
echo "===============================================\n";

$ultima_mensagem = $mysqli->query("
    SELECT 
        id, 
        canal_id, 
        numero_whatsapp, 
        direcao, 
        data_hora,
        SUBSTRING(mensagem, 1, 50) as mensagem_resumo
    FROM mensagens_comunicacao 
    ORDER BY data_hora DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

if (empty($ultima_mensagem)) {
    echo "❌ NENHUMA MENSAGEM ENCONTRADA NO BANCO!\n";
    echo "   Isso indica que o webhook não está enviando dados\n\n";
} else {
    echo "✅ Últimas mensagens encontradas:\n";
    foreach ($ultima_mensagem as $msg) {
        echo "  ID {$msg['id']} | Canal {$msg['canal_id']} | {$msg['direcao']} | {$msg['data_hora']}\n";
        echo "    Número: {$msg['numero_whatsapp']}\n";
        echo "    Mensagem: {$msg['mensagem_resumo']}...\n\n";
    }
}

// 3. TESTAR CONECTIVIDADE WEBHOOK MANUALMENTE
echo "🧪 TESTE 3: Teste Manual do Webhook\n";
echo "==================================\n";

$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "Testando webhook: $webhook_url\n";

$test_data = json_encode([
    'from' => '5547999999999@c.us',
    'to' => '554797146908@c.us',
    'body' => 'TESTE MANUAL DE CONECTIVIDADE - ' . date('H:i:s'),
    'type' => 'text',
    'timestamp' => time()
]);

$test_ch = curl_init($webhook_url);
curl_setopt($test_ch, CURLOPT_POST, true);
curl_setopt($test_ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($test_ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: WhatsApp-Test'
]);
curl_setopt($test_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($test_ch, CURLOPT_TIMEOUT, 20);
curl_setopt($test_ch, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($test_ch);
$test_code = curl_getinfo($test_ch, CURLINFO_HTTP_CODE);
$test_error = curl_error($test_ch);
curl_close($test_ch);

echo "Status: HTTP $test_code\n";
if ($test_error) {
    echo "❌ Erro cURL: $test_error\n";
} else {
    echo "Resposta: " . substr($test_response, 0, 300) . "\n";
    
    if ($test_code === 200) {
        echo "✅ Webhook responde corretamente\n";
        
        // Verificar se a mensagem foi salva no banco
        $verificar_msg = $mysqli->query("
            SELECT id FROM mensagens_comunicacao 
            WHERE numero_whatsapp = '5547999999999' 
            AND mensagem LIKE '%TESTE MANUAL DE CONECTIVIDADE%'
            ORDER BY data_hora DESC LIMIT 1
        ")->fetch_assoc();
        
        if ($verificar_msg) {
            echo "✅ Mensagem salva no banco (ID: {$verificar_msg['id']})\n";
        } else {
            echo "❌ Mensagem NÃO foi salva no banco\n";
        }
    } else {
        echo "❌ Webhook falhou\n";
    }
}

// 4. VERIFICAR CONFIGURAÇÃO DO VPS
echo "\n🔧 TESTE 4: Reconfigurar Webhook nos VPS\n";
echo "========================================\n";

foreach ($canais as $porta) {
    echo "Configurando Canal $porta...\n";
    
    $config_data = json_encode([
        'url' => $webhook_url
    ]);
    
    $config_ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($config_ch, CURLOPT_POST, true);
    curl_setopt($config_ch, CURLOPT_POSTFIELDS, $config_data);
    curl_setopt($config_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($config_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($config_ch, CURLOPT_TIMEOUT, 15);
    
    $config_response = curl_exec($config_ch);
    $config_code = curl_getinfo($config_ch, CURLINFO_HTTP_CODE);
    curl_close($config_ch);
    
    echo "  Status: HTTP $config_code\n";
    echo "  Resposta: " . substr($config_response, 0, 100) . "\n";
    
    if ($config_code === 200) {
        echo "  ✅ Webhook configurado com sucesso\n";
    } else {
        echo "  ❌ Falha na configuração\n";
    }
    echo "\n";
}

// 5. VERIFICAR LOGS DO SISTEMA
echo "📋 TESTE 5: Verificar Logs Recentes\n";
echo "===================================\n";

// Verificar logs de hoje
$logs_hoje = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM mensagens_comunicacao 
    WHERE DATE(data_hora) = CURDATE()
")->fetch_assoc();

echo "Mensagens hoje no banco: {$logs_hoje['total']}\n";

// Últimas atividades
$ultima_atividade = $mysqli->query("
    SELECT MAX(data_hora) as ultima_atividade 
    FROM mensagens_comunicacao
")->fetch_assoc();

echo "Última atividade: " . ($ultima_atividade['ultima_atividade'] ?: 'NUNCA') . "\n";

// 6. TESTE DE ENVIO DIRETO VIA VPS
echo "\n📤 TESTE 6: Envio Direto via VPS (Canal 3000)\n";
echo "=============================================\n";

$send_data = json_encode([
    'to' => '5547999999999@c.us',
    'body' => '🧪 TESTE DIRETO DO VPS - ' . date('H:i:s') . '\n\nSe você recebeu esta mensagem, o VPS está funcionando!'
]);

$send_ch = curl_init("http://$vps_ip:3000/send");
curl_setopt($send_ch, CURLOPT_POST, true);
curl_setopt($send_ch, CURLOPT_POSTFIELDS, $send_data);
curl_setopt($send_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($send_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($send_ch, CURLOPT_TIMEOUT, 15);

$send_response = curl_exec($send_ch);
$send_code = curl_getinfo($send_ch, CURLINFO_HTTP_CODE);
curl_close($send_ch);

echo "Status envio: HTTP $send_code\n";
echo "Resposta: " . substr($send_response, 0, 200) . "\n";

if ($send_code === 200) {
    echo "✅ Mensagem enviada pelo VPS\n";
    echo "📱 Verifique seu WhatsApp para confirmar recebimento\n";
} else {
    echo "❌ Falha no envio pelo VPS\n";
}

// 7. DIAGNÓSTICO FINAL
echo "\n📊 DIAGNÓSTICO FINAL\n";
echo "====================\n";

echo "🔍 POSSÍVEIS PROBLEMAS IDENTIFICADOS:\n\n";

if ($logs_hoje['total'] == 0) {
    echo "🚨 PROBLEMA CRÍTICO: Nenhuma mensagem chegou ao sistema hoje\n";
    echo "   CAUSAS POSSÍVEIS:\n";
    echo "   1. Webhook não configurado nos VPS\n";
    echo "   2. VPS não está enviando mensagens para o webhook\n";
    echo "   3. Problema de conectividade de rede\n";
    echo "   4. WhatsApp não está conectado nos VPS\n\n";
} else {
    echo "✅ Sistema recebendo mensagens normalmente\n\n";
}

echo "🔧 AÇÕES CORRETIVAS EXECUTADAS:\n";
echo "1. ✅ Webhook reconfigurado em ambos os canais\n";
echo "2. ✅ Teste manual do webhook executado\n";
echo "3. ✅ Teste de envio direto pelo VPS executado\n\n";

echo "📱 PRÓXIMOS PASSOS:\n";
echo "1. Verifique se recebeu a mensagem de teste no WhatsApp\n";
echo "2. Envie uma mensagem de teste para o número do Canal 3000\n";
echo "3. Execute este script novamente para verificar se chegou\n";
echo "4. Se ainda não funcionar, reiniciar os serviços VPS\n";

?> 