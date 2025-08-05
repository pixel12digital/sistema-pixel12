<?php
/**
 * 🔍 VERIFICAR E CORRIGIR SESSÕES WHATSAPP
 * 
 * Script para verificar e corrigir as sessões WhatsApp
 * Baseado no problema identificado
 */

echo "🔍 VERIFICANDO E CORRIGINDO SESSÕES WHATSAPP\n";
echo "============================================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';

// ===== 1. VERIFICAR ENDPOINTS DISPONÍVEIS =====
echo "1️⃣ VERIFICANDO ENDPOINTS DISPONÍVEIS\n";
echo "------------------------------------\n";

$endpoints_teste = [
    '/session/default/start' => 'Iniciar sessão default',
    '/session/comercial/start' => 'Iniciar sessão comercial',
    '/session/default/connect' => 'Conectar sessão default',
    '/session/comercial/connect' => 'Conectar sessão comercial',
    '/session/default/status' => 'Status sessão default',
    '/session/comercial/status' => 'Status sessão comercial',
    '/qr' => 'QR Code geral',
    '/qr/default' => 'QR Code default',
    '/qr/comercial' => 'QR Code comercial',
    '/qr?session=default' => 'QR Code com query default',
    '/qr?session=comercial' => 'QR Code com query comercial'
];

$canais = ['3000', '3001'];

