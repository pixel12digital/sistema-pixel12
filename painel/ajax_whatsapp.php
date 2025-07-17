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
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Reduzido de 10 para 5 segundos
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Reduzido de 5 para 2 segundos
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: WhatsApp-Ajax-Proxy/1.0',
        'Accept: application/json',
        'Content-Type: application/json',
        'Connection: close' // Adicionar para evitar keep-alive desnecessário
    ]);
    
    // Otimizações adicionais de performance
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300); // Cache DNS por 5 minutos
    curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
    curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);
    curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Não seguir redirects desnecessários
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    $latency = round(($end_time - $start_time) * 1000, 2); // Calcular latência em ms
    curl_close($ch);
    
    // Log otimizado apenas com informações essenciais
    error_log("[WhatsApp Ajax] $method $endpoint: HTTP $httpCode ({$latency}ms)");
    
    return [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'data' => $response ? json_decode($response, true) : null,
        'error' => $error,
        'raw_response' => $response,
        'curl_info' => $info,
        'latency_ms' => $latency // Adicionar latência na resposta
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
            $number = $result['data']['clients_status']['default']['number'] ?? null;
            $message = $result['data']['clients_status']['default']['message'] ?? '';
            
            // Interpretar corretamente os status da API WhatsApp
            $is_ready = false;
            $status_display = 'Desconectado';
            
            switch ($status) {
                case 'ready':
                    $is_ready = true;
                    $status_display = 'Conectado';
                    break;
                case 'qr_ready':
                    $is_ready = false;
                    $status_display = 'QR Disponível';
                    break;
                case 'connecting':
                    $is_ready = false;
                    $status_display = 'Conectando...';
                    break;
                case 'disconnected':
                default:
                    $is_ready = false;
                    $status_display = 'Desconectado';
                    break;
            }
            
            echo json_encode([
                'ready' => $is_ready,
                'number' => $number,
                'lastSession' => $result['data']['timestamp'] ?? null,
                'sessions' => $result['data']['sessions'] ?? 0,
                'message' => $message,
                'clients_status' => $result['data']['clients_status'] ?? [],
                'status_display' => $status_display,
                'raw_status' => $status,
                'performance' => [
                    'latency_ms' => $result['latency_ms'] ?? 0,
                    'optimized' => true
                ],
                'debug' => [
                    'vps_status' => $status,
                    'http_code' => $result['http_code'],
                    'raw_response_preview' => substr($result['raw_response'], 0, 100)
                ]
            ]);
        } else {
            echo json_encode([
                'ready' => false,
                'error' => $result['error'] ?: 'VPS não respondeu',
                'http_code' => $result['http_code'],
                'performance' => [
                    'latency_ms' => $result['latency_ms'] ?? 0,
                    'timeout_used' => '5s (otimizado)'
                ],
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
        // Baseado no diagnóstico: apenas /status e /sessions funcionam
        // Esta API pode usar endpoints diferentes ou ter QR Code no próprio /status
        
        // Primeiro, verificar se o QR está na resposta do /status
        $status_result = makeVPSRequest('/status?' . http_build_query(['_' => time()]));
        
        if ($status_result['success'] && $status_result['data']) {
            // Verificar se o QR está na resposta do status (caminho correto)
            $qr_data = $status_result['data']['clients_status']['default']['qr'] ?? null;
            $qr_status = $status_result['data']['clients_status']['default']['status'] ?? null;
            
            if (!empty($qr_data)) {
                echo json_encode([
                    'qr' => $qr_data,
                    'ready' => $qr_status === 'ready',
                    'message' => $status_result['data']['clients_status']['default']['message'] ?? 'QR Code encontrado via /status',
                    'endpoint_used' => '/status',
                    'debug' => [
                        'qr_available' => true,
                        'qr_length' => strlen($qr_data),
                        'source' => 'status_endpoint',
                        'qr_status' => $qr_status,
                        'full_path' => 'clients_status.default.qr'
                    ]
                ]);
                break;
            }
        }
        
        // Se não há QR no status, tentar endpoints alternativos otimizados
        $possible_endpoints = [
            // Endpoints mais comuns primeiro (performance)
            '/qr',
            '/sessions/qr',
            '/sessions/default/qr',
            '/client/qr',
            '/api/qr',
            '/generate-qr',
            '/session/qr',
            '/whatsapp/qr',
            '/session/default/qr',
            '/qrcode',
            '/start',
            '/init'
        ];
        
        $qr_result = null;
        $successful_endpoint = null;
        
        // Timeout reduzido para melhor performance
        foreach ($possible_endpoints as $endpoint) {
            $endpoint_with_cache = $endpoint . '?' . http_build_query(['_' => time()]);
            
            // Fazer requisição com timeout menor para performance
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint_with_cache);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Reduzido de 10 para 3 segundos
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Reduzido de 5 para 2 segundos
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: WhatsApp-Ajax-Proxy/1.0',
                'Accept: application/json',
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            error_log("[WhatsApp Ajax] QR Endpoint $endpoint: HTTP $httpCode");
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if ($data && !empty($data['qr'])) {
                    $qr_result = ['data' => $data, 'success' => true];
                    $successful_endpoint = $endpoint;
                    break;
                }
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
                    'successful_endpoint' => $successful_endpoint,
                    'performance_optimized' => true
                ]
            ]);
        } else {
            // QR Code não disponível - retornar informações do status
            echo json_encode([
                'qr' => null,
                'ready' => false,
                'error' => 'QR Code não disponível nesta API WhatsApp',
                'message' => 'Esta API pode usar métodos diferentes para gerar QR Code',
                'debug' => [
                    'tested_endpoints' => $possible_endpoints,
                    'vps_status_working' => $status_result['success'] ?? false,
                    'suggestion' => 'Verificar documentação da API WhatsApp ou usar método alternativo',
                    'status_data' => $status_result['data'] ?? null,
                    'help' => 'Pode ser necessário inicializar sessão via outro método'
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
        // Teste completo de conectividade OTIMIZADO
        $tests = [];
        $all_success = true;
        $total_latency = 0;
        
        // Testes essenciais com performance otimizada
        $endpoints_to_test = [
            'status' => '/status',
            'sessions' => '/sessions'
        ];
        
        foreach ($endpoints_to_test as $test_name => $endpoint) {
            $result = makeVPSRequest($endpoint);
            
            $tests[$test_name] = [
                'success' => $result['success'],
                'http_code' => $result['http_code'],
                'error' => $result['error'],
                'latency_ms' => $result['latency_ms'] ?? 0
            ];
            
            if ($result['success']) {
                $total_latency += ($result['latency_ms'] ?? 0);
            } else {
                $all_success = false;
            }
        }
        
        // Testar QR apenas se outros endpoints funcionarem (para economizar tempo)
        if ($all_success) {
            $qr_result = makeVPSRequest('/qr');
            $tests['qr'] = [
                'success' => $qr_result['success'],
                'http_code' => $qr_result['http_code'],
                'error' => $qr_result['error'],
                'latency_ms' => $qr_result['latency_ms'] ?? 0,
                'note' => $qr_result['http_code'] === 404 ? 'Endpoint não existe (normal)' : ''
            ];
        }
        
        $avg_latency = count($tests) > 0 ? round($total_latency / count(array_filter($tests, fn($t) => $t['success'])), 2) : 0;
        
        echo json_encode([
            'connection_ok' => $all_success,
            'vps_url' => $vps_url,
            'timestamp' => date('Y-m-d H:i:s'),
            'tests' => $tests,
            'performance' => [
                'average_latency_ms' => $avg_latency,
                'total_tests' => count($tests),
                'successful_tests' => count(array_filter($tests, fn($t) => $t['success'])),
                'optimization' => 'Timeouts reduzidos para melhor performance'
            ],
            'status_summary' => $all_success ? 'VPS totalmente funcional' : 'VPS com problemas de conectividade'
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