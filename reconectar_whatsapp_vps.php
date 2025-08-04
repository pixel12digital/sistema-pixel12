<?php
/**
 * 🔄 RECONECTAR WHATSAPP NO VPS
 * 
 * Força reconexão do WhatsApp Web.js no VPS
 */

echo "=== 🔄 RECONECTAR WHATSAPP NO VPS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

$vps_ip = "212.85.11.238";

// ===== 1. VERIFICAR STATUS ATUAL =====
echo "1. 📊 VERIFICANDO STATUS ATUAL:\n";

$canais = [3000, 3001];
$status_canais = [];

foreach ($canais as $porta) {
    echo "   🔍 Verificando Canal $porta...\n";
    
    $status_url = "http://$vps_ip:$porta/status";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $status_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        $ready = $data['ready'] ?? false;
        $connected = $data['clients_status']['default']['status'] ?? 'unknown';
        
        echo "      📡 VPS: ONLINE (HTTP $http_code)\n";
        echo "      📱 WhatsApp: " . ($ready ? "CONECTADO" : "DESCONECTADO") . " ($connected)\n";
        
        $status_canais[$porta] = [
            'vps_online' => true,
            'whatsapp_ready' => $ready,
            'status' => $connected
        ];
    } else {
        echo "      ❌ VPS: OFFLINE (HTTP $http_code)\n";
        $status_canais[$porta] = [
            'vps_online' => false,
            'whatsapp_ready' => false,
            'status' => 'offline'
        ];
    }
    echo "\n";
}

// ===== 2. TENTAR OBTER QR CODE =====
echo "2. 📱 TENTANDO OBTER QR CODE:\n";

foreach ($canais as $porta) {
    if (!$status_canais[$porta]['vps_online']) {
        echo "   ⚠️ Canal $porta offline, pulando...\n";
        continue;
    }
    
    echo "   🔍 Obtendo QR Code do Canal $porta...\n";
    
    $qr_url = "http://$vps_ip:$porta/qr";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $qr_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $qr_response = curl_exec($ch);
    $qr_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($qr_code == 200) {
        $qr_data = json_decode($qr_response, true);
        
        if (isset($qr_data['qr']) && !empty($qr_data['qr'])) {
            echo "      ✅ QR Code disponível!\n";
            echo "      📄 QR: " . substr($qr_data['qr'], 0, 50) . "...\n";
            echo "      🔗 Acesse: http://$vps_ip:$porta/qr para escanear\n";
        } else {
            echo "      ✅ WhatsApp já conectado (sem QR)\n";
        }
    } else {
        echo "      ❌ Erro ao obter QR (HTTP $qr_code)\n";
    }
    echo "\n";
}

// ===== 3. FORÇAR RESTART DOS SERVIÇOS =====
echo "3. 🔄 COMANDOS PARA RESTART VIA SSH:\n";

echo "   🔧 Execute estes comandos no VPS:\n";
echo "   ssh root@$vps_ip\n\n";

echo "   📊 Verificar serviços:\n";
echo "   pm2 list\n";
echo "   pm2 logs --lines 10\n\n";

echo "   🔄 Restart dos serviços WhatsApp:\n";
echo "   pm2 restart whatsapp-3000\n";
echo "   pm2 restart whatsapp-3001\n";
echo "   pm2 restart all\n\n";

echo "   🧹 Limpar sessões se necessário:\n";
echo "   cd /var/whatsapp-api\n";
echo "   rm -rf .wwebjs_auth/\n";
echo "   rm -rf .wwebjs_cache/\n";
echo "   pm2 restart all\n\n";

// ===== 4. CRIAR SCRIPT DE RECONEXÃO AUTOMÁTICA =====
echo "4. 🤖 CRIANDO SCRIPT DE RECONEXÃO AUTOMÁTICA:\n";

$script_reconexao = '#!/bin/bash
# Script de reconexão automática WhatsApp VPS
# Execute via: ssh root@212.85.11.238 "bash reconectar_whatsapp.sh"

echo "=== RECONEXÃO AUTOMÁTICA WHATSAPP ==="
echo "Data: $(date)"

echo "1. Verificando PM2..."
pm2 list

echo "2. Parando serviços WhatsApp..."
pm2 stop whatsapp-3000 2>/dev/null || echo "whatsapp-3000 não estava rodando"
pm2 stop whatsapp-3001 2>/dev/null || echo "whatsapp-3001 não estava rodando"

