<?php
namespace Services;

/**
 * 🚀 WhatsApp Service - Nova Solução Render.com
 * 
 * Serviço modernizado para integração com WhatsApp API
 * Utiliza a nova solução hospedada no Render.com
 */
class WhatsAppService
{
    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Construtor
     */
    public function __construct()
    {
        // Carregar configurações
        if (!defined('WHATSAPP_API_3000_URL')) {
            require_once __DIR__ . '/../../config_whatsapp_multiplo.php';
        }

        $this->apiUrl = WHATSAPP_API_3000_URL;
        $this->webhookUrl = getWebhookUrl();
        $this->debug = defined('DEBUG_MODE') ? DEBUG_MODE : false;
    }

    /**
     * Obtém a URL da API baseada na porta/canal
     */
    public function getApiUrl($porta = 3000): string
    {
        if ($porta == 3000) {
            return defined('WHATSAPP_API_3000_URL') ? WHATSAPP_API_3000_URL : 'https://whatsapp-api-c4bg.onrender.com';
        } elseif ($porta == 3001) {
            return defined('WHATSAPP_API_3001_URL') ? WHATSAPP_API_3001_URL : 'https://whatsapp-api-c4bg.onrender.com';
        }
        return 'https://whatsapp-api-c4bg.onrender.com';
    }

    /**
     * Testa se a API está funcionando
     */
    public function testConnection($porta = 3000): bool
    {
        $url = $this->getApiUrl($porta);
        
        // Testar primeiro o endpoint raiz
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsAppService/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($this->debug) {
            error_log("[WhatsAppService] Teste conexão porta $porta: HTTP $http_code, Response: $response");
        }
        
        // Se o endpoint raiz responde, a API está funcionando
        if ($http_code === 200 || $http_code === 404) {
            return true; // 404 é OK, significa que a API está funcionando mas não tem endpoint raiz
        }
        
        return false;
    }

