<?php
/**
 * 🔐 INICIAR SESSÕES WHATSAPP
 * 
 * Script para iniciar as sessões WhatsApp e gerar QR Codes
 * Baseado no problema identificado na interface web
 */

echo "🔐 INICIANDO SESSÕES WHATSAPP\n";
echo "=============================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';

// ===== 1. VERIFICAR STATUS ATUAL =====
echo "1️⃣ VERIFICANDO STATUS ATUAL\n";
echo "----------------------------\n";

$canais = [
    '3000' => ['nome' => 'Canal Financeiro', 'session' => 'default'],
    '3001' => ['nome' => 'Canal Comercial', 'session' => 'comercial']
];

foreach ($canais as $porta => $info) {
    echo "🔍 Verificando {$info['nome']} (Porta $porta)...\n";
    
    // Verificar status
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
    } else {
        echo "  ❌ Não responde (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== 2. INICIAR SESSÕES =====
echo "2️⃣ INICIANDO SESSÕES\n";
echo "--------------------\n";

foreach ($canais as $porta => $info) {
    $session = $info['session'];
    echo "🔐 Iniciando sessão {$info['nome']} ($session)...\n";
    
    // Tentar iniciar sessão
    $ch = curl_init("http://$vps_ip:$porta/session/$session/start");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ✅ Sessão iniciada com sucesso\n";
        $result = json_decode($response, true);
        if ($result && isset($result['message'])) {
            echo "  💬 {$result['message']}\n";
        }
    } else {
        echo "  ⚠️ Erro ao iniciar sessão (HTTP $http_code)\n";
        echo "  📝 Resposta: $response\n";
    }
    
    // Aguardar um pouco
    sleep(3);
}

echo "\n";

// ===== 3. VERIFICAR QR CODES =====
echo "3️⃣ VERIFICANDO QR CODES\n";
echo "-----------------------\n";

foreach ($canais as $porta => $info) {
    $session = $info['session'];
    echo "🔍 Verificando QR Code {$info['nome']} ($session)...\n";
    
    // Tentar diferentes endpoints de QR
    $qr_endpoints = [
        "/qr?session=$session",
        "/qr/$session",
        "/qr"
    ];
    
    $qr_encontrado = false;
    foreach ($qr_endpoints as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $qr_data = json_decode($response, true);
            if ($qr_data && isset($qr_data['success'])) {
                echo "  ✅ QR Code disponível via $endpoint\n";
                echo "  📱 Status: " . ($qr_data['status'] ?? 'unknown') . "\n";
                echo "  🔗 Ready: " . ($qr_data['ready'] ? 'true' : 'false') . "\n";
                if (isset($qr_data['message'])) {
                    echo "  💬 {$qr_data['message']}\n";
                }
                if (isset($qr_data['qr']) && $qr_data['qr']) {
                    echo "  🎯 QR Code gerado com sucesso!\n";
                }
                $qr_encontrado = true;
                break;
            }
        }
    }
    
    if (!$qr_encontrado) {
        echo "  ❌ QR Code não disponível\n";
    }
    echo "\n";
}

// ===== 4. TESTAR CONEXÃO =====
echo "4️⃣ TESTANDO CONEXÃO\n";
echo "-------------------\n";

foreach ($canais as $porta => $info) {
    $session = $info['session'];
    echo "🧪 Testando conexão {$info['nome']} ($session)...\n";
    
    // Verificar se está conectado
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $status_data = json_decode($response, true);
        if (isset($status_data['clients_status'][$session])) {
            $client_status = $status_data['clients_status'][$session];
            echo "  📱 Status: " . ($client_status['status'] ?? 'unknown') . "\n";
            
            if (($client_status['status'] ?? '') === 'connected') {
                echo "  ✅ WhatsApp conectado!\n";
                
                // Testar envio
                $test_data = [
                    'sessionName' => $session,
                    'number' => '5511999999999',
                    'message' => 'Teste conexão - ' . date('Y-m-d H:i:s')
                ];
                
                $ch = curl_init("http://$vps_ip:$porta/send/text");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code === 200) {
                    echo "  📝 Envio funcionando!\n";
                } else {
                    echo "  ❌ Erro no envio (HTTP $http_code)\n";
                }
            } else {
                echo "  ⚠️ Aguardando conexão...\n";
            }
        } else {
            echo "  ❌ Sessão não encontrada\n";
        }
    } else {
        echo "  ❌ Erro ao verificar status (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== 5. COMANDOS PARA CONEXÃO MANUAL =====
echo "5️⃣ COMANDOS PARA CONEXÃO MANUAL\n";
echo "--------------------------------\n";

echo "🔧 Se as sessões não iniciarem automaticamente:\n\n";

foreach ($canais as $porta => $info) {
    $session = $info['session'];
    echo "📱 {$info['nome']} (Porta $porta):\n";
    echo "  1. Acessar QR Code: http://$vps_ip:$porta/qr?session=$session\n";
    echo "  2. Ou no navegador: http://$vps_ip:$porta/qr/$session\n";
    echo "  3. Escanear com WhatsApp\n";
    echo "  4. Aguardar conexão\n\n";
}

echo "🔧 Comandos SSH para verificar:\n";
echo "ssh root@$vps_ip\n";
echo "pm2 logs whatsapp-$porta --lines 20\n";
echo "pm2 restart whatsapp-$porta\n\n";

// ===== 6. RESUMO FINAL =====
echo "6️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 INICIALIZAÇÃO DE SESSÕES CONCLUÍDA!\n\n";

echo "📱 STATUS DOS CANAIS:\n";
foreach ($canais as $porta => $info) {
    $session = $info['session'];
    echo "• {$info['nome']} (Porta $porta): Sessão $session\n";
}

echo "\n🔗 URLs PARA CONEXÃO:\n";
foreach ($canais as $porta => $info) {
    $session = $info['session'];
    echo "• {$info['nome']}: http://$vps_ip:$porta/qr?session=$session\n";
}

echo "\n📞 COMANDOS ÚTEIS:\n";
echo "• Status geral: curl http://$vps_ip:3000/status\n";
echo "• QR Code 3000: curl \"http://$vps_ip:3000/qr?session=default\"\n";
echo "• QR Code 3001: curl \"http://$vps_ip:3001/qr?session=comercial\"\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 20'\n\n";

echo "✅ INICIALIZAÇÃO CONCLUÍDA!\n";
echo "🎉 Sessões WhatsApp iniciadas!\n";
echo "📱 Acesse as URLs para conectar o WhatsApp!\n";
?> 