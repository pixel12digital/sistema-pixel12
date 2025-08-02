<?php
/**
 * 🔍 DIAGNOSTICAR REDIRECIONAMENTO ENTRE CANAIS
 * 
 * Investigar por que Canal 3000 (Ana) redireciona para Canal 3001
 */

echo "🔍 DIAGNOSTICANDO REDIRECIONAMENTO DE CANAIS\n";
echo "============================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

// 1. VERIFICAR CONFIGURAÇÃO DOS CANAIS
echo "📊 TESTE 1: Configuração dos Canais no Banco\n";
echo "===========================================\n";

$canais = $mysqli->query("SELECT * FROM canais_comunicacao ORDER BY porta")->fetch_all(MYSQLI_ASSOC);

foreach ($canais as $canal) {
    echo "Canal ID {$canal['id']}: {$canal['nome']}\n";
    echo "  Porta: {$canal['porta']}\n";
    echo "  Status: {$canal['status']}\n";
    echo "  Identificador: {$canal['identificador']}\n";
    echo "  Webhook: " . (isset($canal['webhook_url']) ? $canal['webhook_url'] : 'N/A') . "\n\n";
}

// 2. VERIFICAR WEBHOOK CONFIGURADO NO VPS
echo "🔗 TESTE 2: Webhook Configurado no VPS\n";
echo "=====================================\n";

$vps_ip = '212.85.11.238';

// Verificar Canal 3000
echo "Canal 3000 (Ana):\n";
$webhook_3000 = curl_init("http://$vps_ip:3000/webhook/status");
curl_setopt($webhook_3000, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_3000, CURLOPT_TIMEOUT, 10);

$response_3000 = curl_exec($webhook_3000);
$code_3000 = curl_getinfo($webhook_3000, CURLINFO_HTTP_CODE);
curl_close($webhook_3000);

echo "  Status: HTTP $code_3000\n";
echo "  Resposta: " . substr($response_3000, 0, 200) . "\n\n";

// Verificar Canal 3001
echo "Canal 3001 (Comercial):\n";
$webhook_3001 = curl_init("http://$vps_ip:3001/webhook/status");
curl_setopt($webhook_3001, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_3001, CURLOPT_TIMEOUT, 10);

$response_3001 = curl_exec($webhook_3001);
$code_3001 = curl_getinfo($webhook_3001, CURLINFO_HTTP_CODE);
curl_close($webhook_3001);

echo "  Status: HTTP $code_3001\n";
echo "  Resposta: " . substr($response_3001, 0, 200) . "\n\n";

// 3. VERIFICAR MENSAGENS RECENTES
echo "📱 TESTE 3: Mensagens Recentes por Canal\n";
echo "=======================================\n";