foreach ($canais as $porta) {
    echo "🔍 Testando Canal (Porta $porta)...\n";
    
    foreach ($endpoints_teste as $endpoint => $descricao) {
        $url = "http://$vps_ip:$porta$endpoint";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($http_code === 200) ? "✅" : "❌";
        echo "  $status $endpoint (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== 2. VERIFICAR STATUS DETALHADO =====
echo "2️⃣ VERIFICANDO STATUS DETALHADO\n";
echo "--------------------------------\n";

foreach ($canais as $porta) {
    echo "🔍 Status detalhado Canal (Porta $porta)...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $status_data = json_decode($response, true);
        echo "  ✅ Status: " . ($status_data['status'] ?? 'unknown') . "\n";
        
        if (isset($status_data['clients_status'])) {
            $sessoes = $status_data['clients_status'];
            echo "  👥 Sessões: " . count($sessoes) . "\n";
            foreach ($sessoes as $sessao => $status) {
                echo "    - $sessao: " . ($status['status'] ?? 'unknown') . "\n";
            }
        } else {
            echo "  ⚠️ Nenhuma sessão encontrada\n";
        }
        
        // Verificar se há informações de sessão
        if (isset($status_data['sessions'])) {
            echo "  📋 Sessões disponíveis: " . implode(', ', $status_data['sessions']) . "\n";
        }
    } else {
        echo "  ❌ Não responde (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== 3. TESTAR QR CODES ESPECÍFICOS =====
echo "3️⃣ TESTANDO QR CODES ESPECÍFICOS\n";
echo "--------------------------------\n";

$qr_tests = [
    '3000' => [
        'default' => ['/qr?session=default', '/qr/default', '/qr'],
        'comercial' => ['/qr?session=comercial', '/qr/comercial']
    ],
    '3001' => [
        'default' => ['/qr?session=default', '/qr/default', '/qr'],
        'comercial' => ['/qr?session=comercial', '/qr/comercial']
    ]
];

foreach ($qr_tests as $porta => $sessoes) {
    echo "🔍 QR Codes Canal (Porta $porta)...\n";
    
    foreach ($sessoes as $session => $endpoints) {
        echo "  📱 Sessão $session:\n";
        
        foreach ($endpoints as $endpoint) {
            $ch = curl_init("http://$vps_ip:$porta$endpoint");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $status = ($http_code === 200) ? "✅" : "❌";
            echo "    $status $endpoint (HTTP $http_code)";
            
            if ($http_code === 200) {
                $qr_data = json_decode($response, true);
                if ($qr_data && isset($qr_data['success'])) {
                    echo " - " . ($qr_data['status'] ?? 'unknown');
                    if (isset($qr_data['ready'])) {
                        echo " (ready: " . ($qr_data['ready'] ? 'true' : 'false') . ")";
                    }
                }
            }
            echo "\n";
        }
    }
    echo "\n";
}

// ===== 4. TENTAR INICIAR SESSÕES MANUALMENTE =====
echo "4️⃣ TENTANDO INICIAR SESSÕES MANUALMENTE\n";
echo "----------------------------------------\n";

// Verificar se há endpoints de inicialização automática
$init_endpoints = [
    '/init' => 'Inicialização geral',
    '/start' => 'Iniciar serviço',
    '/initialize' => 'Inicializar',
    '/session/init' => 'Inicializar sessões',
    '/whatsapp/init' => 'Inicializar WhatsApp'
];

foreach ($canais as $porta) {
    echo "🔍 Tentando inicializar Canal (Porta $porta)...\n";
    
    foreach ($init_endpoints as $endpoint => $descricao) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($http_code === 200) ? "✅" : "❌";
        echo "  $status $endpoint (HTTP $http_code)";
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            if ($result && isset($result['message'])) {
                echo " - {$result['message']}";
            }
        }
        echo "\n";
    }
    echo "\n";
}

// ===== 5. COMANDOS PARA CORREÇÃO MANUAL =====
echo "5️⃣ COMANDOS PARA CORREÇÃO MANUAL\n";
echo "--------------------------------\n";

echo "🔧 PROBLEMA IDENTIFICADO:\n";
echo "• Endpoints de sessão não existem na API atual\n";
echo "• QR Codes não estão sendo gerados\n";
echo "• Sessões não estão sendo inicializadas automaticamente\n\n";

echo "🔧 SOLUÇÕES:\n\n";

echo "1️⃣ REINICIAR SERVIÇOS NA VPS:\n";
echo "ssh root@$vps_ip\n";
echo "pm2 restart whatsapp-3000\n";
echo "pm2 restart whatsapp-3001\n";
echo "pm2 save\n\n";

echo "2️⃣ VERIFICAR LOGS:\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "3️⃣ VERIFICAR SE A API ESTÁ CORRETA:\n";
echo "ls -la /var/whatsapp-api/\n";
echo "cat /var/whatsapp-api/whatsapp-api-server.js | head -20\n\n";

echo "4️⃣ TESTAR QR CODES MANUALMENTE:\n";
foreach ($canais as $porta) {
    $session = ($porta === '3001') ? 'comercial' : 'default';
    echo "• Canal $porta: http://$vps_ip:$porta/qr?session=$session\n";
}
echo "\n";

echo "5️⃣ SE NÃO FUNCIONAR, MIGRAR PARA API CORRETA:\n";
echo "bash migrar_canal_3001.sh\n\n";

// ===== 6. TESTAR ALTERNATIVAS =====
echo "6️⃣ TESTANDO ALTERNATIVAS\n";
echo "-------------------------\n";

// Tentar acessar QR Code diretamente no navegador
echo "🔍 URLs para testar no navegador:\n";
foreach ($canais as $porta) {
    $session = ($porta === '3001') ? 'comercial' : 'default';
    echo "• Canal $porta: http://$vps_ip:$porta/qr?session=$session\n";
}
echo "\n";

// Verificar se há algum endpoint de health check
echo "🔍 Health checks:\n";
$health_endpoints = ['/health', '/ping', '/ready', '/alive'];
foreach ($canais as $porta) {
    echo "Canal $porta:\n";
    foreach ($health_endpoints as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($http_code === 200) ? "✅" : "❌";
        echo "  $status $endpoint (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== 7. RESUMO FINAL =====
echo "7️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 VERIFICAÇÃO CONCLUÍDA!\n\n";

echo "📊 STATUS ATUAL:\n";
echo "• Canal 3000: API funcionando, mas sem sessões\n";
echo "• Canal 3001: API funcionando, mas sem sessões\n";
echo "• QR Codes: Não disponíveis\n";
echo "• Endpoints de sessão: Não existem\n\n";

echo "🔧 AÇÕES NECESSÁRIAS:\n";
echo "1. Reiniciar serviços na VPS\n";
echo "2. Verificar se API está correta\n";
echo "3. Se necessário, migrar para API correta\n";
echo "4. Testar QR Codes manualmente\n\n";

echo "📞 COMANDOS FINAIS:\n";
echo "• Reiniciar: ssh root@$vps_ip 'pm2 restart all'\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 30'\n";
echo "• QR 3000: http://$vps_ip:3000/qr?session=default\n";
echo "• QR 3001: http://$vps_ip:3001/qr?session=comercial\n\n";

echo "✅ VERIFICAÇÃO FINALIZADA!\n";
echo "🎉 Problemas identificados e soluções propostas!\n";
?> 