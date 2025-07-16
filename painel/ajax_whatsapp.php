<?php
// Proxy PHP para contornar CORS - WhatsApp API
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$vps_url = WHATSAPP_ROBOT_URL;

// Log debug para depuração
error_log("[WhatsApp Ajax Debug] Action: $action, Method: {$_SERVER['REQUEST_METHOD']}, VPS URL: $vps_url");

// Se for apenas um teste de conectividade
if (isset($_GET['test'])) {
    echo json_encode([
        'test' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'ajax_working' => true,
        'vps_url' => $vps_url,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

// Função para fazer requisições à VPS
function makeVPSRequest($endpoint, $method = 'GET', $data = null) {
    global $vps_url;
    
    $url = $vps_url . $endpoint;
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: WhatsApp-Ajax-Proxy/1.0',
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    // Log detalhado para debug
    error_log("[WhatsApp Ajax] URL: $url, HTTP: $httpCode, Error: $error, Response: " . substr($response, 0, 200));
    
    return [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'data' => $response ? json_decode($response, true) : null,
        'error' => $error,
        'raw_response' => $response,
        'curl_info' => $info
    ];
}

// Se não há action, retornar erro
if (empty($action)) {
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

switch ($action) {
    case 'status':
        $endpoint = '/status?' . http_build_query(['_' => time()]);
        $result = makeVPSRequest($endpoint);
        
        if ($result['success'] && $result['data']) {
            // Formatar resposta para compatibilidade com JavaScript original
            $status = $result['data']['clients_status']['default']['status'] ?? 'disconnected';
            echo json_encode([
                'ready' => $status === 'ready',
                'number' => $result['data']['clients_status']['default']['number'] ?? null,
                'lastSession' => $result['data']['timestamp'] ?? null,
                'sessions' => $result['data']['sessions'] ?? 0,
                'message' => $result['data']['message'] ?? '',
                'clients_status' => $result['data']['clients_status'] ?? [],
                'debug' => [
                    'vps_status' => $status,
                    'raw_response_preview' => substr($result['raw_response'], 0, 100)
                ]
            ]);
        } else {
            echo json_encode([
                'ready' => false,
                'error' => $result['error'] ?: 'VPS não respondeu',
                'http_code' => $result['http_code'],
                'debug' => [
                    'endpoint' => $endpoint,
                    'full_url' => $vps_url . $endpoint,
                    'curl_error' => $result['error'],
                    'raw_response' => $result['raw_response']
                ]
            ]);
        }
        break;
    
    case 'qr':
        // Tentar múltiplos endpoints para QR Code baseado no diagnóstico
        $possible_endpoints = [
            '/qr',
            '/generate-qr', 
            '/session/qr',
            '/whatsapp/qr',
            '/session/default/qr'
        ];
        
        $qr_result = null;
        $successful_endpoint = null;
        
        foreach ($possible_endpoints as $endpoint) {
            $endpoint_with_cache = $endpoint . '?' . http_build_query(['_' => time()]);
            $result = makeVPSRequest($endpoint_with_cache);
            
            error_log("[WhatsApp Ajax] Tentando endpoint QR: $endpoint, HTTP: {$result['http_code']}");
            
            if ($result['success'] && $result['data']) {
                $qr_result = $result;
                $successful_endpoint = $endpoint;
                break;
            }
        }
        
        if ($qr_result && $successful_endpoint) {
            echo json_encode([
                'qr' => $qr_result['data']['qr'] ?? null,
                'ready' => $qr_result['data']['ready'] ?? false,
                'message' => 'QR Code encontrado via ' . $successful_endpoint,
                'endpoint_used' => $successful_endpoint,
                'debug' => [
                    'qr_available' => !empty($qr_result['data']['qr']),
                    'qr_length' => strlen($qr_result['data']['qr'] ?? ''),
                    'raw_keys' => array_keys($qr_result['data']),
                    'successful_endpoint' => $successful_endpoint
                ]
            ]);
        } else {
            // Se todos falharam, tentar endpoint de status para mais informações
            $status_result = makeVPSRequest('/status');
            
            echo json_encode([
                'qr' => null,
                'ready' => false,
                'error' => 'Nenhum endpoint de QR Code funcionou',
                'debug' => [
                    'tested_endpoints' => $possible_endpoints,
                    'vps_status_working' => $status_result['success'],
                    'suggestion' => 'Verificar documentação da API WhatsApp para endpoint correto de QR',
                    'status_data' => $status_result['success'] ? $status_result['data'] : null
                ]
            ]);
        }
        break;
    
    case 'logout':
        $endpoint = '/logout?' . http_build_query(['_' => time()]);
        $result = makeVPSRequest($endpoint, 'POST');
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'WhatsApp desconectado com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?: 'Erro ao desconectar WhatsApp',
                'http_code' => $result['http_code'],
                'debug' => [
                    'endpoint' => $endpoint,
                    'curl_error' => $result['error']
                ]
            ]);
        }
        break;
    
    case 'send':
        $to = $_POST['to'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (!$to || !$message) {
            echo json_encode([
                'success' => false,
                'error' => 'Parâmetros obrigatórios: to, message'
            ]);
            break;
        }
        
        $endpoint = '/send';
        $data = [
            'to' => $to,
            'message' => $message
        ];
        
        $result = makeVPSRequest($endpoint, 'POST', $data);
        
        if ($result['success'] && $result['data']) {
            echo json_encode([
                'success' => $result['data']['success'] ?? false,
                'messageId' => $result['data']['messageId'] ?? null,
                'message' => $result['data']['message'] ?? 'Mensagem enviada'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?: 'Erro ao enviar mensagem',
                'http_code' => $result['http_code']
            ]);
        }
        break;
    
    case 'test_connection':
        // Teste completo de conectividade
        $tests = [
            'status' => makeVPSRequest('/status'),
            'qr' => makeVPSRequest('/qr'),
            'sessions' => makeVPSRequest('/sessions')
        ];
        
        $all_success = true;
        $results = [];
        
        foreach ($tests as $test_name => $result) {
            $results[$test_name] = [
                'success' => $result['success'],
                'http_code' => $result['http_code'],
                'error' => $result['error']
            ];
            
            if (!$result['success']) {
                $all_success = false;
            }
        }
        
        echo json_encode([
            'connection_ok' => $all_success,
            'vps_url' => $vps_url,
            'timestamp' => date('Y-m-d H:i:s'),
            'tests' => $results
        ]);
        break;
    
    case 'discover_endpoints':
        // Descoberta rápida de endpoints QR
        $qr_endpoints = [
            '/qr',
            '/generate-qr',
            '/session/qr',
            '/whatsapp/qr',
            '/session/default/qr',
            '/api/qr',
            '/qrcode'
        ];
        
        $results = [];
        $working_endpoints = [];
        
        foreach ($qr_endpoints as $endpoint) {
            $result = makeVPSRequest($endpoint);
            
            $results[$endpoint] = [
                'success' => $result['success'],
                'http_code' => $result['http_code'],
                'error' => $result['error'],
                'has_qr_data' => $result['success'] && $result['data'] && isset($result['data']['qr'])
            ];
            
            if ($result['success'] && $result['http_code'] === 200) {
                $working_endpoints[] = $endpoint;
            }
        }
        
        // Também testar endpoints de informação
        $info_endpoints = ['/status', '/sessions', '/info'];
        foreach ($info_endpoints as $endpoint) {
            $result = makeVPSRequest($endpoint);
            $results[$endpoint] = [
                'success' => $result['success'],
                'http_code' => $result['http_code'],
                'error' => $result['error']
            ];
        }
        
        echo json_encode([
            'discovery_complete' => true,
            'working_endpoints' => $working_endpoints,
            'total_tested' => count($qr_endpoints) + count($info_endpoints),
            'results' => $results,
            'recommendation' => empty($working_endpoints) ? 
                'Nenhum endpoint QR funcionando. Verificar se API WhatsApp está configurada corretamente.' :
                'Endpoints QR encontrados: ' . implode(', ', $working_endpoints),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        break;
    
    case 'raw_request':
        // Ação para descoberta de endpoints - testar qualquer endpoint
        $endpoint = $_POST['endpoint'] ?? '';
        
        if (empty($endpoint)) {
            echo json_encode([
                'error' => 'Endpoint não especificado',
                'debug' => ['received_endpoint' => $endpoint]
            ]);
            break;
        }
        
        // Adicionar cache buster
        $endpoint_with_cache = $endpoint . (strpos($endpoint, '?') ? '&' : '?') . '_=' . time();
        $result = makeVPSRequest($endpoint_with_cache);
        
        error_log("[WhatsApp Ajax] Raw request para: $endpoint, HTTP: {$result['http_code']}, Success: " . ($result['success'] ? 'true' : 'false'));
        
        if ($result['success']) {
            // Retornar resposta bruta para análise
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'endpoint' => $endpoint,
                'http_code' => $result['http_code'],
                'data' => $result['raw_response'],
                'parsed_data' => $result['data'],
                'curl_info' => [
                    'total_time' => $result['curl_info']['total_time'] ?? null,
                    'http_code' => $result['curl_info']['http_code'] ?? null
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'endpoint' => $endpoint,
                'http_code' => $result['http_code'],
                'error' => $result['error'],
                'raw_response' => $result['raw_response']
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
?> 