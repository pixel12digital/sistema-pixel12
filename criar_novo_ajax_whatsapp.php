<?php
// Garantir que nenhum output seja enviado antes do JSON
ob_start();

// Log de execução do script
file_put_contents(__DIR__.'/debug_ajax_whatsapp.log', date('Y-m-d H:i:s')." - ajax_whatsapp.php executado | Action: ".($_GET['action'] ?? 'não definida')."\n", FILE_APPEND);

// Proxy PHP para contornar CORS - WhatsApp API
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config.php';
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $canal_id = $_GET['canal_id'] ?? $_POST['canal_id'] ?? null;
    $porta = $_GET['porta'] ?? $_POST['porta'] ?? null;
    
    // CORREÇÃO: Determinar a porta baseada no canal_id OU no parâmetro porta
    if (!$porta && $canal_id) {
        // Buscar a porta do canal no banco de dados
        require_once 'db.php';
        $sql = "SELECT porta FROM canais_comunicacao WHERE id = ? AND tipo = 'whatsapp'";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $canal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $canal = $result->fetch_assoc();
            if ($canal['porta']) {
                $porta = $canal['porta'];
            }
        }
        $stmt->close();
    }
    
    // CORREÇÃO: Se ainda não tem porta, usar padrão
    if (!$porta) {
        $porta = '3000'; // Padrão porta 3000 (financeiro)
    }
    
    // CORREÇÃO: Usar URL baseada na porta
    if ($porta == '3001' || $porta == 3001) {
        $vps_url = 'http://212.85.11.238:3001'; // Canal comercial
    } else {
        $vps_url = WHATSAPP_ROBOT_URL; // Canal financeiro (padrão)
    }
    
    // Log debug para depuração
    error_log("[WhatsApp Ajax Debug] Action: $action, Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . ", VPS URL: $vps_url, Canal ID: $canal_id, Porta: $porta");
    
    // Se não há action, retornar erro
    if (empty($action)) {
        ob_clean();
        echo json_encode([
            'error' => 'Nenhuma ação especificada',
            'available_actions' => ['status', 'qr', 'logout', 'send', 'test_connection'],
            'debug' => [
                'get_params' => $_GET,
                'post_params' => array_keys($_POST),
                'method' => $_SERVER['REQUEST_METHOD']
            ]
        ]);
        exit;
    }
    
    // Limpar qualquer output anterior
    ob_clean();
    
    switch ($action) {
        case 'status':
            // CORREÇÃO: Consultar diretamente a sessão específica baseada na porta
            $sessionName = 'default'; // Padrão para porta 3000
            if ($porta == '3001' || $porta == 3001) {
                $sessionName = 'comercial'; // Para porta 3001
            }
            
            // Log adicional para debug
            error_log("[WhatsApp Status Debug] Porta: $porta, Session: $sessionName, VPS URL: $vps_url");
            
            // Consultar status da sessão específica
            $endpoint = "/session/{$sessionName}/status";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Aumentar timeout para 10 segundos
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout de conexão de 5 segundos
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            // Log da resposta para debug
            error_log("[WhatsApp Status Response] HTTP Code: $http_code, Response: $response, Curl Error: $curl_error");
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                echo json_encode([
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'message' => $data['message'] ?? 'Status obtido com sucesso',
                    'session' => $sessionName,
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'endpoint_used' => $endpoint
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao obter status da sessão',
                    'http_code' => $http_code,
                    'curl_error' => $curl_error,
                    'session_attempted' => $sessionName,
                    'debug' => [
                        'endpoint' => $endpoint,
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            }
            break;
        
        case 'qr':
            // Determinar a sessão baseada na porta
            $sessionName = 'default'; // Padrão para porta 3000
            if ($porta == '3001' || $porta == 3001) {
                $sessionName = 'comercial'; // Para porta 3001
            }
            
            // Log adicional para debug
            error_log("[WhatsApp QR Debug] Porta: $porta, Session: $sessionName, VPS URL: $vps_url");
            
            // CORREÇÃO: Usar endpoint de status geral em vez de QR específico
            $status_endpoint = "/status";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $status_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Log da resposta para debug
            error_log("[WhatsApp QR Response] HTTP Code: $http_code, Response: $response");
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                
                // Extrair QR do status geral
                $qr = null;
                $message = 'QR Code não disponível';
                
                if (isset($data['qr']) && !empty($data['qr'])) {
                    // QR disponível no status geral
                    $qr = $data['qr'];
                    $message = 'QR Code disponível';
                } elseif (isset($data['clients_status'][$sessionName]['qr']) && !empty($data['clients_status'][$sessionName]['qr'])) {
                    // QR disponível na sessão específica
                    $qr = $data['clients_status'][$sessionName]['qr'];
                    $message = $data['clients_status'][$sessionName]['message'] ?? 'QR Code disponível';
                }
                
                echo json_encode([
                    'success' => !empty($qr),
                    'qr' => $qr,
                    'message' => $message,
                    'expires_in' => 60,
                    'performance' => [
                        'latency_ms' => 0
                    ],
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'endpoint_used' => $status_endpoint,
                        'qr_found_in_status' => isset($data['qr']),
                        'qr_found_in_session' => isset($data['clients_status'][$sessionName]['qr'])
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao obter status do WhatsApp',
                    'http_code' => $http_code,
                    'debug' => [
                        'endpoint' => $status_endpoint,
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            }
            break;
        
        case 'logout':
            // Determinar a sessão baseada na porta
            $sessionName = 'default'; // Padrão para porta 3000
            if ($porta == '3001' || $porta == 3001) {
                $sessionName = 'comercial'; // Para porta 3001
            }
            
            // Log adicional para debug
            error_log("[WhatsApp Logout Debug] Porta: $porta, Session: $sessionName, VPS URL: $vps_url");
            
            $endpoint = "/session/{$sessionName}/disconnect";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POST, true);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Log da resposta para debug
            error_log("[WhatsApp Logout Response] HTTP Code: $http_code, Response: $response");
            
            if ($http_code == 200) {
                echo json_encode([
                    'success' => true,
                    'message' => "WhatsApp desconectado com sucesso (sessão: {$sessionName})",
                    'session_disconnected' => $sessionName,
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'endpoint_used' => $endpoint
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao desconectar WhatsApp',
                    'http_code' => $http_code,
                    'session_attempted' => $sessionName,
                    'debug' => [
                        'endpoint' => $endpoint,
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            }
            break;
        
        case 'send':
            // Implementação do envio de mensagens
            $numero = $_POST['numero'] ?? '';
            $mensagem = $_POST['mensagem'] ?? '';
            
            if (empty($numero) || empty($mensagem)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Número e mensagem são obrigatórios'
                ]);
                break;
            }
            
            // Determinar a sessão baseada na porta
            $sessionName = 'default'; // Padrão para porta 3000
            if ($porta == '3001' || $porta == 3001) {
                $sessionName = 'comercial'; // Para porta 3001
            }
            
            $endpoint = "/send/text";
            $post_data = [
                'sessionName' => $sessionName,
                'number' => $numero,
                'message' => $mensagem
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso',
                    'session' => $sessionName,
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'endpoint_used' => $endpoint
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao enviar mensagem',
                    'http_code' => $http_code,
                    'debug' => [
                        'endpoint' => $endpoint,
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            }
            break;
        
        case 'test_connection':
            // Teste de conexão simples
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Conexão com VPS estabelecida',
                    'vps_url' => $vps_url,
                    'debug' => [
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao conectar com VPS',
                    'http_code' => $http_code,
                    'vps_url' => $vps_url,
                    'debug' => [
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            }
            break;
        
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Ação não reconhecida',
                'available_actions' => ['status', 'qr', 'logout', 'send', 'test_connection'],
                'debug' => [
                    'action_received' => $action,
                    'porta_used' => $porta,
                    'canal_id_received' => $canal_id
                ]
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("[WhatsApp Ajax Error] " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'debug' => [
            'exception' => $e->getMessage(),
            'porta_used' => $porta ?? 'não definida',
            'canal_id_received' => $canal_id ?? 'não definido'
        ]
    ]);
}
?> 