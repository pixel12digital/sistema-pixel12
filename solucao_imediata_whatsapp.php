<?php
/**
 * 🚀 SOLUÇÃO IMEDIATA PARA CANAIS WHATSAPP DESCONECTADOS
 * 
 * Script para resolver o problema identificado na interface web
 */

echo "🚀 SOLUÇÃO IMEDIATA PARA CANAIS WHATSAPP DESCONECTADOS\n";
echo "====================================================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';

// ===== PROBLEMA IDENTIFICADO =====
echo "🔍 PROBLEMA IDENTIFICADO:\n";
echo "--------------------------\n";
echo "• Canal 36 (Porta 3000): DESCONECTADO - 'Sessão não encontrada'\n";
echo "• Canal 37 (Porta 3001): DESCONECTADO\n";
echo "• QR Code: 'Não disponível'\n";
echo "• Endpoints de sessão: Não existem na API atual\n\n";

// ===== SOLUÇÃO IMEDIATA =====
echo "🔧 SOLUÇÃO IMEDIATA:\n";
echo "--------------------\n";

echo "1️⃣ REINICIAR SERVIÇOS NA VPS:\n";
echo "ssh root@$vps_ip\n";
echo "pm2 restart whatsapp-3000\n";
echo "pm2 restart whatsapp-3001\n";
echo "pm2 save\n\n";

echo "2️⃣ VERIFICAR SE A API ESTÁ CORRETA:\n";
echo "ls -la /var/whatsapp-api/\n";
echo "cat /var/whatsapp-api/whatsapp-api-server.js | head -20\n\n";

echo "3️⃣ VERIFICAR LOGS:\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "4️⃣ TESTAR QR CODES MANUALMENTE:\n";
echo "• Canal 3000: http://$vps_ip:3000/qr?session=default\n";
echo "• Canal 3001: http://$vps_ip:3001/qr?session=comercial\n\n";

echo "5️⃣ SE NÃO FUNCIONAR, MIGRAR PARA API CORRETA:\n";
echo "bash migrar_canal_3001.sh\n\n";

// ===== VERIFICAR STATUS ATUAL =====
echo "📊 VERIFICANDO STATUS ATUAL:\n";
echo "----------------------------\n";

$canais = [
    '3000' => 'Canal Financeiro',
    '3001' => 'Canal Comercial'
];

foreach ($canais as $porta => $nome) {
    echo "🔍 Verificando $nome (Porta $porta)...\n";
    
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

// ===== TESTAR QR CODES =====
echo "🎯 TESTANDO QR CODES:\n";
echo "---------------------\n";

foreach ($canais as $porta => $nome) {
    $session = ($porta === '3001') ? 'comercial' : 'default';
    echo "🔍 Testando QR Code $nome ($session)...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/qr?session=$session");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $qr_data = json_decode($response, true);
        if ($qr_data && isset($qr_data['success'])) {
            echo "  ✅ QR Code disponível\n";
            echo "  📱 Status: " . ($qr_data['status'] ?? 'unknown') . "\n";
            echo "  🔗 Ready: " . ($qr_data['ready'] ? 'true' : 'false') . "\n";
            if (isset($qr_data['message'])) {
                echo "  💬 {$qr_data['message']}\n";
            }
        } else {
            echo "  ❌ QR Code não disponível\n";
        }
    } else {
        echo "  ❌ Erro ao acessar QR Code (HTTP $http_code)\n";
    }
    echo "\n";
}

// ===== COMANDOS DE CORREÇÃO =====
echo "🔧 COMANDOS DE CORREÇÃO:\n";
echo "------------------------\n";

echo "1️⃣ CONECTAR NA VPS:\n";
echo "ssh root@$vps_ip\n\n";

echo "2️⃣ REINICIAR TODOS OS SERVIÇOS:\n";
echo "pm2 restart all\n";
echo "pm2 save\n\n";

echo "3️⃣ VERIFICAR STATUS:\n";
echo "pm2 status\n";
echo "curl http://$vps_ip:3000/status\n";
echo "curl http://$vps_ip:3001/status\n\n";

echo "4️⃣ VERIFICAR LOGS:\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "5️⃣ SE NECESSÁRIO, MIGRAR CANAL 3001:\n";
echo "bash migrar_canal_3001.sh\n\n";

// ===== URLs PARA TESTE MANUAL =====
echo "🌐 URLs PARA TESTE MANUAL:\n";
echo "---------------------------\n";

echo "📱 QR CODES:\n";
echo "• Canal 3000: http://$vps_ip:3000/qr?session=default\n";
echo "• Canal 3001: http://$vps_ip:3001/qr?session=comercial\n\n";

echo "📊 STATUS:\n";
echo "• Canal 3000: http://$vps_ip:3000/status\n";
echo "• Canal 3001: http://$vps_ip:3001/status\n\n";

echo "🔗 WEBHOOKS:\n";
echo "• Canal 3000: http://$vps_ip:3000/webhook/config\n";
echo "• Canal 3001: http://$vps_ip:3001/webhook/config\n\n";

// ===== RESUMO FINAL =====
echo "📋 RESUMO FINAL:\n";
echo "----------------\n";

echo "🎯 PROBLEMA:\n";
echo "• Canais WhatsApp desconectados\n";
echo "• QR Codes não disponíveis\n";
echo "• Sessões não encontradas\n\n";

echo "🔧 SOLUÇÃO:\n";
echo "• Reiniciar serviços na VPS\n";
echo "• Verificar se API está correta\n";
echo "• Testar QR Codes manualmente\n";
echo "• Se necessário, migrar para API correta\n\n";

echo "📞 COMANDOS FINAIS:\n";
echo "• Reiniciar: ssh root@$vps_ip 'pm2 restart all'\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 30'\n";
echo "• QR 3000: http://$vps_ip:3000/qr?session=default\n";
echo "• QR 3001: http://$vps_ip:3001/qr?session=comercial\n\n";

echo "✅ SOLUÇÃO IMEDIATA APRESENTADA!\n";
echo "🎉 Execute os comandos para resolver o problema!\n";
?> 