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
    // FORÇAR USO DA VPS 3001 QUE ESTÁ FUNCIONANDO
    $vps_url = 'http://212.85.11.238:3001'; // Sempre usar comercial (funcionando)
    
    // CORREÇÃO: Usar sessão correta baseada na porta
    if ($porta == '3000' || $porta == 3000) {
        $vps_url = 'http://212.85.11.238:3000'; // VPS 3000 para porta 3000
        $sessionName = 'default'; // Sessão default para porta 3000
    } else {
        $vps_url = 'http://212.85.11.238:3001'; // VPS 3001 para porta 3001
        $sessionName = 'comercial'; // Sessão comercial para porta 3001
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
            // CORREÇÃO: Usar endpoint geral /status em vez de /session/:sessionName/status
            // Log adicional para debug
            error_log("[WhatsApp Status Debug] Porta: $porta, Session: $sessionName, VPS URL: $vps_url");
            
            // Consultar status geral (contém todas as sessões)
            $endpoint = "/status";
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
                
                // Extrair status da sessão específica do response geral
                $sessionStatus = null;
                if (isset($data['clients_status'][$sessionName])) {
                    $sessionStatus = $data['clients_status'][$sessionName];
                }
                
                echo json_encode([
                    'success' => true,
                    'status' => $sessionStatus ? $sessionStatus['status'] : 'not_found',
                    'message' => $sessionStatus ? $sessionStatus['message'] : 'Sessão não encontrada',
                    'session' => $sessionName,
                    'raw_response_preview' => json_encode($sessionStatus), // Para debug no frontend
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'endpoint_used' => $endpoint,
                        'available_sessions' => array_keys($data['clients_status'] ?? [])
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
            // $sessionName = 'default'; // Padrão para porta 3000
            // if ($porta == '3001' || $porta == 3001) {
            //     $sessionName = 'comercial'; // Para porta 3001
            // }
            
            // Log adicional para debug
            error_log("[WhatsApp QR Debug] Porta: $porta, Session: $sessionName, VPS URL: $vps_url");
            
            // CORREÇÃO: Usar endpoint /qr com query parameter session
            $qr_endpoint = "/qr?session=" . urlencode($sessionName);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $qr_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Log da resposta para debug
            error_log("[WhatsApp QR Response] HTTP Code: $http_code, Response: $response");
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                
                // VALIDAÇÃO E FALLBACK: Garantir que o QR não comece com "undefined"
                $qr = null;
                if (!empty($data['qr']) && !str_starts_with($data['qr'], 'undefined')) {
                    $qr = $data['qr'];
                } elseif (!empty($data['clients_status'][$sessionName]['qr']) && !str_starts_with($data['clients_status'][$sessionName]['qr'], 'undefined')) {
                    $qr = $data['clients_status'][$sessionName]['qr'];
                }
                
                // Se ainda não tem QR válido, tentar endpoint /status como fallback
                if (empty($qr)) {
                    error_log("[WhatsApp QR Fallback] Tentando endpoint /status como fallback");
                    $status_endpoint = "/status";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $vps_url . $status_endpoint);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $status_response = curl_exec($ch);
                    $status_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($status_http_code == 200) {
                        $status_data = json_decode($status_response, true);
                        if (!empty($status_data['clients_status'][$sessionName]['qr']) && !str_starts_with($status_data['clients_status'][$sessionName]['qr'], 'undefined')) {
                            $qr = $status_data['clients_status'][$sessionName]['qr'];
                            error_log("[WhatsApp QR Fallback] QR obtido via /status: " . substr($qr, 0, 50) . "...");
                        }
                    }
                }
                
                echo json_encode([
                    'success' => !empty($qr),
                    'qr' => $qr,
                    'message' => !empty($qr) ? 'QR Code disponível para escaneamento' : 'QR Code não disponível ou inválido',
                    'ready' => $data['ready'] ?? false,
                    'status' => $data['status'] ?? 'unknown',
                    'expires_in' => 60,
                    'performance' => [
                        'latency_ms' => 0
                    ],
                    'debug' => [
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'endpoint_used' => $qr_endpoint,
                        'qr_valid' => !empty($qr),
                        'qr_length' => $qr ? strlen($qr) : 0,
                        'raw_response_preview' => json_encode($data)
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao obter QR Code do WhatsApp',
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
            // $sessionName = 'default'; // Padrão para porta 3000
            // if ($porta == '3001' || $porta == 3001) {
            //     $sessionName = 'comercial'; // Para porta 3001
            // }
            
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
            // $sessionName = 'default'; // Padrão para porta 3000
            // if ($porta == '3001' || $porta == 3001) {
            //     $sessionName = 'comercial'; // Para porta 3001
            // }
            
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