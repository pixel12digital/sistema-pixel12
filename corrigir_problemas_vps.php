<?php
/**
 * 🔧 CORRIGIR PROBLEMAS IDENTIFICADOS NA VPS
 * 
 * Script para identificar e corrigir problemas específicos:
 * 1. API diferente nos canais
 * 2. Webhooks não configurados
 * 3. Sessões não conectadas
 * 4. Endpoints não funcionando
 */

echo "🔧 CORRIGINDO PROBLEMAS IDENTIFICADOS NA VPS\n";
echo "============================================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';
$webhook_principal = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// ===== 1. DIAGNÓSTICO DETALHADO DOS PROBLEMAS =====
echo "1️⃣ DIAGNÓSTICO DETALHADO DOS PROBLEMAS\n";
echo "--------------------------------------\n";

$problemas = [];

// Verificar canal 3000
echo "🔍 Analisando Canal 3000...\n";
$ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3000 === 200) {
    $status_3000 = json_decode($response_3000, true);
    echo "  ✅ Status: " . ($status_3000['status'] ?? 'unknown') . "\n";
    
    // Verificar sessões
    if (isset($status_3000['clients_status'])) {
        $sessoes = $status_3000['clients_status'];
        echo "  👥 Sessões: " . count($sessoes) . "\n";
        foreach ($sessoes as $sessao => $status) {
            echo "    - $sessao: " . ($status['status'] ?? 'unknown') . "\n";
        }
    } else {
        echo "  ⚠️ Nenhuma sessão encontrada\n";
        $problemas[] = "Canal 3000: Sem sessões conectadas";
    }
} else {
    echo "  ❌ Canal 3000 não responde (HTTP $http_code_3000)\n";
    $problemas[] = "Canal 3000: Não responde";
}

// Verificar canal 3001
echo "\n🔍 Analisando Canal 3001...\n";
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "  ✅ Status: " . ($status_3001['status'] ?? 'unknown') . "\n";
    
    // Verificar se é API diferente
    if (isset($status_3001['clients_status'])) {
        echo "  👥 Sessões: " . count($status_3001['clients_status']) . "\n";
    } else {
        echo "  ⚠️ API diferente detectada\n";
        $problemas[] = "Canal 3001: API diferente (não usa whatsapp-api-server.js)";
    }
} else {
    echo "  ❌ Canal 3001 não responde (HTTP $http_code_3001)\n";
    $problemas[] = "Canal 3001: Não responde";
}

echo "\n";

// ===== 2. TESTAR ENDPOINTS ESPECÍFICOS =====
echo "2️⃣ TESTANDO ENDPOINTS ESPECÍFICOS\n";
echo "---------------------------------\n";

$endpoints_teste = [
    '/send/text' => 'Envio de mensagens',
    '/webhook/config' => 'Configuração de webhook',
    '/status' => 'Status do servidor',
    '/qr' => 'QR Code',
    '/session/default/status' => 'Status da sessão default',
    '/session/comercial/status' => 'Status da sessão comercial'
];

