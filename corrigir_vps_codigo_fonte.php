<?php
/**
 * 🔧 CORRIGIR VPS BASEADO NO CÓDIGO FONTE LOCAL
 * 
 * Script para analisar o código local e ajustar a VPS de acordo
 * Resolve problemas identificados:
 * 1. VPS 3000 sem sessões conectadas
 * 2. VPS 3001 não respondendo
 * 3. Webhooks não configurados
 * 4. Endpoints incorretos
 */

echo "🔧 CORRIGINDO VPS BASEADO NO CÓDIGO FONTE LOCAL\n";
echo "===============================================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';
$webhook_principal = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// ===== 1. ANÁLISE DO CÓDIGO LOCAL =====
echo "1️⃣ ANALISANDO CÓDIGO LOCAL\n";
echo "---------------------------\n";

// Configurações identificadas no código
$config_local = [
    'whatsapp_robot_url' => defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000',
    'whatsapp_timeout' => defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : 10,
    'webhook_principal' => $webhook_principal,
    'canais' => [
        '3000' => [
            'nome' => 'Pixel12Digital',
            'identificador' => '554797146908@c.us',
            'session' => 'default',
            'funcao' => 'financeiro'
        ],
        '3001' => [
            'nome' => 'Comercial - Pixel',
            'identificador' => '554797309525@c.us',
            'session' => 'comercial',
            'funcao' => 'comercial'
        ]
    ]
];

echo "📋 CONFIGURAÇÕES IDENTIFICADAS:\n";
echo "• VPS URL: {$config_local['whatsapp_robot_url']}\n";
echo "• Timeout: {$config_local['whatsapp_timeout']}s\n";
echo "• Webhook: {$config_local['webhook_principal']}\n";
echo "• Canais: " . count($config_local['canais']) . " (3000 e 3001)\n\n";

// ===== 2. DIAGNÓSTICO ATUAL DA VPS =====
echo "2️⃣ DIAGNÓSTICO ATUAL DA VPS\n";
echo "----------------------------\n";

$status_vps = [];

foreach ($config_local['canais'] as $porta => $canal) {
    echo "🔍 Verificando {$canal['nome']} (Porta $porta)...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status_vps[$porta] = [
        'http_code' => $http_code,
        'response' => $response,
        'error' => $error,
        'funcionando' => ($http_code === 200 && !$error)
    ];
    
    if ($status_vps[$porta]['funcionando']) {
        echo "  ✅ Canal respondendo\n";
        $status_data = json_decode($response, true);
        if ($status_data) {
            echo "  📊 Ready: " . ($status_data['ready'] ?? 'unknown') . "\n";
            echo "  📱 Sessões: " . count($status_data['clients_status'] ?? []) . "\n";
            if (isset($status_data['clients_status'])) {
                foreach ($status_data['clients_status'] as $session => $status) {
                    echo "    - $session: " . ($status['status'] ?? 'unknown') . "\n";
                }
            }
        }
    } else {
        echo "  ❌ Canal não responde (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
    }
    echo "\n";
}

// ===== 3. VERIFICAR WEBHOOKS =====
echo "3️⃣ VERIFICANDO WEBHOOKS\n";
echo "-----------------------\n";

$webhooks_status = [];

foreach ($config_local['canais'] as $porta => $canal) {
    if (!$status_vps[$porta]['funcionando']) {
        echo "⚠️ Pulando verificação de webhook para porta $porta (canal não responde)\n";
        continue;
    }
    
    echo "🔍 Verificando webhook {$canal['nome']}...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $webhooks_status[$porta] = [
        'http_code' => $http_code,
        'response' => $response,
        'configurado' => ($http_code === 200)
    ];
    
    if ($webhooks_status[$porta]['configurado']) {
        $webhook_data = json_decode($response, true);
        if ($webhook_data && isset($webhook_data['webhook_url'])) {
            echo "  ✅ Webhook configurado\n";
            echo "  🔗 URL: {$webhook_data['webhook_url']}\n";
            
            if ($webhook_data['webhook_url'] !== $webhook_principal) {
                echo "  ⚠️ URL diferente da esperada\n";
            }
        }
    } else {
        echo "  ❌ Webhook não configurado (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== 4. APLICAR CORREÇÕES =====
echo "4️⃣ APLICANDO CORREÇÕES\n";
echo "----------------------\n";

$correcoes_aplicadas = 0;

// Correção 1: Configurar webhook canal 3000
if ($status_vps['3000']['funcionando'] && 
    (!$webhooks_status['3000']['configurado'] || 
     (isset($webhook_data['webhook_url']) && $webhook_data['webhook_url'] !== $webhook_principal))) {
    
    echo "🔧 Configurando webhook canal 3000...\n";
    
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
        echo "  ✅ Webhook canal 3000 configurado\n";
        $correcoes_aplicadas++;
    } else {
        echo "  ❌ Falha ao configurar webhook (HTTP $http_code)\n";
    }
}

