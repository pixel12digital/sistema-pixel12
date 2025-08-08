<?php
/**
 * API para gerenciar conexões com WhatsApp Web.js no Render.com
 */

// Desabilitar exibição de erros
error_reporting(0);
ini_set('display_errors', 0);

// Headers obrigatórios
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Função para retornar erro JSON
function returnError($message) {
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Função para retornar sucesso JSON
function returnSuccess($data = []) {
    echo json_encode(array_merge([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data));
    exit;
}

// Configuração dos canais
$CANAIS_CONFIG = [
    'pixel12digital' => [
        'identificador' => '554797146908@c.us',
        'nome_exibicao' => 'Pixel12Digital',
        'tipo' => 'IA',
        'descricao' => 'Atendimento por IA (Ana)',
        'porta' => 3000,
        'cor' => '#10b981'
    ],
    'atendimento_humano' => [
        'identificador' => '554797309525@c.us', 
        'nome_exibicao' => 'Pixel - Comercial',
        'tipo' => 'HUMANO',
        'descricao' => 'Atendimento Humano',
        'porta' => 3001,
        'cor' => '#3b82f6'
    ]
];

$RENDER_URL = 'https://whatsapp-web-js-qy62.onrender.com';

// Função para fazer requisições para o Render.com
function makeRenderRequest($endpoint, $method = 'GET', $data = null, $port = null) {
    global $RENDER_URL;
    
    $url = $RENDER_URL . $endpoint;
    
    // Adicionar porta se especificada
    if ($port) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . 'port=' . $port;
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; WhatsApp-API/1.0)');
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'error' => "HTTP $http_code"];
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        return ['success' => false, 'error' => 'Resposta inválida do servidor'];
    }
    
    return $data;
}

// Função para obter QR Code com retry
function getQRCodeWithRetry($maxAttempts = 3, $port = null) {
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $result = makeRenderRequest('/qr', 'GET', null, $port);
        
        if ($result['success'] && isset($result['qr']) && $result['qr'] !== 'QR code não disponível') {
            return $result;
        }
        
        if ($attempt < $maxAttempts) {
            sleep(2);
        }
    }
    
    return $result;
}

// Processar requisição
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$port = $_GET['port'] ?? $_POST['port'] ?? null;

try {
    switch ($action) {
        case 'health':
            $result = makeRenderRequest('/health', 'GET', null, $port);
            returnSuccess($result);
            break;
            
        case 'qr':
            $result = getQRCodeWithRetry(3, $port);
            if ($result['success'] && isset($result['qr']) && $result['qr'] !== 'QR code não disponível') {
                returnSuccess(['qr' => $result['qr']]);
            } else {
                returnError('QR Code não disponível no momento. Tente novamente em alguns segundos.');
            }
            break;
            
        case 'status':
            $result = makeRenderRequest('/status', 'GET', null, $port);
            if ($result['success']) {
                returnSuccess(['connected' => $result['connected'] ?? false]);
            } else {
                returnError($result['error'] ?? 'Erro ao verificar status');
            }
            break;
            
        case 'connect':
            $canal = $_POST['canal'] ?? '';
            $porta = $_POST['porta'] ?? $port;
            
            if (!$canal || !$porta) {
                returnError('Canal e porta são obrigatórios');
            }
            
            $result = makeRenderRequest('/connect', 'POST', [
                'canal' => $canal,
                'porta' => $porta
            ], $porta);
            
            if ($result['success']) {
                returnSuccess(['message' => 'Canal conectado com sucesso']);
            } else {
                returnError($result['error'] ?? 'Erro ao conectar canal');
            }
            break;
            
        case 'test':
            $result = makeRenderRequest('/test', 'GET', null, $port);
            if ($result['success']) {
                returnSuccess(['message' => 'Conexão WhatsApp OK']);
            } else {
                returnError($result['error'] ?? 'Erro ao testar conexão');
            }
            break;
            
        case 'send_message':
            $to = $_POST['to'] ?? '';
            $message = $_POST['message'] ?? '';
            
            if (!$to || !$message) {
                returnError('Destinatário e mensagem são obrigatórios');
            }
            
            $result = makeRenderRequest('/send', 'POST', [
                'to' => $to,
                'message' => $message
            ], $port);
            
            if ($result['success']) {
                returnSuccess(['message' => 'Mensagem enviada com sucesso']);
            } else {
                returnError($result['error'] ?? 'Erro ao enviar mensagem');
            }
            break;
            
        default:
            returnError('Ação não reconhecida. Ações disponíveis: health, qr, status, connect, test, send_message');
            break;
    }
} catch (Exception $e) {
    returnError('Erro interno: ' . $e->getMessage());
}
?> 