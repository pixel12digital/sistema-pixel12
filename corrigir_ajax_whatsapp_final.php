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
    
    // Determinar a porta baseada no canal_id
    $porta = '3000'; // Padrão porta 3000 (financeiro)
    if ($canal_id) {
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
            // CORREÇÃO: Consultar diretamente a sessão específica
            $sessionName = 'default'; // Padrão para porta 3000
            if ($porta == '3001' || $porta == 3001) {
                $sessionName = 'comercial'; // Para porta 3001
            }
            
            // Consultar status da sessão específica
            $endpoint = "/session/{$sessionName}/status";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['status']['status'])) {
                    $vps_status = $data['status']['status'];
                    $is_ready = ($vps_status === 'connected');
                    
                    echo json_encode([
                        'ready' => $is_ready,
                        'number' => $data['status']['number'] ?? null,
                        'lastSession' => date('c'),
                        'sessions' => 1,
                        'message' => $data['status']['message'] ?? 'Status da sessão',
                        'clients_status' => [
                            $sessionName => $data['status']
                        ],
                        'performance' => [
                            'latency_ms' => 0,
                            'optimized' => true
                        ],
                        'debug' => [
                            'vps_status' => $vps_status,
                            'vps_status_parsed' => $is_ready ? 'ready' : 'disconnected',
                            'http_code' => $http_code,
                            'session_checked' => $sessionName,
                            'porta_used' => $porta,
                            'canal_id_received' => $canal_id,
                            'endpoint_used' => $endpoint,
                            'status_mapping' => [
                                'vps_connected' => $vps_status === 'connected',
                                'vps_ready' => $vps_status === 'ready',
                                'vps_authenticated' => $vps_status === 'authenticated',
                                'frontend_ready' => $is_ready
                            ]
                        ]
                    ]);
                } else {
                    // Sessão não existe ou erro
                    echo json_encode([
                        'ready' => false,
                        'number' => null,
                        'lastSession' => null,
                        'sessions' => 0,
                        'message' => 'Sessão não encontrada',
                        'clients_status' => [],
                        'performance' => [
                            'latency_ms' => 0,
                            'optimized' => true
                        ],
                        'debug' => [
                            'vps_status' => 'disconnected',
                            'vps_status_parsed' => 'disconnected',
                            'http_code' => $http_code,
                            'session_checked' => $sessionName,
                            'porta_used' => $porta,
                            'canal_id_received' => $canal_id,
                            'endpoint_used' => $endpoint,
                            'raw_response' => $response
                        ]
                    ]);
                }
            } else {
                echo json_encode([
                    'ready' => false,
                    'error' => 'VPS não respondeu',
                    'http_code' => $http_code,
                    'performance' => [
                        'latency_ms' => 0,
                        'timeout_used' => '5s'
                    ],
                    'debug' => [
                        'endpoint' => $endpoint,
                        'full_url' => $vps_url . $endpoint,
                        'session_checked' => $sessionName,
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
            
            $qr_endpoint = "/qr?session={$sessionName}";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $qr_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                echo json_encode([
                    'success' => true,
                    'qr' => $data['qr'] ?? null,
                    'message' => $data['message'] ?? 'QR Code gerado',
                    'expires_in' => $data['expires_in'] ?? 60,
                    'performance' => [
                        'latency_ms' => 0
                    ],
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao gerar QR Code',
                    'http_code' => $http_code,
                    'debug' => [
                        'endpoint' => $qr_endpoint,
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
            
            $endpoint = "/session/{$sessionName}/disconnect";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POST, true);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo json_encode([
                    'success' => true,
                    'message' => "WhatsApp desconectado com sucesso (sessão: {$sessionName})",
                    'session_disconnected' => $sessionName,
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
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
        
        default:
            echo json_encode([
                'error' => 'Ação não reconhecida',
                'available_actions' => ['status', 'qr', 'logout', 'send', 'test_connection'],
                'received_action' => $action,
                'debug' => [
                    'all_get' => $_GET,
                    'all_post' => array_keys($_POST)
                ]
            ]);
            break;
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log("Erro no ajax_whatsapp.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Error $e) {
    ob_clean();
    error_log("Erro fatal no ajax_whatsapp.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Garantir que nada mais seja enviado
ob_end_flush();
?> 