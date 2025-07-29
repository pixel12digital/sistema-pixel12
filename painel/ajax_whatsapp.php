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
    $vps_url = WHATSAPP_ROBOT_URL;
    
    // CORREÇÃO: Usar URL base local se disponível
    $base_url = defined('LOCAL_BASE_URL') && LOCAL_BASE_URL ? LOCAL_BASE_URL : 'http://localhost';
    
    // Log debug para depuração
    error_log("[WhatsApp Ajax Debug] Action: $action, Method: {$_SERVER['REQUEST_METHOD']}, VPS URL: $vps_url, Base URL: $base_url");
    
    // Se for apenas um teste de conectividade
    if (isset($_GET['test'])) {
        // Limpar qualquer output anterior
        ob_clean();
        
        echo json_encode([
            'test' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'ajax_working' => true,
            'vps_url' => $vps_url,
            'base_url' => $base_url,
            'environment' => defined('LOCAL_BASE_URL') && LOCAL_BASE_URL ? 'local' : 'production',
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        exit;
    }
    
    // Função para fazer requisições à VPS
    function makeVPSRequest($endpoint, $method = 'GET', $data = null) {
        global $vps_url; // Adicionar acesso à variável global
        
        // Log de início para depuração
        file_put_contents(__DIR__.'/debug_ajax_whatsapp.log', date('Y-m-d H:i:s')." - Iniciando requisição: $method $endpoint | Data: ".json_encode($data)."\n", FILE_APPEND);
        
        $start_time = microtime(true);
        $ch = curl_init();
        
        // Log das configurações
        file_put_contents(__DIR__.'/debug_ajax_whatsapp.log', date('Y-m-d H:i:s')." - vps_url: $vps_url | Endpoint final: ".$vps_url . $endpoint."\n", FILE_APPEND);
        
        curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
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
        
        $response = curl_exec($ch);
        $end_time = microtime(true);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        $latency = round(($end_time - $start_time) * 1000, 2); // Calcular latência em ms
        curl_close($ch);
    
        // Log detalhado de erro para depuração
        if ($httpCode !== 200 || $error || !$response) {
            file_put_contents(__DIR__.'/debug_ajax_whatsapp.log', date('Y-m-d H:i:s')." - Erro na requisição: $method $endpoint | HTTP: $httpCode | Erro: $error | Resposta: $response\n", FILE_APPEND);
        }
        
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
        // Limpar qualquer output anterior
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
            $endpoint = '/status?' . http_build_query(['_' => time()]);
            $result = makeVPSRequest($endpoint);
            
            if ($result['success'] && $result['data']) {
                // CORREÇÃO: Interpretar corretamente o formato da resposta da VPS
                $vps_status = 'disconnected';
                $clients_status = $result['data']['clients_status'] ?? [];
                
                // Verificar se há clientes conectados
                if (!empty($clients_status['default']['status'])) {
                    $vps_status = $clients_status['default']['status'];
                }
                
                // Mapear status da VPS para o formato esperado pelo frontend
                $is_ready = ($vps_status === 'connected' || $result['data']['ready'] === true);
                
                // Tentar obter número do cliente se disponível
                $number = null;
                if (!empty($clients_status['default']['number'])) {
                    $number = $clients_status['default']['number'];
                } elseif (!empty($result['data']['status']['number'])) {
                    $number = $result['data']['status']['number'];
                }
                
                echo json_encode([
                    'ready' => $is_ready,
                    'number' => $number,
                    'lastSession' => $result['data']['timestamp'] ?? null,
                    'sessions' => $result['data']['sessions'] ?? 0,
                    'message' => $result['data']['message'] ?? '',
                    'clients_status' => $clients_status,
                    'performance' => [
                        'latency_ms' => $result['latency_ms'] ?? 0,
                        'optimized' => true
                    ],
                    'debug' => [
                        'vps_status' => $vps_status,
                        'vps_status_parsed' => $is_ready ? 'ready' : 'disconnected',
                        'http_code' => $result['http_code'],
                        'raw_response_preview' => $result['raw_response'],
                        'status_mapping' => [
                            'vps_connected' => $vps_status === 'connected',
                            'vps_ready' => $vps_status === 'ready',
                            'vps_authenticated' => $vps_status === 'authenticated',
                            'frontend_ready' => $is_ready
                        ]
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
            // Usar o novo endpoint /qr da VPS
            $qr_endpoint = '/qr?' . http_build_query(['_' => time()]);
            $result = makeVPSRequest($qr_endpoint);
            
            if ($result['success'] && $result['data']) {
                echo json_encode([
                    'success' => true,
                    'qr' => $result['data']['qr'] ?? null,
                    'message' => $result['data']['message'] ?? 'QR Code gerado',
                    'expires_in' => $result['data']['expires_in'] ?? 60,
                    'performance' => [
                        'latency_ms' => $result['latency_ms'] ?? 0
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $result['error'] ?: 'Erro ao gerar QR Code',
                    'http_code' => $result['http_code'],
                    'debug' => [
                        'endpoint' => $qr_endpoint,
                        'curl_error' => $result['error'],
                        'raw_response' => $result['raw_response']
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
            
            // CORREÇÃO: Usar o endpoint correto /send/text
            $endpoint = '/send/text';
            $data = [
                'sessionName' => 'default', // Usar sessão padrão
                'number' => $to,
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
                    'http_code' => $result['http_code'],
                    'debug' => [
                        'endpoint_used' => $endpoint,
                        'data_sent' => $data,
                        'raw_response' => $result['raw_response']
                    ]
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
                'success' => $all_success,
                'tests' => $tests,
                'performance' => [
                    'total_latency_ms' => $total_latency,
                    'average_latency_ms' => $avg_latency,
                    'tests_count' => count($tests)
                ],
                'recommendation' => $all_success ? 
                    'VPS funcionando perfeitamente' : 
                    'Alguns endpoints falharam. Verificar conectividade.',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
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
    // Limpar qualquer output anterior
    ob_clean();
    
    error_log("Erro no ajax_whatsapp.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Error $e) {
    // Limpar qualquer output anterior
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