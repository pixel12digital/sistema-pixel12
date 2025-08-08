<?php
namespace App\Controllers;

require_once __DIR__ . '/../../src/Services/WhatsAppService.php';
require_once __DIR__ . '/../../config_whatsapp_multiplo.php';

use Services\WhatsAppService;

/**
 * 🚀 WhatsApp Controller - Gerenciamento de Canais
 * 
 * Controlador para gerenciar os canais WhatsApp 3000 e 3001
 * Utiliza a nova solução do Render.com
 */
class WhatsAppController
{
    /**
     * @var WhatsAppService
     */
    private $whatsappService;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * Página inicial do WhatsApp
     */
    public function index()
    {
        $canais = $this->getCanaisStatus();
        include __DIR__ . '/../Views/whatsapp/index.php';
    }

    /**
     * Obtém o status de todos os canais
     */
    public function getStatus()
    {
        header('Content-Type: application/json');
        
        $canais = $this->getCanaisStatus();
        
        echo json_encode([
            'success' => true,
            'canais' => $canais,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtém QR Code para um canal específico
     */
    public function getQRCode()
    {
        header('Content-Type: application/json');
        
        $porta = $_GET['porta'] ?? 3000;
        $session = $_GET['session'] ?? 'default';
        
        $result = $this->whatsappService->getQRCode($porta, $session);
        
        echo json_encode($result);
    }

    /**
     * Envia mensagem via WhatsApp
     */
    public function sendMessage()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }
        
        $number = $input['number'] ?? '';
        $message = $input['message'] ?? '';
        $porta = $input['porta'] ?? 3000;
        $session = $input['session'] ?? 'default';
        
        if (!$number || !$message) {
            http_response_code(400);
            echo json_encode(['error' => 'Number and message are required']);
            return;
        }
        
        $result = $this->whatsappService->sendText($number, $message, $porta, $session);
        
        echo json_encode($result);
    }

    /**
     * Configura webhook para um canal
     */
    public function configureWebhook()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $porta = $input['porta'] ?? 3000;
        $webhookUrl = $input['webhook_url'] ?? null;
        
        $result = $this->whatsappService->configureWebhook($webhookUrl, $porta);
        
        echo json_encode($result);
    }

    /**
     * Testa conexão com um canal
     */
    public function testConnection()
    {
        header('Content-Type: application/json');
        
        $porta = $_GET['porta'] ?? 3000;
        
        $result = $this->whatsappService->testConnection($porta);
        
        echo json_encode([
            'success' => $result,
            'porta' => $porta,
            'message' => $result ? 'Conexão OK' : 'Conexão falhou'
        ]);
    }

    /**
     * Obtém informações da sessão
     */
    public function getSessionInfo()
    {
        header('Content-Type: application/json');
        
        $porta = $_GET['porta'] ?? 3000;
        $session = $_GET['session'] ?? 'default';
        
        $result = $this->whatsappService->getSessionInfo($porta, $session);
        
        echo json_encode($result);
    }

    /**
     * Desconecta uma sessão
     */
    public function disconnectSession()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $porta = $input['porta'] ?? 3000;
        $session = $input['session'] ?? 'default';
        
        $result = $this->whatsappService->disconnectSession($porta, $session);
        
        echo json_encode($result);
    }

    /**
     * Obtém status de todos os canais
     */
    private function getCanaisStatus(): array
    {
        $canais = [3000, 3001];
        $resultados = [];
        
        foreach ($canais as $porta) {
            $status = $this->whatsappService->getStatus($porta);
            $canal_info = getCanalInfo($porta);
            $connection_test = $this->whatsappService->testConnection($porta);
            
            $resultados[$porta] = [
                'porta' => $porta,
                'nome' => $canal_info['nome'],
                'session' => $canal_info['session'],
                'numero' => $canal_info['numero'],
                'descricao' => $canal_info['descricao'],
                'url' => $canal_info['url'],
                'status' => $connection_test ? 'online' : 'offline',
                'api_status' => $status,
                'ultima_verificacao' => date('Y-m-d H:i:s')
            ];
        }
        
        return $resultados;
    }

    /**
     * Página de dashboard do WhatsApp
     */
    public function dashboard()
    {
        $canais = $this->getCanaisStatus();
        $total_canais = count($canais);
        $canais_online = 0;
        $canais_offline = 0;
        
        foreach ($canais as $canal) {
            if ($canal['status'] === 'online') {
                $canais_online++;
            } else {
                $canais_offline++;
            }
        }
        
        include __DIR__ . '/../Views/whatsapp/dashboard.php';
    }

    /**
     * Página de configuração
     */
    public function config()
    {
        $canais = $this->getCanaisStatus();
        include __DIR__ . '/../Views/whatsapp/config.php';
    }

    /**
     * Página de logs
     */
    public function logs()
    {
        $log_file = __DIR__ . '/../../logs/whatsapp_' . date('Y-m-d') . '.log';
        $logs = [];
        
        if (file_exists($log_file)) {
            $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logs = array_slice($logs, -50); // Últimas 50 linhas
        }
        
        include __DIR__ . '/../Views/whatsapp/logs.php';
    }
} 