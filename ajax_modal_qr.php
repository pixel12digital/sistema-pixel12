<?php
/**
 * 🔄 AJAX PARA MODAL QR CODE
 * 
 * Endpoint AJAX para o modal de conexão WhatsApp
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'correcao_modal_qr.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$session = $_GET['session'] ?? $_POST['session'] ?? 'default';
$porta = $_GET['porta'] ?? $_POST['porta'] ?? '3001';

// Log da requisição
error_log("[AJAX MODAL] Action: $action, Session: $session, Porta: $porta");

try {
    switch ($action) {
        case 'status':
            $status = getStatusVps3001();
            if ($status) {
                echo json_encode([
                    'success' => true,
                    'data' => $status
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'VPS não está respondendo'
                ]);
            }
            break;
            
        case 'qr':
            $qr_data = getQrCodeVps3001($session);
            echo json_encode($qr_data);
            break;
            
        case 'force_new':
            $qr_data = forceNewQrCode($session);
            echo json_encode($qr_data);
            break;
            
        case 'update':
            $qr_data = updateQrCode($session);
            echo json_encode($qr_data);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Ação não reconhecida',
                'available_actions' => ['status', 'qr', 'force_new', 'update']
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?>