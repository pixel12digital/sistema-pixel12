<?php
/**
 * 🔍 SCRIPT PARA VERIFICAR E FORÇAR SESSÕES
 * 
 * Este script verifica as sessões e força a criação de QR Codes
 */

echo "🔍 VERIFICANDO SESSÕES DOS CANAIS\n";
echo "================================\n\n";

$vps_ip = '212.85.11.238';
$canais = [3000, 3001];

foreach ($canais as $porta) {
    echo "🔄 VERIFICANDO CANAL $porta\n";
    echo "-------------------------\n";
    
    // 1. Verificar status detalhado
    echo "1. Status detalhado:\n";
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $status = json_decode($response, true);
    echo "   📋 Resposta completa: $response\n";
    
    // 2. Listar sessões disponíveis
    echo "2. Sessões disponíveis:\n";
    $ch = curl_init("http://$vps_ip:$porta/sessions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $sessions_response = curl_exec($ch);
    curl_close($ch);
    
    echo "   📋 Sessões: $sessions_response\n";
    
    // 3. Tentar diferentes endpoints para QR
    $qr_endpoints = [
        "/qr",
        "/qr?session=default", 
        "/qr?session=comercial",
        "/qr/default",
        "/qr/comercial"
    ];
    
    echo "3. Tentando diferentes endpoints QR:\n";
    foreach ($qr_endpoints as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $qr_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   📡 $endpoint: ";
        if ($http_code === 200) {
            $qr_data = json_decode($qr_response, true);
            if (isset($qr_data['qr']) && !empty($qr_data['qr'])) {
                echo "✅ QR ENCONTRADO!\n";
                echo "      📋 QR: " . substr($qr_data['qr'], 0, 50) . "...\n";
                break;
            } else {
                echo "⚠️ Sem QR: $qr_response\n";
            }
        } else {
            echo "❌ Erro ($http_code): $qr_response\n";
        }
    }
    
    // 4. Tentar forçar restart da sessão
    echo "4. Tentando restart/recreate:\n";
    $restart_endpoints = [
        "/session/restart",
        "/session/recreate", 
        "/restart",
        "/recreate"
    ];
    
    foreach ($restart_endpoints as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $restart_response = curl_exec($ch);
        $restart_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($restart_code === 200) {
            echo "   ✅ $endpoint: Executado com sucesso\n";
            echo "      📋 Resposta: $restart_response\n";
            
            // Aguardar e tentar QR novamente
            echo "   🔄 Aguardando 5 segundos e tentando QR...\n";
            sleep(5);
            
            $ch = curl_init("http://$vps_ip:$porta/qr");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $new_qr = curl_exec($ch);
            curl_close($ch);
            
            $qr_data = json_decode($new_qr, true);
            if (isset($qr_data['qr']) && !empty($qr_data['qr'])) {
                echo "   🎉 QR GERADO APÓS RESTART!\n";
                echo "      📋 QR: " . substr($qr_data['qr'], 0, 50) . "...\n";
            }
            break;
        }
    }
    
    echo "\n";
}

echo "🎯 INSTRUÇÕES:\n";
echo "==============\n";
echo "1. Se algum QR foi encontrado, use-o no WhatsApp\n";
echo "2. Se não, aguarde 30 segundos e execute este script novamente\n";
echo "3. Alternativamente, clique em 'Forçar Novo QR' no painel\n";
?> 