// Correção 2: Iniciar sessão default no canal 3000
if ($status_vps['3000']['funcionando']) {
    echo "🔧 Iniciando sessão default no canal 3000...\n";
    
    $ch = curl_init("http://$vps_ip:3000/session/start/default");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Sessão default iniciada\n";
        $correcoes_aplicadas++;
    } else {
        echo "  ❌ Falha ao iniciar sessão (HTTP $http_code)\n";
    }
}

// Correção 3: Verificar se VPS 3001 precisa ser reiniciado
if (!$status_vps['3001']['funcionando']) {
    echo "🔧 VPS 3001 não está respondendo - precisa de reinicialização\n";
    echo "  💡 Execute no servidor: pm2 restart whatsapp-3001\n";
    echo "  💡 Ou: pm2 restart all\n";
}

echo "\n";

// ===== 5. TESTAR ENDPOINTS ESPECÍFICOS =====
echo "5️⃣ TESTANDO ENDPOINTS ESPECÍFICOS\n";
echo "---------------------------------\n";

$endpoints_teste = [
    '/status' => 'Status geral',
    '/session/default/status' => 'Status sessão default',
    '/session/comercial/status' => 'Status sessão comercial',
    '/qr?session=default' => 'QR Code default',
    '/qr?session=comercial' => 'QR Code comercial'
];

foreach ($endpoints_teste as $endpoint => $descricao) {
    echo "🔍 Testando $descricao...\n";
    
    // Testar canal 3000
    if ($status_vps['3000']['funcionando']) {
        $ch = curl_init("http://$vps_ip:3000$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status_3000 = ($http_code === 200) ? "✅" : "❌";
        echo "  Canal 3000: $status_3000 (HTTP $http_code)\n";
    } else {
        echo "  Canal 3000: ❌ (não responde)\n";
    }
    
    // Testar canal 3001
    if ($status_vps['3001']['funcionando']) {
        $ch = curl_init("http://$vps_ip:3001$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status_3001 = ($http_code === 200) ? "✅" : "❌";
        echo "  Canal 3001: $status_3001 (HTTP $http_code)\n";
    } else {
        echo "  Canal 3001: ❌ (não responde)\n";
    }
    
    echo "\n";
}

// ===== 6. RESUMO E PRÓXIMOS PASSOS =====
echo "6️⃣ RESUMO E PRÓXIMOS PASSOS\n";
echo "----------------------------\n";

echo "📊 STATUS ATUAL:\n";
foreach ($config_local['canais'] as $porta => $canal) {
    $status = $status_vps[$porta]['funcionando'] ? "✅" : "❌";
    echo "  $status {$canal['nome']} (Porta $porta)\n";
}

echo "\n🔧 CORREÇÕES APLICADAS: $correcoes_aplicadas\n";

if (!$status_vps['3001']['funcionando']) {
    echo "\n⚠️ AÇÕES NECESSÁRIAS:\n";
    echo "1. Conectar via SSH: ssh root@212.85.11.238\n";
    echo "2. Verificar processos: pm2 list\n";
    echo "3. Reiniciar VPS 3001: pm2 restart whatsapp-3001\n";
    echo "4. Ou reiniciar todos: pm2 restart all\n";
    echo "5. Salvar configuração: pm2 save\n";
    echo "6. Executar este script novamente\n";
}

echo "\n✅ Script concluído!\n";
?> 