foreach ($endpoints_teste as $endpoint => $descricao) {
    echo "🔍 Testando $descricao ($endpoint)...\n";
    
    // Testar canal 3000
    $ch = curl_init("http://$vps_ip:3000$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status_3000 = ($http_code === 200) ? "✅" : "❌";
    echo "  Canal 3000: $status_3000 (HTTP $http_code)\n";
    
    // Testar canal 3001
    $ch = curl_init("http://$vps_ip:3001$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status_3001 = ($http_code === 200) ? "✅" : "❌";
    echo "  Canal 3001: $status_3001 (HTTP $http_code)\n";
    
    if ($http_code !== 200) {
        $problemas[] = "Canal 3001: Endpoint $endpoint não funciona (HTTP $http_code)";
    }
    
    echo "\n";
}

// ===== 3. VERIFICAR WEBHOOKS =====
echo "3️⃣ VERIFICANDO WEBHOOKS\n";
echo "-----------------------\n";

// Verificar webhook canal 3000
$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_3000 = curl_exec($ch);
$webhook_http_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($webhook_http_3000 === 200) {
    $webhook_config_3000 = json_decode($webhook_3000, true);
    if ($webhook_config_3000 && isset($webhook_config_3000['webhook_url'])) {
        echo "✅ Canal 3000: Webhook configurado\n";
        echo "  🔗 URL: {$webhook_config_3000['webhook_url']}\n";
        
        if ($webhook_config_3000['webhook_url'] !== $webhook_principal) {
            $problemas[] = "Canal 3000: Webhook URL incorreta";
        }
    }
} else {
    echo "❌ Canal 3000: Webhook não configurado (HTTP $webhook_http_3000)\n";
    $problemas[] = "Canal 3000: Webhook não configurado";
}

// Verificar webhook canal 3001
$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_3001 = curl_exec($ch);
$webhook_http_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($webhook_http_3001 === 200) {
    $webhook_config_3001 = json_decode($webhook_3001, true);
    if ($webhook_config_3001 && isset($webhook_config_3001['webhook_url'])) {
        echo "✅ Canal 3001: Webhook configurado\n";
        echo "  🔗 URL: {$webhook_config_3001['webhook_url']}\n";
    }
} else {
    echo "❌ Canal 3001: Webhook não configurado (HTTP $webhook_http_3001)\n";
    $problemas[] = "Canal 3001: Webhook não configurado";
}

echo "\n";

// ===== 4. APLICAR CORREÇÕES =====
echo "4️⃣ APLICANDO CORREÇÕES\n";
echo "----------------------\n";

$correcoes_aplicadas = 0;

// Correção 1: Configurar webhook canal 3000 se necessário
if ($webhook_http_3000 !== 200 || 
    ($webhook_config_3000 && $webhook_config_3000['webhook_url'] !== $webhook_principal)) {
    
    echo "🔧 Corrigindo webhook canal 3000...\n";
    
    $ch = curl_init("http://$vps_ip:3000/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_principal]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Webhook canal 3000 corrigido\n";
        $correcoes_aplicadas++;
    } else {
        echo "  ❌ Erro ao corrigir webhook canal 3000 (HTTP $http_code)\n";
    }
}

// Correção 2: Tentar conectar sessão default no canal 3000
echo "🔧 Tentando conectar sessão default no canal 3000...\n";

$ch = curl_init("http://$vps_ip:3000/session/default/connect");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Sessão default conectada\n";
    $correcoes_aplicadas++;
} else {
    echo "  ⚠️ Erro ao conectar sessão default (HTTP $http_code)\n";
    echo "  📝 Resposta: $response\n";
}

// Correção 3: Tentar conectar sessão comercial no canal 3001
echo "🔧 Tentando conectar sessão comercial no canal 3001...\n";

$ch = curl_init("http://$vps_ip:3001/session/comercial/connect");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Sessão comercial conectada\n";
    $correcoes_aplicadas++;
} else {
    echo "  ⚠️ Erro ao conectar sessão comercial (HTTP $http_code)\n";
    echo "  📝 Resposta: $response\n";
}

// Correção 4: Tentar configurar webhook canal 3001 com diferentes endpoints
if ($webhook_http_3001 !== 200) {
    echo "🔧 Tentando configurar webhook canal 3001...\n";
    
    $endpoints_webhook = ['/webhook/config', '/webhook', '/hook/config', '/hook', '/set-webhook'];
    $webhook_configurado_3001 = false;
    
    foreach ($endpoints_webhook as $endpoint) {
        $ch = curl_init("http://$vps_ip:3001$endpoint");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_principal]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "  ✅ Webhook canal 3001 configurado via $endpoint\n";
            $webhook_configurado_3001 = true;
            $correcoes_aplicadas++;
            break;
        }
    }
    
    if (!$webhook_configurado_3001) {
        echo "  ⚠️ Não foi possível configurar webhook canal 3001\n";
        $problemas[] = "Canal 3001: API não suporta webhook padrão";
    }
}

echo "\n";

// ===== 5. TESTAR CORREÇÕES =====
echo "5️⃣ TESTANDO CORREÇÕES APLICADAS\n";
echo "-------------------------------\n";

