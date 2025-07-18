<?php
/**
 * Verificador Automático da Chave da API do Asaas
 * Executa verificações periódicas e notifica sobre problemas
 */

require_once 'config.php';

class VerificadorAutomaticoChave {
    private $logFile;
    private $statusFile;
    private $ultimaVerificacao;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/verificador_chave_automatico.log';
        $this->statusFile = __DIR__ . '/../logs/status_chave_atual.json';
        $this->ultimaVerificacao = $this->carregarUltimaVerificacao();
    }
    
    public function verificarChave() {
        $inicio = microtime(true);
        
        try {
            // Testar a chave atual
            $resultado = $this->testarChave(ASAAS_API_KEY);
            
            $status = [
                'timestamp' => date('Y-m-d H:i:s'),
                'valida' => $resultado['success'],
                'http_code' => $resultado['http_code'],
                'response_time' => round((microtime(true) - $inicio) * 1000, 2),
                'chave_mascarada' => substr(ASAAS_API_KEY, 0, 20) . '...',
                'tipo_chave' => strpos(ASAAS_API_KEY, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO'
            ];
            
            // Salvar status atual
            $this->salvarStatus($status);
            
            // Log da verificação
            $this->log("Verificação automática: " . ($status['valida'] ? 'SUCESSO' : 'FALHA') . 
                      " | HTTP: {$status['http_code']} | Tempo: {$status['response_time']}ms");
            
            // Se a chave está inválida, criar alerta
            if (!$status['valida']) {
                $this->criarAlerta($status);
            }
            
            return $status;
            
        } catch (Exception $e) {
            $this->log("ERRO na verificação: " . $e->getMessage());
            return [
                'timestamp' => date('Y-m-d H:i:s'),
                'valida' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function testarChave($chave) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.asaas.com/api/v3/customers?limit=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $chave
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("Erro de conexão: $curlError");
        }
        
        return [
            'success' => $httpCode == 200,
            'http_code' => $httpCode,
            'response' => $result
        ];
    }
    
    private function criarAlerta($status) {
        $alertaFile = __DIR__ . '/../logs/alerta_chave_invalida.json';
        
        $alerta = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tipo' => 'CHAVE_INVALIDA',
            'severidade' => 'ALTA',
            'mensagem' => 'Chave da API do Asaas está inválida',
            'detalhes' => $status,
            'acoes_sugeridas' => [
                '1. Acessar painel do Asaas',
                '2. Verificar status da chave atual',
                '3. Gerar nova chave se necessário',
                '4. Atualizar no sistema via interface'
            ]
        ];
        
        file_put_contents($alertaFile, json_encode($alerta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Log do alerta
        $this->log("ALERTA CRIADO: Chave da API inválida - HTTP {$status['http_code']}");
    }
    
    public function obterStatusAtual() {
        if (file_exists($this->statusFile)) {
            $conteudo = file_get_contents($this->statusFile);
            return json_decode($conteudo, true);
        }
        
        return null;
    }
    
    public function obterAlertas() {
        $alertaFile = __DIR__ . '/../logs/alerta_chave_invalida.json';
        
        if (file_exists($alertaFile)) {
            $conteudo = file_get_contents($alertaFile);
            return json_decode($conteudo, true);
        }
        
        return null;
    }
    
    public function limparAlertas() {
        $alertaFile = __DIR__ . '/../logs/alerta_chave_invalida.json';
        
        if (file_exists($alertaFile)) {
            unlink($alertaFile);
            $this->log("Alertas limpos manualmente");
        }
    }
    
    public function obterHistorico($limite = 50) {
        $historico = [];
        $linhas = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($linhas) {
            $linhas = array_slice($linhas, -$limite);
            foreach ($linhas as $linha) {
                $historico[] = $linha;
            }
        }
        
        return $historico;
    }
    
    private function salvarStatus($status) {
        file_put_contents($this->statusFile, json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private function carregarUltimaVerificacao() {
        $status = $this->obterStatusAtual();
        return $status ? $status['timestamp'] : null;
    }
    
    private function log($mensagem) {
        $data = date('Y-m-d H:i:s') . ' - ' . $mensagem . "\n";
        file_put_contents($this->logFile, $data, FILE_APPEND);
    }
    
    public function deveVerificar() {
        // Verificar a cada 30 minutos
        if (!$this->ultimaVerificacao) {
            return true;
        }
        
        $ultima = strtotime($this->ultimaVerificacao);
        $agora = time();
        
        return ($agora - $ultima) >= 1800; // 30 minutos
    }
}

// Execução via CLI (para cron job)
if (php_sapi_name() === 'cli') {
    $verificador = new VerificadorAutomaticoChave();
    
    if ($verificador->deveVerificar()) {
        $resultado = $verificador->verificarChave();
        
        if ($resultado['valida']) {
            echo "✅ Chave válida - " . $resultado['timestamp'] . "\n";
        } else {
            echo "❌ Chave inválida - " . $resultado['timestamp'] . "\n";
            echo "HTTP Code: " . $resultado['http_code'] . "\n";
        }
    } else {
        echo "⏰ Verificação não necessária ainda\n";
    }
}

// Para uso via web
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $verificador = new VerificadorAutomaticoChave();
    
    switch ($_GET['action']) {
        case 'verificar':
            $resultado = $verificador->verificarChave();
            echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'status':
            $status = $verificador->obterStatusAtual();
            echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'alertas':
            $alertas = $verificador->obterAlertas();
            echo json_encode($alertas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'historico':
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
            $historico = $verificador->obterHistorico($limite);
            echo json_encode($historico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'limpar_alertas':
            $verificador->limparAlertas();
            echo json_encode(['success' => true, 'message' => 'Alertas limpos']);
            break;
            
        default:
            echo json_encode(['error' => 'Ação não reconhecida']);
    }
}
?> 