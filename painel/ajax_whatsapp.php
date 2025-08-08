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
    require_once __DIR__ . '/../config_whatsapp_multiplo.php';
    
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
    
    // CORREÇÃO: Usar URL baseada na configuração inteligente
    $vps_url = getWorkingWhatsAppApiUrl($porta);
    
    // CORREÇÃO: Usar sessão correta baseada na porta ou parâmetro
    $sessionName = $_GET['session'] ?? $_POST['session'] ?? null;
    
    if (!$sessionName) {
        if ($porta == '3000' || $porta == 3000) {
            $sessionName = 'default'; // Sessão default para porta 3000
        } elseif ($porta == '3001' || $porta == 3001) {
            $sessionName = 'comercial'; // Sessão comercial para porta 3001
        } else {
            $sessionName = 'default'; // Padrão
        }
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
            $endpoint = "/status";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($http_code == 200 && !$curl_error) {
                $data = json_decode($response, true);
                
                // Extrair status do response
                $status = null;
                $ready = false;
                $qr = null;
                
                if (isset($data['status'])) {
                    $status = $data['status'];
                } elseif (isset($data['ready'])) {
                    $ready = $data['ready'];
                    $status = $ready ? 'connected' : 'disconnected';
                }
                
                if (isset($data['qr'])) {
                    $qr = $data['qr'];
                }
                
                echo json_encode([
                    'success' => true,
                    'status' => $status,
                    'ready' => $ready,
                    'qr' => $qr,
                    'connected' => $ready,
                    'debug' => [
                        'endpoint_used' => $endpoint,
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'raw_response_preview' => substr($response, 0, 200)
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao conectar com WhatsApp API',
                    'http_code' => $http_code,
                    'curl_error' => $curl_error,
                    'debug' => [
                        'endpoint' => $endpoint,
                        'session_used' => $sessionName,
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id,
                        'vps_url' => $vps_url
                    ]
                ]);
            }
            break;
        
        case 'qr':
            // CORREÇÃO: Implementar busca robusta de QR Code
            $endpoints = [
                "/qr?session=" . urlencode($sessionName),
                "/qr",
                "/api/qr?session=" . urlencode($sessionName),
                "/qrcode?session=" . urlencode($sessionName),
                "/status" // Fallback para verificar se há QR no status
            ];
            
            $qr_found = false;
            $last_error = null;
            $last_response = null;
            
            foreach ($endpoints as $endpoint) {
                $test_url = $vps_url . $endpoint;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $test_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);
                curl_close($ch);
                
                if ($http_code == 200 && !$curl_error && $response) {
                    $data = json_decode($response, true);
                    
                    // Extrair QR do response - múltiplas possibilidades
                    $qr = null;
                    if (isset($data['qr']) && !empty($data['qr'])) {
                        $qr = $data['qr'];
                    } elseif (isset($data['qrcode']) && !empty($data['qrcode'])) {
                        $qr = $data['qrcode'];
                    } elseif (isset($data['qr_code']) && !empty($data['qr_code'])) {
                        $qr = $data['qr_code'];
                    } elseif (isset($data['data']['qr']) && !empty($data['data']['qr'])) {
                        $qr = $data['data']['qr'];
                    } elseif (isset($data['clients_status'][$sessionName]['qr']) && !empty($data['clients_status'][$sessionName]['qr'])) {
                        $qr = $data['clients_status'][$sessionName]['qr'];
                    } elseif (is_string($data) && strlen($data) > 100) {
                        // Possível QR code como string
                        $qr = $data;
                    }
                    
                    if ($qr && !str_contains($qr, 'simulate') && !str_contains($qr, 'error')) {
                        echo json_encode([
                            'success' => true,
                            'qr' => $qr,
                            'ready' => isset($data['ready']) ? $data['ready'] : false,
                            'status' => isset($data['status']) ? $data['status'] : 'qr_ready',
                            'message' => isset($data['message']) ? $data['message'] : 'QR Code disponível',
                            'debug' => [
                                'endpoint_used' => $endpoint,
                                'session_used' => $sessionName,
                                'porta_used' => $porta,
                                'canal_id_received' => $canal_id,
                                'raw_response_preview' => substr($response, 0, 200),
                                'qr_length' => strlen($qr)
                            ]
                        ]);
                        $qr_found = true;
                        break;
                    }
                }
                
                $last_error = $curl_error ?: "HTTP $http_code";
                $last_response = $response;
            }
            
            if (!$qr_found) {
                // Se não encontrou QR, verificar se está conectado
                $status_url = $vps_url . "/status";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $status_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                
                $status_response = curl_exec($ch);
                $status_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($status_http_code == 200) {
                    $status_data = json_decode($status_response, true);
                    $is_connected = false;
                    
                    // Verificar se está conectado
                    if (isset($status_data['ready']) && $status_data['ready'] === true) {
                        $is_connected = true;
                    } elseif (isset($status_data['clients_status'][$sessionName]['ready']) && $status_data['clients_status'][$sessionName]['ready'] === true) {
                        $is_connected = true;
                    } elseif (isset($status_data['status']) && in_array($status_data['status'], ['connected', 'ready', 'authenticated'])) {
                        $is_connected = true;
                    }
                    
                    if ($is_connected) {
                        echo json_encode([
                            'success' => true,
                            'ready' => true,
                            'status' => 'connected',
                            'message' => 'WhatsApp já está conectado',
                            'debug' => [
                                'endpoint_used' => 'status',
                                'session_used' => $sessionName,
                                'porta_used' => $porta,
                                'canal_id_received' => $canal_id
                            ]
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'error' => 'QR Code não disponível no momento',
                            'message' => 'Aguarde alguns segundos e tente novamente',
                            'debug' => [
                                'endpoints_tested' => $endpoints,
                                'session_used' => $sessionName,
                                'porta_used' => $porta,
                                'canal_id_received' => $canal_id,
                                'last_error' => $last_error,
                                'last_response_preview' => substr($last_response, 0, 200),
                                'status_response_preview' => substr($status_response, 0, 200)
                            ]
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Falha ao obter QR Code - API pode não suportar este endpoint',
                        'message' => 'Verifique se o serviço WhatsApp está funcionando',
                        'debug' => [
                            'endpoints_tested' => $endpoints,
                            'session_used' => $sessionName,
                            'porta_used' => $porta,
                            'canal_id_received' => $canal_id,
                            'last_error' => $last_error,
                            'last_response_preview' => substr($last_response, 0, 200),
                            'vps_url' => $vps_url
                        ]
                    ]);
                }
            }
            break;
        
        case 'logout':
            $endpoint = "/logout";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['sessionName' => $sessionName]));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo json_encode([
                    'success' => true,
                    'message' => 'WhatsApp desconectado com sucesso',
                    'debug' => [
                        'endpoint_used' => $endpoint,
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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Conexão com WhatsApp API estabelecida',
                    'vps_url' => $vps_url,
                    'debug' => [
                        'porta_used' => $porta,
                        'canal_id_received' => $canal_id
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao conectar com WhatsApp API',
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