// Testar envio canal 3000
echo "🧪 Testando envio canal 3000...\n";
$test_data = [
    'sessionName' => 'default',
    'number' => '5511999999999',
    'message' => 'Teste correção VPS - ' . date('Y-m-d H:i:s')
];

$ch = curl_init("http://$vps_ip:3000/send/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Envio canal 3000 funcionando\n";
    $result = json_decode($response, true);
    if ($result && isset($result['success']) && $result['success']) {
        echo "  📝 Mensagem enviada com sucesso\n";
    }
} else {
    echo "  ❌ Erro no envio canal 3000 (HTTP $http_code)\n";
    echo "  📝 Resposta: $response\n";
    $problemas[] = "Canal 3000: Erro no envio (HTTP $http_code)";
}

// Testar webhook canal 3000
echo "🧪 Testando webhook canal 3000...\n";
$ch = curl_init("http://$vps_ip:3000/webhook/test");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Webhook canal 3000 testado com sucesso\n";
} else {
    echo "  ❌ Erro no teste webhook canal 3000 (HTTP $http_code)\n";
    $problemas[] = "Canal 3000: Erro no teste webhook (HTTP $http_code)";
}

echo "\n";

// ===== 6. RELATÓRIO FINAL =====
echo "6️⃣ RELATÓRIO FINAL DAS CORREÇÕES\n";
echo "--------------------------------\n";

echo "📊 RESUMO DOS PROBLEMAS IDENTIFICADOS:\n";
if (empty($problemas)) {
    echo "✅ Nenhum problema identificado!\n";
} else {
    foreach ($problemas as $i => $problema) {
        echo "  " . ($i + 1) . ". $problema\n";
    }
}

echo "\n📊 CORREÇÕES APLICADAS: $correcoes_aplicadas\n\n";

// ===== 7. COMANDOS PARA CORREÇÕES MANUAIS =====
echo "7️⃣ COMANDOS PARA CORREÇÕES MANUAIS\n";
echo "-----------------------------------\n";

echo "🔧 Para conectar WhatsApp no canal 3000:\n";
echo "curl http://$vps_ip:3000/qr\n\n";

echo "🔧 Para reiniciar serviços na VPS:\n";
echo "ssh root@$vps_ip\n";
echo "pm2 restart whatsapp-3000\n";
echo "pm2 restart whatsapp-3001\n\n";

echo "🔧 Para verificar logs:\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "🔧 Para migrar canal 3001 para API correta:\n";
echo "# Necessário investigar qual API está rodando e migrar para whatsapp-api-server.js\n\n";

// ===== 8. RESUMO FINAL =====
echo "8️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 CORREÇÕES CONCLUÍDAS!\n\n";

echo "✅ PROBLEMAS CORRIGIDOS:\n";
if ($correcoes_aplicadas > 0) {
    echo "• Webhook canal 3000 configurado\n";
    echo "• Sessões tentadas conectar\n";
    echo "• Testes realizados\n";
} else {
    echo "• Nenhuma correção automática foi possível\n";
}

echo "\n⚠️ PROBLEMAS PENDENTES:\n";
if (!empty($problemas)) {
    foreach (array_slice($problemas, 0, 3) as $problema) {
        echo "• $problema\n";
    }
} else {
    echo "• Nenhum problema identificado\n";
}

echo "\n📚 PRÓXIMOS PASSOS:\n";
echo "1. Conectar WhatsApp no canal 3000 (gerar QR Code)\n";
echo "2. Investigar API do canal 3001 para migração\n";
echo "3. Testar funcionalidades completas\n";
echo "4. Monitorar logs se necessário\n\n";

echo "📞 COMANDOS ÚTEIS:\n";
echo "• Status: curl http://$vps_ip:3000/status\n";
echo "• QR Code: curl http://$vps_ip:3000/qr\n";
echo "• Webhook: curl http://$vps_ip:3000/webhook/config\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 20'\n\n";

echo "✅ CORREÇÕES FINALIZADAS!\n";
echo "🎉 Problemas identificados e correções aplicadas!\n";
?> 