$mensagens_recentes = $mysqli->query("
    SELECT 
        canal_id,
        COUNT(*) as total,
        MAX(data_hora) as ultima_mensagem,
        c.nome as canal_nome,
        c.porta
    FROM mensagens_comunicacao m
    LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
    WHERE DATE(data_hora) = CURDATE()
    GROUP BY canal_id, c.nome, c.porta
    ORDER BY ultima_mensagem DESC
")->fetch_all(MYSQLI_ASSOC);

foreach ($mensagens_recentes as $msg) {
    echo "Canal {$msg['canal_id']} ({$msg['canal_nome']}) - Porta {$msg['porta']}:\n";
    echo "  Mensagens hoje: {$msg['total']}\n";
    echo "  Última mensagem: {$msg['ultima_mensagem']}\n\n";
}

// 4. VERIFICAR ÚLTIMAS MENSAGENS ESPECÍFICAS
echo "🔍 TESTE 4: Últimas 5 Mensagens Detalhadas\n";
echo "==========================================\n";

$ultimas_mensagens = $mysqli->query("
    SELECT 
        m.*,
        c.nome as canal_nome,
        c.porta
    FROM mensagens_comunicacao m
    LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
    ORDER BY m.data_hora DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

foreach ($ultimas_mensagens as $msg) {
    echo "Mensagem ID {$msg['id']}:\n";
    echo "  Canal: {$msg['canal_nome']} (ID: {$msg['canal_id']}, Porta: {$msg['porta']})\n";
    echo "  Número: {$msg['numero_whatsapp']}\n";
    echo "  Direção: {$msg['direcao']}\n";
    echo "  Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    echo "  Data: {$msg['data_hora']}\n\n";
}

// 5. TESTAR WEBHOOK ATUAL
echo "🧪 TESTE 5: Testar Webhook Atual\n";
echo "===============================\n";

$webhook_atual = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "Testando: $webhook_atual\n";

$test_data = json_encode([
    'from' => '5547999999999',
    'body' => 'Teste de direcionamento de canal'
]);

$test_webhook = curl_init($webhook_atual);
curl_setopt($test_webhook, CURLOPT_POST, true);
curl_setopt($test_webhook, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($test_webhook, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($test_webhook, CURLOPT_RETURNTRANSFER, true);
curl_setopt($test_webhook, CURLOPT_TIMEOUT, 15);
curl_setopt($test_webhook, CURLOPT_SSL_VERIFYPEER, false);

$webhook_response = curl_exec($test_webhook);
$webhook_code = curl_getinfo($test_webhook, CURLINFO_HTTP_CODE);
curl_close($test_webhook);

echo "Status: HTTP $webhook_code\n";
echo "Resposta: " . substr($webhook_response, 0, 300) . "\n\n";

if ($webhook_code === 200) {
    $webhook_data = json_decode($webhook_response, true);
    if ($webhook_data) {
        echo "✅ Webhook respondeu com sucesso\n";
        echo "Canal processado: " . (isset($webhook_data['canal_id']) ? $webhook_data['canal_id'] : 'N/A') . "\n";
        echo "Ana respondeu: " . ($webhook_data['success'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "❌ Webhook falhou\n";
}

// 6. VERIFICAR LOGS DE ERRO RECENTES
echo "\n📋 TESTE 6: Verificar Logs de Sistema\n";
echo "====================================\n";

// Verificar se há logs da Ana
$log_ana = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM logs_integracao_ana 
    WHERE DATE(data_log) = CURDATE()
")->fetch_assoc();

echo "Logs Ana hoje: {$log_ana['total']}\n";

// Verificar transferências
$transfer_rafael = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM transferencias_rafael 
    WHERE DATE(data_transferencia) = CURDATE()
")->fetch_assoc();

echo "Transferências Rafael hoje: {$transfer_rafael['total']}\n";

$transfer_humano = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM transferencias_humano 
    WHERE DATE(data_transferencia) = CURDATE()
")->fetch_assoc();

echo "Transferências Humano hoje: {$transfer_humano['total']}\n\n";

// 7. DIAGNÓSTICO FINAL
echo "📊 DIAGNÓSTICO FINAL\n";
echo "===================\n";

echo "🔍 POSSÍVEIS CAUSAS DO REDIRECIONAMENTO:\n\n";

echo "1. 🔗 WEBHOOK CONFIGURADO NO CANAL ERRADO:\n";
echo "   - Canal 3000 pode estar com webhook vazio\n";
echo "   - Canal 3001 pode estar interceptando mensagens\n\n";

echo "2. 📱 REDIRECIONAMENTO AUTOMÁTICO:\n";
echo "   - Sistema pode estar redirecionando comercial automaticamente\n";
echo "   - Ana pode estar transferindo para canal 3001\n\n";

echo "3. ⚙️ CONFIGURAÇÃO DE CANAIS:\n";
echo "   - Canal padrão pode estar errado\n";
echo "   - Identificadores dos canais podem estar trocados\n\n";

echo "🚀 PRÓXIMOS PASSOS PARA CORREÇÃO:\n";
echo "1. Reconfigurar webhook especificamente no Canal 3000\n";
echo "2. Verificar se há redirecionamento automático ativo\n";
echo "3. Testar mensagem direta para Ana\n";
echo "4. Verificar configuração do painel de comunicação\n";

?> 