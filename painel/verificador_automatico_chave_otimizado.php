<?php
/**
 * Verificador Automático da Chave da API do Asaas - VERSÃO OTIMIZADA
 * Sistema otimizado para reduzir requisições e consumo de recursos
 */

require_once __DIR__ . '/../config.php';
require_once 'db.php';

function getApiKeyFromDb() {
    global $mysqli;
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    return $config ? $config['valor'] : '';
}

class VerificadorAutomaticoChaveOtimizado {
    private $logFile;
    private $statusFile;
    private $cacheFile;
    private $ultimaVerificacao;
    private $configFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/verificador_chave_otimizado.log';
        $this->statusFile = __DIR__ . '/../logs/status_chave_atual.json';
        $this->cacheFile = __DIR__ . '/../logs/cache_chave.json';
        $this->configFile = __DIR__ . '/config.php';
        $this->ultimaVerificacao = $this->carregarUltimaVerificacao();
    }
    
    /**
     * Verifica se a chave mudou no arquivo de configuração
     * Evita requisições desnecessárias se a chave não foi alterada
     */
    private function chaveMudou() {
        $cache = $this->carregarCache();
        $chaveAtual = $this->obterChaveAtualDoArquivo();
        
        if (!$cache || !isset($cache['chave_hash'])) {
            return true; // Primeira execução
        }
        
        $hashAtual = md5($chaveAtual);
        return $cache['chave_hash'] !== $hashAtual;
    }
    
    /**
     * Obtém a chave atual do arquivo de configuração
     */
    private function obterChaveAtualDoArquivo() {
        $conteudo = file_get_contents($this->configFile);
        if (preg_match("/define\('ASAAS_API_KEY',\s*'([^']*)'\);/", $conteudo, $matches)) {
            return $matches[1];
        }
        return getApiKeyFromDb(); // Fallback
    }
    
    /**
     * Carrega cache de verificações anteriores
     */
    private function carregarCache() {
        if (file_exists($this->cacheFile)) {
            $conteudo = file_get_contents($this->cacheFile);
            return json_decode($conteudo, true);
        }
        return null;
    }
    
    /**
     * Salva cache com informações da verificação
     */
    private function salvarCache($status) {
        $chaveAtual = $this->obterChaveAtualDoArquivo();
        $cache = [
            'chave_hash' => md5($chaveAtual),
            'ultima_verificacao' => date('Y-m-d H:i:s'),
            'status' => $status,
            'proxima_verificacao' => date('Y-m-d H:i:s', time() + 3600) // 1 hora
        ];
        
        file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    }
    
    /**
     * Verifica se deve fazer nova verificação baseado em múltiplos critérios
     */
    public function deveVerificar() {
        // 1. Verificar se a chave mudou no arquivo
        if ($this->chaveMudou()) {
            $this->log("Chave alterada no arquivo - verificação necessária");
            return true;
        }
        
        // 2. Verificar cache de verificação anterior
        $cache = $this->carregarCache();
        if ($cache && isset($cache['status']['valida'])) {
            $ultimaVerificacao = strtotime($cache['ultima_verificacao']);
            $agora = time();
            
            // Se a chave estava válida, verificar a cada 2 horas
            if ($cache['status']['valida']) {
                $intervalo = 7200; // 2 horas
            } else {
                // Se estava inválida, verificar a cada 30 minutos
                $intervalo = 1800; // 30 minutos
            }
            
            if (($agora - $ultimaVerificacao) < $intervalo) {
                return false; // Ainda não é hora de verificar
            }
        }
        
        // 3. Verificar se há alertas ativos
        $alertas = $this->obterAlertas();
        if ($alertas) {
            $this->log("Alerta ativo - verificação necessária");
            return true;
        }
        
        return true; // Verificação necessária
    }
    
    public function verificarChave() {
        $inicio = microtime(true);
        
        // Verificar se deve fazer a verificação
        if (!$this->deveVerificar()) {
            $cache = $this->carregarCache();
            if ($cache) {
                $this->log("Verificação pulada - usando cache");
                return $cache['status'];
            }
        }
        
        try {
            // Testar a chave atual
            $resultado = $this->testarChave(getApiKeyFromDb());
            
            $status = [
                'timestamp' => date('Y-m-d H:i:s'),
                'valida' => $resultado['success'],
                'http_code' => $resultado['http_code'],
                'response_time' => round((microtime(true) - $inicio) * 1000, 2),
                'chave_mascarada' => substr(getApiKeyFromDb(), 0, 20) . '...',
                'tipo_chave' => strpos(getApiKeyFromDb(), '_test_') !== false ? 'TESTE' : 'PRODUÇÃO',
                'verificacao_real' => true
            ];
            
            // Salvar status e cache
            $this->salvarStatus($status);
            $this->salvarCache($status);
            
            // Log da verificação
            $this->log("Verificação automática: " . ($status['valida'] ? 'SUCESSO' : 'FALHA') . 
                      " | HTTP: {$status['http_code']} | Tempo: {$status['response_time']}ms");
            
            // Se a chave está inválida, criar alerta
            if (!$status['valida']) {
                $this->criarAlerta($status);
            } else {
                // Se está válida, limpar alertas antigos
                $this->limparAlertas();
            }
            
            return $status;
            
        } catch (Exception $e) {
            $this->log("ERRO na verificação: " . $e->getMessage());
            return [
                'timestamp' => date('Y-m-d H:i:s'),
                'valida' => false,
                'error' => $e->getMessage(),
                'verificacao_real' => false
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
        // Primeiro tentar usar cache
        $cache = $this->carregarCache();
        if ($cache && isset($cache['status'])) {
            $status = $cache['status'];
            $status['cache'] = true;
            return $status;
        }
        
        // Se não há cache, verificar arquivo de status
        if (file_exists($this->statusFile)) {
            $conteudo = file_get_contents($this->statusFile);
            $status = json_decode($conteudo, true);
            if ($status) {
                $status['cache'] = false;
                return $status;
            }
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
            $this->log("Alertas limpos automaticamente");
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
    
    public function obterEstatisticas() {
        $cache = $this->carregarCache();
        $alertas = $this->obterAlertas();
        
        return [
            'ultima_verificacao' => $cache ? $cache['ultima_verificacao'] : 'Nunca',
            'proxima_verificacao' => $cache ? $cache['proxima_verificacao'] : 'Desconhecida',
            'tem_alertas' => $alertas !== null,
            'chave_mudou' => $this->chaveMudou(),
            'deve_verificar' => $this->deveVerificar()
        ];
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
}

// Execução via CLI (para cron job)
if (php_sapi_name() === 'cli') {
    $verificador = new VerificadorAutomaticoChaveOtimizado();
    
    if ($verificador->deveVerificar()) {
        $resultado = $verificador->verificarChave();
        
        if ($resultado['valida']) {
            echo "✅ Chave válida - " . $resultado['timestamp'] . "\n";
        } else {
            echo "❌ Chave inválida - " . $resultado['timestamp'] . "\n";
            echo "HTTP Code: " . $resultado['http_code'] . "\n";
        }
    } else {
        echo "⏰ Verificação não necessária - usando cache\n";
        $estatisticas = $verificador->obterEstatisticas();
        echo "Próxima verificação: " . $estatisticas['proxima_verificacao'] . "\n";
    }
}

// Para uso via web
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $verificador = new VerificadorAutomaticoChaveOtimizado();
    
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
            
        case 'estatisticas':
            $estatisticas = $verificador->obterEstatisticas();
            echo json_encode($estatisticas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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