echo "3. Limpando cache/sessões antigas..."
cd /var/whatsapp-api
rm -rf .wwebjs_auth/ 2>/dev/null || echo "Sem sessões antigas"
rm -rf .wwebjs_cache/ 2>/dev/null || echo "Sem cache antigo"

echo "4. Reiniciando serviços..."
pm2 start ecosystem.config.js 2>/dev/null || pm2 restart all

echo "5. Aguardando inicialização..."
sleep 10

echo "6. Verificando status final..."
pm2 list

echo "7. Testando endpoints..."
curl -s http://localhost:3000/status | jq .ready 2>/dev/null || echo "Canal 3000: Verificar manualmente"
curl -s http://localhost:3001/status | jq .ready 2>/dev/null || echo "Canal 3001: Verificar manualmente"

echo "=== RECONEXÃO CONCLUÍDA ==="
echo "Acesse http://212.85.11.238:3000/qr para escanear QR Code"
';

file_put_contents('reconectar_whatsapp.sh', $script_reconexao);
echo "   ✅ Script criado: reconectar_whatsapp.sh\n";

// ===== 5. TESTAR RECONEXÃO VIA CURL =====
echo "5. 🧪 TESTANDO RECONEXÃO VIA API:\n";

foreach ($canais as $porta) {
    if (!$status_canais[$porta]['vps_online']) continue;
    
    echo "   🔄 Tentando restart Canal $porta via API...\n";
    
    // Alguns endpoints possíveis para restart
    $restart_endpoints = [
        "/restart",
        "/session/restart", 
        "/logout",
        "/session/logout"
    ];
    
    foreach ($restart_endpoints as $endpoint) {
        $restart_url = "http://$vps_ip:$porta$endpoint";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $restart_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $restart_response = curl_exec($ch);
        $restart_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($restart_code == 200) {
            echo "      ✅ $endpoint funcionou (HTTP $restart_code)\n";
            echo "      📄 Resposta: " . substr($restart_response, 0, 100) . "...\n";
            break;
        }
    }
}

echo "\n";

// ===== 6. INSTRUÇÕES PARA RECONEXÃO MANUAL =====
echo "6. 📱 INSTRUÇÕES PARA RECONEXÃO MANUAL:\n";

echo "   🔧 OPÇÃO 1 - VIA SSH (RECOMENDADO):\n";
echo "   1. ssh root@$vps_ip\n";
echo "   2. pm2 restart all\n";
echo "   3. Aguarde 30 segundos\n";
echo "   4. Acesse: http://$vps_ip:3000/qr\n";
echo "   5. Escaneie QR Code no WhatsApp\n\n";

echo "   🔧 OPÇÃO 2 - VIA SCRIPT AUTOMÁTICO:\n";
echo "   1. scp reconectar_whatsapp.sh root@$vps_ip:/root/\n";
echo "   2. ssh root@$vps_ip \"chmod +x /root/reconectar_whatsapp.sh\"\n";
echo "   3. ssh root@$vps_ip \"/root/reconectar_whatsapp.sh\"\n\n";

echo "   🔧 OPÇÃO 3 - VIA PAINEL WEB:\n";
echo "   1. Acesse: https://app.pixel12digital.com.br/painel/comunicacao.php\n";
echo "   2. Clique em 'Conectar' nos canais\n";
echo "   3. Escaneie QR Code quando aparecer\n\n";

// ===== 7. VERIFICAÇÃO FINAL =====
echo "7. 🎯 APÓS RECONECTAR, TESTE:\n";

echo "   📱 TESTE REAL:\n";
echo "   1. Envie mensagem para: 554797146908\n";
echo "   2. Digite: \"teste conexão\"\n";
echo "   3. Ana deve responder no WhatsApp\n\n";

echo "   🔍 MONITORAR:\n";
echo "   - Status: http://$vps_ip:3000/status\n";
echo "   - QR Code: http://$vps_ip:3000/qr\n";
echo "   - Logs: ssh root@$vps_ip \"pm2 logs --lines 20\"\n\n";

echo "   📊 STATUS ATUAL DOS CANAIS:\n";
foreach ($status_canais as $porta => $status) {
    $icon = $status['whatsapp_ready'] ? "✅" : "❌";
    echo "   $icon Canal $porta: " . ($status['whatsapp_ready'] ? "CONECTADO" : "DESCONECTADO") . "\n";
}

echo "\n=== 🎯 PRÓXIMAS AÇÕES ===\n";
echo "1. Executar reconexão via SSH\n";
echo "2. Escanear QR Code no WhatsApp\n";
echo "3. Testar envio de mensagem\n";
echo "4. Verificar se Ana responde no WhatsApp\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?> 