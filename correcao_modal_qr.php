<?php
/**
 * 🔧 CORREÇÃO DO MODAL QR CODE
 * 
 * Script para corrigir o problema do QR Code não disponível
 * no modal de conexão WhatsApp
 */

// Incluir configuração da VPS principal
require_once 'config_vps_3001_principal.php';

// Função para obter QR Code da VPS principal
function getQrCodeVps3001($session = 'default') {
    $vps_url = getVpsPrincipal();
    
    // Primeiro, tentar iniciar a sessão se necessário
    $ch = curl_init($vps_url . '/session/start/' . $session);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Aguardar um pouco para a sessão inicializar
    if ($http_code === 200) {
        sleep(2);
    }
    
    // Agora tentar obter o QR Code
    $ch = curl_init($vps_url . '/qr?session=' . $session);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $qr_data = json_decode($response, true);
        if ($qr_data && isset($qr_data['qr'])) {
            return [
                'success' => true,
                'qr' => $qr_data['qr'],
                'ready' => $qr_data['ready'] ?? false
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'QR Code não disponível',
        'http_code' => $http_code,
        'response' => $response
    ];
}

// Função para verificar status da VPS
function getStatusVps3001() {
    $vps_url = getVpsPrincipal();
    
    $ch = curl_init($vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// Função para forçar novo QR Code
function forceNewQrCode($session = 'default') {
    $vps_url = getVpsPrincipal();
    
    // Desconectar sessão atual (se existir)
    $ch = curl_init($vps_url . '/session/' . $session . '/disconnect');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    curl_close($ch);
    
    // Aguardar um pouco
    sleep(3);
    
    // Iniciar nova sessão
    return getQrCodeVps3001($session);
}

// Função para atualizar QR Code
function updateQrCode($session = 'default') {
    return getQrCodeVps3001($session);
}

// Exemplo de uso
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'status':
            $status = getStatusVps3001();
            echo json_encode($status);
            break;
            
        case 'qr':
            $session = $_GET['session'] ?? 'default';
            $qr_data = getQrCodeVps3001($session);
            echo json_encode($qr_data);
            break;
            
        case 'force_new':
            $session = $_GET['session'] ?? 'default';
            $qr_data = forceNewQrCode($session);
            echo json_encode($qr_data);
            break;
            
        case 'update':
            $session = $_GET['session'] ?? 'default';
            $qr_data = updateQrCode($session);
            echo json_encode($qr_data);
            break;
            
        default:
            echo json_encode(['error' => 'Ação não reconhecida']);
    }
    exit;
}
?>