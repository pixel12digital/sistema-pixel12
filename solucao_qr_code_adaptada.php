<?php
/**
 * 🎯 SOLUÇÃO ADAPTADA - QR CODE
 * 
 * Solução adaptada para a estrutura real da VPS 3001
 * Gerado automaticamente em 2025-08-05 08:12:49
 */

// Incluir configuração da VPS principal
require_once 'config_vps_3001_principal.php';

// Função para verificar status real da VPS
function getStatusRealVps3001() {
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

// Função para verificar se QR Code está disponível
function isQrCodeAvailable($session = 'default') {
    $status = getStatusRealVps3001();
    
    if ($status && isset($status['clients_status'][$session])) {
        $client_status = $status['clients_status'][$session];
        return $client_status['ready'] && $client_status['hasQR'];
    }
    
    return false;
}

// Função para aguardar QR Code ficar disponível
function waitForQrCode($session = 'default', $max_attempts = 10) {
    for ($i = 0; $i < $max_attempts; $i++) {
        if (isQrCodeAvailable($session)) {
            return true;
        }
        
        // Aguardar 2 segundos antes da próxima tentativa
        sleep(2);
    }
    
    return false;
}

// Função para obter QR Code (adaptada)
function getQrCodeAdaptado($session = 'default') {
    $vps_url = getVpsPrincipal();
    
    // Primeiro verificar se QR Code está disponível
    if (!isQrCodeAvailable($session)) {
        // Tentar aguardar QR Code ficar disponível
        if (!waitForQrCode($session)) {
            return [
                'success' => false,
                'error' => 'QR Code não está disponível. Aguarde alguns segundos e tente novamente.',
                'suggestion' => 'A sessão pode estar inicializando. Tente novamente em 10-30 segundos.'
            ];
        }
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
        'error' => 'Não foi possível obter o QR Code',
        'http_code' => $http_code,
        'response' => $response
    ];
}

// Função para forçar reinicialização da sessão
function forceSessionRestart($session = 'default') {
    $vps_url = getVpsPrincipal();
    
    // Tentar desconectar sessão atual
    $ch = curl_init($vps_url . '/session/' . $session . '/disconnect');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    curl_close($ch);
    
    // Aguardar um pouco
    sleep(5);
    
    // Aguardar QR Code ficar disponível
    if (waitForQrCode($session, 15)) {
        return getQrCodeAdaptado($session);
    }
    
    return [
        'success' => false,
        'error' => 'Não foi possível reinicializar a sessão',
        'suggestion' => 'Tente novamente ou reinicie o processo no servidor'
    ];
}

// Função para obter informações de debug
function getDebugInfo() {
    $status = getStatusRealVps3001();
    
    if ($status) {
        return [
            'success' => true,
            'vps_ready' => $status['ready'] ?? false,
            'vps_status' => $status['status'] ?? 'unknown',
            'vps_port' => $status['port'] ?? 'N/A',
            'last_session' => $status['lastSession'] ?? 'N/A',
            'clients_status' => $status['clients_status'] ?? []
        ];
    }
    
    return [
        'success' => false,
        'error' => 'VPS não está respondendo'
    ];
}

// Endpoint para requisições AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');
    
    $action = $_GET['action'];
    $session = $_GET['session'] ?? 'default';
    
    switch ($action) {
        case 'status':
            echo json_encode(getDebugInfo());
            break;
            
        case 'qr':
            echo json_encode(getQrCodeAdaptado($session));
            break;
            
        case 'force_restart':
            echo json_encode(forceSessionRestart($session));
            break;
            
        case 'wait_qr':
            $result = waitForQrCode($session);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'QR Code disponível' : 'QR Code não disponível após aguardar'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Ação não reconhecida',
                'available_actions' => ['status', 'qr', 'force_restart', 'wait_qr']
            ]);
    }
    exit;
}
?>