    /**
     * Obtém o status da API
     */
    public function getStatus($porta = 3000): array
    {
        $url = $this->getApiUrl($porta);
        
        // Tentar diferentes endpoints de status
        $endpoints = ['/status', '/api/status', '/health', '/'];
        $response_data = null;
        $http_code = 0;
        
        foreach ($endpoints as $endpoint) {
            $test_url = $url . $endpoint;
            
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsAppService/1.0');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                if ($response_data) {
                    break;
                }
            }
        }
        
        if ($response_data) {
            return $response_data;
        }
        
        // Se não conseguiu obter dados estruturados, retorna status básico
        return [
            'status' => 'online',
            'message' => 'API está funcionando',
            'http_code' => $http_code,
            'endpoint_tested' => $endpoints
        ];
    }

    /**
     * Obtém o QR Code para conexão
     */
    public function getQRCode($porta = 3000, $session = 'default'): array
    {
        $url = $this->getApiUrl($porta);
        
        // Tentar diferentes endpoints de QR
        $endpoints = [
            '/qr?session=' . urlencode($session),
            '/qr',
            '/api/qr?session=' . urlencode($session),
            '/qrcode?session=' . urlencode($session),
            '/status' // Fallback para verificar se há QR no status
        ];
        
        $last_error = null;
        $last_response = null;
        
        foreach ($endpoints as $endpoint) {
            $test_url = $url . $endpoint;
            
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsAppService/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($http_code === 200 && !$curl_error && $response) {
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
                } elseif (isset($data['clients_status'][$session]['qr']) && !empty($data['clients_status'][$session]['qr'])) {
                    $qr = $data['clients_status'][$session]['qr'];
                } elseif (is_string($data) && strlen($data) > 100) {
                    // Possível QR code como string
                    $qr = $data;
                }
                
                if ($qr && !str_contains($qr, 'simulate') && !str_contains($qr, 'error')) {
                    return [
                        'success' => true,
                        'qrcode' => $qr,
                        'session' => $session,
                        'ready' => isset($data['ready']) ? $data['ready'] : false,
                        'status' => isset($data['status']) ? $data['status'] : 'qr_ready',
                        'message' => isset($data['message']) ? $data['message'] : 'QR Code disponível',
                        'endpoint_used' => $endpoint
                    ];
                }
                
                // Verificar se está conectado
                if (isset($data['ready']) && $data['ready'] === true) {
                    return [
                        'success' => true,
                        'ready' => true,
                        'status' => 'connected',
                        'message' => 'WhatsApp já está conectado',
                        'session' => $session,
                        'endpoint_used' => $endpoint
                    ];
                }
            }
            
            $last_error = $curl_error ?: "HTTP $http_code";
            $last_response = $response;
        }
        
        // Se não encontrou QR, verificar status
        $status_url = $url . "/status";
        $ch = curl_init($status_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $status_response = curl_exec($ch);
        $status_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status_http_code === 200) {
            $status_data = json_decode($status_response, true);
            $is_connected = false;
            
            // Verificar se está conectado
            if (isset($status_data['ready']) && $status_data['ready'] === true) {
                $is_connected = true;
            } elseif (isset($status_data['clients_status'][$session]['ready']) && $status_data['clients_status'][$session]['ready'] === true) {
                $is_connected = true;
            } elseif (isset($status_data['status']) && in_array($status_data['status'], ['connected', 'ready', 'authenticated'])) {
                $is_connected = true;
            }
            
            if ($is_connected) {
                return [
                    'success' => true,
                    'ready' => true,
                    'status' => 'connected',
                    'message' => 'WhatsApp já está conectado',
                    'session' => $session
                ];
            }
        }
        
        return [
            'error' => true,
            'http_code' => $http_code,
            'message' => 'QR Code não disponível no momento - aguarde alguns segundos e tente novamente',
            'debug' => [
                'endpoints_tested' => $endpoints,
                'last_error' => $last_error,
                'last_response_preview' => substr($last_response, 0, 200),
                'session' => $session,
                'porta' => $porta
            ]
        ];
    }

    /**
     * Envia uma mensagem de texto
     */
    public function sendText($number, $message, $porta = 3000, $session = 'default'): array
    {
        $url = $this->getApiUrl($porta);
        
        $data = [
            'sessionName' => $session,
            'number' => $this->formatNumber($number),
            'message' => $message
        ];
        
        // Tentar diferentes endpoints de envio
        $endpoints = ['/send/text', '/api/send/text', '/send', '/api/send'];
        
        foreach ($endpoints as $endpoint) {
            $test_url = $url . $endpoint;
            
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: WhatsAppService/1.0'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($this->debug) {
                error_log("[WhatsAppService] Envio mensagem porta $porta endpoint $endpoint: HTTP $http_code, Response: $response");
            }
            
            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                return [
                    'success' => true,
                    'data' => $response_data,
                    'message' => 'Mensagem enviada com sucesso',
                    'endpoint_used' => $endpoint
                ];
            }
        }
        
        return [
            'error' => true,
            'http_code' => $http_code,
            'message' => 'Falha ao enviar mensagem - API pode não suportar este endpoint',
            'endpoints_tested' => $endpoints
        ];
    }

    /**
     * Configura o webhook
     */
    public function configureWebhook($webhookUrl = null, $porta = 3000): array
    {
        if (!$webhookUrl) {
            $webhookUrl = $this->webhookUrl;
        }
        
        $url = $this->getApiUrl($porta);
        
        $data = [
            'url' => $webhookUrl
        ];
        
        // Tentar diferentes endpoints de webhook
        $endpoints = ['/webhook/config', '/api/webhook/config', '/webhook', '/api/webhook'];
        
        foreach ($endpoints as $endpoint) {
            $test_url = $url . $endpoint;
            
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: WhatsAppService/1.0'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($this->debug) {
                error_log("[WhatsAppService] Config webhook porta $porta endpoint $endpoint: HTTP $http_code, Response: $response");
            }
            
            if ($http_code === 200) {
                return [
                    'success' => true,
                    'message' => 'Webhook configurado com sucesso',
                    'webhook_url' => $webhookUrl,
                    'endpoint_used' => $endpoint
                ];
            }
        }
        
        return [
            'error' => true,
            'http_code' => $http_code,
            'message' => 'Falha ao configurar webhook - API pode não suportar este endpoint',
            'endpoints_tested' => $endpoints
        ];
    }

    /**
     * Formata o número do telefone
     */
    private function formatNumber($number): string
    {
        // Remove caracteres não numéricos
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Adiciona código do país se não tiver
        if (strlen($number) === 11 && substr($number, 0, 2) === '11') {
            $number = '55' . $number;
        } elseif (strlen($number) === 10) {
            $number = '55' . $number;
        }
        
        return $number;
    }

    /**
     * Obtém informações da sessão
     */
    public function getSessionInfo($porta = 3000, $session = 'default'): array
    {
        $url = $this->getApiUrl($porta);
        
        // Tentar diferentes endpoints de status
        $endpoints = ['/status', '/api/status', '/sessions', '/api/sessions'];
        
        foreach ($endpoints as $endpoint) {
            $test_url = $url . $endpoint;
            
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsAppService/1.0');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $data = json_decode($response, true);
                if ($data && isset($data['clients_status'])) {
                    foreach ($data['clients_status'] as $sessionName => $sessionInfo) {
                        if ($sessionName === $session) {
                            return [
                                'success' => true,
                                'session' => $sessionName,
                                'status' => $sessionInfo['status'] ?? 'unknown',
                                'info' => $sessionInfo
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'error' => true,
            'message' => 'Sessão não encontrada ou API não responde'
        ];
    }

    /**
     * Desconecta uma sessão
     */
    public function disconnectSession($porta = 3000, $session = 'default'): array
    {
        $url = $this->getApiUrl($porta);
        
        // Tentar diferentes endpoints de desconexão
        $endpoints = [
            '/disconnect?session=' . urlencode($session),
            '/api/disconnect?session=' . urlencode($session),
            '/logout?session=' . urlencode($session)
        ];
        
        foreach ($endpoints as $endpoint) {
            $test_url = $url . $endpoint;
            
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsAppService/1.0');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                return [
                    'success' => true,
                    'message' => 'Sessão desconectada com sucesso',
                    'endpoint_used' => $endpoint
                ];
            }
        }
        
        return [
            'error' => true,
            'http_code' => $http_code,
            'message' => 'Falha ao desconectar sessão'
        ];
    }
} 